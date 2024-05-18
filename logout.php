<?php
session_start();

// Destrói todas as variáveis de sessão
session_destroy();

// Redireciona para a página de login (index.php)
header('Location: main.php');
exit;
?>
