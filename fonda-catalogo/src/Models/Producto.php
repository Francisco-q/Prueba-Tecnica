<?php

/**
 * Modelo Producto - Gestión de productos del catálogo
 * 
 * Este modelo maneja todas las operaciones CRUD para productos
 * Incluye validaciones y formateo de datos
 */

require_once __DIR__ . '/Database.php';

class Producto
{
    private $db;
    private $tableName = 'productos';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Obtiene todos los productos activos
     * 
     * @return array Lista de productos
     */
    public function obtenerTodos(): array
    {
        try {
            $sql = "SELECT id, nombre, precio, url_imagen, fecha_creacion, fecha_actualizacion 
                    FROM {$this->tableName} 
                    WHERE estado_activo = 1 
                    ORDER BY fecha_creacion DESC";

            $stmt = $this->db->query($sql);
            $productos = $stmt->fetchAll();

            // Formatear datos para frontend
            return array_map([$this, 'formatearProducto'], $productos);
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo productos: " . $e->getMessage());
            throw new Exception("Error al obtener productos");
        }
    }

    /**
     * Obtiene un producto por su ID
     * 
     * @param int $id
     * @return array|null Datos del producto o null si no existe
     */
    public function obtenerPorId(int $id): ?array
    {
        try {
            $sql = "SELECT id, nombre, precio, url_imagen, fecha_creacion, fecha_actualizacion 
                    FROM {$this->tableName} 
                    WHERE id = ? AND estado_activo = 1";

            $stmt = $this->db->query($sql, [$id]);
            $producto = $stmt->fetch();

            return $producto ? $this->formatearProducto($producto) : null;
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo producto ID {$id}: " . $e->getMessage());
            throw new Exception("Error al obtener producto");
        }
    }

    /**
     * Crea un nuevo producto
     * 
     * @param array $datos Datos del producto
     * @return int ID del producto creado
     */
    public function crear(array $datos): int
    {
        try {
            // Validar datos
            $datosValidados = $this->validarDatos($datos);

            $sql = "INSERT INTO {$this->tableName} (nombre, precio, url_imagen) VALUES (?, ?, ?)";

            $this->db->query($sql, [
                $datosValidados['nombre'],
                $datosValidados['precio'],
                $datosValidados['url_imagen'] ?? null
            ]);

            $id = (int) $this->db->lastInsertId();

            if (APP_DEBUG) {
                error_log("✅ Producto creado con ID: {$id}");
            }

            return $id;
        } catch (PDOException $e) {
            error_log("❌ Error creando producto: " . $e->getMessage());
            throw new Exception("Error al crear producto");
        }
    }

    /**
     * Actualiza un producto existente
     * 
     * @param int $id ID del producto
     * @param array $datos Nuevos datos
     * @return bool True si se actualizó correctamente
     */
    public function actualizar(int $id, array $datos): bool
    {
        try {
            // Verificar que el producto existe
            if (!$this->obtenerPorId($id)) {
                throw new Exception("Producto no encontrado");
            }

            // Validar datos
            $datosValidados = $this->validarDatos($datos, false); // false = no requiere todos los campos

            // Construir SQL dinámicamente
            $campos = [];
            $valores = [];

            if (isset($datosValidados['nombre'])) {
                $campos[] = "nombre = ?";
                $valores[] = $datosValidados['nombre'];
            }

            if (isset($datosValidados['precio'])) {
                $campos[] = "precio = ?";
                $valores[] = $datosValidados['precio'];
            }

            if (array_key_exists('url_imagen', $datosValidados)) {
                $campos[] = "url_imagen = ?";
                $valores[] = $datosValidados['url_imagen'];
            }

            if (empty($campos)) {
                throw new Exception("No hay datos para actualizar");
            }

            $campos[] = "fecha_actualizacion = CURRENT_TIMESTAMP";
            $valores[] = $id;

            $sql = "UPDATE {$this->tableName} SET " . implode(', ', $campos) . " WHERE id = ?";

            $stmt = $this->db->query($sql, $valores);
            $actualizado = $stmt->rowCount() > 0;

            if (APP_DEBUG && $actualizado) {
                error_log("✅ Producto ID {$id} actualizado correctamente");
            }

            return $actualizado;
        } catch (PDOException $e) {
            error_log("❌ Error actualizando producto ID {$id}: " . $e->getMessage());
            throw new Exception("Error al actualizar producto");
        }
    }

    /**
     * Elimina un producto (soft delete)
     * 
     * @param int $id ID del producto
     * @return bool True si se eliminó correctamente
     */
    public function eliminar(int $id): bool
    {
        try {
            // Obtener datos antes de eliminar (para cleanup de archivos)
            $producto = $this->obtenerPorId($id);
            if (!$producto) {
                throw new Exception("Producto no encontrado");
            }

            // Soft delete
            $sql = "UPDATE {$this->tableName} SET estado_activo = 0, fecha_actualizacion = CURRENT_TIMESTAMP WHERE id = ?";
            $stmt = $this->db->query($sql, [$id]);

            $eliminado = $stmt->rowCount() > 0;

            if ($eliminado && APP_DEBUG) {
                error_log("✅ Producto ID {$id} eliminado correctamente");
            }

            return $eliminado;
        } catch (PDOException $e) {
            error_log("❌ Error eliminando producto ID {$id}: " . $e->getMessage());
            throw new Exception("Error al eliminar producto");
        }
    }

    /**
     * Valida los datos del producto
     * 
     * @param array $datos
     * @param bool $requiereTodos Si requiere todos los campos obligatorios
     * @return array Datos validados
     */
    private function validarDatos(array $datos, bool $requiereTodos = true): array
    {
        $errores = [];
        $datosLimpios = [];

        // Validar nombre
        if (isset($datos['nombre'])) {
            $nombre = trim($datos['nombre']);
            if (empty($nombre)) {
                $errores[] = 'El nombre es requerido';
            } elseif (strlen($nombre) > 255) {
                $errores[] = 'El nombre es muy largo (máximo 255 caracteres)';
            } else {
                $datosLimpios['nombre'] = htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8');
            }
        } elseif ($requiereTodos) {
            $errores[] = 'El nombre es requerido';
        }

        // Validar precio
        if (isset($datos['precio'])) {
            if (!is_numeric($datos['precio'])) {
                $errores[] = 'El precio debe ser numérico';
            } elseif ((float)$datos['precio'] < 0) {
                $errores[] = 'El precio debe ser positivo';
            } else {
                $datosLimpios['precio'] = round((float)$datos['precio'], 2);
            }
        } elseif ($requiereTodos) {
            $errores[] = 'El precio es requerido';
        }

        // Validar URL de imagen (opcional)
        if (array_key_exists('url_imagen', $datos)) {
            $urlImagen = $datos['url_imagen'];
            if ($urlImagen !== null && !empty(trim($urlImagen))) {
                $datosLimpios['url_imagen'] = trim($urlImagen);
            } else {
                $datosLimpios['url_imagen'] = null;
            }
        }

        if (!empty($errores)) {
            throw new Exception('Datos inválidos: ' . implode(', ', $errores));
        }

        return $datosLimpios;
    }

    /**
     * Formatea un producto para el frontend
     * 
     * @param array $producto
     * @return array
     */
    private function formatearProducto(array $producto): array
    {
        return [
            'id' => (int)$producto['id'],
            'nombre' => $producto['nombre'],
            'precio' => (float)$producto['precio'],
            'imagen' => $producto['url_imagen'],
            'fecha_creacion' => $producto['fecha_creacion'],
            'fecha_actualizacion' => $producto['fecha_actualizacion']
        ];
    }

    /**
     * Obtiene estadísticas básicas de productos
     * 
     * @return array
     */
    public function obtenerEstadisticas(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total,
                        AVG(precio) as precio_promedio,
                        MIN(precio) as precio_minimo,
                        MAX(precio) as precio_maximo
                    FROM {$this->tableName} 
                    WHERE estado_activo = 1";

            $stmt = $this->db->query($sql);
            $stats = $stmt->fetch();

            return [
                'total_productos' => (int)$stats['total'],
                'precio_promedio' => round((float)$stats['precio_promedio'], 2),
                'precio_minimo' => (float)$stats['precio_minimo'],
                'precio_maximo' => (float)$stats['precio_maximo']
            ];
        } catch (PDOException $e) {
            error_log("❌ Error obteniendo estadísticas: " . $e->getMessage());
            return [
                'total_productos' => 0,
                'precio_promedio' => 0,
                'precio_minimo' => 0,
                'precio_maximo' => 0
            ];
        }
    }
}
