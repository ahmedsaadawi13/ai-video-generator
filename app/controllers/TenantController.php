// FILE: /app/controllers/TenantController.php
<?php

/**
 * TenantController
 *
 * Handles tenant settings and brand management
 */
class TenantController extends Controller
{
    /**
     * Show tenant settings
     */
    public function settings()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $tenantModel = $this->model('Tenant');
        $tenant = $tenantModel->findById($tenantId);

        $this->view->render('tenant/settings', [
            'user' => $user,
            'tenant' => $tenant,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Update tenant settings
     */
    public function updateSettings()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $timezone = $_POST['timezone'] ?? 'UTC';

        // Validation
        if (empty($name)) {
            setFlash('error', 'Company name is required');
            $this->redirect('/tenant/settings');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlash('error', 'Valid email is required');
            $this->redirect('/tenant/settings');
        }

        $tenantModel = $this->model('Tenant');

        $tenantModel->update($tenantId, [
            'name' => $name,
            'email' => $email,
            'website' => $website,
            'timezone' => $timezone,
        ]);

        setFlash('success', 'Tenant settings updated successfully!');
        $this->redirect('/tenant/settings');
    }

    /**
     * Show brand settings
     */
    public function brand()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $brandSettingModel = $this->model('BrandSetting');
        $brandSettings = $brandSettingModel->findByTenant($tenantId);

        $this->view->render('tenant/brand', [
            'user' => $user,
            'brandSettings' => $brandSettings,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Update brand settings
     */
    public function updateBrand()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $primaryColor = $_POST['primary_color'] ?? '#3B82F6';
        $secondaryColor = $_POST['secondary_color'] ?? '#10B981';
        $defaultFont = $_POST['default_font'] ?? 'Arial';

        $brandSettingModel = $this->model('BrandSetting');

        $brandSettingModel->updateByTenant($tenantId, [
            'primary_color' => $primaryColor,
            'secondary_color' => $secondaryColor,
            'default_font' => $defaultFont,
        ]);

        setFlash('success', 'Brand settings updated successfully!');
        $this->redirect('/tenant/brand');
    }
}
