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


// Função para obter dados climáticos personalizados usando NASA POWER API
function getCustomClimateReport($latitude, $longitude)
{
    $startDate = date('Ymd', strtotime('-9 days')); // Últimos 10 dias
    $endDate = date('Ymd');
    $url = "https://power.larc.nasa.gov/api/temporal/daily/point?parameters=ALLSKY_SFC_SW_DWN,CLRSKY_SFC_SW_DWN,T2M,PRECTOTCORR,RH2M&community=RE&longitude={$longitude}&latitude={$latitude}&format=JSON&start={$startDate}&end={$endDate}";
    $response = callAPI($url);
    $data = json_decode($response, true);

    if (isset($data['properties']['parameter'])) {
        $allSky = $data['properties']['parameter']['ALLSKY_SFC_SW_DWN'];
        $clearSky = $data['properties']['parameter']['CLRSKY_SFC_SW_DWN'];
        $temperature = $data['properties']['parameter']['T2M'];
        $precipitation = $data['properties']['parameter']['PRECTOTCORR'];
        $humidity = $data['properties']['parameter']['RH2M'];

        $climateReport = [];
        foreach ($allSky as $date => $irradiation) {
            $climateReport[] = [
                'date' => $date,
                'irradiation' => $irradiation,
                'clearSkyIrradiation' => $clearSky[$date],
                'temperature' => $temperature[$date],
                'precipitation' => $precipitation[$date],
                'humidity' => $humidity[$date]
            ];
        }
        return $climateReport;
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

    // Verificar se a incidência solar já está no banco de dados
    $stmt = $conexao->prepare("SELECT incidencia FROM incidencia_solar WHERE latitude = ? AND longitude = ?");
    $stmt->bind_param("dd", $latitude, $longitude);
    $stmt->execute();
    $stmt->bind_result($solarInsolation);
    $stmt->fetch();
    $stmt->close();

    // Se a incidência solar não estiver no banco, buscar da API e armazenar
    if (empty($solarInsolation)) {
        $solarInsolation = getSolarData($latitude, $longitude);
        if ($solarInsolation) {
            $stmt = $conexao->prepare("INSERT INTO incidencia_solar (latitude, longitude, incidencia) VALUES (?, ?, ?)");
            $stmt->bind_param("ddd", $latitude, $longitude, $solarInsolation);
            $stmt->execute();
            $stmt->close();
        } else {
            $erroLocalizacao = "Não foi possível obter a incidência solar para a localização fornecida.";
        }
    }

    // Obter relatório climático personalizado
    $climateReport = getCustomClimateReport($latitude, $longitude);
} else {
    $erroLocalizacao = "Não foi possível obter a localização para o CEP fornecido.";
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
  <title>Lumiener - Relatórios Climáticos</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="styleConteudo.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

  <style>
      /* Media query para dispositivos móveis */
      @media (max-width: 768px) {
      .climate-report-container {
        max-height: 400px;
        overflow-y: auto;
        padding-right: 10px; /* Padding to avoid content hiding behind the scrollbar */
      }

      .climate-report-container:-webkit-scrollbar {
        width: 10px;
      }

      .climate-report-container::-webkit-scrollbar-track{
        background: #f1f1f1;
      }

      .climate-report-container::-webkit-scrollbar-thumb{
        background: #888;
        border-radius: 5px;
      }

      .climate-report-container::-webkit-scrollbar-thumb:hover{
        background: #555;
      }

      body.dark-theme .climate-report-container::-webkit-scrollbar-track{
        background: #333;
      }

      body.dark-theme .climate-report-container::-webkit-scrollbar-thumb{
        background: #ffa600;
      }

      body.dark-theme .climate-report-container::-webkit-scrollbar-thumb:hover {
        background: #ff8c00;
      }
    }

    .calculator-container, .climate-report-container {
      background-color: #f9f9f9;
      border: 3px solid #ccc;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
      padding: 25px;
      margin-left: auto;
      margin-right: auto;
      width: 100%;
      max-width: 800px;
      margin-bottom: 20px;
      text-align: center;
    }

    .calculator-title {
      font-size: 24px;
      margin-bottom: 20px;
      font-weight: bold;
      text-align: center;
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

    .calculator-container ul, .climate-report-container ul {
      text-align: left;
      padding: 0 20px;
    }

    .calculator-container li, .climate-report-container li {
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

    body.dark-theme .calculator-container, body.dark-theme .climate-report-container {
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

    body.dark-theme table {
      background-color: #222 !important;
      color: #fff !important;
    }

    body.dark-theme th, body.dark-theme td {
      border: 1px solid #555 !important;
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
    <div class="climate-report-container">
        <h2 class="calculator-title">Relatório Climático - Últimos 10 dias</h2>
        <?php if (isset($climateReport)) { ?>
            <table class="table table-striped table-responsive">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Irradiação Solar (kWh/m²)</th>
                        <th>Temperatura (°C)</th>
                        <th>Precipitação (mm)</th>
                        <th>Umidade Relativa (%)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($climateReport as $day) { ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($day['date'])); ?></td>
                            <td><?php echo round($day['irradiation'], 2); ?></td>
                            <td><?php echo round($day['temperature'], 2); ?></td>
                            <td><?php echo round($day['precipitation'], 2); ?></td>
                            <td><?php echo round($day['humidity'], 2); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } else { ?>
            <p><?php echo $erroLocalizacao; ?></p>
        <?php } ?>
    </div>
   
  </section>
  <footer  class="footer">
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

</html>
