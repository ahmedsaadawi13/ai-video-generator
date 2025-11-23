// FILE: /app/views/admin/index.php
<h1>Platform Administration</h1>
<div class="stats-grid">
    <div class="stat-card"><h2><?php echo $stats['total_tenants']; ?></h2><p>Tenants</p></div>
    <div class="stat-card"><h2><?php echo $stats['total_users']; ?></h2><p>Users</p></div>
    <div class="stat-card"><h2><?php echo $stats['total_projects']; ?></h2><p>Projects</p></div>
    <div class="stat-card"><h2><?php echo $stats['total_renders']; ?></h2><p>Renders</p></div>
</div>
<div class="admin-links">
    <a href="/admin/tenants" class="btn btn-primary">Manage Tenants</a>
    <a href="/admin/plans" class="btn btn-primary">Manage Plans</a>
</div>
