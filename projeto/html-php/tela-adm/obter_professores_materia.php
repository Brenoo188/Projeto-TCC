<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

if ($_SESSION['tipo_usuario'] !== 'Administrador') {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado']);
    exit();
}

if (!isset($_GET['id_materia'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID da matéria não fornecido']);
    exit();
}

$id_materia = (int)$_GET['id_materia'];

// Obter professores vinculados à matéria
$stmt = $conexao->prepare("
    SELECT u.id, u.nome_user, u.email_user
    FROM usuarios u
    INNER JOIN usuario_materia um ON u.id = um.id_usuario
    WHERE um.id_materia = ? AND u.tipo_usuario = 'Professor'
    ORDER BY u.nome_user
");
$stmt->bind_param("i", $id_materia);
$stmt->execute();
$result = $stmt->get_result();

$professores = [];
while ($row = $result->fetch_assoc()) {
    $professores[] = $row;
}

header('Content-Type: application/json');
echo json_encode($professores);
?>