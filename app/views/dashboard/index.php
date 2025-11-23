// FILE: /app/views/dashboard/index.php
<div class="dashboard">
    <h1>Dashboard</h1>
    <p class="subtitle">Welcome back, <?php echo $this->escape($user['first_name']); ?>!</p>

    <?php if ($subscription): ?>
        <div class="subscription-status">
            <h3>Current Plan: <?php echo $this->escape($subscription['plan_name']); ?></h3>
            <p>Status: <span class="badge badge-success"><?php echo ucfirst($subscription['status']); ?></span></p>
            <p>Billing Cycle: <?php echo ucfirst($subscription['billing_cycle']); ?></p>
            <p>Period: <?php echo formatDate($subscription['current_period_start'], 'M d, Y'); ?> - <?php echo formatDate($subscription['current_period_end'], 'M d, Y'); ?></p>
        </div>

        <div class="usage-overview">
            <h3>Usage This Month</h3>
            <div class="usage-grid">
                <div class="usage-card">
                    <h4>Projects</h4>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: <?php echo $usagePercent['projects'] ?? 0; ?>%"></div>
                    </div>
                    <p><?php echo $usage['projects_count']; ?> / <?php echo $subscription['max_projects'] == -1 ? 'Unlimited' : $subscription['max_projects']; ?></p>
                </div>

                <div class="usage-card">
                    <h4>Renders</h4>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: <?php echo $usagePercent['renders'] ?? 0; ?>%"></div>
                    </div>
                    <p><?php echo $usage['renders_count']; ?> / <?php echo $subscription['max_renders_per_month'] == -1 ? 'Unlimited' : $subscription['max_renders_per_month']; ?></p>
                </div>

                <div class="usage-card">
                    <h4>Storage</h4>
                    <div class="usage-bar">
                        <div class="usage-progress" style="width: <?php echo $usagePercent['storage'] ?? 0; ?>%"></div>
                    </div>
                    <p><?php echo formatBytes($usage['storage_mb'] * 1024 * 1024); ?> / <?php echo formatBytes($subscription['max_storage_mb'] * 1024 * 1024); ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="stats-grid">
        <div class="stat-card">
            <h3><?php echo $stats['total_projects']; ?></h3>
            <p>Total Projects</p>
            <a href="/projects">View All →</a>
        </div>

        <div class="stat-card">
            <h3><?php echo $stats['total_renders']; ?></h3>
            <p>Render Jobs</p>
            <a href="/renders">View All →</a>
        </div>

        <div class="stat-card">
            <h3><?php echo $stats['total_videos']; ?></h3>
            <p>Completed Videos</p>
            <a href="/videos">View All →</a>
        </div>

        <div class="stat-card">
            <h3><?php echo $stats['total_assets']; ?></h3>
            <p>Media Assets</p>
            <a href="/assets">View All →</a>
        </div>
    </div>

    <div class="dashboard-sections">
        <div class="section">
            <h3>Recent Projects</h3>
            <?php if (!empty($recentProjects)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentProjects as $project): ?>
                            <tr>
                                <td><a href="/projects/<?php echo $project['id']; ?>"><?php echo $this->escape($project['name']); ?></a></td>
                                <td><?php echo str_replace('_', ' ', ucfirst($project['type'])); ?></td>
                                <td><span class="badge badge-<?php echo $project['status']; ?>"><?php echo ucfirst($project['status']); ?></span></td>
                                <td><?php echo timeAgo($project['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="/projects" class="btn btn-secondary">View All Projects</a>
            <?php else: ?>
                <p>No projects yet. <a href="/projects/create">Create your first project</a>!</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3>Recent Renders</h3>
            <?php if (!empty($recentRenders)): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentRenders as $render): ?>
                            <tr>
                                <td><?php echo str_replace('_', ' ', ucfirst($render['job_type'])); ?></td>
                                <td><span class="badge badge-<?php echo $render['status']; ?>"><?php echo ucfirst($render['status']); ?></span></td>
                                <td><?php echo $render['progress']; ?>%</td>
                                <td><?php echo timeAgo($render['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <a href="/renders" class="btn btn-secondary">View All Renders</a>
            <?php else: ?>
                <p>No renders yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <div class="actions-grid">
            <a href="/projects/create" class="action-btn">
                <span class="icon">+</span>
                <span>New Project</span>
            </a>
            <a href="/assets/upload" class="action-btn">
                <span class="icon">↑</span>
                <span>Upload Asset</span>
            </a>
            <a href="/renders/create" class="action-btn">
                <span class="icon">▶</span>
                <span>Start Render</span>
            </a>
            <a href="/templates" class="action-btn">
                <span class="icon">📄</span>
                <span>Browse Templates</span>
            </a>
        </div>
    </div>
</div>
