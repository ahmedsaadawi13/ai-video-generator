// FILE: /app/views/subscription/index.php
<h1>Subscription</h1>
<?php if ($subscription): ?>
    <div class="subscription-info">
        <h2><?php echo $this->escape($subscription['plan_name']); ?></h2>
        <p>Status: <span class="badge badge-success"><?php echo $subscription['status']; ?></span></p>
        <p>Billing: <?php echo ucfirst($subscription['billing_cycle']); ?></p>
        <p>Period: <?php echo formatDate($subscription['current_period_start']); ?> - <?php echo formatDate($subscription['current_period_end']); ?></p>
    </div>
    <h3>Current Usage</h3>
    <ul>
        <li>Projects: <?php echo $usage['projects_count']; ?> / <?php echo $subscription['max_projects'] == -1 ? 'Unlimited' : $subscription['max_projects']; ?></li>
        <li>Renders: <?php echo $usage['renders_count']; ?> / <?php echo $subscription['max_renders_per_month'] == -1 ? 'Unlimited' : $subscription['max_renders_per_month']; ?></li>
        <li>Storage: <?php echo formatBytes($usage['storage_mb'] * 1024 * 1024); ?> / <?php echo formatBytes($subscription['max_storage_mb'] * 1024 * 1024); ?></li>
    </ul>
    <a href="/subscription/plans" class="btn btn-primary">Upgrade Plan</a>
    <a href="/subscription/invoices" class="btn btn-secondary">View Invoices</a>
<?php endif; ?>
