// FILE: /app/views/projects/edit.php
<div class="projects-edit-page">
    <div class="page-header">
        <h1>Edit Project</h1>
        <a href="/projects/<?php echo $project['id']; ?>" class="btn btn-secondary">← Back</a>
    </div>

    <form method="POST" action="/projects/<?php echo $project['id']; ?>/edit" class="form-card">
        <input type="hidden" name="csrf_token" value="<?php echo $this->escape($csrf_token); ?>">

        <div class="form-group">
            <label for="name">Project Name *</label>
            <input type="text" id="name" name="name" value="<?php echo $this->escape($project['name']); ?>" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"><?php echo $this->escape($project['description']); ?></textarea>
        </div>

        <div class="form-group">
            <label for="platform_preset">Platform Preset</label>
            <select id="platform_preset" name="platform_preset">
                <option value="youtube_horizontal" <?php echo $project['platform_preset'] === 'youtube_horizontal' ? 'selected' : ''; ?>>YouTube (Horizontal)</option>
                <option value="tiktok_vertical" <?php echo $project['platform_preset'] === 'tiktok_vertical' ? 'selected' : ''; ?>>TikTok/Reels (Vertical)</option>
                <option value="square_social" <?php echo $project['platform_preset'] === 'square_social' ? 'selected' : ''; ?>>Square Social</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="aspect_ratio">Aspect Ratio</label>
                <select id="aspect_ratio" name="aspect_ratio">
                    <option value="16:9" <?php echo $project['aspect_ratio'] === '16:9' ? 'selected' : ''; ?>>16:9</option>
                    <option value="9:16" <?php echo $project['aspect_ratio'] === '9:16' ? 'selected' : ''; ?>>9:16</option>
                    <option value="1:1" <?php echo $project['aspect_ratio'] === '1:1' ? 'selected' : ''; ?>>1:1</option>
                </select>
            </div>

            <div class="form-group">
                <label for="resolution">Resolution</label>
                <select id="resolution" name="resolution">
                    <option value="720p" <?php echo $project['resolution'] === '720p' ? 'selected' : ''; ?>>720p</option>
                    <option value="1080p" <?php echo $project['resolution'] === '1080p' ? 'selected' : ''; ?>>1080p</option>
                    <option value="4k" <?php echo $project['resolution'] === '4k' ? 'selected' : ''; ?>>4K</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/projects/<?php echo $project['id']; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
