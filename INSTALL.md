# 🚀 Instalación Rápida - Sistema de Notificaciones Push

## 📋 Requisitos previos

- ✅ **PHP 7.4 o superior**
- ✅ **MySQL 5.7 o superior**
- ✅ **Extensiones PHP:** `curl`, `json`, `openssl`, `pdo_mysql`
- ✅ **Cuenta de Firebase** configurada

## ⚡ Instalación en 5 minutos

### 1️⃣ **Clonar/Descargar el proyecto**
```bash
# Si usas Git
git clone https://github.com/tu-usuario/servidor-push.git
cd servidor-push

# O descarga y extrae el ZIP
```

### 2️⃣ **Crear base de datos**
```sql
CREATE DATABASE push_notifications CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3️⃣ **Configurar variables de entorno**
```bash
# Copiar archivo de ejemplo
cp env.example .env

# Editar .env con tus credenciales
nano .env
```

**Configuración mínima en `.env`:**
```env
# Firebase (obligatorio)
FIREBASE_SERVER_KEY=tu_server_key_aqui
FIREBASE_PROJECT_ID=tu_project_id_aqui

# VAPID (obligatorio para web push)
VAPID_PUBLIC_KEY=tu_vapid_public_key_aqui
VAPID_PRIVATE_KEY=tu_vapid_private_key_aqui
VAPID_SUBJECT=mailto:tu@email.com

# Base de datos (obligatorio)
DB_HOST=localhost
DB_NAME=push_notifications
DB_USER=root
DB_PASS=tu_password

# Servidor (opcional)
APP_URL=http://localhost:8000
APP_DEBUG=true
```

### 4️⃣ **Iniciar servidor**
```bash
cd public
php -S localhost:8000
```

### 5️⃣ **Acceder al sistema**
- Ve a: `http://localhost:8000/login.php`
- **Primera vez:** Se creará automáticamente un usuario admin
- **Credenciales por defecto:**
  - Email: `admin@example.com`
  - Contraseña: `admin123`

## 🔧 Configuración de Firebase

### **Obtener credenciales:**
1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Crea un proyecto o selecciona uno existente
3. Ve a **"Cloud Messaging"**
4. Genera claves VAPID en **"Web Push certificates"**
5. Copia **Server Key** en **"Configuración del proyecto"**

## 📱 Funcionalidades incluidas

- ✅ **Sistema de login** con roles (admin, editor, viewer)
- ✅ **Dashboard** con estadísticas en tiempo real
- ✅ **Crear notificaciones** inmediatas o programadas
- ✅ **Envío masivo** a todos los usuarios o grupos
- ✅ **Gestión de usuarios** y permisos
- ✅ **Historial** de notificaciones enviadas
- ✅ **API REST** para integración con apps
- ✅ **Sistema de cron** para notificaciones programadas

## 🚨 Solución de problemas

### **Error de conexión a base de datos:**
- Verifica que MySQL esté corriendo
- Verifica credenciales en `.env`
- Verifica que la base de datos exista

### **Error de Firebase:**
- Verifica que las claves sean correctas
- Verifica que el proyecto esté activo
- Verifica que Cloud Messaging esté habilitado

### **Error de permisos:**
- Verifica que PHP tenga permisos de escritura
- Verifica que las extensiones estén habilitadas

## 🔄 Actualizaciones

### **Procesar notificaciones programadas:**
```bash
# Agregar a cron (cada minuto)
* * * * * php /ruta/al/proyecto/cron/process-notifications.php

# O ejecutar manualmente
php cron/process-notifications.php
```

### **Logs del sistema:**
- **Cron:** `logs/cron.log`
- **PHP:** Revisa la consola del servidor
- **Base de datos:** Tabla `notification_logs`

## 📚 Próximos pasos

1. **Personaliza la interfaz** según tu marca
2. **Configura HTTPS** para producción
3. **Implementa autenticación adicional** si es necesario
4. **Configura backup** de la base de datos
5. **Monitorea logs** y estadísticas

## 🆘 ¿Necesitas ayuda?

- **Revisa la documentación** en la carpeta `docs/`
- **Verifica la FAQ** en `docs/07-faq.md`
- **Revisa los logs** del sistema
- **Verifica la configuración** paso a paso

---

**¡Tu sistema de notificaciones push está listo!** 🎉

