// FILE: /app/views/subscription/plans.php
<h1>Choose a Plan</h1>
<div class="plans-grid">
    <?php foreach ($plans as $plan): ?>
        <div class="plan-card">
            <h2><?php echo $this->escape($plan['name']); ?></h2>
            <p class="price">$<?php echo number_format($plan['price_monthly'], 0); ?>/mo</p>
            <p><?php echo $this->escape($plan['description']); ?></p>
            <ul>
                <li><?php echo $plan['max_projects'] == -1 ? 'Unlimited' : $plan['max_projects']; ?> Projects</li>
                <li><?php echo $plan['max_renders_per_month'] == -1 ? 'Unlimited' : $plan['max_renders_per_month']; ?> Renders</li>
                <li><?php echo formatBytes($plan['max_storage_mb'] * 1024 * 1024); ?> Storage</li>
            </ul>
            <form method="POST" action="/subscription/subscribe">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                <select name="billing_cycle">
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>
