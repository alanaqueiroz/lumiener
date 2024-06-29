<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: erro401.php');
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
    if (isset($_POST['excluir_conta'])) {
        // Começar a transação
        $conexao->begin_transaction();

        try {
            // Excluir registros dependentes nas tabelas resultados e tarifas
            $stmt = $conexao->prepare("SELECT id FROM simulacoes WHERE id_usuario = ?");
            $stmt->bind_param("i", $id_usuario);
            $stmt->execute();
            $stmt->bind_result($id_simulacao);
            $stmt->store_result();

            while ($stmt->fetch()) {
                // Excluir resultados
                $stmt_delete_resultados = $conexao->prepare("DELETE FROM resultados WHERE id_simulacao = ?");
                $stmt_delete_resultados->bind_param("i", $id_simulacao);
                $stmt_delete_resultados->execute();
                $stmt_delete_resultados->close();

                // Excluir tarifas
                $stmt_delete_tarifas = $conexao->prepare("DELETE FROM tarifas WHERE id_simulacao = ?");
                $stmt_delete_tarifas->bind_param("i", $id_simulacao);
                $stmt_delete_tarifas->execute();
                $stmt_delete_tarifas->close();
            }

            // Excluir registros dependentes na tabela simulacoes
            $stmt_delete_simulacoes = $conexao->prepare("DELETE FROM simulacoes WHERE id_usuario = ?");
            $stmt_delete_simulacoes->bind_param("i", $id_usuario);
            $stmt_delete_simulacoes->execute();
            $stmt_delete_simulacoes->close();

            // Excluir o usuário
            $stmt_delete_usuario = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt_delete_usuario->bind_param("i", $id_usuario);
            $stmt_delete_usuario->execute();
            $stmt_delete_usuario->close();

            // Confirmar a transação
            $conexao->commit();

            session_destroy();
            header('Location: conta_excluida.php');
            exit;
        } catch (Exception $e) {
            // Reverter a transação em caso de erro
            $conexao->rollback();
            $mensagem = '<div class="alert alert-danger" role="alert">Erro ao excluir a conta. Tente novamente.</div>';
        }
    } else {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['password'];
        $cep = $_POST['cep'];
        $localizacao = $_POST['localizacao'];

        // Verificar o formato do email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = '<div class="alert alert-warning" role="alert">Insira um e-mail válido.</div>';
        } else {
            // Verificar se o email já está em uso por outro usuário
            $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->bind_param("si", $email, $id_usuario);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $mensagem = '<div class="alert alert-warning custom-alert" role="alert">Este email já está em uso por outro usuário. Por favor, escolha outro.</div>';
            } else {
                // Validação da senha (se fornecida)
                if (!empty($senha) && (strlen($senha) < 8 || !preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])/', $senha))) {
                    $mensagem = '<div class="alert alert-warning custom-alert" role="alert">A senha deve conter pelo menos 8 caracteres, incluindo 1 letra maiúscula e 1 minúscula, 1 número e 1 caractere especial.</div>';
                } else {
                    // Se tudo estiver válido, atualizar no banco de dados
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
                        $mensagem = '<div class="alert alert-success custom-alert" role="alert">Perfil atualizado com sucesso!</div>';
                    } else {
                        $mensagem = '<div class="alert alert-danger custom-alert" role="alert">Erro ao atualizar o perfil. Tente novamente.</div>';
                    }
                    $stmt2->close();
                }
            }
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
        <input type="submit" value="Salvar Alterações"><br><br>
        <button type="button" class="btn btn-danger" id="excluirContaBtn">Excluir Conta</button>
        <input type="hidden" name="excluir_conta" id="excluirContaInput">
    </form>
    <div class="sign-txt"><a href="selecao_local.php">Voltar</a></div>
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

        $('#excluirContaBtn').click(function() {
            if (confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
                $('#excluirContaInput').val('1');
                $('form').submit();
            }
        });
    });
    </script>
</body>
<footer id="footer" class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>
