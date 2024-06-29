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
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lumiener</title>
  <link rel="stylesheet" href="style2.css">
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
    <div class="sign-txt">
        <h2>Termo de Uso e Política de Privacidade de Dados</h2>
        <p>1. Introdução</p>
        <p>Bem-vindo ao nosso site! A sua privacidade é muito importante para nós. Este documento descreve como coletamos, usamos e protegemos as informações que você nos fornece através deste site.</p>
  
        <p>2. Informações Coletadas</p>
        <p>Ao utilizar nosso site, podemos coletar as seguintes informações pessoais: Nome, Endereço de e-mail, Senha (criptografada para sua segurança), CEP (para localização geográfica).</p>
      
        <p>3. Uso das Informações</p>
        <p>As informações pessoais que coletamos são usadas apenas para os seguintes fins: Personalizar sua experiência e responder melhor às suas necessidades individuais, Melhorar nosso site com base nas informações e feedback que recebemos de você, Processar transações de forma segura, Enviar e-mails periódicos, se você optar por receber atualizações e informações relacionadas aos nossos serviços.</p>
      
        <p>4. Proteção das Informações</p>
        <p>Implementamos uma variedade de medidas de segurança para manter a segurança de suas informações pessoais quando você insere, envia ou acessa suas informações.</p>
      
        <p>5. Cookies</p>
        <p>Utilizamos cookies para entender e salvar suas preferências para futuras visitas, além de compilar dados agregados sobre o tráfego do site e a interação no site, para que possamos oferecer melhores experiências e ferramentas no futuro.</p>
      
        <p>6. Divulgação a Terceiros</p>
        <p>Não vendemos, trocamos ou transferimos suas informações pessoais a terceiros sem seu consentimento, exceto quando necessário para fornecer um serviço solicitado por você.</p>
      
        <p>7. Consentimento</p>
        <p>Ao utilizar nosso site, você consente com nossa política de privacidade.</p>
      
        <p>8. Alterações na Política de Privacidade</p>
        <p>Se decidirmos mudar nossa política de privacidade, atualizaremos esta página para refletir as alterações.</p>
      
        <p>9. Contato</p>
        <p>Se houver dúvidas sobre esta política de privacidade, você pode nos contatar usando as informações fornecidas em nosso site.</p>
      
        <p>Ao utilizar este site, você concorda com os termos e condições descritos acima.</p>
      
        <p>Última atualização: 22/06/2024</p>
    </div>
    <div class="sign-txt"><a href="cadastro.php">Voltar</a></div>
  </div>
</body>
<footer>
  Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>
