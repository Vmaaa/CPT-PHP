<?php
$role = $AUTH['acco_role'];
$activePage = $activePage ?? '';

$dashboardModuleAdmin = ['DASHBOARD' => ['url' => 'pages/dashboard.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard']];
$dashboardModuleProfessor = ['DASHBOARD' => ['url' => 'pages/dashboard_professor.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard']];
$dashboardModuleStudent = ['DASHBOARD' => ['url' => 'pages/dashboard_student.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard']];
$advisedModule   = ['ASESORADOS' => ['url' => 'pages/advised.php',  'icon' => 'fas fa-user-friends',   'label' => 'Asesorados']];
$revisionModule  = ['REVISIONES' => ['url' => 'pages/revisions.php', 'icon' => 'fas fa-check-double',   'label' => 'Revisiones']];
$projectModuleProfessor   = ['PROYECTOS'  => ['url' => 'pages/projects.php',  'icon' => 'fas fa-folder-open',    'label' => 'Proyectos']];
$projectModuleStudent = ['PROYECTO'  => ['url' => 'pages/projects_student.php',  'icon' => 'fas fa-folder-open',    'label' => 'Proyecto']];
$classModuleAdmin        = ['CLASES'     => ['url' => 'pages/classes_admin.php',   'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Clases']];
$classModuleProfessor     = ['CLASES'     => ['url' => 'pages/classes.php',   'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Clases']];
$classModuleStudent       = ['CLASES'     => ['url' => 'pages/classes_student.php',   'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Clases']];


$userModule   = ['USUARIOS' => ['url' => 'pages/user.php',   'icon' => 'fas fa-user-cog',      'label' => 'Usuarios']];
$accountModule = ['CUENTA'    => ['url' => 'pages/account.php', 'icon' => 'fas fa-user-circle',   'label' => 'Mi Cuenta']];
$logoutModule  = ['CERRAR_SESION' => ['icon' => 'fas fa-sign-out-alt',  'label' => 'Cerrar Sesión']];

$rolePermissions = [
  'admin' => array_merge($dashboardModuleAdmin, $advisedModule, $revisionModule, $projectModuleProfessor, $classModuleAdmin, $userModule, $accountModule),
  'student' => array_merge($dashboardModuleStudent, $projectModuleStudent, $classModuleStudent, $accountModule),
  'professor' => array_merge($dashboardModuleProfessor, $revisionModule, $projectModuleProfessor, $classModuleProfessor, $accountModule),
];

$allowedModules = $rolePermissions[$role] ?? [];
?>

<nav class="sidebar">
  <div class="nav-links">
    <?php foreach ($allowedModules as $pageKey => $module): ?>
      <a href="<?= url($module['url']) ?>" class="nav-item <?= $activePage === $pageKey ? 'active' : '' ?>" data-page="<?= strtolower(str_replace(' ', '-', $module['label'])) ?>">
        <i class="<?= $module['icon'] ?> nav-icon"></i>
        <span><?= $module['label'] ?></span>
      </a>
    <?php endforeach; ?>
    <!-- Logout Link -->
    <a href="#" class="nav-item" id="logout-btn">
      <i class="<?= $logoutModule['CERRAR_SESION']['icon'] ?> nav-icon"></i>
      <span><?= $logoutModule['CERRAR_SESION']['label'] ?></span>
      </button>
    </a>
</nav>
