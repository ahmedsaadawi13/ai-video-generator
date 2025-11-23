// FILE: /app/views/home/index.php
<?php $this->setLayout(null); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Video Generator - Create Amazing Videos with AI</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body class="landing-page">
    <nav class="navbar navbar-transparent">
        <div class="container">
            <div class="navbar-brand">
                <a href="/">AI Video Generator</a>
            </div>
            <ul class="navbar-menu">
                <li><a href="/login">Sign In</a></li>
                <li><a href="/register" class="btn btn-primary">Get Started</a></li>
            </ul>
        </div>
    </nav>

    <section class="hero">
        <div class="container text-center">
            <h1>Create Amazing AI Videos in Minutes</h1>
            <p class="lead">Transform text, images, and videos into stunning AI-generated content for social media, marketing, and more.</p>
            <div class="hero-actions">
                <a href="/register" class="btn btn-primary btn-lg">Start Free Trial</a>
                <a href="#features" class="btn btn-secondary btn-lg">Learn More</a>
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <h2 class="text-center">Powerful Features</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <h3>Text to Video</h3>
                    <p>Turn your scripts and ideas into engaging video content with AI-powered generation.</p>
                </div>
                <div class="feature-card">
                    <h3>Image to Video</h3>
                    <p>Bring your still images to life with dynamic motion and AI effects.</p>
                </div>
                <div class="feature-card">
                    <h3>Video Transformation</h3>
                    <p>Stylize and transform existing videos with AI-powered effects and filters.</p>
                </div>
                <div class="feature-card">
                    <h3>Smart Templates</h3>
                    <p>Use pre-built templates for TikTok, YouTube, Instagram, and more.</p>
                </div>
                <div class="feature-card">
                    <h3>Brand Management</h3>
                    <p>Apply your brand colors, fonts, logos, and watermarks automatically.</p>
                </div>
                <div class="feature-card">
                    <h3>Team Collaboration</h3>
                    <p>Work together with your team on video projects and campaigns.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="pricing">
        <div class="container">
            <h2 class="text-center">Simple Pricing</h2>
            <div class="pricing-grid">
                <?php foreach ($plans as $plan): ?>
                    <div class="pricing-card">
                        <h3><?php echo $this->escape($plan['name']); ?></h3>
                        <div class="price">
                            $<?php echo number_format($plan['price_monthly'], 0); ?>
                            <span>/month</span>
                        </div>
                        <p><?php echo $this->escape($plan['description']); ?></p>
                        <ul class="plan-features">
                            <li><?php echo $plan['max_projects'] == -1 ? 'Unlimited' : $plan['max_projects']; ?> Projects</li>
                            <li><?php echo $plan['max_renders_per_month'] == -1 ? 'Unlimited' : $plan['max_renders_per_month']; ?> Renders/month</li>
                            <li><?php echo formatBytes($plan['max_storage_mb'] * 1024 * 1024); ?> Storage</li>
                            <li><?php echo $plan['max_team_members'] == -1 ? 'Unlimited' : $plan['max_team_members']; ?> Team Members</li>
                        </ul>
                        <a href="/register" class="btn btn-primary btn-block">Get Started</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> AI Video Generator. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
