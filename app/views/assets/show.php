// FILE: /app/views/assets/show.php
<h1><?php echo $this->escape($asset['name']); ?></h1>
<div class="asset-details">
    <p>Type: <?php echo ucfirst($asset['file_type']); ?></p>
    <p>Size: <?php echo formatBytes($asset['file_size']); ?></p>
    <p>Created: <?php echo formatDate($asset['created_at']); ?></p>
    <?php if ($asset['tags']): ?>
        <p>Tags: <?php echo $this->escape($asset['tags']); ?></p>
    <?php endif; ?>
</div>
<form method="POST" action="/assets/<?php echo $asset['id']; ?>/delete" onsubmit="return confirm('Are you sure?');">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <button type="submit" class="btn btn-danger">Delete</button>
</form>
