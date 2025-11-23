// FILE: /app/views/projects/create.php
<div class="projects-create-page">
    <div class="page-header">
        <h1>Create New Project</h1>
        <a href="/projects" class="btn btn-secondary">← Back to Projects</a>
    </div>

    <form method="POST" action="/projects/create" class="form-card">
        <input type="hidden" name="csrf_token" value="<?php echo $this->escape($csrf_token); ?>">

        <div class="form-group">
            <label for="name">Project Name *</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label for="type">Project Type *</label>
            <select id="type" name="type" required>
                <option value="text_to_video">Text to Video</option>
                <option value="image_to_video">Image to Video</option>
                <option value="video_to_video">Video to Video (AI Transformation)</option>
                <option value="images_to_video">Images to Video (Slideshow)</option>
            </select>
        </div>

        <div class="form-group">
            <label for="platform_preset">Platform Preset *</label>
            <select id="platform_preset" name="platform_preset" required>
                <option value="youtube_horizontal">YouTube (Horizontal - 16:9)</option>
                <option value="tiktok_vertical">TikTok/Reels (Vertical - 9:16)</option>
                <option value="square_social">Square Social (1:1)</option>
                <option value="instagram_story">Instagram Story (9:16)</option>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="aspect_ratio">Aspect Ratio</label>
                <select id="aspect_ratio" name="aspect_ratio">
                    <option value="16:9">16:9 (Horizontal)</option>
                    <option value="9:16">9:16 (Vertical)</option>
                    <option value="1:1">1:1 (Square)</option>
                    <option value="4:5">4:5 (Portrait)</option>
                </select>
            </div>

            <div class="form-group">
                <label for="resolution">Resolution</label>
                <select id="resolution" name="resolution">
                    <option value="720p">720p (HD)</option>
                    <option value="1080p" selected>1080p (Full HD)</option>
                    <option value="4k">4K (Ultra HD)</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Project</button>
            <a href="/projects" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
