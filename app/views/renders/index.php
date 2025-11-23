// FILE: /app/views/renders/index.php
<h1>Render Jobs</h1>
<a href="/renders/create" class="btn btn-primary">+ New Render</a>
<table class="table">
    <tr><th>Type</th><th>Project</th><th>Status</th><th>Progress</th><th>Created</th><th>Actions</th></tr>
    <?php foreach ($renderJobs as $job): ?>
        <tr>
            <td><?php echo str_replace('_', ' ', $job['job_type']); ?></td>
            <td><?php echo $this->escape($job['project_name']); ?></td>
            <td><span class="badge badge-<?php echo $job['status']; ?>"><?php echo $job['status']; ?></span></td>
            <td><?php echo $job['progress']; ?>%</td>
            <td><?php echo timeAgo($job['created_at']); ?></td>
            <td><a href="/renders/<?php echo $job['id']; ?>">View</a></td>
        </tr>
    <?php endforeach; ?>
</table>
