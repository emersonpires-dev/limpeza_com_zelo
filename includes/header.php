<!DOCTYPE html>
<html lang="pt-br" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Controle de Estoque'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex flex-col min-h-screen"> <nav class="bg-slate-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-white font-bold text-xl">Controle de Estoque</h1>
                    </div>
                </div>
                <div class="hidden md:block">
                    <div class="ml-10 flex items-baseline space-x-4">
                        <a href="dashboard.php" class="text-slate-300 hover:bg-slate-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Dashboard</a>
                        <a href="relatorios.php" class="text-slate-300 hover:bg-slate-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Relatórios</a>
                        <?php if ($_SESSION['usuario_permissao'] === 'admin'): ?>
                            <a href="configuracoes.php" class="text-slate-300 hover:bg-slate-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Configurações</a>
                        <?php endif; ?>
                        <a href="logout.php" class="text-slate-300 hover:bg-slate-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium">Sair</a>
                    </div>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button type="button" id="mobile-menu-button" class="inline-flex items-center justify-center rounded-md bg-slate-800 p-2 text-slate-400 hover:bg-slate-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-slate-800" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Abrir menu principal</span>
                        <svg class="block h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                        <svg class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
                <a href="dashboard.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Dashboard</a>
                <a href="relatorios.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Relatórios</a>
                <?php if ($_SESSION['usuario_permissao'] === 'admin'): ?>
                    <a href="configuracoes.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Configurações</a>
                <?php endif; ?>
                <a href="logout.php" class="text-slate-300 hover:bg-slate-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">Sair</a>
            </div>
        </div>
    </nav>

    <main class="flex-grow py-10"> <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">