<?php
/**
 * Clase para manejar la autenticación de usuarios
 */
class Auth {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Iniciar sesión de usuario
     */
    public function login($email, $password) {
        $user = $this->db->fetch(
            "SELECT * FROM users WHERE email = ?",
            [$email]
        );
        
        if ($user && password_verify($password, $user['password'])) {
            // Crear sesión
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            return [
                'success' => true,
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'name' => $user['name'],
                    'role' => $user['role']
                ]
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Email o contraseña incorrectos'
        ];
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        session_start();
        session_destroy();
        return ['success' => true];
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function isLoggedIn() {
        session_start();
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Obtener usuario actual
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'email' => $_SESSION['user_email'],
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['user_role']
        ];
    }
    
    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole($role) {
        $user = $this->getCurrentUser();
        return $user && $user['role'] === $role;
    }
    
    /**
     * Verificar si el usuario es admin
     */
    public function isAdmin() {
        return $this->hasRole('admin');
    }
    
    /**
     * Verificar si el usuario puede editar
     */
    public function canEdit() {
        $user = $this->getCurrentUser();
        return $user && in_array($user['role'], ['admin', 'editor']);
    }
    
    /**
     * Crear nuevo usuario
     */
    public function createUser($email, $password, $name, $role = 'viewer') {
        // Verificar si el email ya existe
        $existingUser = $this->db->fetch(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existingUser) {
            return [
                'success' => false,
                'message' => 'El email ya está registrado'
            ];
        }
        
        // Hash de la contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insertar usuario
        $userId = $this->db->insert('users', [
            'email' => $email,
            'password' => $hashedPassword,
            'name' => $name,
            'role' => $role
        ]);
        
        if ($userId) {
            return [
                'success' => true,
                'user_id' => $userId,
                'message' => 'Usuario creado exitosamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al crear el usuario'
        ];
    }
    
    /**
     * Cambiar contraseña
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        $user = $this->db->fetch(
            "SELECT password FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user || !password_verify($currentPassword, $user['password'])) {
            return [
                'success' => false,
                'message' => 'Contraseña actual incorrecta'
            ];
        }
        
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        $updated = $this->db->update('users', 
            ['password' => $hashedPassword], 
            'id = ?', 
            [$userId]
        );
        
        if ($updated) {
            return [
                'success' => true,
                'message' => 'Contraseña cambiada exitosamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al cambiar la contraseña'
        ];
    }
    
    /**
     * Obtener todos los usuarios (solo admin)
     */
    public function getAllUsers() {
        if (!$this->isAdmin()) {
            return [];
        }
        
        return $this->db->fetchAll("SELECT id, email, name, role, created_at FROM users ORDER BY created_at DESC");
    }
    
    /**
     * Actualizar rol de usuario (solo admin)
     */
    public function updateUserRole($userId, $newRole) {
        if (!$this->isAdmin()) {
            return [
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción'
            ];
        }
        
        $validRoles = ['admin', 'editor', 'viewer'];
        if (!in_array($newRole, $validRoles)) {
            return [
                'success' => false,
                'message' => 'Rol inválido'
            ];
        }
        
        $updated = $this->db->update('users', 
            ['role' => $newRole], 
            'id = ?', 
            [$userId]
        );
        
        if ($updated) {
            return [
                'success' => true,
                'message' => 'Rol actualizado exitosamente'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Error al actualizar el rol'
        ];
    }
}
