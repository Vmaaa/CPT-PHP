<?php
$role = $AUTH['acco_role'];
$activePage = $activePage ?? '';

$dashboardModule = [ 'DASHBOARD' => ['url' => 'pages/dashboard.php', 'icon' => 'fas fa-chart-pie', 'label' => 'Dashboard'] ];
$agentModule  = [ 'AGENTE'  => ['url' => 'pages/agent.php', 'icon' => 'fas fa-headset', 'label' => 'Agentes'] ];
$agentServiceModule  = [ 'SERVICIO_AGENTE'  => ['url' => 'pages/agent_service.php', 'icon' => 'fas fa-headset', 'label' => 'Agente'] ];

$clientModule = [ 'CONTACTOS' => ['url' => 'pages/contact.php', 'icon' => 'fas fa-users',     'label' => 'Clientes'] ];
$callModule   = [ 'LLAMADAS' => ['url' => 'pages/call.php',   'icon' => 'fas fa-phone-alt', 'label' => 'Llamadas'] ];
$productModule = [ 'PRODUCTOS' => ['url' => 'pages/product.php', 'icon' => 'fas fa-boxes', 'label' => 'Productos'] ];
$saleModule   = [ 'VENTAS'   => ['url' => 'pages/sale.php',   'icon' => 'fas fa-shopping-cart', 'label' => 'Ventas'] ];
$metricModule = [ 'METRICAS' => ['url' => 'pages/metric.php', 'icon' => 'fas fa-chart-line',    'label' => 'Métricas'] ];
$auditModule = [ 'AUDITORIA' => ['url' => 'pages/audit.php', 'icon' => 'fas fa-file-pen',    'label' => 'Auditoría'] ];

$userModule   = [ 'USUARIOS' => ['url' => 'pages/user.php',   'icon' => 'fas fa-user-cog',      'label' => 'Usuarios'] ];

$rolePermissions = [
    'system_admin' => array_merge($dashboardModule, $agentModule, $clientModule, $callModule, $productModule, $saleModule, $metricModule, $auditModule, $userModule),
    'admin' => array_merge($dashboardModule, $agentModule, $clientModule, $callModule, $productModule, $saleModule, $metricModule, $auditModule, $userModule),
    'supervisor'   => array_merge($dashboardModule, $metricModule),
    'supervisor_intra'  => array_merge($dashboardModule, $agentModule, $metricModule, $metricModule, $saleModule, $auditModule),
    'agent'      => array_merge($dashboardModule, $agentServiceModule),
];

$allowedModules = $rolePermissions[$role] ?? [];
?>

<nav class="sidebar">
        <img src="<?= url('/img/logo_white.png') ?>" alt="Logo" class="sidebar-logo"> 
    <div class="nav-links">
        <?php foreach ($allowedModules as $pageKey => $module): ?>
            <a href="<?= url($module['url']) ?>" class="nav-item <?= $activePage === $pageKey ? 'active' : '' ?>" data-page="<?= strtolower(str_replace(' ', '-', $module['label'])) ?>">
                <i class="<?= $module['icon'] ?> nav-icon"></i>
                <span><?= $module['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </div>
</nav>
