<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Auth.php';

// Inicializar componentes
$db = new Database();
$auth = new Auth($db);

// Cerrar sesión
$auth->logout();

// Redirigir al login
header('Location: login.php');
exit;

