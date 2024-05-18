<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id_usuario'])) {
  header('Location: main.php');
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

// Função para fazer chamadas de API
function callAPI($url)
{
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  $data = curl_exec($ch);
  curl_close($ch);
  return $data;
}

// Função para obter coordenadas geográficas a partir do CEP usando OpenCage API
function getCoordinatesFromZip($zipCode, $apiKey)
{
  $url = "https://api.opencagedata.com/geocode/v1/json?q={$zipCode}&key={$apiKey}&pretty=1&no_annotations=1";
  $response = callAPI($url);
  $data = json_decode($response, true);
  if (isset($data['results'][0]['geometry'])) {
    return $data['results'][0]['geometry'];
  } else {
    return null; // Error ou no data
  }
}

// Função para obter a média da incidência solar diária usando NASA POWER API
function getSolarData($latitude, $longitude)
{
  $url = "https://power.larc.nasa.gov/api/temporal/daily/point?parameters=ALLSKY_SFC_SW_DWN&community=RE&longitude={$longitude}&latitude={$latitude}&format=JSON&start=20230101&end=20231231";
  $response = callAPI($url);
  $data = json_decode($response, true);
  if (isset($data['properties']['parameter']['ALLSKY_SFC_SW_DWN'])) {
    return array_sum($data['properties']['parameter']['ALLSKY_SFC_SW_DWN']) / count($data['properties']['parameter']['ALLSKY_SFC_SW_DWN']);
  } else {
    return null; // Error ou no data
  }
}

// Recuperar a localização do usuário a partir do banco de dados
$id_usuario = $_SESSION['id_usuario'];
$stmt = $conexao->prepare("SELECT cep FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$stmt->bind_result($cep);
$stmt->fetch();
$stmt->close();

// Obter a chave da API do OpenCage
$openCageApiKey = '69523433d3404eeba189ce786e00a2dd';

// Converter CEP em coordenadas geográficas
$geometry = getCoordinatesFromZip($cep, $openCageApiKey);
if ($geometry) {
  $latitude = $geometry['lat'];
  $longitude = $geometry['lng'];

  // Obter dados de irradiação solar usando coordenadas
  $solarInsolation = getSolarData($latitude, $longitude);
} else {
  $erroLocalizacao = "Não foi possível obter a localização para o CEP fornecido.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  if (!empty($_POST['valor-conta']) && !empty($_POST['valor-tarifa']) && !empty($_POST['potencia-placa'])) {
    $valorConta = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor-conta']);
    $valorConta = floatval($valorConta);
    $custoEnergia = floatval($_POST['valor-tarifa']);
    $potenciaPlaca = intval($_POST['potencia-placa']);

    // Continuação dos cálculos existentes
    if ($valorConta > 0 && $custoEnergia > 0 && $potenciaPlaca > 0 && $solarInsolation) {
      $consumoMensalEmKWh = $valorConta / $custoEnergia;
      $consumoDiarioKWh = $consumoMensalEmKWh / 30;
      $kwPico = $consumoDiarioKWh / $solarInsolation;
      $wattsNecessarios = $kwPico * 1000;
      $placasNecessarias = ceil($wattsNecessarios / $potenciaPlaca);

      // Calcula economia anual estimada
      $diasPorAno = 365;
      $economiaAnual = $consumoDiarioKWh * $diasPorAno * $custoEnergia;

      // Salvar simulação no banco de dados
      $stmt = $conexao->prepare("INSERT INTO simulacoes (id_usuario) VALUES (?)");
      $stmt->bind_param("i", $id_usuario);
      $stmt->execute();
      $id_simulacao = $stmt->insert_id;
      $stmt->close();

      // Salvar resultados no banco de dados
      $stmt = $conexao->prepare("INSERT INTO resultados (id_simulacao, valor_conta, tarifa, potencia_placa, consumo_mensal_kwh, placas_necessarias, economia_anual) VALUES (?, ?, ?, ?, ?, ?, ?)");
      $stmt->bind_param("idididi", $id_simulacao, $valorConta, $custoEnergia, $potenciaPlaca, $consumoMensalEmKWh, $placasNecessarias, $economiaAnual);
      $stmt->execute();
      $stmt->close();

      $msgSimulacao = "Simulação salva com sucesso!";
    } else {
      $erroCalculo = "Todos os campos devem ser preenchidos e maiores que zero.";
    }

  } else {
    $erroCalculo = "Por favor, preencha todos os campos.";
  }
}

$conexao->close();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" href="images/Logo.ico" type="image/x-icon" />
  <title>Lumiener - Página Inicial</title>
  <link rel="stylesheet" href="styleConteudo.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

  <style>
    /* Estilos CSS para o input range */
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

    .calculator-input {
      margin-bottom: 25px;
    }

    .calculator-input label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    .calculator-input input[type="range"] {
      width: 100%;
      border-radius: 5px;
      background-color: #f0f0f0;
      outline: none;
      appearance: none;
    }

    .calculator-input input[type="range"]::-webkit-slider-thumb {
      appearance: none;
      width: 20px;
      height: 20px;
      border-radius: 50%;
      background-color: #e8a220;
      cursor: pointer;
    }

    .calculator-input input[type="range"]::-webkit-slider-thumb:hover {
      background-color: #e87a20;
    }

    .btn-simulacao {
      background-color: #ffa600;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    .btn-simulacao:hover {
      background-color: #e87a20;
    }

    .btn-whatsapp {
      background-color: #25D366;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }

    .btn-whatsapp i {
      font-size: 24px;
      margin-right: 10px;
    }

    .btn-whatsapp:hover {
      background-color: #1ebe5f;
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
      background-color: #333;
      color: #fff;
      border: 3px solid #222;
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

    body.dark-theme .btn-whatsapp {
      background-color: #28A745;
    }

    body.dark-theme .btn-whatsapp:hover {
      background-color: #1e7e34;
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
    <p id="lumiener" class="lumiener">Lumiener</p>
    <!-- Div da Calculadora Solar -->
    <div class="calculator-container">
      <div class="calculator-title">
        Simulador de Economia com Energia Solar (On-Grid)
        <a href="estatisticas.php" class="btn-simulacao">Visualizar Resultados</a>
      </div>
      <form method="POST" action="">
        <div style="display: flex; align-items: center;">
          <img src="images/Solzinho.png" alt="Logo" style="width: 15%; height: 15%;">
          <p>Incidência Solar Diária Média em sua Região: <?php echo number_format($solarInsolation, 2); ?> kWh/m²</p>
        </div>
        <div class="calculator-input">
          <label for="valor-conta">Valor da Conta de Luz (mensal):</label>
          <div>
            <input type="range" min="0" max="3000" id="valor-conta" name="valor-conta" value="500" step="1" oninput="updateTextInput(this.value);" />
            <output id="rangeValue">R$ 500,00</output>
          </div>
        </div>
        <div class="calculator-input">
          <label for="valor-tarifa">Tarifa em sua Região (R$/kWh):</label>
          <input type="range" id="valor-tarifa" name="valor-tarifa" min="0.00" max="2.00" value="0.89" step="0.01" oninput="updateTarifaDisplay(this.value);">
          <output id="tarifa-display">R$ 0,89</output>
        </div>
        <div class="calculator-input">
          <label>Escolha a potência da placa solar desejada (Wp):</label>
          <select name="potencia-placa">
            <option value="265">265Wp</option>
            <option value="300">300Wp</option>
            <option value="350">350Wp</option>
            <option value="465">465Wp</option>
            <option value="545">545Wp</option>
            <option value="1000">1000Wp</option>
          </select>
        </div>
        <button type="submit" class="btn-simulacao">Calcular</button>
        <p class="aviso">*Todos os valores são aproximados e sujeitos a uma margem de erro.*</p>
        <?php if (isset($msgSimulacao)): ?>
          <p><?php echo $msgSimulacao; ?></p>
        <?php elseif (isset($erroCalculo)): ?>
          <p style="color: red;"><?php echo $erroCalculo; ?></p>
        <?php endif; ?>
      </form>
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

    // Remove caracteres não numéricos ao enviar o formulário
    const form = document.querySelector('form');
    form.addEventListener('submit', function (event) {
      const valorContaInput = document.getElementById("valor-conta");
      let valor = valorContaInput.value;
      valor = valor.replace(/\D/g, ''); // Remove tudo que não for número
      valorContaInput.value = valor;
    });

    // Função para atualizar o texto ao lado do range
    function updateTextInput(val) {
      document.getElementById('rangeValue').innerText = "R$ " + val + ",00";
    }

    function updateTarifaDisplay(value) {
      document.getElementById('tarifa-display').innerText = `R$ ${parseFloat(value).toFixed(2)}`;
    }

    function solicitarOrcamento() {
      const valorConta = document.getElementById("valor-conta").value;
      const tarifa = document.getElementById("valor-tarifa").value;
      const potenciaPlaca = document.querySelector("select[name='potencia-placa']").value;

      const resultadoPlacas = document.querySelector(".result-text p:nth-child(2)");
      if (!resultadoPlacas) {
        alert("Por favor, calcule o número de placas necessárias antes de solicitar um orçamento.");
        return;
      }

      const placasNecessarias = resultadoPlacas.innerText.split(': ')[1];

      const mensagem = `Olá! Estou entrando em contato através do Lumiener. Gostaria de solicitar um orçamento para instalação de placas solares. São necessárias ${placasNecessarias} para minha residência. Pode me passar os valores?`;

      const encodedMessage = encodeURIComponent(mensagem);
      const whatsappUrl = `https://wa.me/5511900000000?text=${encodedMessage}`; // Substitua 5511900000000 pelo número de telefone desejado com código do país e DDD.

      window.open(whatsappUrl, '_blank');
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
</body>
<footer id="footer" class="footer"
  style="text-align: center; padding: 10px 0; font-size: 1em; color: #777; width: 100%; background: #e2e2e2; position: relative; clear: both;">
  &copy; <?php echo date('Y'); ?> Lumiener. Todos os direitos reservados.
</footer>

</html>
