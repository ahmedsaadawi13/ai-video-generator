// FILE: /app/views/analytics/reports.php
<h1>Usage Reports</h1>
<table class="table">
    <tr><th>Month</th><th>Projects</th><th>Renders</th><th>Render Minutes</th><th>Storage</th></tr>
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
