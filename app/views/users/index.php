// FILE: /app/views/users/index.php
<h1>Team Members</h1>
<a href="/users/create" class="btn btn-primary">+ Add User</a>
<table class="table">
    <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?php echo $this->escape($u['first_name'] . ' ' . $u['last_name']); ?></td>
            <td><?php echo $this->escape($u['email']); ?></td>
            <td><?php echo str_replace('_', ' ', ucwords($u['role'])); ?></td>
            <td><span class="badge badge-<?php echo $u['status']; ?>"><?php echo $u['status']; ?></span></td>
            <td>
                <a href="/users/<?php echo $u['id']; ?>/edit">Edit</a> |
                <form method="POST" action="/users/<?php echo $u['id']; ?>/delete" style="display:inline;">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <button type="submit" class="link-button">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
