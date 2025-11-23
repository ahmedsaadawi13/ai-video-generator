// FILE: /app/views/layouts/default.php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Video Generator - <?php echo isset($pageTitle) ? $this->escape($pageTitle) : 'Dashboard'; ?></title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
    <?php if (isAuthenticated()): ?>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-brand">
                    <a href="/dashboard">AI Video Generator</a>
                </div>
                <ul class="navbar-menu">
                    <li><a href="/dashboard">Dashboard</a></li>
                    <li><a href="/projects">Projects</a></li>
                    <li><a href="/assets">Assets</a></li>
                    <li><a href="/templates">Templates</a></li>
                    <li><a href="/renders">Renders</a></li>
                    <li><a href="/videos">Videos</a></li>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'tenant_admin'): ?>
                        <li class="dropdown">
                            <a href="#" class="dropbtn">Settings</a>
                            <div class="dropdown-content">
                                <a href="/tenant/settings">Tenant Settings</a>
                                <a href="/tenant/brand">Brand Settings</a>
                                <a href="/users">Team Members</a>
                                <a href="/subscription">Subscription</a>
                                <a href="/api-keys">API Keys</a>
                            </div>
                        </li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'platform_admin'): ?>
                        <li><a href="/admin">Admin</a></li>
                    <?php endif; ?>
                    <li class="dropdown">
                        <a href="#" class="dropbtn"><?php echo $this->escape($_SESSION['user_name'] ?? 'User'); ?></a>
                        <div class="dropdown-content">
                            <a href="/analytics">Analytics</a>
                            <a href="/logout">Logout</a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>

    <main class="main-content">
        <div class="container">
            <?php if ($flash = getFlash('success')): ?>
                <div class="alert alert-success"><?php echo $flash; ?></div>
            <?php endif; ?>

            <?php if ($flash = getFlash('error')): ?>
                <div class="alert alert-error"><?php echo $flash; ?></div>
            <?php endif; ?>

            <?php echo $content; ?>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> AI Video Generator. All rights reserved.</p>
        </div>
    </footer>

    <script src="/public/js/app.js"></script>
</body>
</html>
