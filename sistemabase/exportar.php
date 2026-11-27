<?php
require_once 'conexao.php';

$animais = [];
$sql = "SELECT id, especie, identificacao, tipo, preco, status FROM animais ORDER BY id DESC";
$db = $conn ?? $conexao ?? $pdo ?? null;

if ($db instanceof mysqli) {
    $res = $db->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $animais[] = $row;
        }
    }
} elseif ($db instanceof PDO) {
    $animais = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

$nomeArquivo = 'relatorio_agropet_' . date('Y-m-d') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

$saida = fopen('php://output', 'w');

// BOM UTF-8 para abrir acentos perfeitamente no Excel
fprintf($saida, chr(0xEF) . chr(0xBB) . chr(0xBF));

// Cabeçalho da planilha
fputcsv($saida, ['ID', 'Especie', 'Identificacao', 'Tipo', 'Valor Base (R$)', 'Status'], ';');

foreach ($animais as $a) {
    $precoFormatado = number_format((float)($a['preco'] ?? 0), 2, ',', '');
    fputcsv($saida, [
        $a['id'] ?? '',
        $a['especie'] ?? '',
        $a['identificacao'] ?? '',
        $a['tipo'] ?? '',
        $precoFormatado,
        strtoupper((string)($a['status'] ?? ''))
    ], ';');
}

fclose($saida);
exit;