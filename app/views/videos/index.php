// FILE: /app/views/videos/index.php
<h1>Videos</h1>
<table class="table">
    <tr><th>Title</th><th>Project</th><th>Duration</th><th>Size</th><th>Created</th><th>Actions</th></tr>
    <?php foreach ($videos as $video): ?>
        <tr>
            <td><?php echo $this->escape($video['title']); ?></td>
            <td><?php echo $this->escape($video['project_name']); ?></td>
            <td><?php echo formatDuration($video['duration']); ?></td>
            <td><?php echo formatBytes($video['file_size']); ?></td>
            <td><?php echo timeAgo($video['created_at']); ?></td>
            <td>
                <a href="/videos/<?php echo $video['id']; ?>">View</a> |
                <a href="/videos/<?php echo $video['id']; ?>/download">Download</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
