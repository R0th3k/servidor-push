<?php
/**
 * Clase para gestionar notificaciones push
 */
class NotificationManager {
    private $db;
    private $push;
    
    public function __construct($database, $pushNotification) {
        $this->db = $database;
        $this->push = $pushNotification;
    }
    
    /**
     * Crear nueva notificación
     */
    public function createNotification($title, $message, $recipients, $type = 'immediate', $scheduledAt = null, $data = [], $createdBy = null) {
        $recipientsJson = json_encode($recipients);
        $dataJson = json_encode($data);
        
        $notificationData = [
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'recipients' => $recipientsJson,
            'data' => $dataJson,
            'created_by' => $createdBy
        ];
        
        if ($scheduledAt) {
            $notificationData['scheduled_at'] = $scheduledAt;
            $notificationData['status'] = 'scheduled';
        } else {
            $notificationData['status'] = 'draft';
        }
        
        $notificationId = $this->db->insert('notifications', $notificationData);
        
        if ($notificationId) {
            // Si es inmediata, enviar ahora
            if ($type === 'immediate') {
                $this->sendNotification($notificationId);
            }
            
            return [
                'success' => true,
                'notification_id' => $notificationId,
                'message' => 'Notificación creada exitosamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al crear la notificación'
        ];
    }
    
    /**
     * Enviar notificación
     */
    public function sendNotification($notificationId) {
        $notification = $this->db->fetch(
            "SELECT * FROM notifications WHERE id = ?",
            [$notificationId]
        );
        
        if (!$notification) {
            return [
                'success' => false,
                'message' => 'Notificación no encontrada'
            ];
        }
        
        // Actualizar estado a enviando
        $this->db->update('notifications', 
            ['status' => 'sending'], 
            'id = ?', 
            [$notificationId]
        );
        
        $recipients = json_decode($notification['recipients'], true);
        $data = json_decode($notification['data'], true);
        
        $successCount = 0;
        $failureCount = 0;
        
        // Enviar según el tipo de destinatarios
        if (isset($recipients['type'])) {
            switch ($recipients['type']) {
                case 'all':
                    $result = $this->sendToAllUsers($notification['title'], $notification['message'], $data);
                    break;
                    
                case 'group':
                    $result = $this->sendToGroup($recipients['group_id'], $notification['title'], $notification['message'], $data);
                    break;
                    
                case 'specific':
                    $result = $this->sendToSpecificUsers($recipients['user_ids'], $notification['title'], $notification['message'], $data);
                    break;
                    
                case 'topic':
                    $result = $this->push->sendToTopic($recipients['topic'], $notification['title'], $notification['message'], $data);
                    break;
                    
                default:
                    $result = ['success' => false, 'message' => 'Tipo de destinatarios no válido'];
            }
            
            if ($result['success']) {
                $successCount = $result['success_count'] ?? 1;
                $failureCount = $result['failure_count'] ?? 0;
            }
        }
        
        // Actualizar estado y estadísticas
        $status = ($failureCount === 0) ? 'sent' : 'failed';
        $this->db->update('notifications', 
            [
                'status' => $status,
                'sent_at' => date('Y-m-d H:i:s')
            ], 
            'id = ?', 
            [$notificationId]
        );
        
        return [
            'success' => true,
            'success_count' => $successCount,
            'failure_count' => $failureCount,
            'message' => 'Notificación enviada'
        ];
    }
    
    /**
     * Enviar a todos los usuarios
     */
    private function sendToAllUsers($title, $message, $data) {
        $devices = $this->db->fetchAll(
            "SELECT device_token FROM devices WHERE is_active = 1"
        );
        
        if (empty($devices)) {
            return ['success' => false, 'message' => 'No hay dispositivos activos'];
        }
        
        $tokens = array_column($devices, 'device_token');
        
        // Enviar en lotes de 500 (límite de Firebase)
        $successCount = 0;
        $failureCount = 0;
        
        foreach (array_chunk($tokens, 500) as $batch) {
            $result = $this->push->sendToMultipleDevices($batch, $title, $message, $data);
            
            if ($result['success']) {
                $successCount += $result['response']['success'] ?? 0;
                $failureCount += $result['response']['failure'] ?? 0;
            }
        }
        
        return [
            'success' => true,
            'success_count' => $successCount,
            'failure_count' => $failureCount
        ];
    }
    
    /**
     * Enviar a grupo específico
     */
    private function sendToGroup($groupId, $title, $message, $data) {
        $devices = $this->db->fetchAll("
            SELECT d.device_token 
            FROM devices d 
            JOIN user_group_members ugm ON d.user_id = ugm.user_id 
            WHERE ugm.group_id = ? AND d.is_active = 1
        ", [$groupId]);
        
        if (empty($devices)) {
            return ['success' => false, 'message' => 'No hay dispositivos en el grupo'];
        }
        
        $tokens = array_column($devices, 'device_token');
        $result = $this->push->sendToMultipleDevices($tokens, $title, $message, $data);
        
        return [
            'success' => $result['success'],
            'success_count' => $result['response']['success'] ?? 0,
            'failure_count' => $result['response']['failure'] ?? 0
        ];
    }
    
    /**
     * Enviar a usuarios específicos
     */
    private function sendToSpecificUsers($userIds, $title, $message, $data) {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $devices = $this->db->fetchAll("
            SELECT device_token FROM devices 
            WHERE user_id IN ({$placeholders}) AND is_active = 1
        ", $userIds);
        
        if (empty($devices)) {
            return ['success' => false, 'message' => 'No hay dispositivos activos para estos usuarios'];
        }
        
        $tokens = array_column($devices, 'device_token');
        $result = $this->push->sendToMultipleDevices($tokens, $title, $message, $data);
        
        return [
            'success' => $result['success'],
            'success_count' => $result['response']['success'] ?? 0,
            'failure_count' => $result['response']['failure'] ?? 0
        ];
    }
    
    /**
     * Obtener notificaciones programadas
     */
    public function getScheduledNotifications() {
        return $this->db->fetchAll("
            SELECT * FROM notifications 
            WHERE type = 'scheduled' AND status = 'scheduled' 
            AND scheduled_at <= NOW()
            ORDER BY scheduled_at ASC
        ");
    }
    
    /**
     * Procesar notificaciones programadas
     */
    public function processScheduledNotifications() {
        $notifications = $this->getScheduledNotifications();
        $processed = 0;
        
        foreach ($notifications as $notification) {
            $this->sendNotification($notification['id']);
            $processed++;
        }
        
        return [
            'success' => true,
            'processed' => $processed,
            'message' => "Se procesaron {$processed} notificaciones programadas"
        ];
    }
    
    /**
     * Obtener estadísticas de notificaciones
     */
    public function getNotificationStats() {
        $stats = [];
        
        // Total de notificaciones
        $stats['total'] = $this->db->fetch("SELECT COUNT(*) as count FROM notifications")['count'];
        
        // Notificaciones enviadas hoy
        $stats['sent_today'] = $this->db->fetch("
            SELECT COUNT(*) as count FROM notifications 
            WHERE DATE(sent_at) = CURDATE() AND status = 'sent'
        ")['count'];
        
        // Notificaciones programadas
        $stats['scheduled'] = $this->db->fetch("
            SELECT COUNT(*) as count FROM notifications 
            WHERE status = 'scheduled'
        ")['count'];
        
        // Notificaciones fallidas
        $stats['failed'] = $this->db->fetch("
            SELECT COUNT(*) as count FROM notifications 
            WHERE status = 'failed'
        ")['count'];
        
        return $stats;
    }
    
    /**
     * Obtener historial de notificaciones
     */
    public function getNotificationHistory($limit = 50, $offset = 0) {
        return $this->db->fetchAll("
            SELECT n.*, u.name as created_by_name 
            FROM notifications n 
            LEFT JOIN users u ON n.created_by = u.id 
            ORDER BY n.created_at DESC 
            LIMIT ? OFFSET ?
        ", [$limit, $offset]);
    }
    
    /**
     * Eliminar notificación
     */
    public function deleteNotification($notificationId) {
        $deleted = $this->db->delete('notifications', 'id = ?', [$notificationId]);
        
        if ($deleted) {
            return [
                'success' => true,
                'message' => 'Notificación eliminada exitosamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al eliminar la notificación'
        ];
    }
    
    /**
     * Actualizar notificación
     */
    public function updateNotification($notificationId, $data) {
        $updated = $this->db->update('notifications', $data, 'id = ?', [$notificationId]);
        
        if ($updated) {
            return [
                'success' => true,
                'message' => 'Notificación actualizada exitosamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al actualizar la notificación'
        ];
    }
}
