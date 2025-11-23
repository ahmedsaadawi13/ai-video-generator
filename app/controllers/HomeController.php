// FILE: /app/controllers/HomeController.php
<?php

/**
 * HomeController
 *
 * Handles the public home page
 */
class HomeController extends Controller
{
    /**
     * Show home page
     */
    public function index()
    {
        // If user is logged in, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $planModel = $this->model('Plan');
        $plans = $planModel->getAllActive();

        $this->view->render('home/index', [
            'plans' => $plans,
        ]);
    }
}
