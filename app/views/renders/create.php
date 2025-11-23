// FILE: /app/views/renders/create.php
<h1>Create Render Job</h1>
<form method="POST" action="/renders/create">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group">
        <label>Select Project</label>
        <select name="project_id" required>
            <?php foreach ($projects as $project): ?>
                <option value="<?php echo $project['id']; ?>"><?php echo $this->escape($project['name']); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Start Render</button>
</form>
