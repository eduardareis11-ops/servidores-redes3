<?php
include 'conexao.php';

$id = $_GET['id'];
$acao = $_GET['acao'];

$check = $conn->query("SELECT status, preco FROM animais WHERE id = '$id'");
$animal = $check->fetch_assoc();

if(!$animal || $animal['status'] != 'ativo') {
    die("<script>alert('Erro: Este animal já foi retirado da criação!'); window.location.href='listar.php';</script>");
}

// Atualiza status do animal
$sql_update = "UPDATE animais SET status = '$acao' WHERE id = '$id'";

if ($conn->query($sql_update) === TRUE) {
    $valor_venda = ($acao == 'venda') ? $animal['preco'] : 0.00;
    
    // Registra na tabela de movimentações
    $sql_log = "INSERT INTO movimentacoes (animal_id, tipo_movimentacao, valor_venda) VALUES ('$id', '$acao', '$valor_venda')";
    $conn->query($sql_log);

    echo "<script>alert('Movimentação ($acao) registrada com sucesso!'); window.location.href = 'listar.php';</script>";
} else {
    echo "Erro: " . $conn->error;
}
$conn->close();
?>