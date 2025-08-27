<?php
$page_title = 'Recuperar Senha';
$status = isset($_GET['status']) ? $_GET['status'] : '';
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
                Recuperar sua senha
            </h2>
            <p class="mt-2 text-center text-sm text-slate-600">
                Ou <a href="login.php" class="font-medium text-slate-600 hover:text-slate-500">volte para o login</a>
            </p>
        </div>

        <?php if ($status == 'email_enviado'): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Sucesso!</strong>
                <span class="block sm:inline">Se o e-mail existir na nossa base de dados, um link de recuperação foi enviado.</span>
            </div>
        <?php endif; ?>
        
        <?php if ($erro == 'email_nao_encontrado'): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Erro:</strong>
                <span class="block sm:inline">O e-mail fornecido não foi encontrado.</span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="core/enviar_recuperacao.php" method="POST">
            <div class="rounded-md shadow-sm">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input id="email" name="email" type="email" autocomplete="email" required class="relative block w-full appearance-none rounded-md border border-slate-300 px-3 py-2 text-slate-900 placeholder-slate-500 focus:z-10 focus:border-slate-500 focus:outline-none focus:ring-slate-500 sm:text-sm" placeholder="Digite o seu e-mail">
                </div>
            </div>

            <div>
                <button type="submit" class="group relative flex w-full justify-center rounded-md border border-transparent bg-slate-800 py-2 px-4 text-sm font-medium text-white hover:bg-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-500 focus:ring-offset-2">
                    Enviar Link de Recuperação
                </button>
            </div>
        </form>
    </div>
</body>
</html>