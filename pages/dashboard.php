<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../inc/inc_auth.php';

$activePage = "DASHBOARD";
$pageTitle = "Dashboard";
$pageScript = "dashboard.js";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <?php require_once __DIR__ . '/../inc/inc_head.php'; ?>
    <title>Dashboard - Sales360 </title>
</head>
<body>
    <div id="app-container" class="app-container">
        <?php require_once __DIR__ . '/../inc/inc_sidebar.php'; ?>
        <div class="main-content">
            <?php require_once __DIR__ . '/../inc/inc_topbar.php'; ?>
        
            <main class="main-content">
                <div id="dashboard" class="page active">
                    <div style="margin-bottom: 2rem;">
                        <p style="color: var(--text-secondary);">Resumen de actividad y métricas clave</p>
                    </div>

                    <!-- ESTADÍSTICAS -->
                    <div class="stats-grid">
                        <!-- Llamadas Hoy -->
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-phone-volume"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-llamadas">0</div>
                                <div class="stat-label">Llamadas Hoy</div>
                                <div class="stat-comparison" id="llamadas-comparison" style="margin-top: 0.5rem; font-size: 0.85rem;">
                                    <span style="color: var(--text-secondary);">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ventas Hoy (cantidad) -->
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="total-ventas">0</div>
                                <div class="stat-label">Ventas Hoy</div>
                                <div class="stat-comparison" id="ventas-comparison" style="margin-top: 0.5rem; font-size: 0.85rem;">
                                    <span style="color: var(--text-secondary);">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tasa de Conversión -->
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="tasa-conversion">0%</div>
                                <div class="stat-label">Tasa de Conversión</div>
                                <div class="stat-comparison" id="tasa-comparison" style="margin-top: 0.5rem; font-size: 0.85rem;">
                                    <span style="color: var(--text-secondary);">Cargando...</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Ingreso Total Hoy -->
                        <div class="stat-card">
                            <div class="stat-icon accent">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value" id="ingreso-total">$0.00</div>
                                <div class="stat-label">Ingreso Total Hoy</div>
                                <div class="stat-comparison" id="ingreso-comparison" style="margin-top: 0.5rem; font-size: 0.85rem;">
                                    <span style="color: var(--text-secondary);">Cargando...</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ACTIVIDAD RECIENTE -->
                    <div class="data-table">
                        <div class="table-header">
                            <div class="table-title">Actividad Reciente (Últimas 5 llamadas)</div>
                            <!-- Botón de actualizar eliminado - actualización automática activa -->
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Agente</th>
                                    <th>Actividad</th>
                                    <th>Contacto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="actividad-reciente-body">
                                <tr>
                                    <td colspan="5" style="text-align: center; color: var(--text-secondary);">
                                        Cargando actividad...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <?php require_once __DIR__ . '/../inc/inc_footer_scripts.php'; ?>
</body>
</html>
