<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "sistema_base";

$conn = new mysqli($servidor, $usuario, $senha, $banco);

if ($conn->connect_error) {
    die("Erro na conexão com o Banco de Dados: " . $conn->connect_error);
}
?>