// FILE: /app/controllers/DashboardController.php
<?php

/**
 * DashboardController
 *
 * Handles the main dashboard
 */
class DashboardController extends Controller
{
    /**
     * Show dashboard
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $tenantModel = $this->model('Tenant');
        $projectModel = $this->model('Project');
        $renderJobModel = $this->model('RenderJob');
        $videoModel = $this->model('Video');
        $assetModel = $this->model('Asset');

        // Get subscription and usage
        $subscription = $tenantModel->getActiveSubscription($tenantId);
        $usage = $tenantModel->getCurrentUsage($tenantId);

        // Get statistics
        $stats = [
            'total_projects' => $projectModel->countByTenant($tenantId),
            'total_renders' => $renderJobModel->countByTenant($tenantId),
            'total_videos' => $videoModel->countByTenant($tenantId),
            'total_assets' => $assetModel->countByTenant($tenantId),
            'storage_used_mb' => $assetModel->getTotalStorageByTenant($tenantId) / (1024 * 1024),
        ];

        // Get recent items
        $recentProjects = $projectModel->getRecent($tenantId, 5);
        $recentRenders = $renderJobModel->getRecent($tenantId, 5);
        $recentVideos = $videoModel->getRecent($tenantId, 5);

        // Calculate usage percentages
        $usagePercent = [];
        if ($subscription) {
            if ($subscription['max_projects'] > 0) {
                $usagePercent['projects'] = calculatePercentage($usage['projects_count'], $subscription['max_projects']);
            }
            if ($subscription['max_renders_per_month'] > 0) {
                $usagePercent['renders'] = calculatePercentage($usage['renders_count'], $subscription['max_renders_per_month']);
            }
            if ($subscription['max_storage_mb'] > 0) {
                $usagePercent['storage'] = calculatePercentage($usage['storage_mb'], $subscription['max_storage_mb']);
            }
        }

        $this->view->render('dashboard/index', [
            'user' => $user,
            'subscription' => $subscription,
            'usage' => $usage,
            'usagePercent' => $usagePercent,
            'stats' => $stats,
            'recentProjects' => $recentProjects,
            'recentRenders' => $recentRenders,
            'recentVideos' => $recentVideos,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }
}
