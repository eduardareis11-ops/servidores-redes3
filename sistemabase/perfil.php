<?php
require_once 'conexao.php';

$db = $conn ?? $conexao ?? $pdo ?? null;
$mensagem = '';

if ($db instanceof mysqli) {
    $db->query("CREATE TABLE IF NOT EXISTS perfil_fazenda (
        id INT PRIMARY KEY,
        nome_produtor VARCHAR(100) NOT NULL,
        cpf_cnpj VARCHAR(20) DEFAULT '',
        telefone VARCHAR(20) DEFAULT '',
        nome_fazenda VARCHAR(120) NOT NULL,
        cidade_estado VARCHAR(100) DEFAULT '',
        area_hectares DECIMAL(10,2) DEFAULT 0.00,
        capacidade_rebanho INT DEFAULT 50,
        meta_patrimonio DECIMAL(12,2) DEFAULT 50000.00,
        especialidade VARCHAR(80) DEFAULT 'Pecuária Mista',
        observacoes_progresso TEXT,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $db->query("INSERT IGNORE INTO perfil_fazenda 
        (id, nome_produtor, nome_fazenda, cidade_estado, area_hectares, capacidade_rebanho, meta_patrimonio, especialidade, observacoes_progresso) 
        VALUES (1, 'Produtor Rural', 'Fazenda AgroPet', 'Boa Viagem - CE', 25.00, 50, 50000.00, 'Pecuária Mista', 'Foco em melhoria genética e expansão do rebanho ativo.')");

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nome_produtor         = trim($_POST['nome_produtor'] ?? '');
        $cpf_cnpj              = trim($_POST['cpf_cnpj'] ?? '');
        $telefone              = trim($_POST['telefone'] ?? '');
        $nome_fazenda          = trim($_POST['nome_fazenda'] ?? '');
        $cidade_estado         = trim($_POST['cidade_estado'] ?? '');
        $area_hectares         = max(0, (float) str_replace(',', '.', $_POST['area_hectares'] ?? 0));
        $capacidade_rebanho    = max(1, (int) ($_POST['capacidade_rebanho'] ?? 50));
        $meta_patrimonio       = max(1, (float) str_replace(',', '.', $_POST['meta_patrimonio'] ?? 50000));
        $especialidade         = trim($_POST['especialidade'] ?? 'Pecuária Mista');
        $observacoes_progresso = trim($_POST['observacoes_progresso'] ?? '');

        $stmt = $db->prepare("UPDATE perfil_fazenda SET 
            nome_produtor = ?, cpf_cnpj = ?, telefone = ?, nome_fazenda = ?, 
            cidade_estado = ?, area_hectares = ?, capacidade_rebanho = ?, 
            meta_patrimonio = ?, especialidade = ?, observacoes_progresso = ? 
            WHERE id = 1");

        if ($stmt) {
            $stmt->bind_param(
                "sssssdidss",
                $nome_produtor, $cpf_cnpj, $telefone, $nome_fazenda,
                $cidade_estado, $area_hectares, $capacidade_rebanho,
                $meta_patrimonio, $especialidade, $observacoes_progresso
            );
            if ($stmt->execute()) {
                $mensagem = 'Dados do produtor, fazenda e metas atualizados com sucesso!';
            }
            $stmt->close();
        }
    }

    $resPerfil = $db->query("SELECT * FROM perfil_fazenda WHERE id = 1");
    $perfil = $resPerfil ? $resPerfil->fetch_assoc() : [];

    $qtdAtivos = 0;
    $patrimonioAtual = 0.0;
    $resAnimais = $db->query("SELECT preco FROM animais WHERE status = 'ativo'");
    if ($resAnimais) {
        while ($row = $resAnimais->fetch_assoc()) {
            $qtdAtivos++;
            $patrimonioAtual += (float) ($row['preco'] ?? 0);
        }
    }

    $receitaVendas = 0.0;
    $resVendas = $db->query("SELECT COALESCE(SUM(valor_venda), 0) AS total FROM movimentacoes WHERE LOWER(TRIM(tipo_movimentacao)) = 'venda'");
    if ($resVendas && $rowVenda = $resVendas->fetch_assoc()) {
        $receitaVendas = (float) $rowVenda['total'];
    }
}

$metaPatrimonio = (float) ($perfil['meta_patrimonio'] ?? 50000);
$capacidade     = (int) ($perfil['capacidade_rebanho'] ?? 50);
$areaHa         = (float) ($perfil['area_hectares'] ?? 0);

$percMeta      = $metaPatrimonio > 0 ? min(100, round(($patrimonioAtual / $metaPatrimonio) * 100, 1)) : 0;
$percLotacao   = $capacidade > 0 ? min(100, round(($qtdAtivos / $capacidade) * 100, 1)) : 0;
$taxaLotacaoHa = $areaHa > 0 ? number_format($qtdAtivos / $areaHa, 2, ',', '.') : '0,00';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroPet - Perfil do Produtor e Fazenda</title>
    <script>
        (function () {
            const savedTheme = localStorage.getItem('agropet_theme');
            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar navbar-modern py-3 mb-4 sticky-top">
    <div class="container d-flex justify-content-between align-items-center gap-2" style="max-width: 1140px;">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2 m-0" href="index.php">
            <span class="brand-icon"><i class="bi bi-shield-fill-plus"></i></span>
            <span class="brand-text">AgroPet</span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <a href="index.php" class="btn btn-soft-neutral px-3 py-2 d-flex align-items-center gap-2 fw-semibold" style="font-size: 0.9rem;">
                <i class="bi bi-arrow-left"></i>
                <span>Voltar ao Painel</span>
            </a>
            <a href="cadastro.php" class="btn-agro">
                <i class="bi bi-plus-circle"></i>
                <span class="d-none d-sm-inline">Novo Animal</span>
            </a>
        </div>
    </div>
</nav>

<main class="container pb-5" style="max-width: 1140px;">

    <?php if ($mensagem): ?>
        <div class="alert alert-success rounded-4 border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>

    <!-- Painel de Progresso do Produtor -->
    <section class="card card-modern p-3 p-sm-4 mb-4">
        <span class="header-badge mb-2">
            <i class="bi bi-award"></i> Indicadores de Evolução
        </span>
        <h4 class="fw-bold mb-1">Progresso da Propriedade</h4>
        <p class="text-muted small mb-4">Cálculo automático cruzando as metas da fazenda com o rebanho ativo cadastrado.</p>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-4 border h-100">
                    <small class="text-muted d-block text-uppercase fw-semibold">Taxa de Lotação</small>
                    <strong class="fs-4 d-block my-1"><?php echo $taxaLotacaoHa; ?> cab/ha</strong>
                    <small class="text-muted"><?php echo $qtdAtivos; ?> ativos em <?php echo number_format($areaHa, 1, ',', '.'); ?> hectares</small>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-4 border h-100">
                    <small class="text-muted d-block text-uppercase fw-semibold">Patrimônio vs Meta</small>
                    <strong class="fs-4 text-primary d-block my-1">R$ <?php echo number_format($patrimonioAtual, 2, ',', '.'); ?></strong>
                    <small class="text-muted">Meta: R$ <?php echo number_format($metaPatrimonio, 2, ',', '.'); ?></small>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="p-3 rounded-4 border h-100">
                    <small class="text-muted d-block text-uppercase fw-semibold">Receita em Vendas</small>
                    <strong class="fs-4 d-block my-1" style="color: #0284c7;">R$ <?php echo number_format($receitaVendas, 2, ',', '.'); ?></strong>
                    <small class="text-muted">Foco: <?php echo htmlspecialchars($perfil['especialidade'] ?? '', ENT_QUOTES, 'UTF-8'); ?></small>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold small">Progresso da Meta Financeira do Rebanho</span>
                <span class="fw-bold text-primary small"><?php echo $percMeta; ?>%</span>
            </div>
            <div class="progress rounded-pill" style="height: 16px;">
                <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $percMeta; ?>%;">
                    <?php echo $percMeta; ?>%
                </div>
            </div>
        </div>

        <div>
            <div class="d-flex justify-content-between mb-1">
                <span class="fw-semibold small">Ocupação da Capacidade da Fazenda (<?php echo $qtdAtivos; ?> / <?php echo $capacidade; ?> cabeças)</span>
                <span class="fw-bold text-success small"><?php echo $percLotacao; ?>%</span>
            </div>
            <div class="progress rounded-pill" style="height: 16px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $percLotacao; ?>%;">
                    <?php echo $percLotacao; ?>%
                </div>
            </div>
        </div>
    </section>

    <!-- Formulário de Cadastro/Edição do Perfil e Fazenda -->
    <section class="card card-modern p-3 p-sm-4">
        <h5 class="fw-bold mb-1">Dados do Produtor e da Fazenda</h5>
        <p class="text-muted small mb-4">Atualize as informações cadastrais e ajuste as metas de crescimento.</p>

        <form method="POST" action="perfil.php">
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Nome do Produtor</label>
                    <input type="text" name="nome_produtor" class="form-control" required value="<?php echo htmlspecialchars($perfil['nome_produtor'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">CPF / CNPJ</label>
                    <input type="text" name="cpf_cnpj" class="form-control" placeholder="000.000.000-00" value="<?php echo htmlspecialchars($perfil['cpf_cnpj'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Telefone / WhatsApp</label>
                    <input type="text" name="telefone" class="form-control" placeholder="(88) 99999-9999" value="<?php echo htmlspecialchars($perfil['telefone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Nome da Fazenda / Propriedade</label>
                    <input type="text" name="nome_fazenda" class="form-control" required value="<?php echo htmlspecialchars($perfil['nome_fazenda'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-md-6">
                    <label class="form-label fw-semibold">Cidade / Estado</label>
                    <input type="text" name="cidade_estado" class="form-control" placeholder="Ex: Boa Viagem - CE" value="<?php echo htmlspecialchars($perfil['cidade_estado'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Área Total (Hectares)</label>
                    <input type="number" step="0.01" min="0" inputmode="decimal" name="area_hectares" class="form-control" value="<?php echo htmlspecialchars($perfil['area_hectares'] ?? '0', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Capacidade Máx. (Cabeças)</label>
                    <input type="number" min="1" inputmode="numeric" name="capacidade_rebanho" class="form-control" required value="<?php echo htmlspecialchars($perfil['capacidade_rebanho'] ?? '50', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Meta de Patrimônio (R$)</label>
                    <input type="number" step="0.01" min="1" inputmode="decimal" name="meta_patrimonio" class="form-control" required value="<?php echo htmlspecialchars($perfil['meta_patrimonio'] ?? '50000', ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold">Foco / Especialidade</label>
                    <select name="especialidade" class="form-select">
                        <?php
                        $opcoes = ['Pecuária de Corte', 'Pecuária Leiteira', 'Pecuária Mista', 'Ovinocaprinocultura', 'Cria e Recria'];
                        foreach ($opcoes as $op) {
                            $sel = (($perfil['especialidade'] ?? '') === $op) ? 'selected' : '';
                            echo "<option value=\"$op\" $sel>$op</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Anotações de Progresso, Pastagem e Planejamento</label>
                    <textarea name="observacoes_progresso" rows="3" class="form-control" placeholder="Registre melhorias no pasto, vacinação, metas de engorda..."><?php echo htmlspecialchars($perfil['observacoes_progresso'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                </div>

                <div class="col-12 text-end">
                    <button type="submit" class="btn-agro px-4">
                        <i class="bi bi-check2-circle"></i> Salvar Perfil e Metas
                    </button>
                </div>
            </div>
        </form>
    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>