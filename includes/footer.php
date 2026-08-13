<?php
/**
 * =============================================================================
 * Application Footer
 * =============================================================================
 * Closes the content area opened by header.php, renders the footer and loads
 * Bootstrap 5 JavaScript plus the custom main.js script.
 * =============================================================================
 */
?>
            </div><!-- /.container-fluid -->
        </main>

        <footer class="app-footer">
            &copy; <?php echo date('Y'); ?> <?php echo e(get_setting('site_name', 'Lab Management')); ?>
        </footer>
    </div><!-- /.app-main -->
</div><!-- /.app-wrapper -->

<!-- Confirm action modal (used by data-confirm forms) -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalTitle">Please confirm</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="confirmModalMessage"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmModalOk">Yes, proceed</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?php echo asset_url('js/main.js'); ?>?v=<?php echo APP_VERSION; ?>"></script>
</body>
</html>
