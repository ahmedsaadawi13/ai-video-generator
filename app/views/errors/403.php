// FILE: /app/views/errors/403.php
<?php $this->setLayout(null); ?>
<!DOCTYPE html>
<html><head><title>403 - Access Denied</title><link rel="stylesheet" href="/public/css/style.css"></head>
<body class="error-page"><div class="error-container"><h1>403</h1><h2>Access Denied</h2>
<p><?php echo isset($message) ? $this->escape($message) : 'You do not have permission to access this resource.'; ?></p>
<a href="/dashboard" class="btn btn-primary">Go to Dashboard</a></div></body></html>
