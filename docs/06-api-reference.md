# 🔌 API Reference - Servidor de Notificaciones Push

## 📋 Información general

**Base URL:** `http://localhost:8000` (desarrollo local)

**Content-Type:** `application/json`

**Autenticación:** No requerida (configurada por Firebase Server Key)

---

## 🚀 Endpoints disponibles

### 1. **GET /health**
Verifica que el servidor esté funcionando.

**Respuesta exitosa (200):**
```json
{
    "status": "OK",
    "message": "Servidor funcionando"
}
```

**Uso:**
```bash
curl http://localhost:8000/health
```

---

### 2. **POST /send**
Envía una notificación push.

**Parámetros requeridos:**
- `type`: Tipo de envío (`device`, `multiple`, `topic`)
- `title`: Título de la notificación
- `body`: Cuerpo del mensaje

**Parámetros opcionales:**
- `data`: Datos adicionales (objeto JSON)

---

#### **2.1 Enviar a dispositivo específico**

**Endpoint:** `POST /send`

**Body:**
```json
{
    "type": "device",
    "device_token": "fcm_token_del_dispositivo",
    "title": "Título de la notificación",
    "body": "Mensaje de la notificación",
    "data": {
        "click_action": "OPEN_ACTIVITY",
        "url": "https://tusitio.com",
        "custom_field": "valor_personalizado"
    }
}
```

**Respuesta exitosa (200):**
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

**Respuesta de error (400):**
```json
{
    "error": "device_token es requerido"
}
```

---

#### **2.2 Enviar a múltiples dispositivos**

**Endpoint:** `POST /send`

**Body:**
```json
{
    "type": "multiple",
    "device_tokens": [
        "token_dispositivo_1",
        "token_dispositivo_2",
        "token_dispositivo_3"
    ],
    "title": "Notificación masiva",
    "body": "Esta notificación se envía a varios dispositivos",
    "data": {
        "campaign_id": "12345",
        "priority": "high"
    }
}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "response": {
        "message_id": "1234567890",
        "success": 3,
        "failure": 0,
        "results": [
            {"message_id": "msg1"},
            {"message_id": "msg2"},
            {"message_id": "msg3"}
        ]
    },
    "http_code": 200
}
```

---

#### **2.3 Enviar a tema (topic)**

**Endpoint:** `POST /send`

**Body:**
```json
{
    "type": "topic",
    "topic": "noticias",
    "title": "Nueva noticia disponible",
    "body": "Hay una noticia importante para ti",
    "data": {
        "news_id": "789",
        "category": "tecnologia"
    }
}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "response": {
        "message_id": "1234567890"
    },
    "http_code": 200
}
```

---

### 3. **POST /subscribe**
Suscribe un dispositivo a un tema específico.

**Endpoint:** `POST /subscribe`

**Body:**
```json
{
    "device_token": "fcm_token_del_dispositivo",
    "topic": "nombre_del_tema"
}
```

**Respuesta exitosa (200):**
```json
{
    "success": true,
    "response": "",
    "http_code": 200
}
```

**Respuesta de error (400):**
```json
{
    "error": "device_token y topic son requeridos"
}
```

---

## 📊 Códigos de respuesta HTTP

| Código | Significado | Descripción |
|--------|-------------|-------------|
| 200 | OK | Solicitud exitosa |
| 400 | Bad Request | Datos inválidos o faltantes |
| 404 | Not Found | Endpoint no encontrado |
| 405 | Method Not Allowed | Método HTTP no permitido |
| 500 | Internal Server Error | Error interno del servidor |

---

## 🔍 Ejemplos de uso

### **Ejemplo 1: Notificación simple**
```bash
curl -X POST http://localhost:8000/send \
  -H "Content-Type: application/json" \
  -d '{
    "type": "device",
    "device_token": "tu_token_aqui",
    "title": "¡Hola!",
    "body": "Esta es una notificación de prueba"
  }'
```

### **Ejemplo 2: Notificación con datos personalizados**
```bash
curl -X POST http://localhost:8000/send \
  -H "Content-Type: application/json" \
  -d '{
    "type": "device",
    "device_token": "tu_token_aqui",
    "title": "Nueva mensaje",
    "body": "Tienes un mensaje nuevo",
    "data": {
      "message_id": "123",
      "sender": "usuario@email.com",
      "timestamp": "2024-01-01T12:00:00Z"
    }
  }'
```

### **Ejemplo 3: Suscribir a tema**
```bash
curl -X POST http://localhost:8000/subscribe \
  -H "Content-Type: application/json" \
  -d '{
    "device_token": "tu_token_aqui",
    "topic": "promociones"
  }'
```

---

## 📱 Tipos de notificación soportados

### **1. Notificaciones de Android**
- Título y cuerpo personalizables
- Icono personalizable
- Sonido personalizable
- Vibración personalizable
- Acciones personalizables
- Datos personalizados

### **2. Notificaciones de iOS**
- Título y cuerpo personalizables
- Sonido personalizable
- Badge personalizable
- Datos personalizados

### **3. Notificaciones Web**
- Título y cuerpo personalizables
- Icono personalizable
- Acciones personalizables
- Datos personalizados

---

## ⚙️ Configuración avanzada

### **Headers personalizados**
Puedes enviar headers adicionales en tus solicitudes:

```bash
curl -X POST http://localhost:8000/send \
  -H "Content-Type: application/json" \
  -H "X-API-Version: 1.0" \
  -H "X-Request-ID: 12345" \
  -d '{...}'
```

### **Timeout de conexión**
El servidor tiene un timeout predeterminado de 30 segundos para las solicitudes a Firebase.

---

## 🚨 Limitaciones y consideraciones

### **Límites de Firebase FCM:**
- **Mensajes por solicitud:** Máximo 500 dispositivos
- **Tamaño del mensaje:** Máximo 4KB
- **Tasa de envío:** Hasta 1000 mensajes por segundo
- **Topics:** Máximo 2000 topics por proyecto

### **Recomendaciones:**
- Envía notificaciones en lotes para múltiples dispositivos
- Usa topics para notificaciones masivas
- Implementa retry logic para mensajes fallidos
- Monitorea las tasas de éxito/fallo

---

## 🔧 Testing y debugging

### **Endpoint de prueba**
```bash
# Verificar que el servidor funciona
curl http://localhost:8000/health

# Enviar notificación de prueba
curl -X POST http://localhost:8000/send \
  -H "Content-Type: application/json" \
  -d '{
    "type": "device",
    "device_token": "test_token",
    "title": "Test",
    "body": "Notificación de prueba"
  }'
```

### **Logs del servidor**
El servidor registra todas las solicitudes y respuestas. Revisa la consola donde ejecutaste `php -S localhost:8000`.

---

## 📚 Recursos adicionales

- [Firebase FCM HTTP v1 API](https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages)
- [FCM Message Format](https://firebase.google.com/docs/cloud-messaging/http-server-ref)
- [Web Push Protocol](https://tools.ietf.org/html/rfc8030)
- [HTTP Status Codes](https://developer.mozilla.org/es/docs/Web/HTTP/Status)
