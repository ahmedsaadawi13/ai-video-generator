// FILE: /app/core/Controller.php
<?php

/**
 * Base Controller Class
 *
 * All controllers extend this class
 */
class Controller
{
    protected $view;
    protected $db;

    public function __construct()
    {
        $this->view = new View();
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Load a model
     */
    protected function model($modelName)
    {
        $modelFile = __DIR__ . '/../models/' . $modelName . '.php';

        if (!file_exists($modelFile)) {
            die("Model not found: $modelName");
        }

        require_once $modelFile;

        if (!class_exists($modelName)) {
            die("Model class not found: $modelName");
        }

        return new $modelName();
    }

    /**
     * Get current authenticated user
     */
    protected function getAuthUser()
    {
        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $userModel = $this->model('User');
        return $userModel->findById($_SESSION['user_id']);
    }

    /**
     * Check if user is authenticated
     */
    protected function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            exit;
        }
    }

    /**
     * Check if user has specific role
     */
    protected function requireRole($roles)
    {
        $this->requireAuth();

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $user = $this->getAuthUser();

        if (!in_array($user['role'], $roles)) {
            http_response_code(403);
            $this->view->render('errors/403', ['message' => 'Access denied']);
            exit;
        }
    }

    /**
     * Get current tenant ID
     */
    protected function getTenantId()
    {
        $user = $this->getAuthUser();
        return $user ? $user['tenant_id'] : null;
    }

    /**
     * Redirect to a URL
     */
    protected function redirect($url)
    {
        header("Location: $url");
        exit;
    }

    /**
     * Return JSON response
     */
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';

            if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }

    /**
     * Generate CSRF token
     */
    protected function generateCsrfToken()
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}
