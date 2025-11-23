<?php
$name = htmlspecialchars($AUTH['acco_name'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$role = htmlspecialchars(role_human_readable($AUTH['acco_role'] ?? ''), ENT_QUOTES, 'UTF-8');
?>
<script>
    window.currentUserId = <?php echo json_encode($AUTH['acco_id'] ?? null); ?>;
</script>
<!-- Header -->
<div class="header">
    <h1 id="section-title"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
    <div class="user-menu">
        <div class="user-info">
            <div class="user-name"><?php echo $name; ?></div>
            <div class="user-role"><?php echo $role; ?></div>
        </div>
        <button class="logout-btn" id="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </button>
    </div>
</div>
