// FILE: /app/views/assets/upload.php
<h1>Upload Asset</h1>
<form method="POST" action="/assets/upload" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group">
        <label>File</label>
        <input type="file" name="file" required accept="image/*,video/*,audio/*">
    </div>
    <div class="form-group">
        <label>Name</label>
        <input type="text" name="name" placeholder="Optional">
    </div>
    <div class="form-group">
        <label>Tags</label>
        <input type="text" name="tags" placeholder="Comma-separated tags">
    </div>
    <button type="submit" class="btn btn-primary">Upload</button>
</form>
