<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? 'login';
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Completa los campos.']);
    exit;
}

try {
    require_once 'conexion.php';
    $pdo = conexion();

    if ($action === 'register') {
        $existing = $pdo->prepare('SELECT id FROM usuarios WHERE email = :email LIMIT 1');
        $existing->execute([':email' => $email]);

        if ($existing->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Ese correo ya está registrado.']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $insert = $pdo->prepare('INSERT INTO usuarios (email, password) VALUES (:email, :password)');
        $insert->execute([':email' => $email, ':password' => $hash]);

        echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente.']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT email, password FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
        exit;
    }

    echo json_encode(['success' => true, 'message' => 'Inicio de sesión correcto.']);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
