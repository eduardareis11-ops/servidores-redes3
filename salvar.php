<?php
include 'conexao.php';

$especie = $_POST['especie'];
$identificacao = $_POST['identificacao'];
$tipo = $_POST['tipo'];
$preco = $_POST['preco'];
$origem = $_POST['origem'];

if(empty($identificacao) || empty($tipo) || empty($especie)) {
    die("<script>alert('Erro: Preencha todos os campos obrigatórios!'); window.history.back();</script>");
}

$sql_animal = "INSERT INTO animais (especie, identificacao, tipo, preco) VALUES ('$especie', '$identificacao', '$tipo', '$preco')";

if ($conn->query($sql_animal) === TRUE) {
    $animal_id = $conn->insert_id;
    
    $sql_movimentacao = "INSERT INTO movimentacoes (animal_id, tipo_movimentacao) VALUES ('$animal_id', '$origem')";
    $conn->query($sql_movimentacao);

    echo "<script>alert('Animal registrado com sucesso!'); window.location.href = 'index.php';</script>";
} else {
    echo "Erro ao salvar: " . $conn->error;
}
$conn->close();
?>