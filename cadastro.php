<?php
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
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['password'];
    $cep = $_POST['cep'];
    $localizacao = $_POST['localizacao'];
    $termos = isset($_POST['termos']);

    if (!$termos) {
        $mensagem = '<div class="alert alert-warning" role="alert">Você deve concordar com os termos de uso.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = '<div class="alert alert-warning" role="alert">Insira um e-mail válido.</div>';
    } else {
        $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensagem = '<div class="alert alert-warning custom-alert" role="alert">Este email já está em uso. Por favor, escolha outro.</div>';
        } elseif (strlen($senha) < 8 || !preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d])/', $senha)) {
            $mensagem = '<div class="alert alert-warning custom-alert" role="alert">A senha deve conter pelo menos 8 caracteres, incluindo 1 letra maiúscula e 1 minúscula, 1 número e 1 caractere especial.</div>';
        } else {
            $hashed_senha = password_hash($senha, PASSWORD_DEFAULT);
            $stmt2 = $conexao->prepare("INSERT INTO usuarios (nome, email, senha, cep, localizacao) VALUES (?, ?, ?, ?, ?)");
            $stmt2->bind_param("sssss", $nome, $email, $hashed_senha, $cep, $localizacao);

            if ($stmt2->execute()) {
                $mensagem = '<div class="alert alert-success" role="alert">Cadastro realizado com sucesso!</div>';
            } else {
                $mensagem = '<div class="alert alert-danger" role="alert">Erro ao cadastrar. Tente novamente.</div>';
            }

            $stmt2->close();
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
    <title>Cadastro</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="images/Logo.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <style>
        .field.termos {
            display: flex;
            align-items: center;
        }

        .field.termos .input-area{
            display: flex;
            align-items: center;
        }

        .field.termos .input-area input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <header>Lumiener</header>
        <?php if (!empty($mensagem)): ?>
            <?= $mensagem ?>
        <?php endif; ?>
        <form method="POST" action="#">
            <div class="field nome">
                <div class="input-area">
                    <input type="text" name="nome" placeholder="Nome">
                    <i class="icon fas fa-user"></i>
                </div>
                <div class="error error-txt"></div>
            </div>
            <div class="field email">
                <div class="input-area">
                    <input type="text" name="email" placeholder="E-mail">
                    <i class="icon fas fa-envelope"></i>
                </div>
                <div class="error error-txt"></div>
            </div>
            <div class="field password">
                <div class="input-area">
                    <input type="password" name="password" placeholder="Senha">
                    <i class="icon fas fa-lock"></i>
                </div>
                <div class="error error-txt"></div>
            </div>
            <div class="field cep">
                <div class="input-area">
                    <input type="text" name="cep" id="cep" placeholder="CEP" oninput="consultaCEP(this.value)"><br>
                    <i class="icon fas fa-map-marker-alt"></i>
                </div>
                <div class="error error-txt"></div>
            </div>
            <div class="field localizacao">
                <div class="input-area">
                    <input type="text" name="localizacao" id="localizacao" placeholder="Localização" readonly>
                    <i class="icon fas fa-map-marked-alt"></i>
                </div>
            </div>
            <div class="field termos">
                <div class="input-area">
                <input type="checkbox" name="termos" id="termos">
                <label for="termos">Li e concordo com os <a href="termos-de-uso.php" target="_blank">termos de uso</a></label>
                </div>
                <div class="error error-txt"></div>
            </div>
            <input type="submit" value="Cadastrar">
        </form>
        <div class="sign-txt">Já é membro? <a href="main.php">Entrar</a></div>
    </div>
    <script>
        $(document).ready(function () {
            $('#cep').mask('00000-000');
        });

        function consultaCEP(cep) {
            cep = cep.replace('-', '');

            if (cep.length === 8) {
                fetch(`https://viacep.com.br/ws/${cep}/json/`)
                    .then(response => response.json())
                    .then(data => {
                        if (!data.erro) {
                            document.getElementById('localizacao').value = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                        } else {
                            document.getElementById('localizacao').value = 'CEP não encontrado';
                        }
                    })
                    .catch(error => {
                        console.error('Erro na consulta do CEP:', error);
                        document.getElementById('localizacao').value = 'Erro na consulta do CEP';
                    });
            } else {
                document.getElementById('localizacao').value = '';
            }
        }

        const form = document.querySelector("form");
        const eField = form.querySelector(".email");
        const eInput = eField.querySelector("input");
        const pField = form.querySelector(".password");
        const pInput = pField.querySelector("input");
        const nField = form.querySelector(".nome");
        const nInput = nField.querySelector("input");
        const cField = form.querySelector(".cep");
        const cInput = cField.querySelector("input");
        const lField = form.querySelector(".localizacao");
        const lInput = lField.querySelector("input");
        const tField = form.querySelector(".termos");
        const tInput = tField.querySelector("input");

        form.onsubmit = (e) => {
            (nInput.value == "") ? shakeError(nField) : checkNome();
            (eInput.value == "") ? shakeError(eField) : checkEmail();
            (pInput.value == "") ? shakeError(pField) : checkPass();
            (cInput.value == "") ? shakeError(cField) : checkCEP();
            (lInput.value == "") ? shakeError(lField) : checkLocalizacao();
            (!tInput.checked) ? shakeError(tField) : checkTermos();
            setTimeout(() => {
                eField.classList.remove("shake");
                pField.classList.remove("shake");
                nField.classList.remove("shake");
                cField.classList.remove("shake");
                lField.classList.remove("shake");
                tField.classList.remove("shake");
            }, 500);

            nInput.onkeyup = () => { checkNome(); }
            eInput.onkeyup = () => { checkEmail(); }
            pInput.onkeyup = () => { checkPass(); }
            cInput.onkeyup = () => { checkCEP(); }
            lInput.onkeyup = () => { checkLocalizacao(); }
            tInput.onchange = () => { checkTermos(); }

            function shakeError(field) {
                field.classList.add("shake", "error");
                e.preventDefault();
            }

            function checkEmail() {
                let pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/;
                if (!eInput.value.match(pattern)) {
                    eField.classList.add("error");
                    eField.classList.remove("valid");
                    let errorTxt = eField.querySelector(".error-txt");
                    (eInput.value != "") ? errorTxt.innerText = "Insira um e-mail válido!" : errorTxt.innerText = "O e-mail não pode ficar vazio";
                    e.preventDefault();
                } else {
                    eField.classList.remove("error");
                    eField.classList.add("valid");
                }
            }

            function checkNome() {
                let pattern = /^[A-Za-záàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ ]+$/;
                if (!nInput.value.match(pattern)) {
                    nField.classList.add("error");
                    nField.classList.remove("valid");
                    let errorTxt = nField.querySelector(".error-txt");
                    errorTxt.innerText = "O nome deve conter apenas letras e espaços.";
                    e.preventDefault();
                } else {
                    nField.classList.remove("error");
                    nField.classList.add("valid");
                }
            }

            function checkPass() {
                let pattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/;
                if (!pInput.value.match(pattern)) {
                    pField.classList.add("error");
                    pField.classList.remove("valid");
                    let errorTxt = pField.querySelector(".error-txt");
                    errorTxt.innerText = "A senha deve conter pelo menos 8 caracteres, incluindo 1 letra maiúscula e 1 minúscula, 1 número e 1 caractere especial.";
                    e.preventDefault();
                } else {
                    pField.classList.remove("error");
                    pField.classList.add("valid");
                }
            }

            function checkCEP() {
                if (cInput.value == "") {
                    cField.classList.add("error");
                    cField.classList.remove("valid");
                    e.preventDefault();
                } else {
                    cField.classList.remove("error");
                    cField.classList.add("valid");
                }
            }

            function checkLocalizacao() {
                if (lInput.value == "") {
                    lField.classList.add("error");
                    lField.classList.remove("valid");
                    e.preventDefault();
                } else {
                    lField.classList.remove("error");
                    lField.classList.add("valid");
                }
            }

            function checkTermos() {
                if (!tInput.checked) {
                    tField.classList.add("error");
                    tField.classList.remove("valid");
                    e.preventDefault();
                } else {
                    tField.classList.remove("error");
                    tField.classList.add("valid");
                }
            }
        }
    </script>
</body>
<footer>
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>