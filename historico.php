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

// Recuperar simulações do usuário incluindo informações de tarifa
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conexao->prepare("SELECT s.id, s.mes, s.ano, s.data_simulacao, s.tipo, 
                                  r.valor_conta, r.potencia_placa, r.consumo_mensal_kwh, 
                                  r.placas_necessarias, r.economia_anual, t.tarifa 
                           FROM simulacoes s
                           JOIN resultados r ON s.id = r.id_simulacao
                           JOIN tarifas t ON s.id = t.id_simulacao
                           WHERE s.id_usuario = ?
                           ORDER BY s.data_simulacao DESC
                           LIMIT 10");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultadoSimulacoes = $stmt->get_result();
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
  <title>Lumiener - Histórico</title>
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
      text-align: center;
      position: relative;
    }

    .calculator-title .excluir {
      position: absolute;
      top: 0;
      right: 0;
      background: none;
      border: none;
      color: black;
      font-size: 30px;
      cursor: pointer;
    }

    .simulacao {
      background-color: #ffc107;
      color: white;
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 8px;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }

    .simulacao button.excluir {
      position: absolute;
      top: 10px;
      right: 10px;
      background: none;
      border: none;
      color: white;
      font-size: 24px;
      cursor: pointer;
    }

    .simulacao button.visualizar {
      background-color: #007bff;
      color: white;
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-top: 20px;
    }

    .simulacao button.visualizar:hover {
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

    body.dark-theme .calculator-container {
      background-color: #333 !important;
      color: #fff !important;
      border: 3px solid #222 !important;
    }

    body.dark-theme .calculator-title {
      color: #ffa600 !important;
    }

    body.dark-theme .simulacao {
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

    .historico-container {
      max-height: 400px;
      overflow-y: auto;
      padding-right: 10px; /* Padding to avoid content hiding behind the scrollbar */
    }

    .historico-container::-webkit-scrollbar {
      width: 10px;
    }

    .historico-container::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    .historico-container::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 5px;
    }

    .historico-container::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    body.dark-theme .historico-container::-webkit-scrollbar-track {
      background: #333;
    }

    body.dark-theme .historico-container::-webkit-scrollbar-thumb {
      background: #ffa600;
    }

    body.dark-theme .historico-container::-webkit-scrollbar-thumb:hover {
      background: #ff8c00;
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
      <h2 class="calculator-title">Histórico de Simulações</h2>
      <div id="alert-container"></div> <!-- Container para a mensagem de sucesso -->

      <div class="historico-container">
        <?php while ($simulacao = $resultadoSimulacoes->fetch_assoc()) : ?>
          <div class="simulacao" id="simulacao-<?php echo $simulacao['id']; ?>">
            <form class="form-excluir" data-id="<?php echo $simulacao['id']; ?>" method="POST">
              <input type="hidden" name="id_simulacao" value="<?php echo $simulacao['id']; ?>">
              <button type="submit" class="excluir" title="Excluir">&times;</button>
            </form>
            <h3><?php echo $meses[$simulacao['mes']] . ' de ' . $simulacao['ano']; ?></h3>
            <h4>(<?php echo ucfirst($simulacao['tipo']); ?>)</h4>
            <form action="detalhes_simulacao.php" method="GET">
              <input type="hidden" name="id" value="<?php echo $simulacao['id']; ?>">
              <button type="submit" class="visualizar">Visualizar</button>
            </form>
          </div>
        <?php endwhile; ?>
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
    });

    // Função AJAX para excluir simulação
    $(document).on('submit', '.form-excluir', function(e) {
      e.preventDefault();
      const form = $(this);
      const id_simulacao = form.data('id');

      if (confirm('Tem certeza que deseja excluir o histórico?')) {
        $.ajax({
          url: 'excluir_simulacao.php',
          type: 'POST',
          data: { id_simulacao: id_simulacao },
          success: function(response) {
            if (response.trim() === 'success') {
              $('#simulacao-' + id_simulacao).remove();
              $('#alert-container').html('<div class="alert alert-success" role="alert">Histórico apagado com sucesso!</div>');
            } else {
              $('#alert-container').html('<div class="alert alert-danger" role="alert">Erro ao excluir histórico!</div>');
            }
          },
          error: function() {
            $('#alert-container').html('<div class="alert alert-danger" role="alert">Erro ao excluir histórico!</div>');
          }
        });
      }
    });
  </script>
</body>
<footer id="footer" class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>
</html>
