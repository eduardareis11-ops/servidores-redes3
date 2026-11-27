<?php
require_once 'conexao.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: listar.php');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM animais WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$animal = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Bloqueia edição se não existir ou já estiver vendido/morto
if (!$animal || $animal['status'] !== 'ativo') {
    header('Location: listar.php');
    exit;
}

$colValor = array_key_exists('valor_estimado', $animal) ? 'valor_estimado' : 'valor';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $especie = trim($_POST['especie'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');
    $valor = (float) str_replace(',', '.', $_POST['valor'] ?? 0);

    $up = $conn->prepare("UPDATE animais SET nome = ?, especie = ?, categoria = ?, $colValor = ? WHERE id = ? AND status = 'ativo'");
    $up->bind_param("sssdi", $nome, $especie, $categoria, $valor, $id);
    $up->execute();
    $up->close();

    header('Location: listar.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroPet - Editar Animal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width: 600px;">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <h4 class="fw-bold mb-3">Editar Animal #<?= $animal['id'] ?></h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Identificação / Nome</label>
                    <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($animal['nome']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Espécie</label>
                    <input type="text" name="especie" class="form-control" required value="<?= htmlspecialchars($animal['especie']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Categoria</label>
                    <input type="text" name="categoria" class="form-control" required value="<?= htmlspecialchars($animal['categoria']) ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Valor Estimado (R$)</label>
                    <input type="number" step="0.01" name="valor" class="form-control" required value="<?= htmlspecialchars($animal[$colValor]) ?>">
                </div>
                <div class="d-flex justify-content-between">
                    <a href="listar.php" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>