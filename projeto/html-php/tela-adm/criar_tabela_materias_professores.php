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

// Criar tabela materias_professores se não existir
$sql_criar_tabela = "
CREATE TABLE IF NOT EXISTS `materias_professores` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_materia` INT NOT NULL COMMENT 'ID da matéria',
  `id_professor` INT NOT NULL COMMENT 'ID do professor',
  `data_vinculacao` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de vínculo',
  `status` ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo' COMMENT 'Status do vínculo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `materia_professor_UNIQUE` (`id_materia`, `id_professor`),
  INDEX `idx_id_materia` (`id_materia`),
  INDEX `idx_id_professor` (`id_professor`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`id_materia`) REFERENCES `materias`(`id_materia`) ON DELETE CASCADE,
  FOREIGN KEY (`id_professor`) REFERENCES `usuarios`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Vínculo entre matérias e professores';

";

if (mysqli_query($conexao, $sql_criar_tabela)) {
    echo json_encode(['success' => true, 'message' => 'Tabela materias_professores criada com sucesso!']);
} else {
    echo json_encode(['error' => 'Erro ao criar tabela: ' . mysqli_error($conexao)]);
}
?>