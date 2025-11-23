// FILE: /app/views/scenes/create.php
<h1>Add Scene to <?php echo $this->escape($project['name']); ?></h1>
<form method="POST" action="/projects/<?php echo $project['id']; ?>/scenes/create">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group">
        <label>Scene Type</label>
        <select name="scene_type">
            <option value="text_only">Text Only</option>
            <option value="text_image">Text + Image</option>
            <option value="image_only">Image Only</option>
            <option value="video_clip">Video Clip</option>
        </select>
    </div>
    <div class="form-group">
        <label>Duration (seconds)</label>
        <input type="number" name="duration" value="5" min="1">
    </div>
    <div class="form-group">
        <label>Prompt/Description</label>
        <textarea name="prompt" rows="3"></textarea>
    </div>
    <div class="form-group">
        <label>Text Content</label>
        <textarea name="text_content" rows="3"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Add Scene</button>
</form>
