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

// Verificar permisos
if (!$auth->canEdit()) {
    header('Location: dashboard.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$error = '';
$success = '';

// Obtener usuarios y grupos para el formulario
$users = $db->fetchAll("SELECT id, name, email FROM users ORDER BY name");
$groups = $db->fetchAll("SELECT id, name, description FROM user_groups ORDER BY name");

// Procesar creación de notificación
if ($_POST['create_notification']) {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $recipientType = $_POST['recipient_type'];
    $data = [];
    
    // Procesar datos adicionales
    if (!empty($_POST['click_action'])) {
        $data['click_action'] = $_POST['click_action'];
    }
    if (!empty($_POST['url'])) {
        $data['url'] = $_POST['url'];
    }
    if (!empty($_POST['custom_data'])) {
        $customData = [];
        foreach ($_POST['custom_data'] as $key => $value) {
            if (!empty($key) && !empty($value)) {
                $customData[$key] = $value;
            }
        }
        if (!empty($customData)) {
            $data['custom'] = $customData;
        }
    }
    
    // Validar campos
    if (empty($title) || empty($message)) {
        $error = 'Por favor completa el título y mensaje';
    } else {
        // Preparar destinatarios según el tipo
        $recipients = [];
        
        switch ($recipientType) {
            case 'all':
                $recipients = ['type' => 'all'];
                break;
                
            case 'group':
                $groupId = $_POST['group_id'];
                if (empty($groupId)) {
                    $error = 'Por favor selecciona un grupo';
                    break;
                }
                $recipients = ['type' => 'group', 'group_id' => $groupId];
                break;
                
            case 'specific':
                $userIds = $_POST['user_ids'] ?? [];
                if (empty($userIds)) {
                    $error = 'Por favor selecciona al menos un usuario';
                    break;
                }
                $recipients = ['type' => 'specific', 'user_ids' => $userIds];
                break;
                
            case 'topic':
                $topic = trim($_POST['topic']);
                if (empty($topic)) {
                    $error = 'Por favor ingresa el nombre del tema';
                    break;
                }
                $recipients = ['type' => 'topic', 'topic' => $topic];
                break;
        }
        
        if (empty($error)) {
            // Crear notificación
            $result = $notificationManager->createNotification(
                $title,
                $message,
                $recipients,
                'immediate',
                null,
                $data,
                $currentUser['id']
            );
            
            if ($result['success']) {
                $success = 'Notificación enviada exitosamente';
                // Limpiar formulario
                $_POST = [];
            } else {
                $error = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Notificación - Sistema de Notificaciones Push</title>
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
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
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
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                        </a>
                        <a class="nav-link active" href="notifications.php">
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
                            <h2><i class="fas fa-plus me-2"></i>Crear Notificación</h2>
                            <p class="text-muted">Envía notificaciones push a tus usuarios</p>
                        </div>
                        <div>
                            <a href="dashboard.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Volver al Dashboard
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Formulario de notificación -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-edit me-2"></i>Nueva Notificación
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <!-- Título y Mensaje -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="title" class="form-label">
                                            <i class="fas fa-heading me-2"></i>Título *
                                        </label>
                                        <input type="text" class="form-control" id="title" name="title" 
                                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="message" class="form-label">
                                            <i class="fas fa-comment me-2"></i>Mensaje *
                                        </label>
                                        <textarea class="form-control" id="message" name="message" rows="3" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                                    </div>
                                </div>
                                
                                <!-- Tipo de destinatarios -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-users me-2"></i>Tipo de Destinatarios *
                                    </label>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="type_all" value="all" <?= ($_POST['recipient_type'] ?? '') === 'all' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="type_all">
                                                    <i class="fas fa-globe me-2"></i>Todos los usuarios
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="type_group" value="group" <?= ($_POST['recipient_type'] ?? '') === 'group' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="type_group">
                                                    <i class="fas fa-layer-group me-2"></i>Grupo específico
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="type_specific" value="specific" <?= ($_POST['recipient_type'] ?? '') === 'specific' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="type_specific">
                                                    <i class="fas fa-user me-2"></i>Usuarios específicos
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="recipient_type" 
                                                       id="type_topic" value="topic" <?= ($_POST['recipient_type'] ?? '') === 'topic' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="type_topic">
                                                    <i class="fas fa-tag me-2"></i>Tema (Topic)
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Opciones específicas según tipo -->
                                <div id="group_options" class="mb-3" style="display: none;">
                                    <label for="group_id" class="form-label">Seleccionar Grupo</label>
                                    <select class="form-select" name="group_id">
                                        <option value="">Selecciona un grupo</option>
                                        <?php foreach ($groups as $group): ?>
                                            <option value="<?= $group['id'] ?>" <?= ($_POST['group_id'] ?? '') == $group['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($group['name']) ?> - <?= htmlspecialchars($group['description']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div id="specific_options" class="mb-3" style="display: none;">
                                    <label class="form-label">Seleccionar Usuarios</label>
                                    <div class="row">
                                        <?php foreach ($users as $user): ?>
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="user_ids[]" 
                                                           value="<?= $user['id'] ?>" id="user_<?= $user['id'] ?>"
                                                           <?= in_array($user['id'], $_POST['user_ids'] ?? []) ? 'checked' : '' ?>>
                                                    <label class="form-check-label" for="user_<?= $user['id'] ?>">
                                                        <?= htmlspecialchars($user['name']) ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div id="topic_options" class="mb-3" style="display: none;">
                                    <label for="topic" class="form-label">Nombre del Tema</label>
                                    <input type="text" class="form-control" name="topic" 
                                           value="<?= htmlspecialchars($_POST['topic'] ?? '') ?>" 
                                           placeholder="Ej: noticias, promociones, mantenimiento">
                                    <small class="form-text text-muted">
                                        Los usuarios deben estar suscritos a este tema para recibir la notificación
                                    </small>
                                </div>
                                
                                <!-- Datos adicionales -->
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fas fa-cog me-2"></i>Datos Adicionales (Opcional)
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="click_action" class="form-label">Acción al hacer clic</label>
                                            <input type="text" class="form-control" name="click_action" 
                                                   value="<?= htmlspecialchars($_POST['click_action'] ?? '') ?>" 
                                                   placeholder="Ej: OPEN_ACTIVITY, OPEN_URL">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="url" class="form-label">URL (opcional)</label>
                                            <input type="url" class="form-control" name="url" 
                                                   value="<?= htmlspecialchars($_POST['url'] ?? '') ?>" 
                                                   placeholder="https://tusitio.com">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Datos personalizados -->
                                <div class="mb-3">
                                    <label class="form-label">Datos Personalizados</label>
                                    <div id="custom_data_fields">
                                        <div class="row mb-2">
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="custom_data[key][]" placeholder="Clave">
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" class="form-control" name="custom_data[value][]" placeholder="Valor">
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCustomField(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addCustomField()">
                                        <i class="fas fa-plus me-2"></i>Agregar Campo
                                    </button>
                                </div>
                                
                                <!-- Botones -->
                                <div class="d-flex justify-content-between">
                                    <a href="dashboard.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Cancelar
                                    </a>
                                    <button type="submit" name="create_notification" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Enviar Notificación
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar/ocultar opciones según tipo de destinatario
        document.querySelectorAll('input[name="recipient_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                hideAllOptions();
                showOptions(this.value);
            });
        });
        
        function hideAllOptions() {
            document.getElementById('group_options').style.display = 'none';
            document.getElementById('specific_options').style.display = 'none';
            document.getElementById('topic_options').style.display = 'none';
        }
        
        function showOptions(type) {
            switch(type) {
                case 'group':
                    document.getElementById('group_options').style.display = 'block';
                    break;
                case 'specific':
                    document.getElementById('specific_options').style.display = 'block';
                    break;
                case 'topic':
                    document.getElementById('topic_options').style.display = 'block';
                    break;
            }
        }
        
        // Mostrar opciones iniciales si hay un tipo seleccionado
        const selectedType = document.querySelector('input[name="recipient_type"]:checked');
        if (selectedType) {
            showOptions(selectedType.value);
        }
        
        // Funciones para campos personalizados
        function addCustomField() {
            const container = document.getElementById('custom_data_fields');
            const newField = document.createElement('div');
            newField.className = 'row mb-2';
            newField.innerHTML = `
                <div class="col-md-5">
                    <input type="text" class="form-control" name="custom_data[key][]" placeholder="Clave">
                </div>
                <div class="col-md-5">
                    <input type="text" class="form-control" name="custom_data[value][]" placeholder="Valor">
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeCustomField(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            `;
            container.appendChild(newField);
        }
        
        function removeCustomField(button) {
            button.closest('.row').remove();
        }
    </script>
</body>
</html>

