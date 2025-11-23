// FILE: /app/views/scenes/index.php
<h1>Scenes for <?php echo $this->escape($project['name']); ?></h1>
<p>Total Duration: <?php echo formatDuration($totalDuration); ?></p>
<a href="/projects/<?php echo $project['id']; ?>/scenes/create" class="btn btn-primary">+ Add Scene</a>
<table class="table">
    <tr><th>#</th><th>Type</th><th>Duration</th><th>Actions</th></tr>
    <?php foreach ($scenes as $scene): ?>
    <tr>
        <td><?php echo $scene['position']; ?></td>
        <td><?php echo str_replace('_', ' ', $scene['scene_type']); ?></td>
        <td><?php echo $scene['duration']; ?>s</td>
        <td><a href="/scenes/<?php echo $scene['id']; ?>/edit">Edit</a></td>
    </tr>
    <?php endforeach; ?>
</table>
