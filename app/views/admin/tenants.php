// FILE: /app/views/admin/tenants.php
<h1>All Tenants</h1>
<table class="table">
    <tr><th>Name</th><th>Email</th><th>Status</th><th>Created</th></tr>
    <?php foreach ($tenants as $t): ?>
        <tr>
            <td><?php echo $this->escape($t['name']); ?></td>
            <td><?php echo $this->escape($t['email']); ?></td>
            <td><span class="badge badge-<?php echo $t['status']; ?>"><?php echo $t['status']; ?></span></td>
            <td><?php echo formatDate($t['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
</table>
