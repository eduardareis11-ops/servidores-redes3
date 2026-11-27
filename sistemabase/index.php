<?php
define('APP_LOADED', true);

$msg         = $_GET['msg'] ?? '';
$id_destaque = isset($_GET['id']) ? (int) $_GET['id'] : 0;

$toast_config = null;
if ($msg === 'sucesso') {
    $toast_config = [
        'titulo'    => 'Registro salvo',
        'subtitulo' => 'O rebanho foi atualizado com sucesso.',
        'icone'     => 'bi-check2'
    ];
} elseif ($msg === 'venda') {
    $toast_config = [
        'titulo'    => 'Venda registrada',
        'subtitulo' => 'Faturamento e patrimônio atualizados.',
        'icone'     => 'bi-currency-dollar'
    ];
} elseif ($msg === 'baixa') {
    $toast_config = [
        'titulo'    => 'Baixa registrada',
        'subtitulo' => 'O histórico do animal foi atualizado.',
        'icone'     => 'bi-clipboard-check'
    ];
} elseif ($msg === 'edicao') {
    $toast_config = [
        'titulo'    => 'Cadastro atualizado',
        'subtitulo' => 'As alterações do animal foram salvas.',
        'icone'     => 'bi-pencil-square'
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgroPet - Painel Inicial</title>
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
            <a href="perfil.php" class="btn btn-outline-primary rounded-pill px-3 py-2 d-flex align-items-center gap-2 fw-semibold" style="font-size: 0.9rem;">
                <i class="bi bi-person-badge"></i>
                <span class="d-none d-sm-inline">Perfil & Fazenda</span>
                <span class="d-inline d-sm-none">Perfil</span>
            </a>
            <a href="cadastro.php" class="btn-agro">
                <i class="bi bi-plus-circle"></i>
                <span class="d-none d-sm-inline">Controle de Rebanho</span>
                <span class="d-inline d-sm-none">Rebanho</span>
            </a>
        </div>
    </div>
</nav>

<?php if ($toast_config): ?>
<div class="agro-toast-wrapper" aria-live="polite" aria-atomic="true">
    <div class="agro-toast" id="agroToast" role="status">
        <div class="agro-toast-icon">
            <i class="bi <?php echo $toast_config['icone']; ?>"></i>
        </div>
        <div class="flex-grow-1">
            <div class="fw-semibold" style="font-size: 0.92rem;"><?php echo $toast_config['titulo']; ?></div>
            <div class="text-muted" style="font-size: 0.82rem;"><?php echo $toast_config['subtitulo']; ?></div>
        </div>
        <button type="button" class="btn-close btn-sm" id="fecharToast" aria-label="Fechar"></button>
        <div class="agro-toast-progress"></div>
    </div>
</div>
<?php endif; ?>

<main class="container pb-5" style="max-width: 1140px;">

    <section class="card card-modern p-3 p-sm-4 p-md-5 mb-4">
        <span class="header-badge mb-2">
            <i class="bi bi-graph-up-arrow"></i> Painel Geral AgroPet
        </span>
        <h1 class="fw-bold mb-2" style="letter-spacing: -0.5px; font-size: clamp(1.35rem, 2.5vw, 1.95rem);">
            Gestão Pecuária e Patrimônio
        </h1>
        <p class="text-muted mb-0" style="max-width: 720px; font-size: 0.95rem;">
            Monitore o rebanho ativo em tempo real, acompanhe indicadores financeiros e registre vendas, edições ou baixas com histórico relacional integrado.
        </p>
    </section>

    <?php
    if (file_exists('listar.php')) {
        include 'listar.php';
    } else {
        echo '<div class="alert alert-primary rounded-4">Arquivo <code>listar.php</code> não encontrado na pasta do projeto.</div>';
    }
    ?>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
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
                window.dispatchEvent(new CustomEvent('agropetThemeChange', { detail: { theme: novoTema } }));
            });
        }
    })();
</script>

<?php if ($toast_config): ?>
<script>
    (function () {
        const toast = document.getElementById('agroToast');
        const btnFechar = document.getElementById('fecharToast');

        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }

        if (!toast) return;

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        function fecharSuavemente() {
            toast.classList.remove('show');
            toast.classList.add('hide');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.parentElement.remove();
                }
            }, 450);
        }

        const timer = setTimeout(fecharSuavemente, 4200);

        if (btnFechar) {
            btnFechar.addEventListener('click', () => {
                clearTimeout(timer);
                fecharSuavemente();
            });
        }
    })();
</script>
<?php endif; ?>
</body>
</html>