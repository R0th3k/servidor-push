# ❓ FAQ y Troubleshooting

## 📋 Preguntas frecuentes

---

## 🔥 **Firebase y configuración**

### **Q: ¿Qué es Firebase Cloud Messaging (FCM)?**
**A:** FCM es el servicio de Google que permite enviar notificaciones push a dispositivos móviles y navegadores web. Es gratuito y muy confiable.

### **Q: ¿Necesito una cuenta de pago de Google?**
**A:** No, FCM es completamente gratuito para uso básico. Solo pagas si excedes los límites gratuitos (que son muy altos).

### **Q: ¿Puedo usar FCM sin Google Play Services?**
**A:** Para Android, es recomendable tener Google Play Services. Para iOS, no es necesario.

### **Q: ¿Qué son las claves VAPID?**
**A:** VAPID (Voluntary Application Server Identification) son claves criptográficas que identifican tu servidor para web push. Son necesarias para enviar notificaciones a navegadores.

---

## ⚙️ **Configuración del servidor**

### **Q: ¿Por qué me da error "Archivo .env no encontrado"?**
**A:** Verifica que:
1. El archivo `.env` esté en la raíz del proyecto (no en subcarpetas)
2. El archivo tenga permisos de lectura
3. La ruta en `config/config.php` sea correcta

### **Q: ¿Cómo sé si mi PHP tiene las extensiones necesarias?**
**A:** Ejecuta en terminal:
```bash
php -m | grep -E "(curl|json|openssl)"
```
Debes ver: `curl`, `json`, `openssl`

### **Q: ¿Puedo usar Apache/Nginx en lugar del servidor PHP local?**
**A:** Sí, es recomendable para producción. Solo asegúrate de:
1. Configurar CORS correctamente
2. Tener las extensiones PHP habilitadas
3. Configurar el document root en la carpeta `public`

---

## 📱 **Dispositivos móviles**

### **Q: ¿Cómo obtengo el token FCM de mi dispositivo?**
**A:** Depende de tu app:
- **Android:** Usa `FirebaseMessaging.getInstance().getToken()`
- **iOS:** Usa `Messaging.messaging().token`
- **Web:** Usa `navigator.serviceWorker.pushManager.subscribe()`

### **Q: ¿Por qué no recibo notificaciones en mi app?**
**A:** Verifica:
1. La app está instalada y configurada con Firebase
2. El token FCM es válido y actual
3. Las notificaciones están habilitadas en el dispositivo
4. La app tiene permisos de notificación

### **Q: ¿Los tokens FCM expiran?**
**A:** Sí, pueden expirar por:
- Reinstalación de la app
- Actualización de la app
- Cambio de dispositivo
- Limpieza de datos de la app

---

## 🌐 **Web Push (navegadores)**

### **Q: ¿Qué navegadores soportan web push?**
**A:** 
- ✅ Chrome 42+
- ✅ Firefox 44+
- ✅ Edge 17+
- ✅ Safari 16+ (macOS 13+)
- ❌ Internet Explorer (no soportado)

### **Q: ¿Por qué no funciona en mi navegador?**
**A:** Verifica:
1. El navegador soporta Service Workers
2. El navegador soporta Push API
3. Estás usando HTTPS (requerido para producción)
4. Los permisos están habilitados

### **Q: ¿Qué es un Service Worker?**
**A:** Es un script que se ejecuta en segundo plano en el navegador. Es necesario para recibir notificaciones push cuando la página no está abierta.

---

## 🚨 **Errores comunes**

### **Error 401 (Unauthorized)**
**Síntomas:** El servidor responde con código 401
**Causas:**
- `FIREBASE_SERVER_KEY` incorrecta
- Firebase no configurado correctamente
- Proyecto Firebase eliminado o deshabilitado

**Solución:**
1. Verifica tu Server Key en Firebase Console
2. Asegúrate de que el proyecto esté activo
3. Regenera las claves si es necesario

### **Error 400 (Bad Request)**
**Síntomas:** El servidor responde con código 400
**Causas:**
- JSON malformado
- Campos requeridos faltantes
- Valores inválidos

**Solución:**
1. Verifica que el JSON sea válido
2. Asegúrate de incluir todos los campos requeridos
3. Verifica los tipos de datos

### **Error 500 (Internal Server Error)**
**Síntomas:** El servidor responde con código 500
**Causas:**
- Error en el código PHP
- Problema de configuración
- Error de conexión con Firebase

**Solución:**
1. Revisa los logs del servidor
2. Verifica la configuración
3. Asegúrate de que Firebase esté accesible

---

## 🔧 **Solución de problemas paso a paso**

### **Problema: No puedo configurar Firebase**
1. Ve a [Firebase Console](https://console.firebase.google.com/)
2. Crea un nuevo proyecto
3. Ve a "Cloud Messaging"
4. Genera las claves VAPID
5. Copia el Server Key y Project ID

### **Problema: El servidor no inicia**
1. Verifica que tienes PHP 7.4+
2. Verifica las extensiones: `php -m | grep -E "(curl|json|openssl)"`
3. Verifica que el archivo `.env` existe
4. Verifica que las rutas en `config/config.php` son correctas

### **Problema: Las notificaciones no se envían**
1. Verifica que el servidor esté funcionando (`/health`)
2. Verifica que las credenciales de Firebase sean correctas
3. Verifica que el token del dispositivo sea válido
4. Revisa los logs de Firebase Console

### **Problema: Error de CORS**
1. El servidor ya incluye headers CORS básicos
2. Si persiste, configura CORS en tu servidor web
3. Para desarrollo, usa el servidor PHP local

---

## 📊 **Monitoreo y debugging**

### **¿Cómo monitoreo el envío de notificaciones?**
1. **Firebase Console:** Ve a "Cloud Messaging" → "Reports"
2. **Logs del servidor:** Revisa la consola donde ejecutaste PHP
3. **Respuestas de la API:** Cada envío devuelve información de éxito/fallo

### **¿Cómo sé si un token es válido?**
1. Envía una notificación de prueba
2. Si devuelve `success: true`, el token es válido
3. Si devuelve `success: false`, el token puede haber expirado

### **¿Cómo manejo tokens expirados?**
1. Implementa un sistema de refresh de tokens
2. Almacena la fecha de último uso del token
3. Solicita un nuevo token cuando sea necesario

---

## 🚀 **Optimización y escalabilidad**

### **¿Cómo envío a muchos dispositivos?**
1. **Topics:** Para notificaciones masivas (recomendado)
2. **Lotes:** Máximo 500 dispositivos por solicitud
3. **Colas:** Implementa un sistema de colas para envíos masivos

### **¿Cómo mejoro la tasa de entrega?**
1. **Retry logic:** Reintenta mensajes fallidos
2. **Rate limiting:** Respeta los límites de Firebase
3. **Monitoreo:** Revisa las tasas de éxito/fallo
4. **Tokens limpios:** Elimina tokens expirados

### **¿Cuántas notificaciones puedo enviar por segundo?**
**A:** Firebase permite hasta 1000 mensajes por segundo por proyecto. Para más, contacta a Google.

---

## 🔒 **Seguridad**

### **¿Es seguro exponer mi Server Key?**
**A:** La Server Key debe mantenerse privada en tu servidor. Nunca la expongas en código del cliente o repositorios públicos.

### **¿Cómo protejo mi API?**
1. **Autenticación:** Implementa un sistema de autenticación
2. **Rate limiting:** Limita las solicitudes por IP/usuario
3. **Validación:** Valida todos los datos de entrada
4. **HTTPS:** Usa HTTPS en producción

### **¿Puedo limitar quién puede enviar notificaciones?**
**A:** Sí, implementa autenticación y autorización en tu API. Solo usuarios autorizados deberían poder enviar notificaciones.

---

## 📚 **Recursos adicionales**

### **Documentación oficial:**
- [Firebase FCM](https://firebase.google.com/docs/cloud-messaging)
- [Web Push Protocol](https://tools.ietf.org/html/rfc8030)
- [Service Worker API](https://developer.mozilla.org/es/docs/Web/API/Service_Worker_API)

### **Herramientas de testing:**
- [Postman](https://www.postman.com/) - Para probar la API
- [Firebase Console](https://console.firebase.google.com/) - Para monitorear
- [Chrome DevTools](https://developer.chrome.com/docs/devtools/) - Para debuggear web push

### **Comunidad:**
- [Stack Overflow](https://stackoverflow.com/questions/tagged/firebase-cloud-messaging)
- [Firebase Community](https://firebase.google.com/community)
- [GitHub Issues](https://github.com/firebase/firebase-js-sdk/issues)

---

## 🆘 **¿Aún tienes problemas?**

Si ninguna de estas soluciones funciona:

1. **Revisa los logs** del servidor y Firebase Console
2. **Verifica la configuración** paso a paso
3. **Prueba con un proyecto nuevo** de Firebase
4. **Busca en la documentación oficial** de Firebase
5. **Pregunta en la comunidad** de Firebase

**¡Recuerda: La mayoría de problemas se resuelven revisando la configuración paso a paso!** 🎯
