<?php
session_start();

// Conectar ao banco de dados
$host = 'localhost';
$banco = 'banco';
$usuario = 'root';
$senhaBanco = '';

$conexao = new mysqli($host, $usuario, $senhaBanco, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $nomeUsuario = $_POST['username'];
    $cep = $_POST['cep'];
    $novaSenha = $_POST['password'];

    if (strlen($novaSenha) < 8 || !preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])/', $novaSenha)) {
        $mensagem = '<div class="alert alert-warning" role="alert">A senha deve conter pelo menos 8 caracteres, incluindo 1 letra maiúscula e 1 minúscula, 1 número e 1 caractere especial.</div>';
    } else {
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND nome = ? AND cep = ?");
        $stmt->bind_param("sss", $email, $nomeUsuario, $cep);
        $stmt->execute();
        $stmt->bind_result($id_usuario);
        $stmt->fetch();
        $stmt->close(); // Fechar o primeiro statement

        if ($id_usuario) {
            $hashedSenha = password_hash($novaSenha, PASSWORD_DEFAULT);
            $stmt = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedSenha, $id_usuario);
            if ($stmt->execute()) {
                $mensagem = '<div class="alert alert-success" role="alert">Senha redefinida com sucesso!</div>';
            } else {
                $mensagem = '<div class="alert alert-danger" role="alert">Erro ao redefinir a senha. Tente novamente.</div>';
            }
            $stmt->close(); // Fechar o segundo statement
        } else {
            $mensagem = '<div class="alert alert-danger" role="alert">Informações incorretas. Verifique os dados e tente novamente.</div>';
        }
    }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/Logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
    <div class="wrapper">
        <header>Lumiener<br> Redefinir Senha</header>
        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-info"><?= $mensagem ?></div>
        <?php endif; ?>
        <form method="POST" action="#">
            <div class="field email">
                <div class="input-area">
                    <input type="email" name="email" placeholder="E-mail antigo" required>
                    <i class="icon fas fa-envelope"></i>
                </div>
            </div>
            <div class="field username">
                <div class="input-area">
                    <input type="text" name="username" placeholder="Nome de usuário" required>
                    <i class="icon fas fa-user"></i>
                </div>
            </div>
            <div class="field cep">
                <div class="input-area">
                    <input type="text" name="cep" id="cep" placeholder="CEP" required>
                    <i class="icon fas fa-map-marker-alt"></i>
                </div>
            </div>
            <div class="field password">
                <div class="input-area">
                    <input type="password" name="password" placeholder="Nova Senha" required>
                    <i class="icon fas fa-lock"></i>
                </div>
            </div>
            <input type="submit" value="Redefinir Senha">
        </form>
        <div class="sign-txt"><a href="main.php">Voltar</a></div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#cep').mask('00000-000');
        });
    </script>
</body>
<footer id="footer" class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="landing-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>
