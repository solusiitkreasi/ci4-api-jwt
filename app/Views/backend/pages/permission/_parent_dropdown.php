<?php
// Dropdown parent menu untuk permission
use App\Models\PermissionModel;
$permissionModel = new PermissionModel();
$parents = $permissionModel->where('parent_id', 0)->findAll();
?>
<select class="form-select" id="parent_id" name="parent_id">
    <option value="">-- Pilih Parent Menu --</option>
    <?php foreach ($parents as $parent): ?>
        <option value="<?= $parent['id'] ?>" <?= (isset($permission['parent_id']) && $permission['parent_id'] == $parent['id']) ? 'selected' : '' ?>><?= esc($parent['name']) ?></option>
    <?php endforeach; ?>
</select>
