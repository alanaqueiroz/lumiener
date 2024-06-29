<?php
http_response_code(401);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erro 401</title>
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
    </br></br>
        <header>401</br>Acesso Não Autorizado</header>
        </br>
        <div class="sign-txt">Você não tem permissão para acessar esta página. Por favor, verifique se você está logado ou possui as credenciais corretas.</div>
        </br>
        <div class="sign-txt"><a href="main.php">Fazer Login</a></div>
    </br></br> 
    </div>
    <script>
    </script>
</body>
<footer>
    Copyright &copy; <?php echo date('Y'); ?> <a href="landing-page.html">Lumiener</a>. All rights reserved.
</footer>

</html>