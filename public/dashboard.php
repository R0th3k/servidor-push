<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/PushNotification.php';
require_once __DIR__ . '/../src/NotificationManager.php';

// Inicializar componentes
$db = new Database();
$auth = new Auth($db);
$push = new PushNotification();
$notificationManager = new NotificationManager($db, $push);

// Verificar autenticación
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$stats = $notificationManager->getNotificationStats();
$recentNotifications = $notificationManager->getNotificationHistory(10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Notificaciones Push</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            margin: 0.25rem 0;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255,255,255,0.1);
        }
        .card-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar p-3">
                    <div class="text-center mb-4">
                        <h4 class="text-white">🚀 Push Notifications</h4>
                        <small class="text-white-50">Sistema de gestión</small>
                    </div>
                    
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link" href="notifications.php">
                            <i class="fas fa-bell me-2"></i> Notificaciones
                        </a>
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i> Usuarios
                        </a>
                        <a class="nav-link" href="groups.php">
                            <i class="fas fa-layer-group me-2"></i> Grupos
                        </a>
                        <a class="nav-link" href="devices.php">
                            <i class="fas fa-mobile-alt me-2"></i> Dispositivos
                        </a>
                        <?php if ($auth->isAdmin()): ?>
                        <a class="nav-link" href="settings.php">
                            <i class="fas fa-cog me-2"></i> Configuración
                        </a>
                        <?php endif; ?>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                        </a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <!-- Header -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h2>Dashboard</h2>
                            <p class="text-muted">Bienvenido, <?= htmlspecialchars($currentUser['name']) ?></p>
                        </div>
                        <div>
                            <a href="create-notification.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Nueva Notificación
                            </a>
                        </div>
                    </div>
                    
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?= $stats['total'] ?></h3>
                                            <p class="mb-0">Total Notificaciones</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-bell fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?= $stats['sent_today'] ?></h3>
                                            <p class="mb-0">Enviadas Hoy</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-paper-plane fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?= $stats['scheduled'] ?></h3>
                                            <p class="mb-0">Programadas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-stats">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h3 class="mb-0"><?= $stats['failed'] ?></h3>
                                            <p class="mb-0">Fallidas</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Notifications -->
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-history me-2"></i> Notificaciones Recientes
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($recentNotifications)): ?>
                                        <p class="text-muted text-center">No hay notificaciones recientes</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Título</th>
                                                        <th>Estado</th>
                                                        <th>Creada por</th>
                                                        <th>Fecha</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recentNotifications as $notification): ?>
                                                        <tr>
                                                            <td>
                                                                <strong><?= htmlspecialchars($notification['title']) ?></strong>
                                                                <br>
                                                                <small class="text-muted"><?= htmlspecialchars(substr($notification['message'], 0, 50)) ?>...</small>
                                                            </td>
                                                            <td>
                                                                <?php
                                                                $statusClass = '';
                                                                $statusText = '';
                                                                switch ($notification['status']) {
                                                                    case 'sent':
                                                                        $statusClass = 'badge bg-success';
                                                                        $statusText = 'Enviada';
                                                                        break;
                                                                    case 'scheduled':
                                                                        $statusClass = 'badge bg-warning';
                                                                        $statusText = 'Programada';
                                                                        break;
                                                                    case 'failed':
                                                                        $statusClass = 'badge bg-danger';
                                                                        $statusText = 'Fallida';
                                                                        break;
                                                                    default:
                                                                        $statusClass = 'badge bg-secondary';
                                                                        $statusText = ucfirst($notification['status']);
                                                                }
                                                                ?>
                                                                <span class="<?= $statusClass ?>"><?= $statusText ?></span>
                                                            </td>
                                                            <td><?= htmlspecialchars($notification['created_by_name'] ?? 'Sistema') ?></td>
                                                            <td><?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-user me-2"></i> Información del Usuario
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                                    </div>
                                    <h6 class="text-center"><?= htmlspecialchars($currentUser['name']) ?></h6>
                                    <p class="text-muted text-center"><?= htmlspecialchars($currentUser['email']) ?></p>
                                    
                                    <div class="d-grid gap-2">
                                        <span class="badge bg-primary"><?= ucfirst($currentUser['role']) ?></span>
                                        <a href="profile.php" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit me-2"></i> Editar Perfil
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">
                                        <i class="fas fa-tools me-2"></i> Acciones Rápidas
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="create-notification.php" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus me-2"></i> Nueva Notificación
                                        </a>
                                        <a href="schedule-notification.php" class="btn btn-warning btn-sm">
                                            <i class="fas fa-clock me-2"></i> Programar Notificación
                                        </a>
                                        <a href="bulk-notification.php" class="btn btn-info btn-sm">
                                            <i class="fas fa-users me-2"></i> Envío Masivo
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
