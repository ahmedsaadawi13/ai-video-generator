// FILE: /app/views/auth/register.php
<?php $this->setLayout(null); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AI Video Generator</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-box">
            <h1>AI Video Generator</h1>
            <h2>Create Account</h2>

            <?php if ($flash = getFlash('error')): ?>
                <div class="alert alert-error"><?php echo $flash; ?></div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <input type="hidden" name="csrf_token" value="<?php echo $this->escape($csrf_token); ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="company_name">Company Name</label>
                    <input type="text" id="company_name" name="company_name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Create Account</button>
            </form>

            <p class="auth-footer">
                Already have an account? <a href="/login">Sign in</a>
            </p>
        </div>
    </div>
</body>
</html>
