# 🎉 Enviar tu primera notificación push

## 📋 ¿Qué vamos a hacer?

Probar que tu servidor funciona enviando una notificación push real a un dispositivo o navegador.

---

## 🚀 Paso 1: Verificar que el servidor funciona

### 1.1 Probar endpoint de salud
Asegúrate de que tu servidor esté corriendo:
```bash
cd public
php -S localhost:8000
```

Ve a tu navegador: `http://localhost:8000/health`

**Debes ver:**
```json
{"status":"OK","message":"Servidor funcionando"}
```

### 1.2 Verificar configuración
Asegúrate de que tu archivo `.env` tenga todas las credenciales correctas de Firebase.

---

## 📱 Paso 2: Probar con dispositivo móvil (Android/iOS)

### 2.1 Obtener token de dispositivo
Para probar, necesitas un token de dispositivo real. Puedes:

**Opción A: Usar app de prueba**
- Instala una app que genere tokens FCM
- O usa tu propia app Android/iOS

**Opción B: Usar token de ejemplo (solo para probar)**
- Usa un token falso para verificar que el servidor responde

### 2.2 Enviar notificación de prueba
Usa Postman, cURL o cualquier cliente HTTP:

**Endpoint:** `POST http://localhost:8000/send`

**Headers:**
```
Content-Type: application/json
```

**Body (JSON):**
```json
{
    "type": "device",
    "device_token": "tu_token_aqui",
    "title": "¡Hola! 👋",
    "body": "Esta es tu primera notificación push",
    "data": {
        "click_action": "OPEN_ACTIVITY",
        "url": "https://tusitio.com"
    }
}
```

### 2.3 Respuesta esperada
Si todo funciona, verás algo como:
```json
{
    "success": true,
    "response": {
        "message_id": "1234567890",
        "success": 1,
        "failure": 0
    },
    "http_code": 200
}
```

---

## 🌐 Paso 3: Probar con web push (navegador)

### 3.1 Crear página de prueba
Crea `public/test.html`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>Test Push Notifications</title>
    <meta charset="UTF-8">
</head>
<body>
    <h1>🧪 Test Push Notifications</h1>
    
    <button id="subscribe">Suscribirse a notificaciones</button>
    <button id="send">Enviar notificación de prueba</button>
    
    <div id="status"></div>
    
    <script>
        let swRegistration = null;
        let isSubscribed = false;
        
        // Verificar si el navegador soporta service workers
        if ('serviceWorker' in navigator && 'PushManager' in window) {
            console.log('Service Worker y Push Manager soportados');
            init();
        } else {
            console.log('Service Worker o Push Manager no soportados');
            document.getElementById('status').innerHTML = 'Tu navegador no soporta notificaciones push';
        }
        
        function init() {
            navigator.serviceWorker.register('/sw.js')
                .then(function(swReg) {
                    console.log('Service Worker registrado', swReg);
                    swRegistration = swReg;
                    initializeUI();
                })
                .catch(function(error) {
                    console.error('Error registrando Service Worker', error);
                });
        }
        
        function initializeUI() {
            document.getElementById('subscribe').addEventListener('click', function() {
                if (isSubscribed) {
                    unsubscribeUser();
                } else {
                    subscribeUser();
                }
            });
            
            document.getElementById('send').addEventListener('click', function() {
                sendTestNotification();
            });
        }
        
        function subscribeUser() {
            const applicationServerKey = urlB64ToUint8Array('TU_VAPID_PUBLIC_KEY_AQUI');
            
            swRegistration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: applicationServerKey
            })
            .then(function(subscription) {
                console.log('Usuario suscrito:', subscription);
                isSubscribed = true;
                updateSubscriptionOnServer(subscription);
                updateBtn();
            })
            .catch(function(err) {
                console.log('Error suscribiendo usuario:', err);
            });
        }
        
        function unsubscribeUser() {
            swRegistration.pushManager.getSubscription()
                .then(function(subscription) {
                    if (subscription === null) {
                        console.log('Usuario no suscrito');
                        return;
                    }
                    
                    return subscription.unsubscribe();
                })
                .catch(function(error) {
                    console.log('Error desuscribiendo:', error);
                })
                .then(function() {
                    updateSubscriptionOnServer(null);
                    isSubscribed = false;
                    updateBtn();
                });
        }
        
        function updateSubscriptionOnServer(subscription) {
            if (subscription) {
                console.log('Suscripción:', JSON.stringify(subscription));
                document.getElementById('status').innerHTML = 
                    '<strong>Suscrito!</strong> Token: ' + btoa(JSON.stringify(subscription));
            } else {
                document.getElementById('status').innerHTML = 'No suscrito';
            }
        }
        
        function updateBtn() {
            const btn = document.getElementById('subscribe');
            if (isSubscribed) {
                btn.textContent = 'Desuscribirse';
            } else {
                btn.textContent = 'Suscribirse a notificaciones';
            }
        }
        
        function sendTestNotification() {
            // Enviar notificación usando tu servidor
            fetch('/send', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    type: 'device',
                    device_token: 'token_del_navegador',
                    title: 'Test desde navegador',
                    body: 'Notificación enviada desde la página de prueba'
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Respuesta:', data);
                document.getElementById('status').innerHTML = 
                    '<strong>Respuesta del servidor:</strong> ' + JSON.stringify(data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
        
        function urlB64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding)
                .replace(/-/g, '+')
                .replace(/_/g, '/');
            
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            
            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }
    </script>
</body>
</html>
```

### 3.2 Crear Service Worker
Crea `public/sw.js`:

```javascript
self.addEventListener('push', function(event) {
    console.log('Push recibido:', event);
    
    let options = {
        body: 'Notificación push recibida',
        icon: '/icon.png',
        badge: '/badge.png',
        vibrate: [100, 50, 100],
        data: {
            dateOfArrival: Date.now(),
            primaryKey: 1
        },
        actions: [
            {
                action: 'explore',
                title: 'Ver más',
                icon: '/icon.png'
            },
            {
                action: 'close',
                title: 'Cerrar',
                icon: '/icon.png'
            }
        ]
    };
    
    if (event.data) {
        try {
            const data = event.data.json();
            options.title = data.title || 'Notificación';
            options.body = data.body || 'Mensaje recibido';
            options.data = { ...options.data, ...data.data };
        } catch (e) {
            console.log('Error parseando datos:', e);
        }
    }
    
    event.waitUntil(
        self.registration.showNotification('Notificación Push', options)
    );
});

self.addEventListener('notificationclick', function(event) {
    console.log('Notificación clickeada:', event);
    
    event.notification.close();
    
    if (event.action === 'explore') {
        // Abrir URL o hacer algo
        event.waitUntil(
            clients.openWindow('https://tusitio.com')
        );
    }
});
```

### 3.3 Probar web push
1. Ve a `http://localhost:8000/test.html`
2. Haz clic en "Suscribirse a notificaciones"
3. Acepta los permisos del navegador
4. Copia el token que aparece
5. Usa ese token para enviar una notificación

---

## 🧪 Paso 4: Probar diferentes tipos de envío

### 4.1 Enviar a múltiples dispositivos
```json
{
    "type": "multiple",
    "device_tokens": [
        "token1",
        "token2",
        "token3"
    ],
    "title": "Notificación masiva",
    "body": "Esta notificación se envía a varios dispositivos"
}
```

### 4.2 Enviar a tema (topic)
```json
{
    "type": "topic",
    "topic": "noticias",
    "title": "Nueva noticia",
    "body": "Hay una noticia importante para ti"
}
```

### 4.3 Suscribir dispositivo a tema
```json
{
    "device_token": "tu_token",
    "topic": "noticias"
}
```

---

## ✅ Paso 5: Verificar que funciona

### 5.1 En el dispositivo móvil
- Deberías recibir la notificación
- Al tocarla, debería abrir tu app

### 5.2 En el navegador
- Deberías ver la notificación del sistema
- Al hacer clic, debería ejecutar la acción configurada

### 5.3 En el servidor
- Los logs deberían mostrar respuestas exitosas
- El código HTTP debería ser 200

---

## 🆘 ¿Problemas?

### ❌ Error 401 (Unauthorized)
- Verifica que tu `FIREBASE_SERVER_KEY` sea correcta
- Asegúrate de que Firebase esté configurado correctamente

### ❌ Error 400 (Bad Request)
- Verifica que el JSON sea válido
- Asegúrate de que todos los campos requeridos estén presentes

### ❌ No recibes notificaciones
- Verifica que el token del dispositivo sea válido
- Asegúrate de que la app esté instalada y configurada
- Revisa los logs de Firebase Console

### ❌ Error de CORS
- El servidor ya incluye headers CORS básicos
- Si persiste, verifica que estés usando el servidor PHP local

---

## 🎯 Siguiente paso

Una vez que las notificaciones funcionen, puedes:
- **[Configurar apps móviles →](./04-apps-moviles.md)**
- **[Configurar web push avanzado →](./05-web-push.md)**
- **[Ver API completa →](./06-api-reference.md)**

---

## 📚 Recursos adicionales

- [Firebase FCM Testing](https://firebase.google.com/docs/cloud-messaging/test-fcm)
- [Web Push Protocol](https://tools.ietf.org/html/rfc8030)
- [Service Worker API](https://developer.mozilla.org/es/docs/Web/API/Service_Worker_API)
- [Push API](https://developer.mozilla.org/es/docs/Web/API/Push_API)
