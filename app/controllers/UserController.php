// FILE: /app/controllers/UserController.php
<?php

/**
 * UserController
 *
 * Handles user management (team members)
 */
class UserController extends Controller
{
    /**
     * List all users
     */
    public function index()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $userModel = $this->model('User');

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $users = $userModel->findByTenantWithPagination($tenantId, $page, $perPage);
        $totalUsers = $userModel->countByTenant($tenantId);
        $totalPages = totalPages($totalUsers, $perPage);

        $this->view->render('users/index', [
            'user' => $user,
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'totalUsers' => $totalUsers,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $user = $this->getAuthUser();

        $this->view->render('users/create', [
            'user' => $user,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Store new user
     */
    public function store()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'editor';

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

        if (!empty($errors)) {
            setFlash('error', implode('<br>', $errors));
            $this->redirect('/users/create');
        }

        $userModel = $this->model('User');

        // Check if email already exists
        if ($userModel->findByEmail($email)) {
            setFlash('error', 'Email already exists');
            $this->redirect('/users/create');
        }

        $userModel->create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'password' => $password,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'status' => 'active',
        ]);

        setFlash('success', 'User created successfully!');
        $this->redirect('/users');
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);

        $tenantId = $this->getTenantId();
        $currentUser = $this->getAuthUser();

        $userModel = $this->model('User');

        // Verify user belongs to tenant
        $editUser = $this->db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $editUser->execute([$id, $tenantId]);
        $editUser = $editUser->fetch();

        if (!$editUser) {
            setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $this->view->render('users/edit', [
            'user' => $currentUser,
            'editUser' => $editUser,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $userModel = $this->model('User');

        // Verify user belongs to tenant
        $editUser = $this->db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $editUser->execute([$id, $tenantId]);
        $editUser = $editUser->fetch();

        if (!$editUser) {
            setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $role = $_POST['role'] ?? $editUser['role'];
        $status = $_POST['status'] ?? $editUser['status'];

        // Validation
        if (empty($firstName) || empty($lastName)) {
            setFlash('error', 'First name and last name are required');
            $this->redirect('/users/' . $id . '/edit');
        }

        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => $role,
            'status' => $status,
        ];

        // Update password if provided
        if (!empty($_POST['password'])) {
            if (strlen($_POST['password']) < 6) {
                setFlash('error', 'Password must be at least 6 characters');
                $this->redirect('/users/' . $id . '/edit');
            }
            $updateData['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $userModel->update($id, $updateData);

        setFlash('success', 'User updated successfully!');
        $this->redirect('/users');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        // Prevent deleting self
        if ($id == $_SESSION['user_id']) {
            setFlash('error', 'You cannot delete your own account');
            $this->redirect('/users');
        }

        $userModel = $this->model('User');

        // Verify user belongs to tenant
        $editUser = $this->db->prepare("SELECT * FROM users WHERE id = ? AND tenant_id = ?");
        $editUser->execute([$id, $tenantId]);
        $editUser = $editUser->fetch();

        if (!$editUser) {
            setFlash('error', 'User not found');
            $this->redirect('/users');
        }

        $userModel->delete($id);

        setFlash('success', 'User deleted successfully');
        $this->redirect('/users');
    }
}
