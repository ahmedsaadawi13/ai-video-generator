// FILE: /app/views/scenes/edit.php
<h1>Edit Scene</h1>
<form method="POST" action="/scenes/<?php echo $scene['id']; ?>/edit">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group">
        <label>Scene Type</label>
        <select name="scene_type">
            <option value="text_only" <?php echo $scene['scene_type'] === 'text_only' ? 'selected' : ''; ?>>Text Only</option>
            <option value="text_image" <?php echo $scene['scene_type'] === 'text_image' ? 'selected' : ''; ?>>Text + Image</option>
            <option value="image_only" <?php echo $scene['scene_type'] === 'image_only' ? 'selected' : ''; ?>>Image Only</option>
            <option value="video_clip" <?php echo $scene['scene_type'] === 'video_clip' ? 'selected' : ''; ?>>Video Clip</option>
        </select>
    </div>
    <div class="form-group">
        <label>Duration (seconds)</label>
        <input type="number" name="duration" value="<?php echo $scene['duration']; ?>" min="1">
    </div>
    <div class="form-group">
        <label>Prompt</label>
        <textarea name="prompt" rows="3"><?php echo $this->escape($scene['prompt']); ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>
