// FILE: /app/controllers/ApiController.php
<?php

require_once __DIR__ . '/../services/AiVideoService.php';

/**
 * ApiController
 *
 * Handles public REST API endpoints for video generation
 */
class ApiController extends Controller
{
    private $apiKey = null;
    private $apiTenantId = null;

    /**
     * Authenticate API request
     */
    private function authenticateApi()
    {
        // Get API key from header
        $headers = getallheaders();
        $apiKeyHeader = $headers['X-API-KEY'] ?? $headers['X-Api-Key'] ?? '';

        if (empty($apiKeyHeader)) {
            $this->json(['error' => 'Missing API key'], 401);
        }

        $apiKeyModel = $this->model('ApiKey');
        $keyData = $apiKeyModel->verify($apiKeyHeader);

        if (!$keyData) {
            $this->json(['error' => 'Invalid or expired API key'], 401);
        }

        $this->apiKey = $keyData;
        $this->apiTenantId = $keyData['tenant_id'];

        return true;
    }

    /**
     * Create a new render job via API
     *
     * POST /api/v1/render
     *
     * Request body (JSON):
     * {
     *   "project_id": 123,
     *   "type": "text_to_video|image_to_video|video_to_video|images_to_video",
     *   "parameters": {
     *     "script": "text content",
     *     "image_id": 456,
     *     "video_id": 789,
     *     "image_ids": [1, 2, 3],
     *     "style": "cinematic",
     *     "duration": 30,
     *     "aspect_ratio": "16:9",
     *     "resolution": "1080p"
     *   }
     * }
     */
    public function createRenderJob()
    {
        $this->authenticateApi();

        // Only accept POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);

        if (!$input) {
            $this->json(['error' => 'Invalid JSON'], 400);
        }

        $projectId = isset($input['project_id']) ? (int)$input['project_id'] : 0;
        $type = $input['type'] ?? '';
        $parameters = $input['parameters'] ?? [];

        // Validate input
        if (empty($projectId)) {
            $this->json(['error' => 'project_id is required'], 400);
        }

        $validTypes = ['text_to_video', 'image_to_video', 'video_to_video', 'images_to_video'];
        if (!in_array($type, $validTypes)) {
            $this->json(['error' => 'Invalid type. Must be one of: ' . implode(', ', $validTypes)], 400);
        }

        // Verify project belongs to tenant
        $projectModel = $this->model('Project');
        $stmt = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$projectId, $this->apiTenantId]);
        $project = $stmt->fetch();

        if (!$project) {
            $this->json(['error' => 'Project not found or access denied'], 404);
        }

        // Check quota
        $tenantModel = $this->model('Tenant');
        if (!$tenantModel->canRenderVideo($this->apiTenantId)) {
            $this->json(['error' => 'Monthly render limit exceeded. Please upgrade your plan.'], 429);
        }

        // Validate type-specific parameters
        $errors = $this->validateRenderParameters($type, $parameters);
        if (!empty($errors)) {
            $this->json(['error' => 'Validation errors', 'details' => $errors], 400);
        }

        // Set defaults
        $parameters['aspect_ratio'] = $parameters['aspect_ratio'] ?? $project['aspect_ratio'] ?? '16:9';
        $parameters['resolution'] = $parameters['resolution'] ?? $project['resolution'] ?? '1080p';
        $parameters['duration'] = isset($parameters['duration']) ? (int)$parameters['duration'] : 30;

        // Get user ID from API key or use first admin
        $userStmt = $this->db->prepare("SELECT id FROM users WHERE tenant_id = ? AND role = 'tenant_admin' LIMIT 1");
        $userStmt->execute([$this->apiTenantId]);
        $userResult = $userStmt->fetch();
        $userId = $userResult['id'];

        // Create render job
        $renderJobModel = $this->model('RenderJob');

        $jobId = $renderJobModel->insert([
            'tenant_id' => $this->apiTenantId,
            'project_id' => $projectId,
            'user_id' => $userId,
            'job_type' => $type,
            'status' => 'queued',
            'progress' => 0,
            'parameters' => json_encode($parameters),
        ]);

        // Track analytics
        $analyticsModel = $this->model('Analytics');
        $analyticsModel->track($this->apiTenantId, $userId, 'api_render_created', [
            'render_job_id' => $jobId,
            'type' => $type,
            'api_key_id' => $this->apiKey['id'],
        ]);

        // Return response
        $this->json([
            'success' => true,
            'data' => [
                'job_id' => $jobId,
                'status' => 'queued',
                'type' => $type,
                'project_id' => $projectId,
                'parameters' => $parameters,
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ], 201);
    }

    /**
     * Get render job status and details
     *
     * GET /api/v1/render/{job_id}
     */
    public function getRenderJob($id)
    {
        $this->authenticateApi();

        // Only accept GET
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $renderJobModel = $this->model('RenderJob');

        // Verify render job belongs to tenant
        $stmt = $this->db->prepare("SELECT * FROM render_jobs WHERE id = ? AND tenant_id = ?");
        $stmt->execute([$id, $this->apiTenantId]);
        $renderJob = $stmt->fetch();

        if (!$renderJob) {
            $this->json(['error' => 'Render job not found'], 404);
        }

        $response = [
            'success' => true,
            'data' => [
                'job_id' => $renderJob['id'],
                'project_id' => $renderJob['project_id'],
                'status' => $renderJob['status'],
                'progress' => $renderJob['progress'],
                'type' => $renderJob['job_type'],
                'parameters' => json_decode($renderJob['parameters'], true),
                'error_message' => $renderJob['error_message'],
                'created_at' => $renderJob['created_at'],
                'started_at' => $renderJob['started_at'],
                'completed_at' => $renderJob['completed_at'],
            ],
        ];

        // If completed, include video details
        if ($renderJob['status'] === 'completed') {
            $videoStmt = $this->db->prepare("SELECT * FROM videos WHERE render_job_id = ?");
            $videoStmt->execute([$id]);
            $video = $videoStmt->fetch();

            if ($video) {
                $response['data']['video'] = [
                    'id' => $video['id'],
                    'title' => $video['title'],
                    'filename' => $video['filename'],
                    'file_path' => $video['file_path'],
                    'file_size' => $video['file_size'],
                    'duration' => $video['duration'],
                    'resolution' => $video['resolution'],
                    'thumbnail_url' => $video['thumbnail_url'],
                    'public_token' => $video['public_token'],
                ];
            }
        }

        $this->json($response, 200);
    }

    /**
     * Validate render parameters based on type
     */
    private function validateRenderParameters($type, $parameters)
    {
        $errors = [];

        switch ($type) {
            case 'text_to_video':
                if (empty($parameters['script'])) {
                    $errors[] = 'script is required for text_to_video';
                }
                break;

            case 'image_to_video':
                if (empty($parameters['image_id'])) {
                    $errors[] = 'image_id is required for image_to_video';
                }
                break;

            case 'video_to_video':
                if (empty($parameters['video_id'])) {
                    $errors[] = 'video_id is required for video_to_video';
                }
                break;

            case 'images_to_video':
                if (empty($parameters['image_ids']) || !is_array($parameters['image_ids'])) {
                    $errors[] = 'image_ids array is required for images_to_video';
                }
                if (isset($parameters['image_ids']) && count($parameters['image_ids']) < 2) {
                    $errors[] = 'At least 2 images are required for images_to_video';
                }
                break;
        }

        return $errors;
    }
}
