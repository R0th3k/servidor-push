# ⚙️ Instalar y configurar el servidor

## 📋 ¿Qué vamos a hacer?

Instalar el servidor PHP que enviará las notificaciones push usando Firebase.

---

## 🚀 Paso 1: Verificar requisitos

### 1.1 Verificar PHP
Abre una terminal y ejecuta:
```bash
php --version
```

**Debe mostrar PHP 7.4 o superior.** Si no tienes PHP, instálalo primero.

### 1.2 Verificar extensiones PHP
Ejecuta:
```bash
php -m | grep -E "(curl|json|openssl)"
```

**Debes ver:** `curl`, `json`, `openssl`

Si falta alguna, instálala en tu sistema.

---

## 📁 Paso 2: Crear estructura del proyecto

### 2.1 Crear carpetas
En tu proyecto, crea esta estructura:
```bash
mkdir -p src config public examples
```

### 2.2 Verificar archivo .env
Asegúrate de que ya tienes el archivo `.env` creado con tu configuración de Firebase.

---

## 🔧 Paso 3: Crear archivos del servidor

### 3.1 Crear archivo de configuración
Crea `config/config.php`:

```php
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
```

### 3.2 Crear clase principal de notificaciones
Crea `src/PushNotification.php`:

```php
<?php
require_once __DIR__ . '/../config/config.php';

class PushNotification {
    private $serverKey;
    private $projectId;
    
    public function __construct() {
        $this->serverKey = FIREBASE_SERVER_KEY;
        $this->projectId = FIREBASE_PROJECT_ID;
    }
    
    // Enviar a dispositivo específico (Android/iOS)
    public function sendToDevice($deviceToken, $title, $body, $data = []) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $message = [
            'to' => $deviceToken,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ],
            'data' => $data
        ];
        
        return $this->sendRequest($url, $message);
    }
    
    // Enviar a múltiples dispositivos
    public function sendToMultipleDevices($deviceTokens, $title, $body, $data = []) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $message = [
            'registration_ids' => $deviceTokens,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ],
            'data' => $data
        ];
        
        return $this->sendRequest($url, $message);
    }
    
    // Enviar a tema (topic)
    public function sendToTopic($topic, $title, $body, $data = []) {
        $url = 'https://fcm.googleapis.com/fcm/send';
        
        $message = [
            'to' => '/topics/' . $topic,
            'notification' => [
                'title' => $title,
                'body' => $body,
                'sound' => 'default'
            ],
            'data' => $data
        ];
        
        return $this->sendRequest($url, $message);
    }
    
    // Suscribir dispositivo a tema
    public function subscribeToTopic($deviceToken, $topic) {
        $url = 'https://iid.googleapis.com/iid/v1/' . $deviceToken . '/rel/topics/' . $topic;
        
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode === 200,
            'response' => $response,
            'http_code' => $httpCode
        ];
    }
    
    private function sendRequest($url, $message) {
        $headers = [
            'Authorization: key=' . $this->serverKey,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'success' => $httpCode === 200,
            'response' => json_decode($response, true),
            'http_code' => $httpCode
        ];
    }
}
```

### 3.3 Crear archivo principal del servidor
Crea `public/index.php`:

```php
<?php
require_once __DIR__ . '/../src/PushNotification.php';

// Habilitar CORS para desarrollo
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Crear instancia
$push = new PushNotification();

// Obtener método HTTP
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Router simple
switch ($method) {
    case 'POST':
        if ($path === '/send') {
            handleSendNotification($push);
        } elseif ($path === '/subscribe') {
            handleSubscribeToTopic($push);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
        }
        break;
        
    case 'GET':
        if ($path === '/health') {
            echo json_encode(['status' => 'OK', 'message' => 'Servidor funcionando']);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint no encontrado']);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Método no permitido']);
}

function handleSendNotification($push) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Datos JSON inválidos']);
        return;
    }
    
    $type = $input['type'] ?? 'device';
    $title = $input['title'] ?? '';
    $body = $input['body'] ?? '';
    $data = $input['data'] ?? [];
    
    $result = null;
    
    switch ($type) {
        case 'device':
            $deviceToken = $input['device_token'] ?? '';
            if (!$deviceToken) {
                http_response_code(400);
                echo json_encode(['error' => 'device_token es requerido']);
                return;
            }
            $result = $push->sendToDevice($deviceToken, $title, $body, $data);
            break;
            
        case 'multiple':
            $deviceTokens = $input['device_tokens'] ?? [];
            if (empty($deviceTokens)) {
                http_response_code(400);
                echo json_encode(['error' => 'device_tokens es requerido']);
                return;
            }
            $result = $push->sendToMultipleDevices($deviceTokens, $title, $body, $data);
            break;
            
        case 'topic':
            $topic = $input['topic'] ?? '';
            if (!$topic) {
                http_response_code(400);
                echo json_encode(['error' => 'topic es requerido']);
                return;
            }
            $result = $push->sendToTopic($topic, $title, $body, $data);
            break;
            
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Tipo inválido. Usa: device, multiple, o topic']);
            return;
    }
    
    echo json_encode($result);
}

function handleSubscribeToTopic($push) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['device_token']) || !isset($input['topic'])) {
        http_response_code(400);
        echo json_encode(['error' => 'device_token y topic son requeridos']);
        return;
    }
    
    $result = $push->subscribeToTopic($input['device_token'], $input['topic']);
    echo json_encode($result);
}
```

---

## 🧪 Paso 4: Probar el servidor

### 4.1 Iniciar servidor local
En tu terminal, desde la carpeta del proyecto:
```bash
cd public
php -S localhost:8000
```

### 4.2 Probar que funciona
Abre tu navegador y ve a:
```
http://localhost:8000/health
```

**Debes ver:**
```json
{"status":"OK","message":"Servidor funcionando"}
```

---

## ✅ Paso 5: Verificar configuración

### 5.1 Revisar archivos
Asegúrate de que tienes:
- ✅ `config/config.php`
- ✅ `src/PushNotification.php`
- ✅ `public/index.php`
- ✅ `.env` con tus credenciales de Firebase

### 5.2 Verificar permisos
- Los archivos deben ser legibles por PHP
- El archivo `.env` debe estar en la raíz del proyecto

---

## 🆘 ¿Problemas?

### ❌ Error "Archivo .env no encontrado"
- Verifica que el archivo `.env` esté en la raíz del proyecto
- Verifica que la ruta en `config/config.php` sea correcta

### ❌ Error de PHP
- Verifica que tienes PHP 7.4+
- Verifica que tienes las extensiones: curl, json, openssl

### ❌ Error de CORS
- El servidor ya incluye headers CORS básicos
- Si usas un servidor web real (Apache/Nginx), configura CORS ahí

---

## 🎯 Siguiente paso

Una vez que el servidor esté funcionando, ve a:
**[Enviar primera notificación →](./03-primera-notificacion.md)**

---

## 📚 Recursos adicionales

- [PHP cURL](https://www.php.net/manual/es/book.curl.php)
- [Firebase FCM HTTP v1 API](https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages)
- [HTTP Status Codes](https://developer.mozilla.org/es/docs/Web/HTTP/Status)
