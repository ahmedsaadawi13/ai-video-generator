// FILE: /app/views/templates/index.php
<h1>Video Templates</h1>
<a href="/templates/create" class="btn btn-primary">+ Create Template</a>
<div class="templates-grid">
    <?php foreach ($templates as $template): ?>
        <div class="template-card">
            <h3><?php echo $this->escape($template['name']); ?></h3>
            <p><?php echo $this->escape($template['description']); ?></p>
            <p>Category: <?php echo $this->escape($template['category']); ?></p>
            <p>Used <?php echo $template['usage_count']; ?> times</p>
            <a href="/templates/<?php echo $template['id']; ?>">View</a>
        </div>
    <?php endforeach; ?>
</div>
