// FILE: /app/controllers/SubscriptionController.php
<?php

/**
 * SubscriptionController
 *
 * Handles subscription and billing
 */
class SubscriptionController extends Controller
{
    /**
     * Show current subscription
     */
    public function index()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $subscriptionModel = $this->model('Subscription');
        $tenantModel = $this->model('Tenant');

        $subscription = $subscriptionModel->getActiveByTenant($tenantId);
        $usage = $tenantModel->getCurrentUsage($tenantId);

        $this->view->render('subscription/index', [
            'user' => $user,
            'subscription' => $subscription,
            'usage' => $usage,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show available plans
     */
    public function plans()
    {
        $this->requireAuth();

        $user = $this->getAuthUser();

        $planModel = $this->model('Plan');
        $plans = $planModel->getAllActive();

        $this->view->render('subscription/plans', [
            'user' => $user,
            'plans' => $plans,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe()
    {
        $this->requireAuth();
        $this->requireRole(['tenant_admin']);
        $this->validateCsrfToken();

        $tenantId = $this->getTenantId();

        $planId = (int)($_POST['plan_id'] ?? 0);
        $billingCycle = $_POST['billing_cycle'] ?? 'monthly';

        $planModel = $this->model('Plan');
        $plan = $planModel->findById($planId);

        if (!$plan) {
            setFlash('error', 'Plan not found');
            $this->redirect('/subscription/plans');
        }

        // Calculate amount
        $amount = ($billingCycle === 'yearly') ? $plan['price_yearly'] : $plan['price_monthly'];

        // Create invoice
        $invoiceModel = $this->model('Invoice');
        $invoiceNumber = $invoiceModel->generateInvoiceNumber();

        $tax = $amount * 0.10; // 10% tax simulation
        $total = $amount + $tax;

        $invoiceId = $invoiceModel->insert([
            'tenant_id' => $tenantId,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax' => $tax,
            'total' => $total,
            'status' => 'pending',
            'due_date' => date('Y-m-d', strtotime('+7 days')),
        ]);

        // Process payment (simulated)
        $paymentModel = $this->model('Payment');
        $paymentId = $paymentModel->createPayment([
            'tenant_id' => $tenantId,
            'invoice_id' => $invoiceId,
            'amount' => $total,
            'payment_method' => 'card',
        ]);

        // Mark invoice as paid
        $invoiceModel->markAsPaid($invoiceId);

        // Create or update subscription
        $subscriptionModel = $this->model('Subscription');
        $existingSubscription = $subscriptionModel->getActiveByTenant($tenantId);

        if ($existingSubscription) {
            // Update existing subscription
            $subscriptionModel->update($existingSubscription['id'], [
                'plan_id' => $planId,
                'billing_cycle' => $billingCycle,
                'current_period_start' => date('Y-m-d'),
                'current_period_end' => date('Y-m-d', strtotime($billingCycle === 'yearly' ? '+1 year' : '+1 month')),
            ]);
        } else {
            // Create new subscription
            $subscriptionModel->insert([
                'tenant_id' => $tenantId,
                'plan_id' => $planId,
                'status' => 'active',
                'billing_cycle' => $billingCycle,
                'current_period_start' => date('Y-m-d'),
                'current_period_end' => date('Y-m-d', strtotime($billingCycle === 'yearly' ? '+1 year' : '+1 month')),
            ]);
        }

        // Create notification
        $notificationModel = $this->model('Notification');
        $notificationModel->create(
            $tenantId,
            $_SESSION['user_id'],
            'payment_success',
            'Payment Received',
            'Thank you! Your payment of $' . number_format($total, 2) . ' has been received.'
        );

        setFlash('success', 'Subscription updated successfully!');
        $this->redirect('/subscription');
    }

    /**
     * Show invoices
     */
    public function invoices()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $invoiceModel = $this->model('Invoice');

        // Pagination
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 20;

        $invoices = $invoiceModel->findByTenantWithPagination($tenantId, $page, $perPage);

        $this->view->render('subscription/invoices', [
            'user' => $user,
            'invoices' => $invoices,
            'page' => $page,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Show usage details
     */
    public function usage()
    {
        $this->requireAuth();

        $tenantId = $this->getTenantId();
        $user = $this->getAuthUser();

        $usageModel = $this->model('UsageTracking');
        $tenantModel = $this->model('Tenant');

        $currentUsage = $tenantModel->getCurrentUsage($tenantId);
        $usageHistory = $usageModel->getHistory($tenantId, 12);
        $subscription = $tenantModel->getActiveSubscription($tenantId);

        $this->view->render('subscription/usage', [
            'user' => $user,
            'currentUsage' => $currentUsage,
            'usageHistory' => $usageHistory,
            'subscription' => $subscription,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }
}
