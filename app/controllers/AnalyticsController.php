// FILE: /app/controllers/AnalyticsController.php
<?php

/**
 * AnalyticsController
 *
 * Handles analytics and reporting
 */
class AnalyticsController extends Controller
{
    /**
     * Show analytics dashboard
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $analyticsModel = $this->model('Analytics');
        $projectModel = $this->model('Project');
        $renderJobModel = $this->model('RenderJob');

        // Get date range (last 30 days by default)
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');

        // Get event counts
        $projectsCreated = $analyticsModel->getEventCount($tenantId, 'project_created', $startDate, $endDate);
        $rendersCompleted = $analyticsModel->getEventCount($tenantId, 'render_completed', $startDate, $endDate);
        $videosDownloaded = $analyticsModel->getEventCount($tenantId, 'video_downloaded', $startDate, $endDate);

        // Get popular events
        $popularEvents = $analyticsModel->getPopularEvents($tenantId, 10);

        // Get project type distribution
        $projectTypes = $this->db->prepare(
            "SELECT type, COUNT(*) as count FROM projects
             WHERE tenant_id = ? AND created_at BETWEEN ? AND ?
             GROUP BY type"
        );
        $projectTypes->execute([$tenantId, $startDate, $endDate]);
        $projectTypeData = $projectTypes->fetchAll();

        // Get render status distribution
        $renderStatuses = $this->db->prepare(
            "SELECT status, COUNT(*) as count FROM render_jobs
             WHERE tenant_id = ? AND created_at BETWEEN ? AND ?
             GROUP BY status"
        );
        $renderStatuses->execute([$tenantId, $startDate, $endDate]);
        $renderStatusData = $renderStatuses->fetchAll();

        $this->view->render('analytics/index', [
            'user' => $user,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'projectsCreated' => $projectsCreated,
            'rendersCompleted' => $rendersCompleted,
            'videosDownloaded' => $videosDownloaded,
            'popularEvents' => $popularEvents,
            'projectTypeData' => $projectTypeData,
            'renderStatusData' => $renderStatusData,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show detailed reports
     */
    public function reports()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $usageModel = $this->model('UsageTracking');
        $usageHistory = $usageModel->getHistory($tenantId, 12);

        $this->view->render('analytics/reports', [
            'user' => $user,
            'usageHistory' => $usageHistory,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }
}
