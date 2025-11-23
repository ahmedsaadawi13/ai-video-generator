// FILE: /app/controllers/ProjectController.php
<?php

/**
 * ProjectController
 *
 * Handles video projects (CRUD operations)
 */
class ProjectController extends Controller
{
    /**
     * List all projects
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $projectModel = $this->model('Project');

        // Get filters
        $filters = [
            'type' => $_GET['type'] ?? '',
            'status' => $_GET['status'] ?? '',
        ];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $projects = $projectModel->findByTenantWithPagination($tenantId, $page, $perPage, $filters);
        $totalProjects = $projectModel->countByTenant($tenantId, $filters);
        $totalPages = totalPages($totalProjects, $perPage);

        $this->view->render('projects/index', [
            'user' => $user,
            'projects' => $projects,
            'filters' => $filters,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalProjects' => $totalProjects,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show create project form
     */
    public function create()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $tenantModel = $this->model('Tenant');

        // Check if can create more projects
        if (!$tenantModel->canCreateProject($tenantId)) {
            setFlash('error', 'You have reached your project limit. Please upgrade your plan.');
            $this->redirect('/subscription/plans');
        }

        $this->view->render('projects/create', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Store new project
     */
    public function store()
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();
        $userId = $_SESSION['user_id'];

        $tenantModel = $this->model('Tenant');

        // Check if can create more projects
        if (!$tenantModel->canCreateProject($tenantId)) {
            setFlash('error', 'You have reached your project limit. Please upgrade your plan.');
            $this->redirect('/subscription/plans');
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? 'text_to_video';
        $platformPreset = $_POST['platform_preset'] ?? 'youtube_horizontal';
        $aspectRatio = $_POST['aspect_ratio'] ?? '16:9';
        $resolution = $_POST['resolution'] ?? '1080p';

        // Validation
        if (empty($name)) {
            setFlash('error', 'Project name is required');
            $this->redirect('/projects/create');
        }

        $projectModel = $this->model('Project');

        $projectId = $projectModel->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'type' => $type,
            'platform_preset' => $platformPreset,
            'aspect_ratio' => $aspectRatio,
            'resolution' => $resolution,
            'status' => 'draft',
        ]);

        // Track usage
        $usageModel = $this->model('UsageTracking');
        $usageModel->incrementProjects($tenantId);

        // Track analytics
        $analyticsModel = $this->model('Analytics');
        $analyticsModel->track($tenantId, $userId, 'project_created', [
            'project_id' => $projectId,
            'project_type' => $type,
        ]);

        setFlash('success', 'Project created successfully!');
        $this->redirect('/projects/' . $projectId);
    }

    /**
     * Show project details
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $projectModel = $this->model('Project');
        $sceneModel = $this->model('Scene');

        $project = $projectModel->findByIdWithScenes($id, $tenantId);

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $this->view->render('projects/show', [
            'user' => $user,
            'project' => $project,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show edit project form
     */
    public function edit($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $projectModel = $this->model('Project');
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$id, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $this->view->render('projects/edit', [
            'user' => $user,
            'project' => $project,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Update project
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $projectModel = $this->model('Project');

        // Verify project belongs to tenant
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$id, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $platformPreset = $_POST['platform_preset'] ?? $project['platform_preset'];
        $aspectRatio = $_POST['aspect_ratio'] ?? $project['aspect_ratio'];
        $resolution = $_POST['resolution'] ?? $project['resolution'];

        // Validation
        if (empty($name)) {
            setFlash('error', 'Project name is required');
            $this->redirect('/projects/' . $id . '/edit');
        }

        $projectModel->update($id, [
            'name' => $name,
            'description' => $description,
            'platform_preset' => $platformPreset,
            'aspect_ratio' => $aspectRatio,
            'resolution' => $resolution,
        ]);

        setFlash('success', 'Project updated successfully!');
        $this->redirect('/projects/' . $id);
    }

    /**
     * Delete project
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $projectModel = $this->model('Project');

        // Verify project belongs to tenant
        $project = $this->db->prepare("SELECT * FROM projects WHERE id = ? AND tenant_id = ?");
        $project->execute([$id, $tenantId]);
        $project = $project->fetch();

        if (!$project) {
            setFlash('error', 'Project not found');
            $this->redirect('/projects');
        }

        $projectModel->delete($id);

        setFlash('success', 'Project deleted successfully');
        $this->redirect('/projects');
    }
}
