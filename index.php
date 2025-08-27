
<?php
// ======================================================================
// ARQUIVO: index.php
// Objetivo: Ponto de entrada principal da aplicação.
// ======================================================================

/* Este arquivo serve como o portão de entrada do sistema.
   No futuro, ele terá uma lógica para verificar se o usuário já está logado.
   - Se o usuário estiver logado, ele será redirecionado para o painel principal (dashboard.php).
   - Se não estiver logado, ele será enviado para a página de login (login.php).

   Por enquanto, estamos apenas redirecionando para o login.
*/

header('Location: login.php');
exit(); // É importante usar exit() após um redirecionamento para garantir que o script pare a execução.

?>

