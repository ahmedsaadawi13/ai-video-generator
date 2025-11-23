// FILE: /app/views/api-keys/index.php
<h1>API Keys</h1>
<?php if (isset($_SESSION['new_api_key'])): ?>
    <div class="alert alert-success">
        <p><strong>New API Key (copy now, won't be shown again):</strong></p>
        <code><?php echo $_SESSION['new_api_key']; ?></code>
        <?php unset($_SESSION['new_api_key']); ?>
    </div>
<?php endif; ?>
<form method="POST" action="/api-keys/generate">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <input type="text" name="name" placeholder="API Key Name" required>
    <button type="submit" class="btn btn-primary">Generate New Key</button>
</form>
<table class="table">
    <tr><th>Name</th><th>Created</th><th>Last Used</th><th>Status</th><th>Actions</th></tr>
    <?php foreach ($apiKeys as $key): ?>
        <tr>
            <td><?php echo $this->escape($key['name']); ?></td>
            <td><?php echo formatDate($key['created_at']); ?></td>
            <td><?php echo $key['last_used_at'] ? timeAgo($key['last_used_at']) : 'Never'; ?></td>
            <td><span class="badge badge-<?php echo $key['is_active'] ? 'success' : 'inactive'; ?>"><?php echo $key['is_active'] ? 'Active' : 'Revoked'; ?></span></td>
            <td>
                <?php if ($key['is_active']): ?>
                    <form method="POST" action="/api-keys/<?php echo $key['id']; ?>/revoke" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        <button type="submit" class="link-button">Revoke</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
