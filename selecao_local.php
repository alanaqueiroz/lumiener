<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: erro401.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tipo_local = $_POST['tipo-local'];
    $_SESSION['tipo_local'] = $tipo_local;
    if ($tipo_local == 'residencial') {
        header('Location: residencial.php');
    } elseif ($tipo_local == 'empresarial') {
        header('Location: empresarial.php');
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="images/Logo.ico" type="image/x-icon" />
  <title>Lumiener - Selecione o tipo de Local</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styleConteudo.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
  
  <style>
    .calculator-container {
      background-color: #f9f9f9;
      border: 3px solid #ccc;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
      padding: 25px;
      margin-left: auto;
      margin-right: auto;
      width: 100%;
      max-width: 800px; /* Tamanho máximo fixo */
      margin-bottom: 20px; /* Margem inferior para não sobrepor o footer */
      text-align: center;
    }

    .calculator-title {
      font-size: 24px;
      margin-bottom: 20px;
      font-weight: bold;
    }

    .btn-tipo-local {
      background-color: #ffa600;
      color: white;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      margin: 10px;
      font-size: 16px;
      cursor: pointer;
      width: 150px;
      transition: background-color 0.3s;
    }

    .btn-tipo-local:hover {
      background-color: #e87a20;
    }

    .theme-btn {
      width: 40px; 
      height: 20px;
      background: gray; 
      border-radius: 10px;
      position: relative;
      transition: background 0.5s;
    }

    .theme-ball {
      position: absolute;
      top: 2px; 
      left: 2px; 
      width: 16px;
      height: 16px;
      background-color: #fff;
      border-radius: 50%;
      transition: left 0.5s;
    }

    .theme-btn.active {
      background: #ffa600; 
    }

    .theme-btn .theme-ball.active {
      left: 22px; 
    }

    .theme-btn,
    .theme-btn .theme-ball,
    .home,
    .footer#footer {
        transition: var(--transition); 
    }

    body.dark-theme {
      background-color: #333;
    }

    body.dark-theme .home {
      background-color: #000;
    }

    body.dark-theme .lumiener#lumiener {
      color: #ffa600;
    }

    body.dark-theme .footer#footer {
      background-color: #000 !important;
      color: #ffa600 !important;
    }

    body.dark-theme .sidebar,
    body.dark-theme .logo,
    body.dark-theme .nav-links {
      background-color: #333;
      color: #fff;
    }

    body.dark-theme .calculator-container {
      background-color: #333 !important;
      color: #fff !important;
      border: 3px solid #222 !important;
    }

    body.dark-theme .calculator-title {
      color: #ffa600 !important;
    }

    body.dark-theme p {
      color: #fff;
    }

    body.dark-theme .nav-links i {
      color: #fff;
    }

    body.dark-theme .btn-menu {
      color: #fff; 
    }

    body.dark-theme .nav-links .title {
      color: #fff;
    }

    body.dark-theme .tooltip {
      color: #fff;
      background: #333;
    }

    body.dark-theme .nav-links li a {
      background: #333; 
      border-radius: 12px;
    }

    body.dark-theme .nav-links li:hover a {
      background: #ffa600; 
      border-radius: 12px; 
    }

    .theme-icon.sun {
      color: #fff; 
    }

    .footer {
      text-align: center;
      padding: 10px 0;
      font-size: 1em;
      color: #777;
      width: calc(100% - 78px);
      background: #e2e2e2;
      position: absolute;
      bottom: 0;
      left: 78px;
      transition: var(--transition);
    }

    .sidebar.expand ~ .footer {
      left: 250px;
      width: calc(100% - 250px);
      transition: var(--transition);
    }
  </style>
</head>

<body>
<section class="sidebar">
    <div class="nav-header">
      <p class="logo"><?php echo $_SESSION['nome_usuario']; ?></p>
      <i class="bx bx-menu-alt-right btn-menu"></i>
    </div>
    <ul class="nav-links">
      <li>
        <a href="selecao_local.php">
          <i class="bx bx-home-alt-2"></i>
          <span class="title">Início</span>
        </a>
        <span class="tooltip">Início</span>
      </li>
      <li>
        <a href="sobre.php">
          <i class="bx bx-calculator"></i>
          <span class="title">Sobre os cálculos</span>
        </a>
        <span class="tooltip">Sobre os cálculos</span>
      </li>
      <li>
        <a href="historico.php">
          <i class='bx bx-history'></i>
          <span class="title">Histórico</span>
        </a>
        <span class="tooltip">Histórico</span>
      </li>
      <li>
        <a href="relatorio.php">
          <i class="bx bx-bar-chart-alt-2"></i>
          <span class="title">Estatísticas</span>
        </a>
        <span class="tooltip">Estatísticas</span>
      </li>
      <li>
        <a href="perfil.php">
          <i class="bx bx-cog"></i>
          <span class="title">Perfil</span>
        </a>
        <span class="tooltip">Perfil</span>
      </li>
      <li>
        <a href="logout.php" onclick="return confirm('Tem certeza de que deseja sair?')">
          <i class="bx bx-log-out"></i>
          <span class="title">Sair</span>
        </a>
        <span class="tooltip">Sair</span>
      </li>
    </ul>
    <div class="theme-wrapper">
      <i class="bx bxs-moon theme-icon"></i>
      <p>Dark Theme</p>
      <div class="theme-btn">
        <span class="theme-ball"></span>
      </div>
    </div>
  </section>
  <section class="home">
    <p id="lumiener" class="lumiener" style="margin: 1.5%;">Lumiener</p>
    <div class="calculator-container">
      <h2 class="calculator-title">QUAL O TIPO DE LOCAL?</h2>
      <form method="POST" action="">
        <button type="submit" name="tipo-local" value="residencial" class="btn-tipo-local"><b>RESIDENCIAL</b></button>
        <button type="submit" name="tipo-local" value="empresarial" class="btn-tipo-local"><b>EMPRESARIAL</b></button>
      </form>
    </div>
  </section>
  <footer id="footer" class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
  </footer>
  <script>
    const btn_menu = document.querySelector(".btn-menu");
    const side_bar = document.querySelector(".sidebar");

    btn_menu.addEventListener("click", function () {
      side_bar.classList.toggle("expand");
      changebtn();
    });

    function changebtn() {
      if (side_bar.classList.contains("expand")) {
        btn_menu.classList.replace("bx-menu", "bx-menu-alt-right");
      } else {
        btn_menu.classList.replace("bx-menu-alt-right", "bx-menu");
      }
    }

    document.querySelector('.theme-btn').addEventListener('click', function() {
      const isDarkMode = this.classList.toggle('active');
      document.querySelector('.theme-ball').classList.toggle('active');
      document.body.classList.toggle('dark-theme'); 

      localStorage.setItem('darkTheme', isDarkMode ? 'true' : 'false');

      updateThemeIconAndText(isDarkMode);
    });

    function updateThemeIconAndText(isDarkMode) {
      const themeIcon = document.querySelector('.theme-icon');
      const themeText = document.querySelector('.theme-wrapper p');
      if (isDarkMode) {
          themeIcon.className = 'bx bxs-sun theme-icon sun'; 
          themeText.textContent = 'Light Theme';
      } else {
          themeIcon.className = 'bx bxs-moon theme-icon'; 
          themeText.textContent = 'Dark Theme';
      }
    }

    document.addEventListener('DOMContentLoaded', function() {
      const isDarkTheme = localStorage.getItem('darkTheme') === 'true';
      if (isDarkTheme) {
          document.body.classList.add('dark-theme');
          const themeBtn = document.querySelector('.theme-btn');
          const themeBall = document.querySelector('.theme-ball');
          themeBtn.classList.add('active');
          themeBall.classList.add('active');
          updateThemeIconAndText(true);
      }
    });
  </script>
</body>
</html>
