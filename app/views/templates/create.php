// FILE: /app/views/templates/create.php
<h1>Create Template</h1>
<form method="POST" action="/templates/create">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" required>
    </div>
    <div class="form-group">
        <label>Description</label>
        <textarea name="description" rows="3"></textarea>
    </div>
    <div class="form-group">
        <label>Category</label>
        <select name="category">
            <option value="product_promo">Product Promo</option>
            <option value="tiktok_short">TikTok Short</option>
            <option value="youtube_intro">YouTube Intro</option>
            <option value="quote_video">Quote Video</option>
        </select>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_public"> Make Public</label>
    </div>
    <button type="submit" class="btn btn-primary">Create</button>
</form>
