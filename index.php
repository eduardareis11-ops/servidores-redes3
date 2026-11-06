<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Animais - Sistema Pecuária</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <div class="card shadow p-4 mb-4">
            <h3 class="mb-3">Registrar Novo Animal</h3>
            <form action="salvar.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Espécie (Categoria)</label>
                    <select class="form-select" name="especie" required>
                        <option value="Gado">Gado (Bovino)</option>
                        <option value="Ovelha">Ovelha (Ovino)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Identificação (Brinco/Nome)</label>
                    <input type="text" class="form-control" name="identificacao" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Tipo / Classificação (Ex: Bezerro, Vaca, Borrego)</label>
                    <input type="text" class="form-control" name="tipo" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Preço Estimado / Valor Base (R$)</label>
                    <input type="number" step="0.01" class="form-control" name="preco" placeholder="0.00" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Origem do Registro</label>
                    <select class="form-select" name="origem" required>
                        <option value="cadastro_inicial">Animal já existente na propriedade</option>
                        <option value="nascimento">Novo Nascimento</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Salvar Cadastro</button>
            </form>
        </div>
        <a href="listar.php" class="btn btn-warning">Painel do Rebanho (Estoque)</a>
    </div>
</body>
</html>