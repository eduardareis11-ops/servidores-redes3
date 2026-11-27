<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroPet - Controle de Rebanho</title>
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
            <button type="button" id="btnThemeToggle" class="btn-theme-toggle" title="Alternar tema claro/escuro" aria-label="Alternar tema">
                <i class="bi bi-moon-stars" id="themeIcon"></i>
            </button>
            <a href="index.php" class="nav-pill-link">
                <i class="bi bi-arrow-left"></i>
                <span class="d-none d-sm-inline">Voltar ao Início</span>
                <span class="d-inline d-sm-none">Voltar</span>
            </a>
        </div>
    </div>
</nav>

<main class="container pb-5" style="max-width: 820px;">
    <section class="card card-modern p-3 p-sm-4 p-md-5">
        <div class="mb-4 pb-3 border-bottom">
            <span class="header-badge mb-2">
                <i class="bi bi-shield-check"></i> Controle de Rebanho
            </span>
            <h2 class="fw-bold mb-1" style="letter-spacing: -0.5px; font-size: clamp(1.3rem, 2.5vw, 1.75rem);">
                Registrar Novo Animal
            </h2>
            <p class="text-muted small mb-0">Selecione a espécie, a classificação e informe os dados para salvar no banco de dados.</p>
        </div>

        <form id="formCadastro" action="salvar.php" method="POST">
            <div class="row g-3">

                <!-- Espécie -->
                <div class="col-12 col-md-6">
                    <label for="especie" class="form-label">Espécie (Categoria)</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-modern">
                            <i class="bi bi-tags"></i>
                        </span>
                        <select name="especie" id="especie" class="form-select input-with-prefix" required>
                            <option value="Gado" selected>Gado (Bovino)</option>
                            <option value="Ovelha">Ovelha (Ovino)</option>
                        </select>
                    </div>
                </div>

                <!-- Tipo / Classificação -->
                <div class="col-12 col-md-6">
                    <label for="tipo" class="form-label">Tipo / Classificação</label>
                    <select name="tipo" id="tipo" class="form-select" required></select>
                </div>

                <!-- Identificação -->
                <div class="col-12 col-md-6">
                    <label for="identificacao" class="form-label">Identificação (Brinco / Nome)</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-modern">
                            <i class="bi bi-upc-scan"></i>
                        </span>
                        <input type="text" name="identificacao" id="identificacao" class="form-control input-with-prefix" placeholder="Ex: BR-104 ou Mimosa" maxlength="100" required>
                    </div>
                </div>

                <!-- Preço Estimado -->
                <div class="col-12 col-md-6">
                    <label for="preco" class="form-label">Preço Estimado / Valor Base (R$)</label>
                    <div class="input-group">
                        <span class="input-group-text input-group-text-modern fw-semibold">R$</span>
                        <input type="number" step="0.01" min="0" inputmode="decimal" name="preco" id="preco" class="form-control input-with-prefix" placeholder="0.00" required>
                    </div>
                </div>

                <!-- Origem do Registro -->
                <div class="col-12">
                    <label for="origem" class="form-label">Origem do Registro</label>
                    <select name="origem" id="origem" class="form-select" required>
                        <option value="cadastro_inicial">Animal já existente na propriedade</option>
                        <option value="nascimento">Nascimento na propriedade</option>
                    </select>
                </div>

                <!-- Botões Responsivos (Empilham no celular e alinham no PC) -->
                <div class="col-12 mt-4 pt-3 border-top d-flex flex-column-reverse flex-sm-row justify-content-between align-items-stretch align-items-sm-center gap-2">
                    <div class="d-flex gap-2">
                        <a href="index.php" class="btn btn-soft-neutral flex-fill flex-sm-grow-0 px-3 py-2">
                            Cancelar
                        </a>
                        <button type="reset" class="btn btn-soft-neutral flex-fill flex-sm-grow-0 px-3 py-2">
                            Limpar
                        </button>
                    </div>
                    <button type="submit" id="btnSalvar" class="btn-agro px-4 py-2">
                        <i class="bi bi-check-circle"></i> Salvar Cadastro
                    </button>
                </div>

            </div>
        </form>
    </section>
</main>

<script>
    const tiposPorEspecie = {
        'Gado': ['Bezerro(a)', 'Novilho(a)', 'Vaca', 'Boi', 'Touro'],
        'Ovelha': ['Borrego(a)', 'Ovelha (Matriz)', 'Carneiro (Reprodutor)', 'Capão']
    };

    const selectEspecie = document.getElementById('especie');
    const selectTipo = document.getElementById('tipo');
    const formCadastro = document.getElementById('formCadastro');
    const btnSalvar = document.getElementById('btnSalvar');

    function atualizarTipos() {
        const opcoes = tiposPorEspecie[selectEspecie.value] ? tiposPorEspecie[selectEspecie.value] : [];
        selectTipo.innerHTML = '';

        opcoes.forEach(tipo => {
            const option = document.createElement('option');
            option.value = tipo;
            option.textContent = tipo;
            selectTipo.appendChild(option);
        });
    }

    selectEspecie.addEventListener('change', atualizarTipos);
    formCadastro.addEventListener('reset', () => requestAnimationFrame(atualizarTipos));

    formCadastro.addEventListener('submit', () => {
        btnSalvar.disabled = true;
        btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...';
    });

    document.addEventListener('DOMContentLoaded', atualizarTipos);

    (function () {
        const btnToggle = document.getElementById('btnThemeToggle');
        const themeIcon = document.getElementById('themeIcon');

        function sincronizarBotaoTema() {
            const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (themeIcon) {
                themeIcon.className = isDark ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars';
            }
        }

        sincronizarBotaoTema();

        if (btnToggle) {
            btnToggle.addEventListener('click', () => {
                const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                const novoTema = isDark ? 'light' : 'dark';
                if (novoTema === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                } else {
                    document.documentElement.removeAttribute('data-theme');
                }
                localStorage.setItem('agropet_theme', novoTema);
                sincronizarBotaoTema();
            });
        }
    })();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>