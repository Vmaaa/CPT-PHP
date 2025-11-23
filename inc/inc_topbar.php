<?php
$name = htmlspecialchars($AUTH['acco_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars(role_human_readable($AUTH['acco_role'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<script>
    window.currentUserId = <?php echo json_encode($AUTH['acco_id'] ?? null); ?>;
</script>
