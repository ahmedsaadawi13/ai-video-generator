// FILE: /app/views/tenant/settings.php
<h1>Tenant Settings</h1>
<form method="POST" action="/tenant/settings">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group"><label>Company Name</label><input type="text" name="name" value="<?php echo $this->escape($tenant['name']); ?>" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo $this->escape($tenant['email']); ?>" required></div>
    <div class="form-group"><label>Website</label><input type="url" name="website" value="<?php echo $this->escape($tenant['website']); ?>"></div>
    <div class="form-group">
        <label>Timezone</label>
        <select name="timezone">
            <option value="UTC" <?php echo $tenant['timezone'] === 'UTC' ? 'selected' : ''; ?>>UTC</option>
            <option value="America/New_York" <?php echo $tenant['timezone'] === 'America/New_York' ? 'selected' : ''; ?>>America/New_York</option>
            <option value="America/Los_Angeles" <?php echo $tenant['timezone'] === 'America/Los_Angeles' ? 'selected' : ''; ?>>America/Los_Angeles</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Save Settings</button>
</form>
