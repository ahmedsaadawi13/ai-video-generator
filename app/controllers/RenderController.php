// FILE: /app/controllers/RenderController.php
<?php

require_once __DIR__ . '/../services/AiVideoService.php';

/**
 * RenderController
 *
 * Handles video rendering jobs
 */
class RenderController extends Controller
{
    /**
     * List all render jobs
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $renderJobModel = $this->model('RenderJob');

        // Get filters
        $filters = [
            'status' => $_GET['status'] ?? '',
        ];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $renderJobs = $renderJobModel->findByTenantWithPagination($tenantId, $page, $perPage, $filters);
        $totalJobs = $renderJobModel->countByTenant($tenantId, $filters);
        $totalPages = totalPages($totalJobs, $perPage);

        $this->view->render('renders/index', [
            'user' => $user,
            'renderJobs' => $renderJobs,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalJobs' => $totalJobs,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show create render job form
     */
    public function create()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $tenantModel = $this->model('Tenant');

        // Check if can render more videos
        if (!$tenantModel->canRenderVideo($tenantId)) {
            setFlash('error', 'You have reached your monthly render limit. Please upgrade your plan.');
            $this->redirect('/subscription/plans');
        }

        $projectModel = $this->model('Project');
        $projects = $projectModel->findByTenant($tenantId);

        $this->view->render('renders/create', [
            'user' => $user,
            'projects' => $projects,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Create new render job
     */
    public function store()
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();
        $userId = $_SESSION['user_id'];

        $tenantModel = $this->model('Tenant');

        // Check if can render more videos
        if (!$tenantModel->canRenderVideo($tenantId)) {
            setFlash('error', 'You have reached your monthly render limit. Please upgrade your plan.');
            $this->redirect('/subscription/plans');
        }

        $projectId = (int)($_POST['project_id'] ?? 0);

        // Verify project belongs to tenant
        $projectModel = $this->model('Project');
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$projectId, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/renders/create');
        }

        // Create render job
        $renderJobModel = $this->model('RenderJob');

        $parameters = [
            'project_id' => $projectId,
            'aspect_ratio' => $project['aspect_ratio'],
            'resolution' => $project['resolution'],
            'duration' => 30, // Default, calculate from scenes
        ];

        $jobId = $renderJobModel->insert([
            'tenant_id' => $tenantId,
            'project_id' => $projectId,
            'user_id' => $userId,
            'job_type' => $project['type'],
            'status' => 'queued',
            'progress' => 0,
            'parameters' => json_encode($parameters),
        ]);

        // Initialize AI service (mock)
        $aiService = new AiVideoService();

        // Queue job with AI service (simulated)
        // In production, this would make an actual API call

        setFlash('success', 'Render job created and queued successfully!');
        $this->redirect('/renders/' . $jobId);
    }

    /**
     * Show render job details
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $renderJobModel = $this->model('RenderJob');

        $renderJob = $this->db->prepare(
            "SELECT rj.*, p.name as project_name, u.first_name, u.last_name
             FROM render_jobs rj
             JOIN projects p ON rj.project_id = p.id
             JOIN users u ON rj.user_id = u.id
             WHERE rj.id = ? AND rj.tenant_id = ?"
        );
        $renderJob->execute([$id, $tenantId]);
        $renderJob = $renderJob->fetch();

        if (!$renderJob) {
            setFlash('error', 'Render job not found');
            $this->redirect('/renders');
        }

        // Get associated video if completed
        $video = null;
        if ($renderJob['status'] === 'completed') {
            $videoStmt = $this->db->prepare("SELECT * FROM videos WHERE render_job_id = ?");
            $videoStmt->execute([$id]);
            $video = $videoStmt->fetch();
        }

        $this->view->render('renders/show', [
            'user' => $user,
            'renderJob' => $renderJob,
            'video' => $video,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Retry failed render job
     */
    public function retry($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $renderJobModel = $this->model('RenderJob');

        // Verify render job belongs to tenant
        $renderJob = $this->db->prepare("SELECT * FROM render_jobs WHERE id = ? AND tenant_id = ?");
        $renderJob->execute([$id, $tenantId]);
        $renderJob = $renderJob->fetch();

        if (!$renderJob) {
            setFlash('error', 'Render job not found');
            $this->redirect('/renders');
        }

        if ($renderJob['status'] !== 'failed') {
            setFlash('error', 'Only failed jobs can be retried');
            $this->redirect('/renders/' . $id);
        }

        // Reset job to queued
        $renderJobModel->updateStatus($id, 'queued', 0, null);

        setFlash('success', 'Render job queued for retry');
        $this->redirect('/renders/' . $id);
    }
}
