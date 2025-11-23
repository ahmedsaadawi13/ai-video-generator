// FILE: /app/controllers/ApiKeyController.php
<?php

/**
 * ApiKeyController
 *
 * Handles API key management
 */
class ApiKeyController extends Controller
{
    /**
     * List all API keys
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $apiKeyModel = $this->model('ApiKey');
        $apiKeys = $apiKeyModel->findByTenant($tenantId);

        $this->view->render('api-keys/index', [
            'user' => $user,
            'apiKeys' => $apiKeys,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Generate new API key
     */
    public function generate()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $name = trim($_POST['name'] ?? 'API Key');

        $apiKeyModel = $this->model('ApiKey');
        $result = $apiKeyModel->generate($tenantId, $name);

        // Store the key in session to display once
        $_SESSION['new_api_key'] = $result['api_key'];

        setFlash('success', 'API key generated successfully! Make sure to copy it now - you won\'t see it again.');
        $this->redirect('/api-keys');
    }

    /**
     * Revoke API key
     */
    public function revoke($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        // Verify API key belongs to tenant
        $apiKey = $this->db->prepare("SELECT * FROM api_keys WHERE id = ? AND tenant_id = ?");
        $apiKey->execute([$id, $tenantId]);
        $apiKey = $apiKey->fetch();

        if (!$apiKey) {
            setFlash('error', 'API key not found');
            $this->redirect('/api-keys');
        }

        $apiKeyModel = $this->model('ApiKey');
        $apiKeyModel->revoke($id);

        setFlash('success', 'API key revoked successfully');
        $this->redirect('/api-keys');
    }
}
