// FILE: /app/views/admin/create-plan.php
<h1>Create Plan</h1>
<form method="POST" action="/admin/plans/create">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
    <div class="form-group"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-group"><label>Slug</label><input type="text" name="slug" required></div>
    <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
    <div class="form-group"><label>Monthly Price</label><input type="number" name="price_monthly" step="0.01" value="0"></div>
    <div class="form-group"><label>Yearly Price</label><input type="number" name="price_yearly" step="0.01" value="0"></div>
    <div class="form-group"><label>Max Projects (-1 = unlimited)</label><input type="number" name="max_projects" value="-1"></div>
    <div class="form-group"><label>Max Renders/Month (-1 = unlimited)</label><input type="number" name="max_renders_per_month" value="-1"></div>
    <div class="form-group"><label>Max Storage MB (-1 = unlimited)</label><input type="number" name="max_storage_mb" value="-1"></div>
    <div class="form-group"><label>Max Team Members (-1 = unlimited)</label><input type="number" name="max_team_members" value="-1"></div>
    <button type="submit" class="btn btn-primary">Create Plan</button>
</form>
