<?php include 'conexao.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel Geral e Patrimônio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <?php
        // Totais de Animais
        $total_geral = $conn->query("SELECT COUNT(*) as total FROM animais WHERE status = 'ativo'")->fetch_assoc()['total'];
        $total_gado = $conn->query("SELECT COUNT(*) as total FROM animais WHERE status = 'ativo' AND especie = 'Gado'")->fetch_assoc()['total'];
        $total_ovelhas = $conn->query("SELECT COUNT(*) as total FROM animais WHERE status = 'ativo' AND especie = 'Ovelha'")->fetch_assoc()['total'];

        // Soma Financeira (Valor Total em Estoque)
        $valor_estoque = $conn->query("SELECT SUM(preco) as total_valor FROM animais WHERE status = 'ativo'")->fetch_assoc()['total_valor'] ?? 0;
        ?>

        <!-- Cards com Totais e Valor de Estoque -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white p-3 shadow-sm">
                    <h5>Total Geral</h5>
                    <h2><?php echo $total_geral; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white p-3 shadow-sm">
                    <h5>Total Gado</h5>
                    <h2><?php echo $total_gado; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white p-3 shadow-sm">
                    <h5>Total Ovelhas</h5>
                    <h2><?php echo $total_ovelhas; ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-dark text-white p-3 shadow-sm">
                    <h5>Valor Total do Rebanho</h5>
                    <h2>R$ <?php echo number_format($valor_estoque, 2, ',', '.'); ?></h2>
                </div>
            </div>
        </div>

        <h3 class="mb-3">Tabela de Preços e Animais Ativos</h3>
        
        <table class="table table-striped table-hover shadow bg-white">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Espécie</th>
                    <th>Identificação</th>
                    <th>Tipo</th>
                    <th>Preço Estimado</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM animais WHERE status = 'ativo' ORDER BY especie ASC, id DESC";
                $resultado = $conn->query($sql);

                if ($resultado->num_rows > 0) {
                    while($linha = $resultado->fetch_assoc()) {
                        $badge = ($linha['especie'] == 'Gado') ? 'bg-success' : 'bg-info';
                        echo "<tr>";
                        echo "<td>" . $linha['id'] . "</td>";
                        echo "<td><span class='badge ".$badge."'>" . $linha['especie'] . "</span></td>";
                        echo "<td>" . $linha['identificacao'] . "</td>";
                        echo "<td>" . $linha['tipo'] . "</td>";
                        echo "<td><strong>R$ " . number_format($linha['preco'], 2, ',', '.') . "</strong></td>";
                        echo "<td>
                                <a href='movimentar.php?id=".$linha['id']."&acao=venda' class='btn btn-sm btn-primary'>Registrar Venda</a>
                                <a href='movimentar.php?id=".$linha['id']."&acao=morte' class='btn btn-sm btn-danger'>Registrar Morte</a>
                              </td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>Nenhum animal ativo cadastrado.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="index.php" class="btn btn-primary">Cadastrar Novo Animal</a>
    </div>
</body>
</html>