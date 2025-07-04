<?php
// Fungsi rekursif untuk render permission tree sebagai sortable list dengan drag handle
function renderPermissionTree($permissions, $role_permission_ids, $level = 0, $parentId = null) {
    $ulId = $parentId ? 'perm-ul-'.$parentId : 'perm-ul-root';
    echo '<ul class="permission-list" id="'.$ulId.'" data-parent="'.($parentId ?? '').'">';
    foreach ($permissions as $perm) {
        $isParent = empty($perm['parent_id']) || $perm['parent_id'] == 0;
        $labelStyle = $isParent ? 'font-weight:bold;' : '';
        $dataParent = $parentId ? 'data-parent="'.$parentId.'"' : '';
        echo '<li class="permission-item" data-id="'.$perm['id'].'" '.$dataParent.' style="margin-left:'.($level*20).'px">';
        echo '<span class="drag-handle" title="Geser"><i class="bi bi-list"></i>☰</span>';
        echo '<div class="form-check d-inline-block">';
        echo '<input class="form-check-input" type="checkbox" name="permission_ids[]" value="'.htmlspecialchars($perm['id']).'" id="perm'.$perm['id'].'" '.(in_array($perm['id'], $role_permission_ids) ? ' checked' : '').' >';
        echo '<label class="form-check-label" for="perm'.$perm['id'].'" style="'.$labelStyle.'">'.htmlspecialchars($perm['name']).' <small>('.htmlspecialchars($perm['slug']).')</small></label>';
        echo '</div>';
        if (!empty($perm['children'])) {
            renderPermissionTree($perm['children'], $role_permission_ids, $level+1, $perm['id']);
        }
        echo '</li>';
    }
    echo '</ul>';
}
