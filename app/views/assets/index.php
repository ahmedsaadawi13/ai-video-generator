// FILE: /app/views/assets/index.php
<h1>Media Assets</h1>
<p>Storage Used: <?php echo formatBytes($storageUsed); ?></p>
<a href="/assets/upload" class="btn btn-primary">+ Upload Asset</a>
<form method="GET" class="filters">
    <select name="file_type" onchange="this.form.submit()">
        <option value="">All Types</option>
        <option value="image" <?php echo $filters['file_type'] === 'image' ? 'selected' : ''; ?>>Images</option>
        <option value="video" <?php echo $filters['file_type'] === 'video' ? 'selected' : ''; ?>>Videos</option>
        <option value="audio" <?php echo $filters['file_type'] === 'audio' ? 'selected' : ''; ?>>Audio</option>
    </select>
    <input type="text" name="search" placeholder="Search..." value="<?php echo $this->escape($filters['search']); ?>">
</form>
<div class="assets-grid">
    <?php foreach ($assets as $asset): ?>
        <div class="asset-card">
            <h4><?php echo $this->escape($asset['name']); ?></h4>
            <p><?php echo ucfirst($asset['file_type']); ?> - <?php echo formatBytes($asset['file_size']); ?></p>
            <a href="/assets/<?php echo $asset['id']; ?>">View</a>
        </div>
    <?php endforeach; ?>
</div>
