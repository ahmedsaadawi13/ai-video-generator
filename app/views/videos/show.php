// FILE: /app/views/videos/show.php
<h1><?php echo $this->escape($video['title']); ?></h1>
<div class="video-details">
    <p>Project: <?php echo $this->escape($video['project_name']); ?></p>
    <p>Duration: <?php echo formatDuration($video['duration']); ?></p>
    <p>Resolution: <?php echo $this->escape($video['resolution']); ?></p>
    <p>File Size: <?php echo formatBytes($video['file_size']); ?></p>
    <p>Views: <?php echo $video['view_count']; ?></p>
    <p>Downloads: <?php echo $video['download_count']; ?></p>
    <p>Created: <?php echo formatDate($video['created_at']); ?></p>
</div>
<div class="video-actions">
    <a href="/videos/<?php echo $video['id']; ?>/download" class="btn btn-primary">Download</a>
    <form method="POST" action="/videos/<?php echo $video['id']; ?>/duplicate" style="display:inline;">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <button type="submit" class="btn btn-secondary">Duplicate Project</button>
    </form>
</div>
