// FILE: /app/controllers/VideoController.php
<?php

/**
 * VideoController
 *
 * Handles completed videos
 */
class VideoController extends Controller
{
    /**
     * List all videos
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $videoModel = $this->model('Video');

        // Get filters
        $filters = [
            'search' => $_GET['search'] ?? '',
        ];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $videos = $videoModel->findByTenantWithPagination($tenantId, $page, $perPage, $filters);
        $totalVideos = $videoModel->countByTenant($tenantId, $filters);
        $totalPages = totalPages($totalVideos, $perPage);

        $this->view->render('videos/index', [
            'user' => $user,
            'videos' => $videos,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalVideos' => $totalVideos,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show video details
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $videoModel = $this->model('Video');

        $video = $this->db->prepare(
            "SELECT v.*, p.name as project_name
             FROM videos v
             JOIN projects p ON v.project_id = p.id
             WHERE v.id = ? AND v.tenant_id = ?"
        );
        $video->execute([$id, $tenantId]);
        $video = $video->fetch();

        if (!$video) {
            setFlash('error', 'Video not found');
            $this->redirect('/videos');
        }

        // Increment view count
        $videoModel->incrementViews($id);

        $this->view->render('videos/show', [
            'user' => $user,
            'video' => $video,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Download video
     */
    public function download($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();

        $videoModel = $this->model('Video');

        $video = $this->db->prepare("SELECT * FROM videos WHERE id = ? AND tenant_id = ?");
        $video->execute([$id, $tenantId]);
        $video = $video->fetch();

        if (!$video) {
            setFlash('error', 'Video not found');
            $this->redirect('/videos');
        }

        // Increment download count
        $videoModel->incrementDownloads($id);

        // Track analytics
        $analyticsModel = $this->model('Analytics');
        $analyticsModel->track($tenantId, $_SESSION['user_id'], 'video_downloaded', [
            'video_id' => $id,
        ]);

        // In production, this would serve the actual file
        // For now, redirect with success message
        setFlash('success', 'Video download started: ' . $video['filename']);
        $this->redirect('/videos/' . $id);
    }

    /**
     * Duplicate video as new project
     */
    public function duplicate($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();
        $userId = $_SESSION['user_id'];

        $videoModel = $this->model('Video');
        $projectModel = $this->model('Project');

        $video = $this->db->prepare("SELECT * FROM videos WHERE id = ? AND tenant_id = ?");
        $video->execute([$id, $tenantId]);
        $video = $video->fetch();

        if (!$video) {
            setFlash('error', 'Video not found');
            $this->redirect('/videos');
        }

        // Duplicate the project
        $newProjectId = $projectModel->duplicate($video['project_id'], $tenantId, $userId);

        if ($newProjectId) {
            setFlash('success', 'Project duplicated successfully!');
            $this->redirect('/projects/' . $newProjectId);
        } else {
            setFlash('error', 'Failed to duplicate project');
            $this->redirect('/videos/' . $id);
        }
    }
}
