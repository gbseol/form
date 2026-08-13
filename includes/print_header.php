<?php
/**
 * =============================================================================
 * Print Layout Header
 * =============================================================================
 * Minimal HTML head for printable report / detail pages. Used together with
 * print_footer.php. Pages using this header must set $page_title.
 * =============================================================================
 */

require_once __DIR__ . '/auth.php';
require_login();

$site_name = get_setting('site_name', 'Lab Management');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($page_title ?? 'Print'); ?> | <?php echo e($site_name); ?></title>
    <meta name="robots" content="noindex, nofollow">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo asset_url('css/print.css'); ?>?v=<?php echo APP_VERSION; ?>" rel="stylesheet">
</head>
<body>
<div class="print-toolbar no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">
        <i class="bi bi-printer"></i> Print / Save as PDF
    </button>
    <a href="javascript:history.back()" class="btn btn-outline-secondary">Go Back</a>
</div>
<div class="print-body">
