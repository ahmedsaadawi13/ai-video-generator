// FILE: /app/views/renders/show.php
<h1>Render Job #<?php echo $renderJob['id']; ?></h1>
<p>Type: <?php echo str_replace('_', ' ', $renderJob['job_type']); ?></p>
<p>Status: <span class="badge badge-<?php echo $renderJob['status']; ?>"><?php echo $renderJob['status']; ?></span></p>
<p>Progress: <?php echo $renderJob['progress']; ?>%</p>
<p>Project: <?php echo $this->escape($renderJob['project_name']); ?></p>
<?php if ($renderJob['error_message']): ?>
    <div class="alert alert-error"><?php echo $this->escape($renderJob['error_message']); ?></div>
    <form method="POST" action="/renders/<?php echo $renderJob['id']; ?>/retry">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <button type="submit" class="btn btn-primary">Retry</button>
    </form>
<?php endif; ?>
<?php if ($video): ?>
    <h3>Output Video</h3>
    <p><a href="/videos/<?php echo $video['id']; ?>">View Video</a></p>
<?php endif; ?>
