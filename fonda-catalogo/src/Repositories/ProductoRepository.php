<?php

namespace FondaJuanita\Repositories;

use PDO;
use PDOException;

class ProductoRepository
{
    private $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Obtener todos los productos
    public function getAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos ORDER BY fecha_creacion DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtener un producto por ID
    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        return $producto ?: null;
    }

    // Crear un nuevo producto
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("INSERT INTO productos (nombre, precio, imagen) VALUES (?, ?, ?)");
        $stmt->execute([
            $data['nombre'],
            $data['precio'],
            $data['imagen'] ?? null
        ]);
        return (int)$this->db->lastInsertId();
    }

    // Actualizar un producto existente
    public function update(int $id, array $data): bool
    {
        $sql = "UPDATE productos SET nombre = ?, precio = ?";
        $params = [$data['nombre'], $data['precio']];
        if (isset($data['imagen'])) {
            $sql .= ", imagen = ?";
            $params[] = $data['imagen'];
        }
        $sql .= " WHERE id = ?";
        $params[] = $id;
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    // Eliminar un producto
    public function delete(int $id): bool
    {
        // Obtener imagen para eliminar archivo físico si es necesario
        $stmt = $this->db->prepare("SELECT imagen FROM productos WHERE id = ?");
        $stmt->execute([$id]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($producto && $producto['imagen']) {
            $imagePath = __DIR__ . '/../../../uploads/' . $producto['imagen'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        $stmt = $this->db->prepare("DELETE FROM productos WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
