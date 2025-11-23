// FILE: /app/views/subscription/usage.php
<h1>Usage Details</h1>
<h2>Current Month</h2>
<ul>
    <li>Projects: <?php echo $currentUsage['projects_count']; ?></li>
    <li>Renders: <?php echo $currentUsage['renders_count']; ?></li>
    <li>Render Minutes: <?php echo $currentUsage['render_minutes']; ?></li>
    <li>Storage: <?php echo formatBytes($currentUsage['storage_mb'] * 1024 * 1024); ?></li>
</ul>
<h2>Usage History</h2>
<table class="table">
    <tr><th>Month</th><th>Projects</th><th>Renders</th><th>Minutes</th><th>Storage</th></tr>
    <?php foreach ($usageHistory as $usage): ?>
        <tr>
            <td><?php echo formatDate($usage['month'], 'M Y'); ?></td>
            <td><?php echo $usage['projects_count']; ?></td>
            <td><?php echo $usage['renders_count']; ?></td>
            <td><?php echo $usage['render_minutes']; ?></td>
            <td><?php echo formatBytes($usage['storage_mb'] * 1024 * 1024); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
