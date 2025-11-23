// FILE: /app/views/analytics/index.php
<h1>Analytics</h1>
<form method="GET" class="filters">
    <input type="date" name="start_date" value="<?php echo $startDate; ?>">
    <input type="date" name="end_date" value="<?php echo $endDate; ?>">
    <button type="submit" class="btn btn-secondary">Filter</button>
</form>
<div class="stats-grid">
    <div class="stat-card"><h2><?php echo $projectsCreated; ?></h2><p>Projects Created</p></div>
    <div class="stat-card"><h2><?php echo $rendersCompleted; ?></h2><p>Renders Completed</p></div>
    <div class="stat-card"><h2><?php echo $videosDownloaded; ?></h2><p>Videos Downloaded</p></div>
</div>
<h3>Project Types</h3>
<table class="table">
    <?php foreach ($projectTypeData as $data): ?>
        <tr><td><?php echo str_replace('_', ' ', ucwords($data['type'])); ?></td><td><?php echo $data['count']; ?></td></tr>
    <?php endforeach; ?>
</table>
<h3>Render Status</h3>
<table class="table">
    <?php foreach ($renderStatusData as $data): ?>
        <tr><td><?php echo ucfirst($data['status']); ?></td><td><?php echo $data['count']; ?></td></tr>
    <?php endforeach; ?>
</table>
