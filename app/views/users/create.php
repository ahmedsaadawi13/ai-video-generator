// FILE: /app/views/users/create.php
<h1>Add Team Member</h1>
<form method="POST" action="/users/create">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group"><label>First Name</label><input type="text" name="first_name" required></div>
    <div class="form-group"><label>Last Name</label><input type="text" name="last_name" required></div>
    <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
    <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
    <div class="form-group">
        <label>Role</label>
        <select name="role">
            <option value="editor">Editor</option>
            <option value="viewer">Viewer</option>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Add User</button>
</form>
