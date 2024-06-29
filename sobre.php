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
$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="images/Logo.ico" type="image/x-icon" />
  <title>Lumiener - Entenda os Cálculos</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styleConteudo.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

  <style>
        /* Media query para dispositivos móveis */
    @media (max-width: 768px) {
      .calculator-container {
        max-height: 500px;
        overflow-y: auto;
        padding-right: 10px; /* Padding to avoid content hiding behind the scrollbar */
      }

      .calculator-container::-webkit-scrollbar,
      .climate-report-container::-webkit-scrollbar {
        width: 10px;
      }

      .calculator-container::-webkit-scrollbar-track,
      .climate-report-container::-webkit-scrollbar-track {
        background: #f1f1f1;
      }

      .calculator-container::-webkit-scrollbar-thumb,
      .climate-report-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 5px;
      }

      .calculator-container::-webkit-scrollbar-thumb:hover,
      .climate-report-container::-webkit-scrollbar-thumb:hover {
        background: #555;
      }

      body.dark-theme .calculator-container::-webkit-scrollbar-track,
      body.dark-theme .climate-report-container::-webkit-scrollbar-track {
        background: #333;
      }

      body.dark-theme .calculator-container::-webkit-scrollbar-thumb,
      body.dark-theme .climate-report-container::-webkit-scrollbar-thumb {
        background: #ffa600;
      }

      body.dark-theme .calculator-container::-webkit-scrollbar-thumb:hover,
      body.dark-theme .climate-report-container::-webkit-scrollbar-thumb:hover {
        background: #ff8c00;
      }
    }

    .calculator-container {
      background-color: #f9f9f9;
      border: 3px solid #ccc;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
      padding: 25px;
      margin-left: auto;
      margin-right: auto;
      width: 100%;
      max-width: 800px;
      /* Tamanho máximo fixo */
      margin-bottom: 20px;
      /* Margem inferior para não sobrepor o footer */
      text-align: center;
    }

    .calculator-title {
      font-size: 24px;
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
      position: relative;
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

    .calculator-container ul {
      text-align: left;
      padding: 0 20px;
    }

    .calculator-container li {
      margin-bottom: 10px;
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

    body.dark-theme .result-text {
      background-color: #444;
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

    body.dark-theme input[type=range]::-webkit-slider-runnable-track {
      border-radius: 50px;
      background: #ccc;
    }

    body.dark-theme .btn-whatsapp {
      background-color: #28A745;
    }

    body.dark-theme .btn-whatsapp:hover {
      background-color: #1e7e34;
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

    .sidebar.expand~.footer {
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
      <h2 class="calculator-title">Entenda os Cálculos</h2>
      <p>Como é feito o cálculo do nosso sistema:</p>
      <ul>
        <li>Divide-se o valor da conta de luz pelo custo da tarifa de energia para obter o consumo mensal em kWh.</li>
        <li>Divide-se o consumo mensal em kWh por 30 para calcular o consumo diário.</li>
        <li>Divide-se o consumo diário pela incidência solar diária média para encontrar a capacidade de geração
          necessária em kW (diária).</li>
        <li>Multiplicar a capacidade de geração necessária em kW por 1000 para converter para Watts necessários (pois 1 kW = 1000 Watts).</li>
        <li>Divide-se os Watts necessários pela potência de uma placa solar para calcular o número de painéis
          necessários.</li>
      </ul>
    </div>
  </section>
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

    document.querySelector('.theme-btn').addEventListener('click', function () {
      const isDarkMode = this.classList.toggle('active');
      this.querySelector('.theme-ball').classList.toggle('active');
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

    document.addEventListener('DOMContentLoaded', function () {
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
  <footer id="footer" class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
  </footer>
</body>

</html>