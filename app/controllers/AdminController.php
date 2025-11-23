// FILE: /app/controllers/AdminController.php
<?php

/**
 * AdminController
 *
 * Handles platform admin functions
 */
class AdminController extends Controller
{
    /**
     * Admin dashboard
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin']);

        $user = $this->getAuthUser();

        $tenantModel = $this->model('Tenant');
        $userModel = $this->model('User');
        $projectModel = $this->model('Project');
        $renderJobModel = $this->model('RenderJob');

        // Get platform statistics
        $stats = [
            'total_tenants' => $this->db->query("SELECT COUNT(*) as total FROM tenants")->fetch()['total'],
            'total_users' => $this->db->query("SELECT COUNT(*) as total FROM users")->fetch()['total'],
            'total_projects' => $this->db->query("SELECT COUNT(*) as total FROM projects")->fetch()['total'],
            'total_renders' => $this->db->query("SELECT COUNT(*) as total FROM render_jobs")->fetch()['total'],
        ];

        $this->view->render('admin/index', [
            'user' => $user,
            'stats' => $stats,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * List all tenants
     */
    public function tenants()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin']);

        $user = $this->getAuthUser();

        $tenantModel = $this->model('Tenant');
        $tenants = $tenantModel->getAllActive();

        $this->view->render('admin/tenants', [
            'user' => $user,
            'tenants' => $tenants,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * List all plans
     */
    public function plans()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin']);

        $user = $this->getAuthUser();

        $planModel = $this->model('Plan');
        $plans = $planModel->getAllActive();

        $this->view->render('admin/plans', [
            'user' => $user,
            'plans' => $plans,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show create plan form
     */
    public function createPlan()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin']);

        $user = $this->getAuthUser();

        $this->view->render('admin/create-plan', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Store new plan
     */
    public function storePlan()
    {
        $this->requireAuth();
        $this->requireRole(['platform_admin']);
        $this->validateCsrfToken();

        $name = trim($_POST['name'] ?? '');
        $slug = strtolower(trim($_POST['slug'] ?? ''));
        $description = trim($_POST['description'] ?? '');
        $priceMonthly = (float)($_POST['price_monthly'] ?? 0);
        $priceYearly = (float)($_POST['price_yearly'] ?? 0);
        $maxProjects = (int)($_POST['max_projects'] ?? -1);
        $maxRendersPerMonth = (int)($_POST['max_renders_per_month'] ?? -1);
        $maxRenderMinutesPerMonth = (int)($_POST['max_render_minutes_per_month'] ?? -1);
        $maxStorageMb = (int)($_POST['max_storage_mb'] ?? -1);
        $maxTeamMembers = (int)($_POST['max_team_members'] ?? -1);

        // Validation
        if (empty($name) || empty($slug)) {
            setFlash('error', 'Name and slug are required');
            $this->redirect('/admin/plans/create');
        }

        $planModel = $this->model('Plan');

        $planModel->insert([
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'price_monthly' => $priceMonthly,
            'price_yearly' => $priceYearly,
            'max_projects' => $maxProjects,
            'max_renders_per_month' => $maxRendersPerMonth,
            'max_render_minutes_per_month' => $maxRenderMinutesPerMonth,
            'max_storage_mb' => $maxStorageMb,
            'max_team_members' => $maxTeamMembers,
            'is_active' => 1,
        ]);

        setFlash('success', 'Plan created successfully!');
        $this->redirect('/admin/plans');
    }
}
