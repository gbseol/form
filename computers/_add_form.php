<?php
/**
 * =============================================================================
 * Computers - Add Form (compatibility wrapper)
 * =============================================================================
 * The add page now uses the shared simplified `_form.php` partial. This file
 * is kept so that any direct reference to the old partial name keeps working.
 *
 * Requires:
 *   $v    - array of current values (or empty defaults)
 *   $labs - list of labs for the dropdown
 * =============================================================================
 */

$isEdit = $isEdit ?? false;
include __DIR__ . '/_form.php';
