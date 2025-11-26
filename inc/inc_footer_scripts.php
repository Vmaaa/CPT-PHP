<!-- /inc/inc_footer_scripts.php -->
<script>
    window.APP_BASE_URL = "<?= BASE_URL ?>";
    window.APP_JS_URL = "<?= JS_URL ?>";
</script>
<script src="<?= JS_URL ?>/cookie_manager.js" defer></script>
<script src="<?= JS_URL ?>/general.js" defer></script>
<script src="<?= JS_URL ?>/swal.js" defer></script>

<?php
if (!empty($pageScript)) {
    echo '<script src="' . JS_URL . '/' . $pageScript . '" defer></script>';
}
?>
