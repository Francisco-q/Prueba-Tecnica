<?php
/**
 * Constantes de Configuración - Fonda Juanita
 * 
 * Este archivo contiene todas las constantes del sistema
 * organizadas por categorías para fácil mantenimiento
 */

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'fonda_juanita');
define('DB_USER', 'fonda_user');
define('DB_PASS', 'fonda_pass123');
define('DB_CHARSET', 'utf8mb4');

// Configuración de Uploads
define('UPLOAD_DIR', 'uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', [
    'image/jpeg',
    'image/png', 
    'image/gif',
    'image/webp'
]);

// Configuración de la Aplicación
define('APP_NAME', 'Fonda Juanita - Catálogo');
define('APP_VERSION', '1.0.0');
define('APP_TIMEZONE', 'America/Santiago');
define('APP_DEBUG', true); // Cambiar a false en producción

// Configuración de Logs
define('LOG_DIR', 'storage/logs/');
define('LOG_LEVEL', 'debug'); // debug, info, warning, error

// URLs y Rutas
define('BASE_URL', '/fonda-catalogo/');
define('API_BASE_URL', '/fonda-catalogo/api/');

// Configuración de Seguridad
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600); // 1 hora

// Configuración de Performance
define('ENABLE_CACHE', false);
define('CACHE_DURATION', 300); // 5 minutos

return [
    'database' => [
        'host' => DB_HOST,
        'name' => DB_NAME,
        'user' => DB_USER,
        'pass' => DB_PASS,
        'charset' => DB_CHARSET
    ],
    'upload' => [
        'directory' => UPLOAD_DIR,
        'max_size' => MAX_FILE_SIZE,
        'allowed_types' => ALLOWED_IMAGE_TYPES
    ],
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'timezone' => APP_TIMEZONE,
        'debug' => APP_DEBUG
    ],
    'logs' => [
        'directory' => LOG_DIR,
        'level' => LOG_LEVEL
    ],
    'urls' => [
        'base' => BASE_URL,
        'api_base' => API_BASE_URL
    ],
    'security' => [
        'csrf_token_name' => CSRF_TOKEN_NAME,
        'session_timeout' => SESSION_TIMEOUT
    ],
    'performance' => [
        'enable_cache' => ENABLE_CACHE,
        'cache_duration' => CACHE_DURATION
    ]
];
