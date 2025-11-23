// FILE: /app/views/admin/plans.php
<h1>Subscription Plans</h1>
<a href="/admin/plans/create" class="btn btn-primary">+ Create Plan</a>
<table class="table">
    <tr><th>Name</th><th>Monthly</th><th>Yearly</th><th>Projects</th><th>Renders/mo</th><th>Storage</th><th>Status</th></tr>
    <?php foreach ($plans as $plan): ?>
        <tr>
            <td><?php echo $this->escape($plan['name']); ?></td>
            <td>$<?php echo number_format($plan['price_monthly'], 2); ?></td>
            <td>$<?php echo number_format($plan['price_yearly'], 2); ?></td>
            <td><?php echo $plan['max_projects'] == -1 ? 'Unlimited' : $plan['max_projects']; ?></td>
            <td><?php echo $plan['max_renders_per_month'] == -1 ? 'Unlimited' : $plan['max_renders_per_month']; ?></td>
            <td><?php echo formatBytes($plan['max_storage_mb'] * 1024 * 1024); ?></td>
            <td><?php echo $plan['is_active'] ? 'Active' : 'Inactive'; ?></td>
        </tr>
    <?php endforeach; ?>
</table>
