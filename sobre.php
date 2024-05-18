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
  <link rel="stylesheet" href="styleConteudo.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>
  
  <style>
    .calculator-container {
      background-color: #f9f9f9;
      border: 1px solid #ccc;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin: 20px auto;
      width: 70%;
      text-align: center;
    }

    .calculator-title {
      font-size: 24px;
      color: #333;
      margin-bottom: 20px;
    }

    .theme-btn {
      width: 40px; /* Largura total do interruptor */
      height: 20px;
      background: gray; /* Cor de fundo quando está desativado */
      border-radius: 10px;
      position: relative;
      transition: background 0.5s;
    }

    .theme-ball {
      position: absolute;
      top: 2px; /* Espaço pequeno do topo para parecer um botão */
      left: 2px; /* Começa à esquerda quando desativado */
      width: 16px;
      height: 16px;
      background-color: #fff;
      border-radius: 50%;
      transition: left 0.5s;
    }

    .theme-btn.active {
      background: #ffa600; /* Cor laranja, igual aos ícones do menu lateral */
    }

    .theme-btn .theme-ball.active {
      left: 22px; /* Mova para a direita quando ativado */
    }

    .theme-btn,
    .theme-btn .theme-ball,
    .home,
    .footer#footer {
        transition: var(--transition);  /* Usa a mesma duração de transição definida no root */
    }

    .calculator-container ul {
      text-align: left; /* Alinha o texto à esquerda dentro da lista */
      padding: 0 20px; /* Adiciona padding para a lista não colar nas bordas */
    }

    .calculator-container li {
      margin-bottom: 10px; /* Adiciona espaço entre os itens da lista */
    }

    body.dark-theme {
      background-color: #333;
    }

    body.dark-theme .home
    {
      background-color: #000;
    }

    body.dark-theme .lumiener#lumiener{
      color: #ffa600;
    }

    body.dark-theme .footer#footer{
      background-color: #000 !important;
      color: #ffa600 !important;
    }

    body.dark-theme .sidebar,
    body.dark-theme .logo,
    body.dark-theme .nav-links
    {
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

    body.dark-theme .result-text
    {
      background-color: #444;
    }

    body.dark-theme p{
      color: #fff;
    }

    /* CSS para ícones no tema escuro */
    body.dark-theme .nav-links i{
        color: #fff;/* Cor branca para os ícones no tema escuro */
    }

    /* CSS para o ícone do menu no modo escuro */
    body.dark-theme .btn-menu {
        color: #fff; /* Muda a cor para branco */
    }

    /* Muda a cor do texto dos títulos dos links para branco no tema escuro */
    body.dark-theme .nav-links .title {
        color: #fff;
    }

    /* Muda a cor do texto dos tooltips para branco no tema escuro */
    body.dark-theme .tooltip {
        color: #fff;
        background: #333; /* ou outra cor que se destaque no fundo escuro */
    }

    /* Fundo padrão para os ícones no modo escuro */
    body.dark-theme .nav-links li a {
      background: #333; /* Fundo escuro para os ícones no modo escuro */
      border-radius: 12px; /* Mantém as bordas arredondadas */
    }

    /* Fundo ao passar o mouse no modo escuro */
    body.dark-theme .nav-links li:hover a {
      background: #ffa600; /* Laranja ao passar o mouse */
      border-radius: 12px; /* Mantém as bordas arredondadas */
    }

    /* Estilo da trilha para o modo escuro */
    body.dark-theme input[type=range]::-webkit-slider-runnable-track {
      border-radius: 50px;
      background: #ccc; /* Sua cor desejada para o modo escuro */
    }

    body.dark-theme .btn-whatsapp {
      background-color: #28A745; /* Cor mais escura para botão WhatsApp no tema escuro */
    }

    body.dark-theme .btn-whatsapp:hover {
      background-color: #1e7e34;
    }

    .theme-icon.sun {
    color: #fff; /* Define a cor do ícone para branco */
    }

  </style>
</head>

<body>
<section class="sidebar">
    <div class="nav-header">
      <p class="logo" style=""><?php echo $_SESSION['nome_usuario'];?></p>
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
    <p id="lumiener" class="lumiener">Lumiener</p>
    <div class="calculator-container">
      <div class="calculator-title">Entenda os Cálculos</div>
      <p>Como é feito o calcúlo do nosso sistema:</p>
      <ul>
        <li>A "Solar Insolation" (incidência solar diária média) é fundamental por várias razões:</li>
        <li>Cálculo da Capacidade de Geração de Energia: Saber a incidência solar na localização do usuário permite calcular quantos kWh de energia podem ser gerados por cada metro quadrado de painel solar por dia. Isso ajuda a determinar quantos painéis são necessários para atender às necessidades de energia do usuário.</li>
        <li>Eficiência do Sistema Solar: A eficácia de um sistema solar depende fortemente da quantidade de sol disponível. Em regiões com maior irradiação solar, menos painéis podem ser necessários para gerar a mesma quantidade de energia.
        Planejamento de Investimento: Ao compreender a irradiação solar, os investidores ou proprietários podem fazer cálculos mais precisos sobre o retorno do investimento em sistemas solares.</li>
        <li><b>Na seção do script onde o cálculo é realizado:</b></li>
        <li>Calcula-se o Consumo Mensal em kWh: Divide-se o valor da conta de luz pelo custo da energia por kWh (tarifa).</li>
        <li>Calcula-se o Consumo Diário: O consumo mensal é dividido por 30 dias.</li>
        <li>Determina-se o kW Pico Necessário: Divide-se o consumo diário pela "Solar Insolation" para encontrar a capacidade de geração necessária por dia em kW pico.</li>
        <li>Transforma-se kW Pico em Watts Necessários: Multiplica-se por 1000 para converter kW em Watts.</li>
        <li>Calcula-se o Número de Placas Necessárias: Divide-se os Watts necessários pela potência de uma placa solar individual.</li>
        <li>Cada etapa do cálculo é impactada pela "Solar Insolation", pois ela determina quanta energia solar está disponível para ser convertida em eletricidade. Sem essa informação, não é possível prever com precisão quantos painéis solares são necessários.</li>
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

      document.querySelector('.theme-btn').addEventListener('click', function() {
      const isDarkMode = this.classList.toggle('active');
      this.querySelector('.theme-ball').classList.toggle('active');
      document.body.classList.toggle('dark-theme'); // Alterna a classe do tema escuro no body

      // Salvar a preferência de tema no Local Storage
      localStorage.setItem('darkTheme', isDarkMode ? 'true' : 'false');

      // Alterar ícone e texto conforme o tema
      updateThemeIconAndText(isDarkMode);
  });

  function updateThemeIconAndText(isDarkMode) {
      const themeIcon = document.querySelector('.theme-icon');
      const themeText = document.querySelector('.theme-wrapper p');
      if (isDarkMode) {
          themeIcon.className = 'bx bxs-sun theme-icon sun'; // Muda para ícone de sol
          themeText.textContent = 'Light Theme';
      } else {
          themeIcon.className = 'bx bxs-moon theme-icon'; // Muda para ícone de lua
          themeText.textContent = 'Dark Theme';
      }
  }

  // Verificar a preferência de tema salva ao carregar a página
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
<footer id="footer" class="footer" style="text-align: center; padding: 10px 0; font-size: 1em; color: #777; width: 100%; background: #e2e2e2; position: relative; clear: both;">
  &copy; <?php echo date('Y'); ?> Lumiener. Todos os direitos reservados.
</footer>
</html>
