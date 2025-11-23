# GitHub Setup Commands - AI Video Generator

This guide provides step-by-step instructions to initialize Git, create a GitHub repository, and push the AI Video Generator codebase.

## Prerequisites

- Git installed (`git --version`)
- GitHub account
- GitHub CLI installed (optional but recommended)

## Step 1: Initialize Git Repository

```bash
cd /path/to/ai-video-generator

# Initialize Git repository
git init

# Add all files to staging
git add .

# Create initial commit
git commit -m "Initial commit: Complete AI Video Generator SaaS platform

- Custom lightweight MVC framework (PHP 7.0+)
- Multi-tenant architecture with role-based access control
- Project management with scene-based timeline editing
- Media library for images, videos, and audio
- Video template system with presets
- Render queue with background processing
- Subscription system with usage tracking and quota enforcement
- Billing system with invoices and payments
- Public REST API for video generation
- Analytics and reporting dashboard
- Complete documentation and testing suite"
```

## Step 2: Create GitHub Repository

### Option A: Using GitHub CLI (Recommended)

```bash
# Install GitHub CLI (if not installed)
# Ubuntu/Debian
sudo apt install gh

# macOS
brew install gh

# Authenticate with GitHub
gh auth login

# Create repository (choose visibility)
# For public repository:
gh repo create ai-video-generator --public --source=. --remote=origin

# For private repository:
gh repo create ai-video-generator --private --source=. --remote=origin
```

### Option B: Using GitHub Web Interface

1. Go to https://github.com/new
2. Repository name: `ai-video-generator`
3. Description: "Multi-tenant SaaS platform for AI-powered video generation"
4. Choose Public or Private
5. Do NOT initialize with README (we already have one)
6. Click "Create repository"

Then connect your local repository:

```bash
# Add GitHub as remote origin
git remote add origin https://github.com/YOUR_USERNAME/ai-video-generator.git

# Verify remote
git remote -v
```

## Step 3: Push to GitHub

```bash
# Rename default branch to main (if needed)
git branch -M main

# Push to remote repository
git push -u origin main
```

## Step 4: Verify Upload

Visit your repository: `https://github.com/YOUR_USERNAME/ai-video-generator`

You should see:
- README.md displayed on home page
- All directories and files
- Initial commit message

## Step 5: Add .gitignore (Optional)

Create `.gitignore` file:

```bash
cat > .gitignore << 'EOF'
# Environment files
.env

# Storage (uploads and outputs)
storage/uploads/*
storage/outputs/*
!storage/uploads/.gitkeep
!storage/outputs/.gitkeep

# Logs
*.log

# OS files
.DS_Store
Thumbs.db

# IDE files
.idea/
.vscode/
*.swp
*.swo

# Temporary files
*.tmp
*.temp
EOF

# Create .gitkeep files to preserve directory structure
touch storage/uploads/.gitkeep
touch storage/outputs/.gitkeep

# Add and commit .gitignore
git add .gitignore storage/uploads/.gitkeep storage/outputs/.gitkeep
git commit -m "Add .gitignore and preserve storage directory structure"
git push origin main
```

## Step 6: Create Repository Description and Topics

### Using GitHub CLI:

```bash
# Set repository description
gh repo edit --description "Multi-tenant SaaS platform for AI-powered video generation built with PHP & MySQL"

# Add topics (tags)
gh repo edit --add-topic "php,mysql,saas,multi-tenant,video-generation,ai,mvc,rest-api"
```

### Using GitHub Web Interface:

1. Go to repository Settings
2. Edit "About" section
3. Add description
4. Add topics: php, mysql, saas, multi-tenant, video-generation, ai, mvc, rest-api

## Step 7: Configure Repository Settings

### Branch Protection (Recommended)

```bash
# Protect main branch
gh api repos/:owner/:repo/branches/main/protection \
  --method PUT \
  --field required_status_checks='null' \
  --field enforce_admins=true \
  --field required_pull_request_reviews='{"required_approving_review_count":1}' \
  --field restrictions='null'
```

### Enable Issues and Wiki

1. Go to repository Settings
2. Under "Features", enable:
   - Issues
   - Wiki
   - Projects (optional)

## Step 8: Create README Badges (Optional)

Add badges to top of README.md:

```markdown
# AI Video Generator

[![PHP Version](https://img.shields.io/badge/PHP-7.0%2B-blue)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)](https://mysql.com)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](CONTRIBUTING.md)

[Rest of README...]
```

## Step 9: Create Additional Files

### LICENSE

Create MIT License:

```bash
cat > LICENSE << 'EOF'
MIT License

Copyright (c) 2025 AI Video Generator

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
EOF

git add LICENSE
git commit -m "Add MIT License"
git push origin main
```

### CONTRIBUTING.md

```bash
cat > CONTRIBUTING.md << 'EOF'
# Contributing to AI Video Generator

We welcome contributions! Please follow these guidelines.

## How to Contribute

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests (`php tests/BasicTests.php`)
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

## Code Standards

- Follow PSR-12 coding standards
- Add comments for complex logic
- Write unit tests for new features
- Update documentation

## Reporting Issues

Use GitHub Issues to report bugs or request features.

Include:
- PHP version
- MySQL version
- Steps to reproduce
- Expected vs actual behavior
EOF

git add CONTRIBUTING.md
git commit -m "Add contributing guidelines"
git push origin main
```

## Step 10: Create GitHub Release

### Tag the release:

```bash
# Create annotated tag
git tag -a v1.0.0 -m "Release version 1.0.0 - Initial release

Complete multi-tenant SaaS platform with:
- Multi-tenant architecture
- Video generation from text, images, videos
- Template system
- Subscription & billing
- Public REST API
- Background job processing
- Complete documentation"

# Push tag to GitHub
git push origin v1.0.0
```

### Create Release on GitHub:

```bash
# Using GitHub CLI
gh release create v1.0.0 \
  --title "AI Video Generator v1.0.0" \
  --notes "**Initial Release**

## Features
- Multi-tenant SaaS architecture
- Text to Video generation
- Image to Video conversion
- Video transformation
- Multi-image slideshow creation
- Project management with scene editing
- Media library
- Template system
- Subscription & billing
- Public REST API
- Analytics dashboard

## Documentation
- Complete installation guide
- API documentation
- Deployment guide
- Testing checklist
- Code review

## Demo Credentials
See README.md for demo credentials.

## Requirements
- PHP 7.0+
- MySQL 5.7+

Full changelog and documentation available in repository."
```

## Step 11: Set Up GitHub Actions (Optional)

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: root
          MYSQL_DATABASE: ai_video_generator
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=3

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '7.4'
          extensions: pdo, pdo_mysql, gd

      - name: Import Database
        run: mysql -uroot -proot -h127.0.0.1 ai_video_generator < database.sql

      - name: Run Tests
        run: php tests/BasicTests.php
```

## Quick Reference Commands

```bash
# Clone repository
git clone https://github.com/YOUR_USERNAME/ai-video-generator.git

# Create new branch
git checkout -b feature-name

# Stage changes
git add .

# Commit changes
git commit -m "Description of changes"

# Push changes
git push origin branch-name

# Pull latest changes
git pull origin main

# View commit history
git log --oneline

# Check status
git status

# View differences
git diff
```

## Collaboration Workflow

1. **Fork** the repository
2. **Clone** your fork: `git clone https://github.com/YOUR_USERNAME/ai-video-generator.git`
3. **Add upstream**: `git remote add upstream https://github.com/ORIGINAL_OWNER/ai-video-generator.git`
4. **Create branch**: `git checkout -b feature-name`
5. **Make changes** and commit
6. **Push to fork**: `git push origin feature-name`
7. **Create Pull Request** on GitHub
8. **Code review** and merge

## Keeping Fork Updated

```bash
# Fetch upstream changes
git fetch upstream

# Merge upstream main into your main
git checkout main
git merge upstream/main

# Push to your fork
git push origin main
```

## Repository Maintenance

### Regular Tasks

```bash
# Tag new releases
git tag -a v1.1.0 -m "Release notes"
git push origin v1.1.0

# Delete old branches
git branch -d old-feature
git push origin --delete old-feature

# Clean up local repository
git gc
git prune
```

## Support

For questions or issues with Git/GitHub setup:
- GitHub Documentation: https://docs.github.com
- Git Documentation: https://git-scm.com/doc

---

**Repository Created!** 🎉

Your AI Video Generator is now on GitHub and ready for collaboration!

Visit: `https://github.com/YOUR_USERNAME/ai-video-generator`
