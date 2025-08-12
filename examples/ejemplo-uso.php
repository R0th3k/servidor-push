<?php
/**
 * Ejemplo de uso del servidor de notificaciones push
 * 
 * Este archivo muestra cómo usar la API para enviar diferentes tipos de notificaciones
 */

// Incluir la clase principal
require_once __DIR__ . '/../src/PushNotification.php';

// Crear instancia
$push = new PushNotification();

echo "🚀 Servidor de Notificaciones Push - Ejemplos de uso\n";
echo "==================================================\n\n";

// Ejemplo 1: Enviar a un dispositivo específico
echo "1️⃣ Enviando notificación a dispositivo específico...\n";
$result = $push->sendToDevice(
    'TOKEN_DEL_DISPOSITIVO_AQUI', // Reemplaza con un token real
    '¡Hola! 👋',
    'Esta es tu primera notificación push',
    [
        'click_action' => 'OPEN_ACTIVITY',
        'url' => 'https://tusitio.com',
        'timestamp' => date('Y-m-d H:i:s')
    ]
);

echo "Resultado: " . ($result['success'] ? '✅ Éxito' : '❌ Fallo') . "\n";
echo "Respuesta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Ejemplo 2: Enviar a múltiples dispositivos
echo "2️⃣ Enviando notificación a múltiples dispositivos...\n";
$result = $push->sendToMultipleDevices(
    [
        'TOKEN_1_AQUI', // Reemplaza con tokens reales
        'TOKEN_2_AQUI',
        'TOKEN_3_AQUI'
    ],
    'Notificación masiva 📢',
    'Esta notificación se envía a varios dispositivos',
    [
        'campaign_id' => '12345',
        'priority' => 'high',
        'type' => 'promotional'
    ]
);

echo "Resultado: " . ($result['success'] ? '✅ Éxito' : '❌ Fallo') . "\n";
echo "Respuesta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Ejemplo 3: Enviar a un tema (topic)
echo "3️⃣ Enviando notificación a tema 'noticias'...\n";
$result = $push->sendToTopic(
    'noticias',
    'Nueva noticia disponible 📰',
    'Hay una noticia importante para ti',
    [
        'news_id' => '789',
        'category' => 'tecnologia',
        'priority' => 'medium'
    ]
);

echo "Resultado: " . ($result['success'] ? '✅ Éxito' : '❌ Fallo') . "\n";
echo "Respuesta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// Ejemplo 4: Suscribir dispositivo a tema
echo "4️⃣ Suscribiendo dispositivo a tema 'promociones'...\n";
$result = $push->subscribeToTopic(
    'TOKEN_DEL_DISPOSITIVO_AQUI', // Reemplaza con un token real
    'promociones'
);

echo "Resultado: " . ($result['success'] ? '✅ Éxito' : '❌ Fallo') . "\n";
echo "Respuesta: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

echo "🎉 Ejemplos completados!\n";
echo "Para probar con tokens reales, reemplaza 'TOKEN_...' con tokens válidos de FCM.\n";
echo "Puedes obtener tokens de:\n";
echo "- Apps móviles (Android/iOS)\n";
echo "- Navegadores web (web push)\n";
echo "- Firebase Console\n";
?>
