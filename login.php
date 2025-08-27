<?php
session_start();
// Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: dashboard.php');
    exit();
}
$page_title = 'Login - Controle de Estoque';
$erro = isset($_GET['erro']) ? $_GET['erro'] : '';
?>
<!DOCTYPE html>
<html lang="pt-br" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold tracking-tight text-slate-900">
                Painel de login
            </h2>
        </div>

        <?php if ($erro == 'credenciais_invalidas' || $erro == 'login_invalido'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro:</strong>
                <span class="block sm:inline">O e-mail ou a senha estão incorretos.</span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="core/autenticacao.php" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="relative block w-full appearance-none rounded-none rounded-t-md border border-slate-300 px-3 py-2 text-slate-900 placeholder-slate-500 focus:z-10 focus:border-slate-500 focus:outline-none focus:ring-slate-500 sm:text-sm" placeholder="Email">
                </div>
                <div class="relative">
                    <label for="senha" class="sr-only">Senha</label>
                    <input id="senha" name="senha" type="password" autocomplete="current-password" required class="relative block w-full appearance-none rounded-none rounded-b-md border border-slate-300 px-3 py-2 text-slate-900 placeholder-slate-500 focus:z-10 focus:border-slate-500 focus:outline-none focus:ring-slate-500 sm:text-sm" placeholder="Senha">
                    <div id="toggleSenha" class="absolute inset-y-0 right-0 pr-3 flex items-center text-sm leading-5 cursor-pointer">
                        <svg id="olho-aberto" xmlns="http://www.w.org/2000/svg" class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z" /><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.022 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" /></svg>
                        <svg id="olho-fechado" xmlns="http://www.w.org/2000/svg" class="h-5 w-5 text-slate-400 hidden" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3.707 2.293a1 1 0 00-1.414 1.414l14 14a1 1 0 001.414-1.414l-1.473-1.473A10.014 10.014 0 0019.542 10C18.268 5.943 14.478 3 10 3a9.958 9.958 0 00-4.512 1.074l-1.78-1.781zM10 12a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" /><path d="M2 10s3.939-7 8-7a9.97 9.97 0 012.512.523l-1.78 1.78A4 4 0 0010 6a4 4 0 00-4 4c0 .59.123 1.148.34 1.66l-1.78 1.78A9.952 9.952 0 012 10z" /></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember_me" name="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-slate-600 focus:ring-slate-500">
                    <label for="remember_me" class="ml-2 block text-sm text-slate-900">Lembrar-me</label>
                </div>
                <div class="text-sm">
                    <a href="recuperar_senha.php" class="font-medium text-slate-600 hover:text-slate-500">Esqueceu a sua senha?</a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-slate-800 py-2 px-4 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Entrar
                </button>
            </div>
        </form>
    </div>

    <script>
        const toggleSenha = document.getElementById('toggleSenha');
        const senhaInput = document.getElementById('senha');
        const olhoAberto = document.getElementById('olho-aberto');
        const olhoFechado = document.getElementById('olho-fechado');

        toggleSenha.addEventListener('click', function () {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            
            olhoAberto.classList.toggle('hidden');
            olhoFechado.classList.toggle('hidden');
        });
    </script>
</body>
</html>