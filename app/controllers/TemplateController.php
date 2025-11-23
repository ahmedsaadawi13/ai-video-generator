// FILE: /app/controllers/TemplateController.php
<?php

/**
 * TemplateController
 *
 * Handles video templates
 */
class TemplateController extends Controller
{
    /**
     * List all templates
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $templateModel = $this->model('Template');

        // Get filters
        $filters = [
            'category' => $_GET['category'] ?? '',
        ];

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $templates = $templateModel->findWithPagination($tenantId, $page, $perPage, $filters);

        $this->view->render('templates/index', [
            'user' => $user,
            'templates' => $templates,
            'filters' => $filters,
            'page' => $page,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show create template form
     */
    public function create()
    {
        $this->requireAuth();

        $user = $this->getAuthUser();

        $this->view->render('templates/create', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Store new template
     */
    public function store()
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();
        $userId = $_SESSION['user_id'];

        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? '';
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            setFlash('error', 'Template name is required');
            $this->redirect('/templates/create');
        }

        $templateModel = $this->model('Template');

        $templateId = $templateModel->insert([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'name' => $name,
            'description' => $description,
            'category' => $category,
            'is_public' => $isPublic,
            'usage_count' => 0,
        ]);

        setFlash('success', 'Template created successfully!');
        $this->redirect('/templates/' . $templateId);
    }

    /**
     * Show template details
     */
    public function show($id)
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $templateModel = $this->model('Template');

        $template = $templateModel->findByIdWithScenes($id);

        if (!$template) {
            setFlash('error', 'Template not found');
            $this->redirect('/templates');
        }

        // Check access
        if (!$template['is_public'] && $template['tenant_id'] != $tenantId) {
            setFlash('error', 'Access denied');
            $this->redirect('/templates');
        }

        $this->view->render('templates/show', [
            'user' => $user,
            'template' => $template,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Delete template
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $templateModel = $this->model('Template');

        // Verify template belongs to tenant
        $template = $this->db->prepare("SELECT * FROM templates WHERE id = ? AND tenant_id = ?");
        $template->execute([$id, $tenantId]);
        $template = $template->fetch();

        if (!$template) {
            setFlash('error', 'Template not found or access denied');
            $this->redirect('/templates');
        }

        $templateModel->delete($id);

        setFlash('success', 'Template deleted successfully');
        $this->redirect('/templates');
    }
}
