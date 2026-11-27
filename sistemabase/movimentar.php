<?php
require_once 'conexao.php';

$id   = (int) ($_POST['id'] ?? $_GET['id'] ?? 0);
$acao = strtolower(trim((string) ($_POST['acao'] ?? $_GET['acao'] ?? '')));

if ($id <= 0) {
    header('Location: index.php');
    exit;
}

if (!in_array($acao, ['venda', 'morte'], true)) {
    header('Location: index.php');
    exit;
}

$novo_status = ($acao === 'venda') ? 'vendido' : 'morto';
$tipo_mov    = ($acao === 'venda') ? 'venda' : 'morte';
$msg_retorno = ($acao === 'venda') ? 'venda' : 'baixa';

$valor_informado = isset($_POST['valor_venda']) && $_POST['valor_venda'] !== ''
    ? (float) str_replace(',', '.', (string) $_POST['valor_venda'])
    : null;

$db = $conn ?? $conexao ?? $pdo ?? null;

if ($db instanceof mysqli) {
    $preco_final = null;

    if ($acao === 'venda') {
        if ($valor_informado !== null) {
            $preco_final = $valor_informado;
        } else {
            $stmtBusca = $db->prepare("SELECT preco FROM animais WHERE id = ? LIMIT 1");
            if ($stmtBusca) {
                $stmtBusca->bind_param("i", $id);
                $stmtBusca->execute();
                $res = $stmtBusca->get_result();
                if ($row = $res->fetch_assoc()) {
                    $preco_final = (float) $row['preco'];
                }
                $stmtBusca->close();
            }
        }
    }

    $stmtUpd = $db->prepare("UPDATE animais SET status = ? WHERE id = ?");
    $stmtUpd->bind_param("si", $novo_status, $id);

    if ($stmtUpd->execute()) {
        $stmtUpd->close();

        $stmtMov = $db->prepare("INSERT INTO movimentacoes (animal_id, tipo_movimentacao, valor_venda) VALUES (?, ?, ?)");
        if ($stmtMov) {
            $stmtMov->bind_param("isd", $id, $tipo_mov, $preco_final);
            $stmtMov->execute();
            $stmtMov->close();
        }

        header("Location: index.php?msg={$msg_retorno}&id={$id}");
        exit;
    } else {
        die("Erro ao atualizar movimentação: " . $stmtUpd->error);
    }

} elseif ($db instanceof PDO) {
    try {
        $preco_final = null;

        if ($acao === 'venda') {
            if ($valor_informado !== null) {
                $preco_final = $valor_informado;
            } else {
                $stmtBusca = $db->prepare("SELECT preco FROM animais WHERE id = ? LIMIT 1");
                $stmtBusca->execute([$id]);
                $row = $stmtBusca->fetch(PDO::FETCH_ASSOC);
                $preco_final = $row ? (float) $row['preco'] : 0.0;
            }
        }

        $stmtUpd = $db->prepare("UPDATE animais SET status = ? WHERE id = ?");
        $stmtUpd->execute([$novo_status, $id]);

        $stmtMov = $db->prepare("INSERT INTO movimentacoes (animal_id, tipo_movimentacao, valor_venda) VALUES (?, ?, ?)");
        $stmtMov->execute([$id, $tipo_mov, $preco_final]);

        header("Location: index.php?msg={$msg_retorno}&id={$id}");
        exit;
    } catch (PDOException $e) {
        die("Erro ao atualizar movimentação: " . $e->getMessage());
    }

} else {
    die("Erro: variável de conexão não encontrada em conexao.php.");
}