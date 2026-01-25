<?php
$role = $AUTH['acco_role'];
$activePage = $activePage ?? '';

$advisedModule   = ['ASESORADOS' => ['url' => 'pages/advised.php',  'icon' => 'fas fa-user-friends',   'label' => 'Asesorados']];
$revisionModule  = ['REVISIONES' => ['url' => 'pages/revisions.php', 'icon' => 'fas fa-check-double',   'label' => 'Revisiones']];
$projectModuleProfessor   = ['PROYECTO_P'  => ['url' => 'pages/projects_professor.php',  'icon' => 'fas fa-folder-open',    'label' => 'Proyectos']];
$projectModuleStudent = ['PROYECTO_S'  => ['url' => 'pages/projects_student.php',  'icon' => 'fas fa-folder-open',    'label' => 'Proyecto']];
$classModuleAdmin        = ['CLASES_A'     => ['url' => 'pages/classes_admin.php',   'icon' => 'fas fa-school', 'label' => 'Clases']];
$classModuleProfessor     = ['CLASES_P'     => ['url' => 'pages/classes.php',   'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Clases']];
$classModuleStudent       = ['CLASES_S'     => ['url' => 'pages/classes_student.php',   'icon' => 'fas fa-chalkboard-teacher', 'label' => 'Clases']];
$projectModuleAdmin      = ['PROYECTOS_ADMIN' => ['url' => 'pages/projects_admin.php',  'icon' => 'fas fa-folder-open',    'label' => 'Proyectos']];

$userModule   = ['USUARIOS' => ['url' => 'pages/account_permission.php',   'icon' => 'fas fa-user-cog',      'label' => 'Usuarios']];
$accountModule = ['CUENTA'    => ['url' => 'pages/account.php', 'icon' => 'fas fa-user-circle',   'label' => 'Mi Cuenta']];
$logoutModule  = ['CERRAR_SESION' => ['icon' => 'fas fa-sign-out-alt',  'label' => 'Cerrar Sesión']];

$rolePermissions = [
  'admin' => [
    'Administrador' => array_merge($classModuleAdmin, $userModule, $projectModuleAdmin),
    'Profesor' => array_merge($advisedModule, $revisionModule, $projectModuleProfessor, $classModuleProfessor),
    'Mi Cuenta' => $accountModule,
  ],
  'student' => [
    'Estudiante' => array_merge($projectModuleStudent, $classModuleStudent),
    'Mi Cuenta' => $accountModule,
  ],
  'professor' => [
    'Profesor' => array_merge($revisionModule, $projectModuleProfessor, $classModuleProfessor),
    'Mi Cuenta' => $accountModule,
  ],
];

$allowedModules = $rolePermissions[$role] ?? [];
?>

<nav class="sidebar">
  <div class="nav-links">
    <?php foreach ($allowedModules as $sectionTitle => $modules): ?>
      <div class="nav-section">
        <h3 class="nav-section-title"><?= $sectionTitle ?></h3>
        <?php foreach ($modules as $pageKey => $module): ?>
          <a href="<?= url($module['url']) ?>" class="nav-item <?= $activePage === $pageKey ? 'active' : '' ?>" data-page="<?= strtolower(str_replace(' ', '-', $module['label'])) ?>">
            <i class="<?= $module['icon'] ?> nav-icon"></i>
            <span><?= $module['label'] ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <!-- Logout Link -->
    <a href="#" class="nav-item" id="logout-btn">
      <i class="<?= $logoutModule['CERRAR_SESION']['icon'] ?> nav-icon"></i>
      <span><?= $logoutModule['CERRAR_SESION']['label'] ?></span>
    </a>
  </div>
</nav>
