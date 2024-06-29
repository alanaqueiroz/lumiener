<?php
session_start();

$host = 'localhost';
$banco = 'banco';
$usuario = 'root';
$senha = '';

$conexao = new mysqli($host, $usuario, $senha, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$mensagem_erro = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha_inserida = $_POST['senha'];

    // Use instruções preparadas para evitar injeção de SQL
    $stmt = $conexao->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($id_usuario, $nome_usuario, $senha_armazenada);
    $stmt->fetch();
    $stmt->close();

    // Verifique se a senha inserida corresponde à senha armazenada
    if ($senha_armazenada && password_verify($senha_inserida, $senha_armazenada)) {
        $_SESSION['id_usuario'] = $id_usuario;
        $_SESSION['nome_usuario'] = $nome_usuario;
        header('Location: selecao_local.php');
        exit;
    } else {
      $mensagem_erro = '<div class="alert alert-warning custom-alert" role="alert">Credenciais inválidas. Tente novamente.</div>';
    }
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumiener</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="images/Logo.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
  <div class="wrapper">
    <header>Lumiener</header>
    <a href="lading-page.html">
    <img src="images/thumbnail.png" alt="Logo" style="width: 100px; height: 100px; margin-top: 10px;">
    </a>
    <form method="POST" action="#">
      <div class="field email">
      <?php if (!empty($mensagem_erro)) { echo $mensagem_erro; } ?>
        <div class="input-area">
          <input type="email" name="email" required placeholder="E-mail">
          <i class="icon fas fa-envelope"></i>
          <i class="error error-icon fas fa-exclamation-circle"></i>
        </div>
        <div class="error error-txt">Inserir seu e-mail</div>
      </div>
      <div class="field password">
        <div class="input-area">
          <input type="password" name="senha" required placeholder="Senha">
          <i class="icon fas fa-lock"></i>
          <i class="error error-icon fas fa-exclamation-circle"></i>
        </div>
        <div class="error error-txt">Inserir sua senha</div>
      </div>  
      <div class="sign-txt"><a href="redefinir.php">Esqueceu sua senha?</a></div>
      <input type="submit" value="Entrar">
    </form>
    <div class="sign-txt">Ainda não é membro? <br><a href="cadastro.php">Cadastre-se</a></div>
  </div>
</body>
<footer>
  Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>
