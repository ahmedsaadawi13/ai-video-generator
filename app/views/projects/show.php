// FILE: /app/views/projects/show.php
<div class="project-details-page">
    <div class="page-header">
        <h1><?php echo $this->escape($project['name']); ?></h1>
        <div class="actions">
            <a href="/projects/<?php echo $project['id']; ?>/edit" class="btn btn-secondary">Edit</a>
            <a href="/projects/<?php echo $project['id']; ?>/scenes" class="btn btn-primary">Manage Scenes</a>
        </div>
    </div>

    <div class="project-info">
        <div class="info-grid">
            <div class="info-item">
                <label>Type:</label>
                <span><?php echo str_replace('_', ' ', ucwords($project['type'])); ?></span>
            </div>
            <div class="info-item">
                <label>Platform:</label>
                <span><?php echo str_replace('_', ' ', ucwords($project['platform_preset'])); ?></span>
            </div>
            <div class="info-item">
                <label>Aspect Ratio:</label>
                <span><?php echo $this->escape($project['aspect_ratio']); ?></span>
            </div>
            <div class="info-item">
                <label>Resolution:</label>
                <span><?php echo $this->escape($project['resolution']); ?></span>
            </div>
            <div class="info-item">
                <label>Status:</label>
                <span class="badge badge-<?php echo $project['status']; ?>"><?php echo ucfirst($project['status']); ?></span>
            </div>
            <div class="info-item">
                <label>Created:</label>
                <span><?php echo formatDate($project['created_at']); ?></span>
            </div>
        </div>

        <?php if ($project['description']): ?>
            <div class="description">
                <label>Description:</label>
                <p><?php echo $this->escape($project['description']); ?></p>
            </div>
        <?php endif; ?>
    </div>

    <div class="project-scenes">
        <h3>Scenes (<?php echo count($project['scenes']); ?>)</h3>
        <?php if (!empty($project['scenes'])): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Prompt</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($project['scenes'] as $scene): ?>
                        <tr>
                            <td><?php echo $scene['position']; ?></td>
                            <td><?php echo str_replace('_', ' ', ucwords($scene['scene_type'])); ?></td>
                            <td><?php echo $scene['duration']; ?>s</td>
                            <td><?php echo $this->escape(substr($scene['prompt'] ?? '', 0, 50)); ?><?php echo strlen($scene['prompt'] ?? '') > 50 ? '...' : ''; ?></td>
                            <td>
                                <a href="/scenes/<?php echo $scene['id']; ?>/edit">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No scenes yet. <a href="/projects/<?php echo $project['id']; ?>/scenes/create">Add your first scene</a>.</p>
        <?php endif; ?>
    </div>
</div>
