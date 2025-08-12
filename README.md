# 🚀 Sistema Completo de Notificaciones Push con Firebase

Un sistema completo y profesional para gestionar notificaciones push con **Firebase Cloud Messaging**, incluyendo **dashboard web**, **sistema de login**, **programación de notificaciones** y **API REST**.

## ✨ Características principales

### 🔐 **Sistema de Autenticación**
- ✅ **Login seguro** con sesiones PHP
- ✅ **Roles de usuario** (Admin, Editor, Viewer)
- ✅ **Gestión de usuarios** y permisos
- ✅ **Registro automático** del primer usuario

### 📊 **Dashboard Administrativo**
- ✅ **Estadísticas en tiempo real** de notificaciones
- ✅ **Historial completo** de envíos
- ✅ **Gestión de dispositivos** y tokens
- ✅ **Sistema de grupos** para usuarios

### 📱 **Gestión de Notificaciones**
- ✅ **Envío inmediato** a dispositivos específicos
- ✅ **Programación automática** con cron jobs
- ✅ **Envío masivo** a todos los usuarios
- ✅ **Notificaciones por grupos** o temas
- ✅ **Datos personalizados** y acciones personalizadas

### 🔧 **Tecnologías**
- ✅ **PHP 7.4+** (sin dependencias externas)
- ✅ **MySQL** para persistencia de datos
- ✅ **Firebase Cloud Messaging** para envío
- ✅ **Bootstrap 5** para interfaz moderna
- ✅ **JavaScript vanilla** para interactividad

## 🚀 Instalación Rápida

### **1. Requisitos**
- PHP 7.4+ con extensiones: `curl`, `json`, `openssl`, `pdo_mysql`
- MySQL 5.7+
- Cuenta de Firebase configurada

### **2. Instalación**
```bash
# Clonar proyecto
git clone git@github.com:R0th3k/servidor-push.git
cd servidor-push

# Crear base de datos
mysql -u root -p -e "CREATE DATABASE push_notifications"

# Configurar variables de entorno
cp env.example .env
# Editar .env con tus credenciales de Firebase

# Iniciar servidor
cd public
php -S localhost:8000
```

### **3. Acceder al sistema**
- URL: `http://localhost:8000/login.php`
- **Primera vez:** Se crea usuario admin automáticamente
- **Credenciales:** `admin@example.com` / `admin123`

## 📁 Estructura del proyecto

```
servidor-push/
├── 📚 docs/                    # Documentación completa
├── 🔧 src/                     # Código fuente PHP
│   ├── Database.php           # Gestión de base de datos
│   ├── Auth.php               # Sistema de autenticación
│   ├── PushNotification.php   # Envío de notificaciones
│   └── NotificationManager.php # Gestión de notificaciones
├── 🌐 public/                  # Archivos web públicos
│   ├── dashboard.php          # Dashboard principal
│   ├── login.php              # Página de login
│   ├── create-notification.php # Crear notificaciones
│   └── logout.php             # Cerrar sesión
├── ⚙️ config/                  # Configuración
│   └── config.php             # Configuración principal
├── ⏰ cron/                    # Scripts automáticos
│   └── process-notifications.php # Procesar notificaciones programadas
├── 📝 env.example             # Variables de entorno de ejemplo
├── 📖 INSTALL.md              # Guía de instalación rápida
└── 📋 README.md               # Este archivo
```

## 🔥 Configuración de Firebase

### **Obtener credenciales:**
1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Crea proyecto o selecciona existente
3. Ve a **"Cloud Messaging"**
4. Genera claves VAPID en **"Web Push certificates"**
5. Copia **Server Key** en **"Configuración del proyecto"**

### **Configuración mínima en `.env`:**
```env
FIREBASE_SERVER_KEY=tu_server_key_aqui
FIREBASE_PROJECT_ID=tu_project_id_aqui
VAPID_PUBLIC_KEY=tu_vapid_public_key_aqui
VAPID_PRIVATE_KEY=tu_vapid_private_key_aqui
VAPID_SUBJECT=mailto:tu@email.com
DB_HOST=localhost
DB_NAME=push_notifications
DB_USER=root
DB_PASS=tu_password
```

## 📱 Casos de uso

### **Aplicaciones móviles:**
- Notificaciones de chat en tiempo real
- Alertas de sistema y mantenimiento
- Promociones y ofertas especiales
- Recordatorios y notificaciones programadas

### **Sitios web:**
- Web push notifications para navegadores
- Alertas de seguridad y autenticación
- Notificaciones de contenido nuevo
- Recordatorios de eventos

### **Sistemas empresariales:**
- Notificaciones de mantenimiento
- Alertas de sistema críticas
- Comunicaciones internas
- Reportes automáticos

## 🔄 Funcionalidades avanzadas

### **Programación de notificaciones:**
```bash
# Agregar a cron (cada minuto)
* * * * * php /ruta/al/proyecto/cron/process-notifications.php
```

### **API REST para integración:**
- Envío programático de notificaciones
- Integración con aplicaciones externas
- Webhooks para eventos del sistema

### **Sistema de grupos:**
- Crear grupos de usuarios
- Enviar notificaciones por categorías
- Gestión de suscripciones

## 🚨 Solución de problemas

### **Error común: "Archivo .env no encontrado"**
```bash
# Verificar que el archivo existe
ls -la env.example
cp env.example .env
```

### **Error de base de datos:**
- Verificar que MySQL esté corriendo
- Verificar credenciales en `.env`
- Verificar que la base de datos exista

### **Error de Firebase:**
- Verificar que las claves sean correctas
- Verificar que el proyecto esté activo
- Verificar que Cloud Messaging esté habilitado

## 📚 Documentación

- **📖 Instalación rápida:** `INSTALL.md`
- **🔥 Configurar Firebase:** `docs/01-configurar-firebase.md`
- **⚙️ Instalar servidor:** `docs/02-instalar-servidor.md`
- **🎉 Primera notificación:** `docs/03-primera-notificacion.md`
- **🔌 API Reference:** `docs/06-api-reference.md`
- **❓ FAQ y troubleshooting:** `docs/07-faq.md`

## 🤝 Contribuir

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT. Ver el archivo `LICENSE` para más detalles.

## 🆘 Soporte

- **📧 Email:** tu-email@ejemplo.com
- **🐛 Issues:** [GitHub Issues](https://github.com/tu-usuario/servidor-push/issues)
- **📖 Wiki:** [Documentación completa](https://github.com/tu-usuario/servidor-push/wiki)

---

**¡Construido con ❤️ para hacer las notificaciones push fáciles y profesionales!**

## 🌟 Características destacadas

- **🚀 Sin dependencias externas** - Solo PHP puro
- **🔒 Seguridad empresarial** - Sistema de roles y permisos
- **📱 Multiplataforma** - Web, Android, iOS
- **⚡ Performance optimizado** - Envío en lotes y procesamiento asíncrono
- **🎨 Interfaz moderna** - Bootstrap 5 con diseño responsive
- **📊 Analytics completo** - Estadísticas detalladas de envíos
