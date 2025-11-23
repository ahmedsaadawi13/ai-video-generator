// FILE: /app/views/users/edit.php
<h1>Edit User</h1>
<form method="POST" action="/users/<?php echo $editUser['id']; ?>/edit">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group"><label>First Name</label><input type="text" name="first_name" value="<?php echo $this->escape($editUser['first_name']); ?>" required></div>
    <div class="form-group"><label>Last Name</label><input type="text" name="last_name" value="<?php echo $this->escape($editUser['last_name']); ?>" required></div>
    <div class="form-group">
        <label>Role</label>
        <select name="role">
            <option value="tenant_admin" <?php echo $editUser['role'] === 'tenant_admin' ? 'selected' : ''; ?>>Tenant Admin</option>
            <option value="editor" <?php echo $editUser['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
            <option value="viewer" <?php echo $editUser['role'] === 'viewer' ? 'selected' : ''; ?>>Viewer</option>
        </select>
    </div>
    <div class="form-group"><label>Password (leave blank to keep current)</label><input type="password" name="password"></div>
    <button type="submit" class="btn btn-primary">Save Changes</button>
</form>
