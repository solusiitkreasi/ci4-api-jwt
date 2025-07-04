<?php
// Dropdown semua permission sebagai parent
use App\Models\PermissionModel;
$permissionModel = new PermissionModel();
$allParents = $permissionModel->findAll();
?>
<select class="form-select" id="allparent_id" name="parent_id">
    <option value="">-- Pilih Parent Permission --</option>
    <?php foreach ($allParents as $parent): ?>
        <option value="<?= $parent['id'] ?>" <?= (isset($permission['parent_id']) && $permission['parent_id'] == $parent['id']) ? 'selected' : '' ?>><?= esc($parent['name']) ?></option>
    <?php endforeach; ?>
</select>
