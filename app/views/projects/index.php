// FILE: /app/views/projects/index.php
<div class="projects-page">
    <div class="page-header">
        <h1>Projects</h1>
        <a href="/projects/create" class="btn btn-primary">+ New Project</a>
    </div>

    <div class="filters">
        <form method="GET" action="/projects">
            <select name="type" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="text_to_video" <?php echo $filters['type'] === 'text_to_video' ? 'selected' : ''; ?>>Text to Video</option>
                <option value="image_to_video" <?php echo $filters['type'] === 'image_to_video' ? 'selected' : ''; ?>>Image to Video</option>
                <option value="video_to_video" <?php echo $filters['type'] === 'video_to_video' ? 'selected' : ''; ?>>Video to Video</option>
                <option value="images_to_video" <?php echo $filters['type'] === 'images_to_video' ? 'selected' : ''; ?>>Images to Video</option>
            </select>

            <select name="status" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="draft" <?php echo $filters['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                <option value="ready" <?php echo $filters['status'] === 'ready' ? 'selected' : ''; ?>>Ready</option>
                <option value="rendering" <?php echo $filters['status'] === 'rendering' ? 'selected' : ''; ?>>Rendering</option>
                <option value="completed" <?php echo $filters['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
            </select>
        </form>
    </div>

    <?php if (!empty($projects)): ?>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Platform</th>
                    <th>Resolution</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><a href="/projects/<?php echo $project['id']; ?>"><?php echo $this->escape($project['name']); ?></a></td>
                        <td><?php echo str_replace('_', ' ', ucwords($project['type'])); ?></td>
                        <td><?php echo str_replace('_', ' ', ucwords($project['platform_preset'])); ?></td>
                        <td><?php echo $this->escape($project['resolution']); ?></td>
                        <td><span class="badge badge-<?php echo $project['status']; ?>"><?php echo ucfirst($project['status']); ?></span></td>
                        <td><?php echo $this->escape($project['first_name'] . ' ' . $project['last_name']); ?></td>
                        <td><?php echo timeAgo($project['created_at']); ?></td>
                        <td>
                            <a href="/projects/<?php echo $project['id']; ?>">View</a> |
                            <a href="/projects/<?php echo $project['id']; ?>/edit">Edit</a> |
                            <form method="POST" action="/projects/<?php echo $project['id']; ?>/delete" style="display:inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <button type="submit" class="link-button">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $filters['type'] ? '&type=' . $filters['type'] : ''; ?><?php echo $filters['status'] ? '&status=' . $filters['status'] : ''; ?>"
                       class="<?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>No projects found.</p>
            <a href="/projects/create" class="btn btn-primary">Create Your First Project</a>
        </div>
    <?php endif; ?>
</div>
