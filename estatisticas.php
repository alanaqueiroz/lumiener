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

// Recuperar simulações do usuário
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conexao->prepare("SELECT s.id, s.data_simulacao, r.valor_conta, r.tarifa, r.potencia_placa, r.consumo_mensal_kwh, r.placas_necessarias, r.economia_anual 
                           FROM simulacoes s
                           JOIN resultados r ON s.id = r.id_simulacao
                           WHERE s.id_usuario = ?
                           ORDER BY s.data_simulacao DESC
                           LIMIT 10");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resultadoSimulacoes = $stmt->get_result();
$stmt->close();

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="images/Logo.ico" type="image/x-icon" />
  <title>Lumiener - Estatísticas</title>
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
      max-width: 60%;
    }

    .calculator-title {
      font-size: 20px;
      margin-bottom: 10px;
    }

    .simulacao {
      background-color: #ffc107;
      color: white;
      padding: 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      position: relative;
    }

    .simulacao button.excluir {
      position: absolute;
      top: 15px;
      right: 15px;
      background: none;
      border: none;
      color: white;
      font-size: 16px;
      cursor: pointer;
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
        <a href="conteudo.php">
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
        <a href="estatisticas.php">
          <i class="bx bx-bar-chart"></i>
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
      <div class="calculator-title">Estatísticas</div>

      <?php while ($row = $resultadoSimulacoes->fetch_assoc()): ?>
        <div class="simulacao">
          <button class="excluir" onclick="excluirSimulacao(<?php echo $row['id']; ?>)">X</button>
          <p>Data: <?php echo $row['data_simulacao']; ?></p>
          <p>Valor da Conta: R$ <?php echo number_format($row['valor_conta'], 2); ?></p>
          <p>Tarifa: R$ <?php echo number_format($row['tarifa'], 2); ?></p>
          <p>Potência da Placa: <?php echo $row['potencia_placa']; ?> Wp</p>
          <p>Consumo Mensal: <?php echo number_format($row['consumo_mensal_kwh'], 2); ?> kWh</p>
          <p>Placas Necessárias: <?php echo $row['placas_necessarias']; ?></p>
          <p>Economia Anual: R$ <?php echo number_format($row['economia_anual'], 2); ?></p>
        </div>
      <?php endwhile; ?>
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

    function excluirSimulacao(idSimulacao) {
      if (confirm('Tem certeza que deseja excluir esta simulação?')) {
        fetch(`excluir_simulacao.php?id=${idSimulacao}`)
          .then(response => response.text())
          .then(result => {
            if (result === 'success') {
              location.reload();
            } else {
              alert('Erro ao excluir a simulação.');
            }
          });
      }
    }
  </script>
</body>
<footer id="footer" class="footer" style="text-align: center; padding: 10px 0; font-size: 1em; color: #777; width: 100%; background: #e2e2e2; position: relative; clear: both;">
  &copy; <?php echo date('Y'); ?> Lumiener. Todos os direitos reservados.
</footer>

</html>
