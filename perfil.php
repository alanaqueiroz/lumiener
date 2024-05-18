<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

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

// Recuperar dados do usuário para preencher o formulário
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conexao->prepare("SELECT nome, email, cep, localizacao FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($nome, $email, $cep, $localizacao);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['password'];
    $cep = $_POST['cep'];
    $localizacao = $_POST['localizacao'];

    // Atualizar informações do usuário
    $query = "UPDATE usuarios SET nome=?, email=?, cep=?, localizacao=?";
    if (!empty($senha)) {
        $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
        $query .= ", senha=?";
    }
    $query .= " WHERE id=?";
    
    $stmt2 = $conexao->prepare($query);
    if (!empty($senha)) {
        $stmt2->bind_param("sssssi", $nome, $email, $cep, $localizacao, $hashed_senha, $id_usuario);
    } else {
        $stmt2->bind_param("ssssi", $nome, $email, $cep, $localizacao, $id_usuario);
    }

    if ($stmt2->execute()) {
        $mensagem = '<div class="alert alert-success" role="alert">Perfil atualizado com sucesso!</div>';
    } else {
        $mensagem = '<div class="alert alert-danger" role="alert">Erro ao atualizar o perfil. Tente novamente.</div>';
    }
    $stmt2->close();
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Atualizar Perfil</title>
  <link rel="stylesheet" href="style.css">
  <link rel="icon" href="images/Logo.ico" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>
  <div class="wrapper">
    <header>Lumiener<br> Atualizar Perfil</header>
    <?php if (!empty($mensagem)): ?>
        <div class="success-message"><?= $mensagem ?></div>
    <?php endif; ?>
    <form method="POST" action="#">
        <div class="field nome">
            <div class="input-area">
                <input type="text" name="nome" placeholder="Nome" value="<?= htmlspecialchars($nome) ?>">
                <i class="icon fas fa-user"></i>
            </div>
        </div>
        <div class="field email">
            <div class="input-area">
                <input type="text" name="email" placeholder="E-mail" value="<?= htmlspecialchars($email) ?>">
                <i class="icon fas fa-envelope"></i>
            </div>
        </div>
        <div class="field password">
            <div class="input-area">
                <input type="password" name="password" placeholder="Nova Senha">
                <i class="icon fas fa-lock"></i>
            </div>
        </div>
        <div class="field cep">
            <div class="input-area">
                <input type="text" name="cep" id="cep" placeholder="CEP" value="<?= htmlspecialchars($cep) ?>" oninput="consultaCEP(this.value)">
                <i class="icon fas fa-map-marker-alt"></i>
            </div>
        </div>
        <div class="field localizacao">
            <div class="input-area">
                <input type="text" name="localizacao" id="localizacao" placeholder="Localização" value="<?= htmlspecialchars($localizacao) ?>" readonly>
                <i class="icon fas fa-map-marked-alt"></i>
            </div>
        </div>
        <input type="submit" value="Salvar Alterações">
    </form>
    <div class="sign-txt"><a href="conteudo.php">Voltar para a Página Inicial</a></div>
  </div>
  <script>
    $(document).ready(function() {
        $('#cep').mask('00000-000');
        $('#cep').on('blur', function() {
            var cep = $(this).val().replace(/\D/g, '');
            if(cep.length === 8) {
                $.ajax({
                    url: `https://viacep.com.br/ws/${cep}/json/`,
                    dataType: 'json',
                    success: function(resposta) {
                        if (!resposta.erro) {
                            $('#localizacao').val(`${resposta.logradouro}, ${resposta.bairro}, ${resposta.localidade} - ${resposta.uf}`);
                        } else {
                            $('#localizacao').val('CEP não encontrado');
                        }
                    },
                    error: function() {
                        $('#localizacao').val('Erro ao buscar o CEP');
                    }
                });
            }
        });
    });
    </script>
</body>
<footer>
  <b>&copy; <?php echo date('Y'); ?> Lumiener. Todos os direitos reservados.</b>
</footer>
</html>
