<?php
if (!defined('APP_LOADED')) {
    header('Location: index.php');
    exit;
}

require_once 'conexao.php';

$animais = [];
$movimentacoes_por_animal = [];
$faturamento_vendas = 0.0;
$tem_registros_venda_mov = false;
$erro_banco = null;

$sqlAnimais = "SELECT * FROM animais ORDER BY id DESC";
$sqlMov     = "SELECT * FROM movimentacoes ORDER BY id ASC";

$db = $conn ?? $conexao ?? $pdo ?? null;

if ($db instanceof mysqli) {
    $resAnimais = $db->query($sqlAnimais);
    if ($resAnimais) {
        while ($row = $resAnimais->fetch_assoc()) {
            $animais[] = $row;
        }
    } else {
        $erro_banco = "Erro ao consultar animais: " . $db->error;
    }

    $resMov = $db->query($sqlMov);
    if ($resMov) {
        while ($m = $resMov->fetch_assoc()) {
            $aid = (int)($m['animal_id'] ?? 0);
            $tipoM = strtolower(trim((string)($m['tipo_movimentacao'] ?? '')));
            $valM  = (float)($m['valor_venda'] ?? 0);
            $dataRaw = $m['data_movimentacao'] ?? $m['data'] ?? $m['created_at'] ?? $m['data_hora'] ?? '';

            if ($tipoM === 'venda') {
                $tem_registros_venda_mov = true;
                $faturamento_vendas += $valM;
            }

            $movimentacoes_por_animal[$aid][] = [
                'id'    => (int)($m['id'] ?? 0),
                'tipo'  => $tipoM,
                'valor' => $valM,
                'data'  => $dataRaw ? date('d/m/Y H:i', strtotime($dataRaw)) : 'Movimentação #' . (int)($m['id'] ?? 0)
            ];
        }
    }
} elseif ($db instanceof PDO) {
    try {
        $animais = $db->query($sqlAnimais)->fetchAll(PDO::FETCH_ASSOC);
        $todasMov = $db->query($sqlMov)->fetchAll(PDO::FETCH_ASSOC);

        foreach ($todasMov as $m) {
            $aid = (int)($m['animal_id'] ?? 0);
            $tipoM = strtolower(trim((string)($m['tipo_movimentacao'] ?? '')));
            $valM  = (float)($m['valor_venda'] ?? 0);
            $dataRaw = $m['data_movimentacao'] ?? $m['data'] ?? $m['created_at'] ?? $m['data_hora'] ?? '';

            if ($tipoM === 'venda') {
                $tem_registros_venda_mov = true;
                $faturamento_vendas += $valM;
            }

            $movimentacoes_por_animal[$aid][] = [
                'id'    => (int)($m['id'] ?? 0),
                'tipo'  => $tipoM,
                'valor' => $valM,
                'data'  => $dataRaw ? date('d/m/Y H:i', strtotime($dataRaw)) : 'Movimentação #' . (int)($m['id'] ?? 0)
            ];
        }
    } catch (PDOException $e) {
        $erro_banco = "Erro ao consultar dados: " . $e->getMessage();
    }
} else {
    $erro_banco = "Conexão com o banco de dados não encontrada em conexao.php.";
}

$patrimonio_total = 0.0;
$total_ativos = 0;
$qtd_gado = 0;
$qtd_ovelha = 0;
$total_finalizados = 0;
$faturamento_fallback = 0.0;

foreach ($animais as $row) {
    $status = strtolower(trim((string)($row['status'] ?? 'ativo')));
    $especie = strtolower(trim((string)($row['especie'] ?? '')));
    $preco = (float)($row['preco'] ?? 0);

    if ($status === 'ativo') {
        $patrimonio_total += $preco;
        $total_ativos++;
        if ($especie === 'gado') {
            $qtd_gado++;
        } elseif ($especie === 'ovelha') {
            $qtd_ovelha++;
        }
    } else {
        $total_finalizados++;
        if ($status === 'vendido') {
            $faturamento_fallback += $preco;
        }
    }
}

if (!$tem_registros_venda_mov) {
    $faturamento_vendas = $faturamento_fallback;
}
?>

<?php if ($erro_banco): ?>
    <div class="alert alert-danger rounded-4 border-0 shadow-sm mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <?php echo htmlspecialchars($erro_banco, ENT_QUOTES, 'UTF-8'); ?>
    </div>
<?php endif; ?>

<!-- 4 Cards de Métricas -->
<section class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card p-3 p-sm-4 h-100">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Patrimônio Ativo</span>
                    <h4 class="fw-bold text-primary mt-1 mb-0">
                        R$ <?php echo number_format($patrimonio_total, 2, ',', '.'); ?>
                    </h4>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                    <i class="bi bi-cash-coin fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card p-3 p-sm-4 h-100">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Receita em Vendas</span>
                    <h4 class="fw-bold mt-1 mb-0" style="color: #0284c7;">
                        R$ <?php echo number_format($faturamento_vendas, 2, ',', '.'); ?>
                    </h4>
                </div>
                <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                    <i class="bi bi-graph-up fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card p-3 p-sm-4 h-100">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Rebanho Ativo</span>
                    <h4 class="fw-bold mt-1 mb-0">
                        <?php echo $total_ativos; ?> <span class="fs-6 fw-normal text-muted">cabeças</span>
                    </h4>
                    <small class="text-muted"><?php echo $qtd_gado; ?> Gado · <?php echo $qtd_ovelha; ?> Ovelha</small>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                    <i class="bi bi-clipboard-data fs-4"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-modern stat-card p-3 p-sm-4 h-100">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase">Vendidos / Baixas</span>
                    <h4 class="fw-bold text-secondary mt-1 mb-0">
                        <?php echo $total_finalizados; ?> <span class="fs-6 fw-normal text-muted">registros</span>
                    </h4>
                    <small class="text-muted">Total histórico: <?php echo count($animais); ?></small>
                </div>
                <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                    <i class="bi bi-archive fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Gráficos Interativos (Chart.js) -->
<section class="row g-3 mb-4 no-print">
    <div class="col-12 col-lg-5">
        <div class="card card-modern p-3 p-sm-4 h-100">
            <h6 class="fw-bold mb-1">Distribuição do Rebanho Ativo</h6>
            <p class="text-muted small mb-3">Proporção por espécie cadastrada</p>
            <div style="position: relative; height: 220px;">
                <canvas id="chartEspecies"></canvas>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-7">
        <div class="card card-modern p-3 p-sm-4 h-100">
            <h6 class="fw-bold mb-1">Resumo Financeiro da Propriedade</h6>
            <p class="text-muted small mb-3">Comparativo entre valor estimado ativo e vendas realizadas (R$)</p>
            <div style="position: relative; height: 220px;">
                <canvas id="chartFinanceiro"></canvas>
            </div>
        </div>
    </div>
</section>

<!-- Tabela com Busca, Filtros, Exportar CSV e Imprimir -->
<section class="card card-modern p-3 p-sm-4">
    <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold mb-1">Rebanho Cadastrado</h5>
            <p class="text-muted small mb-0">Gerencie registros, visualize o histórico relacional ou exporte relatórios.</p>
        </div>

        <div class="d-flex flex-column flex-sm-row flex-wrap gap-2 align-items-stretch align-items-sm-center no-print">
            <div class="input-group input-group-sm" style="min-width: 190px; flex: 1 1 200px;">
                <span class="input-group-text input-group-text-modern">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="buscaTabela" class="form-control input-with-prefix" placeholder="Buscar animal...">
            </div>

            <div class="btn-group btn-group-sm" role="group" aria-label="Filtrar por status">
                <button type="button" class="btn btn-outline-secondary filter-btn active" data-filter="todos">Todos</button>
                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="ativo">Ativos</button>
                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="vendido">Vendidos</button>
                <button type="button" class="btn btn-outline-secondary filter-btn" data-filter="morto">Baixas</button>
            </div>

            <div class="d-flex gap-2">
                <a href="exportar.php" class="btn btn-sm btn-soft-neutral flex-fill px-3" title="Baixar planilha Excel (CSV)">
                    <i class="bi bi-file-earmark-spreadsheet text-primary me-1"></i> CSV
                </a>

                <button type="button" onclick="window.print()" class="btn btn-sm btn-soft-neutral flex-fill px-3" title="Imprimir ou Salvar PDF">
                    <i class="bi bi-printer text-primary me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="tabelaRebanho">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Espécie</th>
                    <th>Identificação</th>
                    <th>Tipo</th>
                    <th>Valor Base</th>
                    <th>Status</th>
                    <th class="text-end col-acoes">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($animais) > 0): ?>
                    <?php foreach ($animais as $indice => $animal): ?>
                        <?php
                            $id = (int)($animal['id'] ?? 0);
                            $especieRaw = (string)($animal['especie'] ?? 'Gado');
                            $especieNorm = strtolower(trim($especieRaw));
                            $identificacao = (string)($animal['identificacao'] ?? $animal['nome'] ?? '-');
                            $tipo = (string)($animal['tipo'] ?? '-');
                            $preco = (float)($animal['preco'] ?? 0);
                            $statusRaw = strtolower(trim((string)($animal['status'] ?? 'ativo')));

                            $destacarLinha = false;
                            if (($msg ?? '') === 'sucesso' and $indice === 0) {
                                $destacarLinha = true;
                            }
                            if (in_array(($msg ?? ''), ['venda', 'baixa', 'edicao'], true) and ($id_destaque ?? 0) === $id) {
                                $destacarLinha = true;
                            }
                            $classeAnimacao = $destacarLinha ? ' linha-recem-cadastrada' : '';
                        ?>
                        <tr class="linha-animal<?php echo $classeAnimacao; ?>" data-status="<?php echo htmlspecialchars($statusRaw, ENT_QUOTES, 'UTF-8'); ?>">
                            <td class="fw-semibold text-muted">#<?php echo $id; ?></td>
                            <td>
                                <?php if ($especieNorm === 'gado'): ?>
                                    <span class="badge badge-agro-primary px-2 py-1 rounded-2">Gado</span>
                                <?php else: ?>
                                    <span class="badge badge-agro-secondary px-2 py-1 rounded-2"><?php echo htmlspecialchars($especieRaw, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?php echo htmlspecialchars($identificacao, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="text-muted"><?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="fw-semibold text-nowrap">R$ <?php echo number_format($preco, 2, ',', '.'); ?></td>
                            <td>
                                <?php if ($statusRaw === 'ativo'): ?>
                                    <span class="badge bg-primary rounded-pill px-3">Ativo</span>
                                <?php elseif ($statusRaw === 'vendido'): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3">Vendido</span>
                                <?php else: ?>
                                    <span class="badge bg-danger rounded-pill px-3">Morto</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end text-nowrap col-acoes">
                                <button type="button"
                                    class="btn btn-sm btn-soft-neutral rounded-3 btn-historico"
                                    data-id="<?php echo $id; ?>"
                                    data-nome="<?php echo htmlspecialchars($identificacao, ENT_QUOTES, 'UTF-8'); ?>"
                                    title="Ver Histórico de Movimentações">
                                    <i class="bi bi-clock-history"></i>
                                </button>

                                <?php if ($statusRaw === 'ativo'): ?>
                                    <button type="button"
                                        class="btn btn-sm btn-soft-neutral rounded-3 btn-editar"
                                        data-id="<?php echo $id; ?>"
                                        data-especie="<?php echo htmlspecialchars($especieRaw, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-tipo="<?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-nome="<?php echo htmlspecialchars($identificacao, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-preco="<?php echo number_format($preco, 2, '.', ''); ?>"
                                        title="Editar Cadastro">
                                        <i class="bi bi-pencil"></i>
                                    </button>

                                    <button type="button"
                                        class="btn btn-sm btn-outline-primary rounded-3 btn-acao"
                                        data-id="<?php echo $id; ?>"
                                        data-nome="<?php echo htmlspecialchars($identificacao, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-preco="<?php echo number_format($preco, 2, '.', ''); ?>"
                                        data-acao="venda">
                                        <i class="bi bi-currency-dollar"></i> Vender
                                    </button>

                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger rounded-3 btn-acao"
                                        data-id="<?php echo $id; ?>"
                                        data-nome="<?php echo htmlspecialchars($identificacao, ENT_QUOTES, 'UTF-8'); ?>"
                                        data-preco="0"
                                        data-acao="morte">
                                        <i class="bi bi-x-circle"></i> Baixa
                                    </button>
                                <?php else: ?>
                                    <span class="text-muted small ms-1"><i class="bi bi-lock me-1"></i>Finalizado</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            Nenhum animal cadastrado ainda. Clique em <strong>Controle de Rebanho</strong> no menu superior para adicionar.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL 1: Histórico Relacional do Animal -->
<div class="modal fade" id="modalHistorico" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-modern shadow-lg">
            <div class="modal-header border-0 pb-0">
                <div>
                    <span class="header-badge mb-1"><i class="bi bi-diagram-3"></i> Histórico Relacional</span>
                    <h5 class="modal-title fw-bold" id="histTitulo">Movimentações do Animal</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body" id="histCorpo"></div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-soft-neutral px-4" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 2: Editar Animal -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="editar.php" method="POST" id="formEditar" class="modal-content modal-content-modern shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square text-primary me-2"></i>Editar Animal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">
                <div class="row g-3">
                    <div class="col-12 col-sm-6">
                        <label for="edit_especie" class="form-label">Espécie</label>
                        <select name="especie" id="edit_especie" class="form-select" required>
                            <option value="Gado">Gado (Bovino)</option>
                            <option value="Ovelha">Ovelha (Ovino)</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="edit_tipo" class="form-label">Tipo / Classificação</label>
                        <select name="tipo" id="edit_tipo" class="form-select" required></select>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="edit_identificacao" class="form-label">Identificação</label>
                        <input type="text" name="identificacao" id="edit_identificacao" class="form-control" maxlength="100" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label for="edit_preco" class="form-label">Valor Base (R$)</label>
                        <input type="number" step="0.01" min="0" inputmode="decimal" name="preco" id="edit_preco" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-soft-neutral px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btnSalvarEdicao" class="btn-agro px-4">Salvar Alterações</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 3: Venda Negociada ou Baixa -->
<div class="modal fade" id="modalConfirmacao" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="movimentar.php" method="POST" id="formMovimentar" class="modal-content modal-content-modern shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitulo">Confirmar Movimentação</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="mov_id">
                <input type="hidden" name="acao" id="mov_acao">
                <p class="text-muted mb-3" id="modalMensagem"></p>

                <div id="grupoValorVenda">
                    <label for="mov_valor_venda" class="form-label">Valor Final Negociado na Venda (R$)</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-modern fw-semibold">R$</span>
                        <input type="number" step="0.01" min="0" inputmode="decimal" name="valor_venda" id="mov_valor_venda" class="form-control input-with-prefix">
                    </div>
                    <small class="text-muted">Você pode ajustar o valor caso o animal tenha valorizado.</small>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-soft-neutral px-3" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btnConfirmarAcao" class="btn px-4 fw-semibold" style="border-radius: 10px;">Confirmar</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    const historicoPorAnimal = <?php echo json_encode($movimentacoes_por_animal, JSON_UNESCAPED_UNICODE); ?>;
    const dadosGrafico = {
        gado: <?php echo (int)$qtd_gado; ?>,
        ovelha: <?php echo (int)$qtd_ovelha; ?>,
        patrimonio: <?php echo round($patrimonio_total, 2); ?>,
        vendas: <?php echo round($faturamento_vendas, 2); ?>
    };

    const tiposPorEspecie = {
        'Gado': ['Bezerro(a)', 'Novilho(a)', 'Vaca', 'Boi', 'Touro'],
        'Ovelha': ['Borrego(a)', 'Ovelha (Matriz)', 'Carneiro (Reprodutor)', 'Capão']
    };

    (function () {
        let chartEspecies = null;
        let chartFinanceiro = null;

        function renderizarGraficos() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            const corTexto = isDark ? '#94a3b8' : '#64748b';
            const corGrade = isDark ? 'rgba(148, 163, 184, 0.12)' : 'rgba(15, 23, 42, 0.06)';

            if (chartEspecies) chartEspecies.destroy();
            if (chartFinanceiro) chartFinanceiro.destroy();

            const ctxEsp = document.getElementById('chartEspecies');
            if (ctxEsp) {
                chartEspecies = new Chart(ctxEsp, {
                    type: 'doughnut',
                    data: {
                        labels: ['Gado (Bovino)', 'Ovelha (Ovino)'],
                        datasets: [{
                            data: [dadosGrafico.gado, dadosGrafico.ovelha],
                            backgroundColor: ['#2563eb', '#38bdf8'],
                            borderWidth: 0,
                            hoverOffset: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '68%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { color: corTexto, usePointStyle: true, padding: 16 }
                            }
                        }
                    }
                });
            }

            const ctxFin = document.getElementById('chartFinanceiro');
            if (ctxFin) {
                chartFinanceiro = new Chart(ctxFin, {
                    type: 'bar',
                    data: {
                        labels: ['Patrimônio Ativo', 'Receita em Vendas'],
                        datasets: [{
                            label: 'Valor (R$)',
                            data: [dadosGrafico.patrimonio, dadosGrafico.vendas],
                            backgroundColor: ['#2563eb', '#0ea5e9'],
                            borderRadius: 10,
                            barThickness: 42
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { color: corTexto }
                            },
                            y: {
                                grid: { color: corGrade },
                                ticks: {
                                    color: corTexto,
                                    callback: value => 'R$ ' + value.toLocaleString('pt-BR')
                                }
                            }
                        }
                    }
                });
            }
        }

        renderizarGraficos();
        window.addEventListener('agropetThemeChange', renderizarGraficos);

        const buscaInput = document.getElementById('buscaTabela');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const linhas = document.querySelectorAll('.linha-animal');
        let filtroAtual = 'todos';

        function aplicarFiltros() {
            const termo = (buscaInput ? buscaInput.value : '').toLowerCase().trim();
            linhas.forEach(linha => {
                const status = linha.dataset.status ? linha.dataset.status : '';
                const texto = linha.textContent.toLowerCase();
                const bateStatus = (filtroAtual === 'todos') ? true : (status === filtroAtual);
                const bateTexto = (termo === '') ? true : texto.includes(termo);
                linha.style.display = (bateStatus && bateTexto) ? '' : 'none';
            });
        }

        if (buscaInput) buscaInput.addEventListener('input', aplicarFiltros);

        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                filterBtns.forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filtroAtual = btn.dataset.filter;
                aplicarFiltros();
            });
        });

        const labelsMov = {
            'cadastro_inicial': 'Cadastro Inicial na Propriedade',
            'nascimento': 'Nascimento Registrado',
            'venda': 'Venda Concluída',
            'morte': 'Baixa / Óbito Registrado'
        };

        document.querySelectorAll('.btn-historico').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nome = btn.dataset.nome;
                const lista = historicoPorAnimal[id] ? historicoPorAnimal[id] : [];

                document.getElementById('histTitulo').textContent = `${nome} (#${id})`;
                const corpo = document.getElementById('histCorpo');

                if (lista.length === 0) {
                    corpo.innerHTML = '<p class="text-muted small mb-0">Nenhuma movimentação registrada na tabela de histórico para este animal.</p>';
                } else {
                    let html = '<div class="agro-timeline">';
                    lista.forEach(item => {
                        const tituloMov = labelsMov[item.tipo] ? labelsMov[item.tipo] : item.tipo;
                        const valorTxt = item.valor > 0
                            ? `<div class="small fw-semibold text-primary mt-1">Valor negociado: R$ ${item.valor.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</div>`
                            : '';
                        html += `
                            <div class="agro-timeline-item">
                                <span class="agro-timeline-dot"></span>
                                <div class="fw-semibold" style="font-size: 0.92rem;">${tituloMov}</div>
                                <div class="text-muted small">${item.data}</div>
                                ${valorTxt}
                            </div>
                        `;
                    });
                    html += '</div>';
                    corpo.innerHTML = html;
                }

                new bootstrap.Modal(document.getElementById('modalHistorico')).show();
            });
        });

        const editEspecie = document.getElementById('edit_especie');
        const editTipo = document.getElementById('edit_tipo');

        function preencherTiposEdicao(especie, tipoAtual) {
            const opcoes = tiposPorEspecie[especie] ? tiposPorEspecie[especie] : [];
            editTipo.innerHTML = '';
            let encontrou = false;

            opcoes.forEach(op => {
                const opt = document.createElement('option');
                opt.value = op;
                opt.textContent = op;
                if (op === tipoAtual) {
                    opt.selected = true;
                    encontrou = true;
                }
                editTipo.appendChild(opt);
            });

            if (!encontrou && tipoAtual && tipoAtual !== '-') {
                const optExtra = document.createElement('option');
                optExtra.value = tipoAtual;
                optExtra.textContent = tipoAtual;
                optExtra.selected = true;
                editTipo.appendChild(optExtra);
            }
        }

        if (editEspecie) {
            editEspecie.addEventListener('change', () => preencherTiposEdicao(editEspecie.value, ''));
        }

        document.querySelectorAll('.btn-editar').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('edit_id').value = btn.dataset.id;
                editEspecie.value = btn.dataset.especie;
                preencherTiposEdicao(btn.dataset.especie, btn.dataset.tipo);
                document.getElementById('edit_identificacao').value = btn.dataset.nome;
                document.getElementById('edit_preco').value = btn.dataset.preco;

                new bootstrap.Modal(document.getElementById('modalEditar')).show();
            });
        });

        const btnConfirmar = document.getElementById('btnConfirmarAcao');
        const formMovimentar = document.getElementById('formMovimentar');

        document.querySelectorAll('.btn-acao').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const nome = btn.dataset.nome;
                const acao = btn.dataset.acao;
                const preco = btn.dataset.preco;

                document.getElementById('mov_id').value = id;
                document.getElementById('mov_acao').value = acao;

                const titulo = document.getElementById('modalTitulo');
                const msg = document.getElementById('modalMensagem');
                const grupoVenda = document.getElementById('grupoValorVenda');
                const inputVenda = document.getElementById('mov_valor_venda');

                btnConfirmar.disabled = false;

                if (acao === 'venda') {
                    titulo.textContent = 'Registrar Venda';
                    msg.innerHTML = `Confirme os dados da venda do animal <strong>${nome}</strong> (#${id}):`;
                    grupoVenda.style.display = 'block';
                    inputVenda.required = true;
                    inputVenda.value = preco;
                    btnConfirmar.className = 'btn btn-primary px-4 fw-semibold';
                    btnConfirmar.textContent = 'Confirmar Venda';
                } else {
                    titulo.textContent = 'Registrar Baixa (Morte)';
                    msg.innerHTML = `Deseja registrar a baixa do animal <strong>${nome}</strong> (#${id})? Essa ação altera o patrimônio ativo.`;
                    grupoVenda.style.display = 'none';
                    inputVenda.required = false;
                    inputVenda.value = '';
                    btnConfirmar.className = 'btn btn-danger px-4 fw-semibold';
                    btnConfirmar.textContent = 'Confirmar Baixa';
                }

                new bootstrap.Modal(document.getElementById('modalConfirmacao')).show();
            });
        });

        if (formMovimentar) {
            formMovimentar.addEventListener('submit', () => {
                btnConfirmar.disabled = true;
                btnConfirmar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';
            });
        }
    })();
</script>