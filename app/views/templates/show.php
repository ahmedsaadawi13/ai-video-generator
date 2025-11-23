// FILE: /app/views/templates/show.php
<h1><?php echo $this->escape($template['name']); ?></h1>
<p><?php echo $this->escape($template['description']); ?></p>
<p>Category: <?php echo $this->escape($template['category']); ?></p>
<h3>Scenes</h3>
<table class="table">
    <?php foreach ($template['scenes'] as $scene): ?>
        <tr>
            <td><?php echo $scene['position']; ?></td>
            <td><?php echo $scene['scene_type']; ?></td>
            <td><?php echo $scene['duration']; ?>s</td>
        </tr>
    <?php endforeach; ?>
</table>
