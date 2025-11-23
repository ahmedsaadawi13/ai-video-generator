// FILE: /app/views/tenant/brand.php
<h1>Brand Settings</h1>
<form method="POST" action="/tenant/brand">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group"><label>Primary Color</label><input type="color" name="primary_color" value="<?php echo $this->escape($brandSettings['primary_color']); ?>"></div>
    <div class="form-group"><label>Secondary Color</label><input type="color" name="secondary_color" value="<?php echo $this->escape($brandSettings['secondary_color']); ?>"></div>
    <div class="form-group">
        <label>Default Font</label>
        <select name="default_font">
            <option value="Arial" <?php echo $brandSettings['default_font'] === 'Arial' ? 'selected' : ''; ?>>Arial</option>
            <option value="Roboto" <?php echo $brandSettings['default_font'] === 'Roboto' ? 'selected' : ''; ?>>Roboto</option>
            <option value="Montserrat" <?php echo $brandSettings['default_font'] === 'Montserrat' ? 'selected' : ''; ?>>Montserrat</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Save Brand Settings</button>
</form>
