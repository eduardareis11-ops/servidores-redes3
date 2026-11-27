<?php
// Garante que o arquivo só seja acessado via envio do formulário (POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: cadastro.php');
    exit;
}

require_once 'conexao.php';

// Recebe e trata os dados enviados pelo formulário de cadastro.php
$especie       = trim($_POST['especie'] ?? 'Gado');
$tipo          = trim($_POST['tipo'] ?? '');
$identificacao = trim($_POST['identificacao'] ?? '');
$preco         = (float) str_replace(',', '.', $_POST['preco'] ?? 0);
$origem        = trim($_POST['origem'] ?? 'cadastro_inicial');
$status        = 'ativo';

// Detecta automaticamente se conexao.php usa MySQLi ou PDO
$db = $conn ?? $conexao ?? $pdo ?? null;

if ($db instanceof mysqli) {
    // 1. Insere o animal na tabela 'animais' usando Prepared Statement (segurança contra SQL Injection)
    $stmt = $db->prepare("INSERT INTO animais (especie, identificacao, tipo, preco, status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssds", $especie, $identificacao, $tipo, $preco, $status);

    if ($stmt->execute()) {
        $animal_id = $db->insert_id;
        $stmt->close();

        // 2. Registra a entrada inicial no histórico ('movimentacoes')
        $stmtMov = $db->prepare("INSERT INTO movimentacoes (animal_id, tipo_movimentacao) VALUES (?, ?)");
        if ($stmtMov) {
            $stmtMov->bind_param("is", $animal_id, $origem);
            $stmtMov->execute();
            $stmtMov->close();
        }

        // Redireciona sem travar a tela para disparar a animação fluida
        header("Location: index.php?msg=sucesso");
        exit;
    } else {
        die("Erro ao salvar no banco de dados: " . $stmt->error);
    }

} elseif ($db instanceof PDO) {
    try {
        // 1. Insere o animal na tabela 'animais'
        $stmt = $db->prepare("INSERT INTO animais (especie, identificacao, tipo, preco, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$especie, $identificacao, $tipo, $preco, $status]);

        $animal_id = $db->lastInsertId();

        // 2. Registra a entrada inicial no histórico ('movimentacoes')
        $stmtMov = $db->prepare("INSERT INTO movimentacoes (animal_id, tipo_movimentacao) VALUES (?, ?)");
        $stmtMov->execute([$animal_id, $origem]);

        // Redireciona sem travar a tela para disparar a animação fluida
        header("Location: index.php?msg=sucesso");
        exit;
    } catch (PDOException $e) {
        die("Erro ao salvar no banco de dados: " . $e->getMessage());
    }

} else {
    die("Erro: variável de conexão não encontrada em conexao.php.");
}