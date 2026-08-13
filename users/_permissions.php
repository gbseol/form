<?php
/**
 * =============================================================================
 * Users - Module Permissions partial
 * =============================================================================
 * Renders the "Module Permissions" section with one checkbox per module.
 * Only a Super Admin sees and can edit this; Super Admin accounts themselves
 * always have full access, so their checkboxes are not shown as editable.
 *
 * Requires:
 *   $selectedModules     - array of module keys currently enabled for the user
 *   $isSuperAdminAccount - bool: is the account being edited a Super Admin?
 * =============================================================================
 */

if (!defined('IN_APP')) {
    http_response_code(403);
    exit('Direct access is not allowed.');
}

$isSuperAdminAccount = $isSuperAdminAccount ?? false;
$canEditPerms        = has_role('super_admin') && !$isSuperAdminAccount;
?>

<div class="card mb-3">
    <div class="card-header"><i class="bi bi-shield-lock me-1"></i> Module Permissions</div>
    <div class="card-body">
        <?php if (!$canEditPerms): ?>
            <p class="text-muted small mb-0">Super Admin accounts always have access to every module.</p>
        <?php else: ?>
            <p class="text-muted small mb-3">Tick the modules this account may open. Unticked modules are hidden completely from this user.</p>
            <div class="row g-2">
                <?php foreach (module_list() as $key => $label): ?>
                    <div class="col-6 col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?php echo e($key); ?>"
                                   id="perm_<?php echo e($key); ?>"
                                   <?php echo in_array($key, $selectedModules, true) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="perm_<?php echo e($key); ?>"><?php echo e($label); ?></label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="form-text mt-2">Unchecking every box blocks access to all modules.</div>
        <?php endif; ?>
    </div>
</div>
