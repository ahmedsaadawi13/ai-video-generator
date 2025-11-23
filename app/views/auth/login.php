// FILE: /app/views/auth/login.php
<?php $this->setLayout(null); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Video Generator</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>AI Video Generator</h1>
            <h2>Sign In</h2>

            <?php if ($flash = getFlash('success')): ?>
                <div class="alert alert-success"><?php echo $flash; ?></div>
            <?php endif; ?>

            <?php if ($flash = getFlash('error')): ?>
                <div class="alert alert-error"><?php echo $flash; ?></div>
            <?php endif; ?>

            <form method="POST" action="/login">
                <input type="hidden" name="csrf_token" value="<?php echo $this->escape($csrf_token); ?>">

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>

            <p class="auth-footer">
                Don't have an account? <a href="/register">Sign up</a>
            </p>

            <div class="demo-credentials">
                <p><strong>Demo Credentials:</strong></p>
                <p>Email: <code>john@example.com</code></p>
                <p>Password: <code>password</code></p>
            </div>
        </div>
    </div>
</body>
</html>
