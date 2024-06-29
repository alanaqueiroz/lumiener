<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: index.php');
    exit;
}

// Verificar se o ID da simulação foi fornecido
if (!isset($_GET['id'])) {
    die("ID da simulação não fornecido.");
}

$id_simulacao = intval($_GET['id']);

// Conectar ao banco de dados
$host = 'localhost';
$banco = 'banco';
$usuario = 'root';
$senhaBanco = '';

$conexao = new mysqli($host, $usuario, $senhaBanco, $banco);

if ($conexao->connect_error) {
    die("Erro de conexão: " . $conexao->connect_error);
}

// Recuperar detalhes da simulação específica incluindo informações de tarifa
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conexao->prepare("SELECT s.id, s.mes, s.ano, s.data_simulacao, s.tipo, 
                                  r.valor_conta, r.potencia_placa, r.consumo_mensal_kwh, 
                                  r.placas_necessarias, r.economia_anual, t.tarifa 
                           FROM simulacoes s
                           JOIN resultados r ON s.id = r.id_simulacao
                           JOIN tarifas t ON s.id = t.id_simulacao
                           WHERE s.id_usuario = ? AND s.id = ?");
$stmt->bind_param("ii", $id_usuario, $id_simulacao);
$stmt->execute();
$resultadoSimulacao = $stmt->get_result()->fetch_assoc();
$stmt->close();

$conexao->close();

$meses = [
  1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
  5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
  9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="images/Logo.ico" type="image/x-icon" />
  <title>Lumiener - Detalhes da Simulação</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styleConteudo.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

  <style>
      /* Media query para dispositivos móveis */
      @media (max-width: 768px) {
      .details-content {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px; /* Padding to avoid content hiding behind the scrollbar */
      }

      .details-content:-webkit-scrollbar {
        width: 10px;
      }

      .details-content::-webkit-scrollbar-track{
        background: #f1f1f1;
      }

      .details-content::-webkit-scrollbar-thumb{
        background: #888;
        border-radius: 5px;
      }

      .details-content::-webkit-scrollbar-thumb:hover{
        background: #555;
      }

      body.dark-theme .details-content::-webkit-scrollbar-track{
        background: #333;
      }

      body.dark-theme .details-content::-webkit-scrollbar-thumb{
        background: #ffa600;
      }

      body.dark-theme .details-content::-webkit-scrollbar-thumb:hover {
        background: #ff8c00;
      }
    }

    .details-container {
      background-color: #f9f9f9;
      border: 3px solid #ccc;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
      padding: 25px;
      margin-left: auto;
      margin-right: auto;
      width: 100%;
      max-width: 800px; /* Tamanho máximo fixo */
      margin-bottom: 20px; /* Margem inferior para não sobrepor o footer */
    }

    .details-title {
      font-size: 24px;
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
    }

    .details-content {
      background-color: #ffc107;
      color: white;
      padding: 20px;
      border-radius: 8px;
      overflow-wrap: break-word;
    }

    .details-content h2 {
      font-size: 20px;
      margin-bottom: 15px;
      color: white;
    }

    .details-content p {
      margin: 10px 0;
    }

    .btn-back {
      background-color: #007bff;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      display: block;
      margin: 20px auto;
      text-align: center;
    }

    .btn-back:hover {
      background-color: #0056b3;
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

    body.dark-theme .details-container {
      background-color: #333 !important;
      color: #fff !important;
      border: 3px solid #222 !important;
    }

    body.dark-theme .details-title {
      color: #ffa600 !important;
    }

    body.dark-theme .details-content {
      background-color: #444;
    }

    body.dark-theme .details-content h2 {
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

    body.dark-theme input[type=range]::-webkit-slider-runnable-track {
      border-radius: 50px;
      background: #ccc;
    }

    .theme-icon.sun {
      color: #fff;
    }

    .btn-whatsapp {
      background-color: #25D366;
      color: #fff;
      width: 60px;
      height: 60px;
      border: none;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .btn-whatsapp i {
      font-size: 40px;
    }

    .btn-whatsapp:hover {
      background-color: #ffffff;
      color: #25D366;
      text-decoration: none;
    }

    body.dark-theme .btn-whatsapp {
      background-color: #25D366;
    }

    body.dark-theme .btn-whatsapp:hover {
      background-color: #ffffff;
      color: #25D366;
    }

    .btn-back-container {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 20px;
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
    <div class="details-container">
      <div class="details-title">Detalhes da Simulação</div>
      <div class="details-content">
        <h2><?php echo $meses[$resultadoSimulacao['mes']] . ' de ' . $resultadoSimulacao['ano']; ?></h2><br>
      <p>
          <strong>Data da Simulação:</strong> <?php echo date('d/m/Y H:i', strtotime($resultadoSimulacao['data_simulacao'])); ?>
  </p><br>
        <p><strong>Valor da Conta:</strong> R$ <?php echo number_format($resultadoSimulacao['valor_conta'], 2, ',', '.'); ?></p><br>
        <p><strong>Tarifa:</strong> R$ <?php echo number_format($resultadoSimulacao['tarifa'], 2, ',', '.'); ?></p><br>
        <p><strong>Consumo Mensal:</strong> <?php echo number_format($resultadoSimulacao['consumo_mensal_kwh'], 2, ',', '.'); ?> kWh</p><br>
        <p><strong>Potência dos Painéis:</strong> <?php echo $resultadoSimulacao['potencia_placa']; ?> W</p><br>
        <p><strong>Quantidade de Painéis:</strong> <?php echo $resultadoSimulacao['placas_necessarias']; ?></p><br>
      </div>
      <div class="btn-back-container">
      <button class="btn-back" onclick="window.history.back()">Voltar</button>
      <a href="https://wa.me/5514997526346?text=Olá Junior, estou entrando em contato através do Lumiener. Gostaria de obter um orçamento detalhado para a instalação de painéis solares de aproximadamente <?php echo $resultadoSimulacao['potencia_placa']; ?>W" class="btn-whatsapp">
        <i class='bx bxl-whatsapp'></i>
      </a>
    </div>
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

      $('.valor-conta').mask('000.000,00', { reverse: true });
      $('.valor-tarifa').mask('0,00', { reverse: true });
      $('.valor-economia').mask('000.000,00', { reverse: true });
    });
  </script>
</body>
<footer id="footer" class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>
