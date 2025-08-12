# 🔥 Configurar Firebase Cloud Messaging

## 📋 ¿Qué vamos a hacer?

Configurar Firebase para poder enviar notificaciones push a dispositivos móviles y navegadores web.

---

## 🚀 Paso 1: Crear cuenta de Firebase

### 1.1 Ir a Firebase Console
- Ve a [https://console.firebase.google.com/](https://console.firebase.google.com/)
- Haz clic en **"Crear un proyecto"** o **"Add project"**

### 1.2 Nombrar el proyecto
- Escribe un nombre para tu proyecto (ej: "Mi App Notificaciones")
- Puedes desactivar Google Analytics si quieres
- Haz clic en **"Crear proyecto"**

### 1.3 Esperar que se cree
- Firebase tardará unos minutos en configurar todo
- Cuando termine, haz clic en **"Continuar"**

---

## 🔧 Paso 2: Configurar Cloud Messaging

### 2.1 Ir a Cloud Messaging
- En el menú izquierdo, busca **"Cloud Messaging"**
- Haz clic en **"Cloud Messaging"**

### 2.2 Configurar Web Push
- Haz clic en la pestaña **"Web Push certificates"**
- Haz clic en **"Generate key pair"**
- **¡IMPORTANTE!** Guarda estas claves:
  - **VAPID public key** (clave pública)
  - **VAPID private key** (clave privada)

### 2.3 Configurar Android (opcional)
- Ve a la pestaña **"Android"**
- Haz clic en **"Add app"**
- Selecciona **"Android"**
- Sigue los pasos para configurar tu app Android

---

## 📱 Paso 3: Obtener credenciales del servidor

### 3.1 Ir a Configuración del proyecto
- En el menú izquierdo, haz clic en el **⚙️ (engranaje)**
- Selecciona **"Configuración del proyecto"**

### 3.2 Obtener Server Key
- Ve a la pestaña **"Cloud Messaging"**
- Busca **"Server key"** o **"Clave del servidor"**
- Copia esa clave (la necesitarás para enviar notificaciones)

### 3.3 Obtener Project ID
- En la misma página, busca **"Project ID"**
- Copia el ID del proyecto

---

## 📝 Paso 4: Crear archivo de configuración

### 4.1 Crear archivo .env
En tu proyecto, crea un archivo llamado `.env` con esta información:

```env
# Firebase Configuration
FIREBASE_SERVER_KEY=tu_server_key_aqui
FIREBASE_PROJECT_ID=tu_project_id_aqui

# VAPID Keys (para web push)
VAPID_PUBLIC_KEY=tu_vapid_public_key_aqui
VAPID_PRIVATE_KEY=tu_vapid_private_key_aqui
VAPID_SUBJECT=mailto:tu@email.com

# Configuración del servidor
APP_URL=http://localhost:8000
APP_DEBUG=true
```

### 4.2 Reemplazar valores
- Cambia `tu_server_key_aqui` por tu Server Key real
- Cambia `tu_project_id_aqui` por tu Project ID real
- Cambia `tu_vapid_public_key_aqui` por tu VAPID public key
- Cambia `tu_vapid_private_key_aqui` por tu VAPID private key
- Cambia `tu@email.com` por tu email real

---

## ✅ Paso 5: Verificar configuración

### 5.1 Probar conexión
- Ve a la siguiente guía: [Instalar servidor](./02-instalar-servidor.md)
- Sigue los pasos para probar que todo funciona

### 5.2 Verificar en Firebase Console
- Regresa a Firebase Console
- Ve a **"Cloud Messaging"**
- Deberías ver que está configurado correctamente

---

## 🆘 ¿Problemas?

### ❌ No puedo ver Cloud Messaging
- Asegúrate de que tu proyecto esté completamente creado
- Espera unos minutos y recarga la página

### ❌ No encuentro las claves VAPID
- Ve a **"Cloud Messaging"** → **"Web Push certificates"**
- Si no ves esa opción, tu proyecto puede estar en una región que no la soporta

### ❌ No puedo generar claves VAPID
- Intenta crear un nuevo proyecto
- Asegúrate de usar un navegador moderno (Chrome, Firefox, Edge)

---

## 🎯 Siguiente paso

Una vez que tengas Firebase configurado, ve a:
**[Instalar servidor →](./02-instalar-servidor.md)**

---

## 📚 Recursos adicionales

- [Documentación oficial de Firebase](https://firebase.google.com/docs)
- [Guía de Cloud Messaging](https://firebase.google.com/docs/cloud-messaging)
- [VAPID Protocol](https://tools.ietf.org/html/rfc8292)
