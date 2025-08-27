<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php'; // en caso de usar autoload
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Models/Database.php';
require_once __DIR__ . '/../src/Repositories/ProductoRepository.php';
require_once __DIR__ . '/../src/Controllers/ProductoController.php';

use FondaJuanita\Repositories\ProductoRepository;
use FondaJuanita\Controllers\ProductoController;

try {
    // Conexión PDO
    $dbInstance = Database::getInstance();
    $pdo = $dbInstance->getConnection();

    $repository = new ProductoRepository($pdo);
    $controller = new ProductoController($repository);

    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $result = $controller->index();
        echo json_encode(['success' => true, 'data' => $result]);
        exit;
    }

    // Para simplificar, aceptamos POST con campo 'action'
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? null;

        if ($action === 'create') {
            $id = $controller->store($input['data'] ?? []);
            echo json_encode(['success' => true, 'id' => $id]);
            exit;
        }

        if ($action === 'update') {
            $ok = $controller->update($input['id'] ?? 0, $input['data'] ?? []);
            echo json_encode(['success' => (bool)$ok]);
            exit;
        }

        if ($action === 'delete') {
            $ok = $controller->destroy($input['id'] ?? 0);
            echo json_encode(['success' => (bool)$ok]);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
} catch (Exception $e) {
    error_log($e->getMessage()); // Log del error para depuración
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
