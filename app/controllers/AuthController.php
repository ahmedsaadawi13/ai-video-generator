// FILE: /app/controllers/AuthController.php
<?php

/**
 * AuthController
 *
 * Handles authentication (login, registration, logout)
 */
class AuthController extends Controller
{
    /**
     * Show login form
     */
    public function login()
    {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->view->render('auth/login', [
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Process login
     */
    public function loginPost()
    {
        $this->validateCsrfToken();

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($email) || empty($password)) {
            setFlash('error', 'Please provide email and password');
            $this->redirect('/login');
        }

        $userModel = $this->model('User');
        $user = $userModel->verify($email, $password);

        if (!$user) {
            setFlash('error', 'Invalid email or password');
            $this->redirect('/login');
        }

        // Check user status
        if ($user['status'] !== 'active') {
            setFlash('error', 'Your account is inactive');
            $this->redirect('/login');
        }

        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['tenant_id'] = $user['tenant_id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];

        setFlash('success', 'Welcome back, ' . $user['first_name'] . '!');
        $this->redirect('/dashboard');
    }

    /**
     * Show registration form
     */
    public function register()
    {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->view->render('auth/register', [
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Process registration
     */
    public function registerPost()
    {
        $this->validateCsrfToken();

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $companyName = trim($_POST['company_name'] ?? '');

        // Validation
        $errors = [];

        if (empty($firstName)) {
            $errors[] = 'First name is required';
        }

        if (empty($lastName)) {
            $errors[] = 'Last name is required';
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (empty($password) || strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match';
        }

        if (empty($companyName)) {
            $errors[] = 'Company name is required';
        }

        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            $this->redirect('/register');
        }

        $userModel = $this->model('User');
        $tenantModel = $this->model('Tenant');

        // Check if email already exists
        if ($userModel->findByEmail($email)) {
            setFlash('error', 'Email already exists');
            $this->redirect('/register');
        }

        // Create tenant
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $companyName));
        $slug = trim($slug, '-');

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while ($tenantModel->findBySlug($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $tenantId = $tenantModel->insert([
            'name' => $companyName,
            'slug' => $slug,
            'email' => $email,
            'status' => 'active',
        ]);

        // Create user
        $userId = $userModel->create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'tenant_admin',
            'status' => 'active',
        ]);

        // Assign free plan
        $planModel = $this->model('Plan');
        $freePlan = $planModel->findBySlug('free');

        if ($freePlan) {
            $subscriptionModel = $this->model('Subscription');
            $subscriptionModel->insert([
                'tenant_id' => $tenantId,
                'plan_id' => $freePlan['id'],
                'status' => 'active',
                'billing_cycle' => 'monthly',
                'current_period_start' => date('Y-m-d'),
                'current_period_end' => date('Y-m-d', strtotime('+1 month')),
            ]);
        }

        setFlash('success', 'Registration successful! Please log in.');
        $this->redirect('/login');
    }

    /**
     * Logout
     */
    public function logout()
    {
        session_destroy();
        $this->redirect('/login');
    }
}
