<?php
// Cargar variables de entorno
function loadEnv($path) {
    if (!file_exists($path)) {
        die("Error: Archivo .env no encontrado en: $path\n");
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Cargar configuración
loadEnv(__DIR__ . '/../.env');

// Configuración de Firebase
define('FIREBASE_SERVER_KEY', $_ENV['FIREBASE_SERVER_KEY']);
define('FIREBASE_PROJECT_ID', $_ENV['FIREBASE_PROJECT_ID']);

// Configuración VAPID
define('VAPID_PUBLIC_KEY', $_ENV['VAPID_PUBLIC_KEY']);
define('VAPID_PRIVATE_KEY', $_ENV['VAPID_PRIVATE_KEY']);
define('VAPID_SUBJECT', $_ENV['VAPID_SUBJECT']);

// Configuración del servidor
define('APP_URL', $_ENV['APP_URL']);
define('APP_DEBUG', $_ENV['APP_DEBUG'] === 'true');

// Configuración de base de datos
define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'push_notifications');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASS'] ?? '');

// Configuración adicional
define('ADMIN_EMAIL', $_ENV['ADMIN_EMAIL'] ?? '');
define('TIMEZONE', $_ENV['TIMEZONE'] ?? 'America/Mexico_City');

// Configurar zona horaria
date_default_timezone_set(TIMEZONE);

// Configurar manejo de errores
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Configurar sesiones
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS

// Función para obtener configuración
function config($key, $default = null) {
    return $_ENV[$key] ?? $default;
}

// Función para debug
function debug($data) {
    if (APP_DEBUG) {
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}

