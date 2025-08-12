<?php
/**
 * Script para procesar notificaciones programadas
 * Este archivo debe ejecutarse desde cron cada minuto:
 * * * * * * php /ruta/al/proyecto/cron/process-notifications.php
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/PushNotification.php';
require_once __DIR__ . '/../src/NotificationManager.php';

// Configurar zona horaria
date_default_timezone_set('America/Mexico_City');

// Log del proceso
$logFile = __DIR__ . '/../logs/cron.log';
$timestamp = date('Y-m-d H:i:s');

// Función para escribir logs
function writeLog($message) {
    global $logFile, $timestamp;
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
    
    // Crear directorio de logs si no existe
    $logDir = dirname($logFile);
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

try {
    writeLog("Iniciando procesamiento de notificaciones programadas");
    
    // Inicializar componentes
    $db = new Database();
    $push = new PushNotification();
    $notificationManager = new NotificationManager($db, $push);
    
    // Procesar notificaciones programadas
    $result = $notificationManager->processScheduledNotifications();
    
    if ($result['success']) {
        writeLog("Procesamiento completado: {$result['processed']} notificaciones procesadas");
        
        if ($result['processed'] > 0) {
            writeLog("Mensaje: {$result['message']}");
        }
    } else {
        writeLog("Error en el procesamiento: " . ($result['message'] ?? 'Error desconocido'));
    }
    
    // Verificar notificaciones fallidas
    $failedNotifications = $db->fetchAll("
        SELECT COUNT(*) as count FROM notifications 
        WHERE status = 'failed' AND DATE(created_at) = CURDATE()
    ");
    
    if ($failedNotifications[0]['count'] > 0) {
        writeLog("ADVERTENCIA: {$failedNotifications[0]['count']} notificaciones fallidas hoy");
    }
    
    writeLog("Procesamiento finalizado exitosamente");
    
} catch (Exception $e) {
    writeLog("ERROR CRÍTICO: " . $e->getMessage());
    writeLog("Stack trace: " . $e->getTraceAsString());
    
    // Enviar notificación de error al administrador (opcional)
    // Solo si tienes un email configurado
    if (defined('ADMIN_EMAIL') && !empty(ADMIN_EMAIL)) {
        $subject = "Error en procesamiento de notificaciones";
        $message = "Se produjo un error al procesar las notificaciones programadas:\n\n";
        $message .= "Error: " . $e->getMessage() . "\n";
        $message .= "Fecha: " . $timestamp . "\n";
        $message .= "Archivo: " . $e->getFile() . "\n";
        $message .= "Línea: " . $e->getLine() . "\n";
        
        mail(ADMIN_EMAIL, $subject, $message);
    }
    
    exit(1);
}

writeLog("----------------------------------------");
exit(0);

