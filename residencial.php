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
} else {
    $erroLocalizacao = "Não foi possível obter a localização para o CEP fornecido.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['valor-conta']) && !empty($_POST['valor-tarifa']) && !empty($_POST['potencia-placa']) && !empty($_POST['mes-ano'])) {
        $valorConta = str_replace(['R$', '.', ','], ['', '', '.'], $_POST['valor-conta']);
        $valorConta = floatval($valorConta);

        $custoEnergia = number_format($_POST['valor-tarifa'], 2, '.', '');
        $custoEnergia = floatval($custoEnergia);

        $potenciaPlaca = intval($_POST['potencia-placa']);
        list($ano, $mes) = explode('-', $_POST['mes-ano']);

        $tipoTarifa = $valorConta <= 3000 ? 'residencial' : 'empresarial';

        // Verificar se já existe uma simulação para o mesmo mês, ano e tipo
        $stmt = $conexao->prepare("SELECT COUNT(*) FROM simulacoes WHERE id_usuario = ? AND mes = ? AND ano = ? AND tipo = ?");
        $stmt->bind_param("iiis", $id_usuario, $mes, $ano, $tipoTarifa);
        $stmt->execute();
        $stmt->bind_result($num_simulacoes);
        $stmt->fetch();
        $stmt->close();

        if ($num_simulacoes > 0) {
            $erroCalculo = "Exclua a simulação referente a esse mês para refazê-la.";
        } else {
            if ($valorConta > 0 && $custoEnergia > 0 && $potenciaPlaca > 0 && $solarInsolation) {
                $consumoMensalEmKWh = $valorConta / $custoEnergia;
                $consumoDiarioKWh = $consumoMensalEmKWh / 30;
                $kwPico = $consumoDiarioKWh / $solarInsolation;
                $wattsNecessarios = $kwPico * 1000;
                $placasNecessarias = ceil($wattsNecessarios / $potenciaPlaca);

                $diasPorAno = 365;
                $economiaAnual = $consumoDiarioKWh * $diasPorAno * $custoEnergia;

                $stmt = $conexao->prepare("INSERT INTO simulacoes (id_usuario, mes, ano, tipo) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiis", $id_usuario, $mes, $ano, $tipoTarifa);
                if (!$stmt->execute()) {
                    error_log("Erro ao executar statement: " . $stmt->error);
                }
                $id_simulacao = $stmt->insert_id;
                $stmt->close();

                $stmt = $conexao->prepare("INSERT INTO resultados (id_simulacao, valor_conta, potencia_placa, consumo_mensal_kwh, placas_necessarias, economia_anual) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("iddddi", $id_simulacao, $valorConta, $potenciaPlaca, $consumoMensalEmKWh, $placasNecessarias, $economiaAnual);
                if (!$stmt->execute()) {
                    error_log("Erro ao executar statement: " . $stmt->error);
                }
                $stmt->close();

                $stmt = $conexao->prepare("INSERT INTO tarifas (id_simulacao, tarifa) VALUES (?, ?)");
                $stmt->bind_param("id", $id_simulacao, $custoEnergia);
                if (!$stmt->execute()) {
                    error_log("Erro ao executar statement: " . $stmt->error);
                }
                $stmt->close();

                $msgSimulacao = "Simulação salva com sucesso em seu Histórico!";
            } else {
                $erroCalculo = "Todos os campos devem ser preenchidos e maiores que zero.";
            }
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
    <title>Lumiener - Residencial</title>
    <link rel="stylesheet" href="styleConteudo.css" />
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.11/jquery.mask.min.js"></script>

    <style>
        /* Media query para dispositivos móveis */
        @media (max-width: 768px) {
            .calculator-container {
                max-height: 750px;
                overflow-y: auto;
                padding-right: 10px;
                /* Padding to avoid content hiding behind the scrollbar */
            }

            .calculator-container:-webkit-scrollbar {
                width: 10px;
            }

            .calculator-container::-webkit-scrollbar-track {
                background: #f1f1f1;
            }

            .calculator-container::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 5px;
            }

            .calculator-container::-webkit-scrollbar-thumb:hover {
                background: #555;
            }

            body.dark-theme .calculator-container::-webkit-scrollbar-track {
                background: #333;
            }

            body.dark-theme .calculator-container::-webkit-scrollbar-thumb {
                background: #ffa600;
            }

            body.dark-theme .calculator-container::-webkit-scrollbar-thumb:hover {
                background: #ff8c00;
            }
        }

        /* Estilos CSS para o input range */
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

        html,
        body {
            height: 100%;
            overflow-x: hidden;
        }

        .aviso {
            color: #808080 !important;
            /* Define a cor para cinza */
            font-weight: bold !important;
            /* Torna o texto negrito */
            font-size: 0.8em !important;
            /* Define o tamanho do texto para ser menor que o padrão */
        }

        .mensagem-limitada {
            border: 3px solid #ccc;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            padding: 10px;
            margin-left: auto;
            margin-right: auto;
            width: 100%;
            height: 100%;
            max-width: 500px;
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
        <!-- Div da Calculadora Solar -->
        <?php if (isset($msgSimulacao)): ?>
        <div class="alert alert-success mensagem-limitada"><?php echo $msgSimulacao; ?></div>
        <?php elseif (isset($erroCalculo)): ?>
        <div class="alert alert-warning mensagem-limitada"><?php echo $erroCalculo; ?></div>
        <?php endif; ?>
        <div class="calculator-container">
            <h2 class="calculator-title">Simulador de Investimento em Painéis Solares</h2>
            <form method="POST" action="">
                <div style="display: flex; align-items: center;">
                    <img src="images/Solzinho.png" alt="Logo" style="width: 15%; height: 15%;">
                    <h5>Incidência Solar Diária Média em sua Região: <?php echo number_format($solarInsolation, 2); ?>
                        kWh/m²</h5>
                </div>
                <div class="calculator-input">
                    <label for="valor-conta">Valor da sua Conta de Luz:</label>
                    <div>
                        <input type="range" min="0" max="3000" id="valor-conta" name="valor-conta" value="500" step="1"
                            oninput="updateTextInput(this.value);" />
                        <output id="rangeValue">R$ 500,00</output>
                    </div>
                </div>
                <div class="calculator-input">
                    <label for="mes-ano">Qual foi o mês/ano desse consumo?</label>
                    <input type="month" id="mes-ano" name="mes-ano">
                </div>
                <div class="calculator-input">
                    <label for="valor-tarifa">Ajuste a Tarifa por kWh para esse mês:</label>
                    <input type="range" id="valor-tarifa" name="valor-tarifa" min="0.00" max="5.00" value="0.90"
                        step="0.01" oninput="updateTarifaDisplay(this.value);">
                    <output id="tarifa-display">R$ 0,90</output>
                </div>
                <div class="calculator-input">
                    <label>Escolha a potência do painel solar desejado (Watt-pico):</label>
                    <select name="potencia-placa">
                        <option value="280">280W</option>
                        <option value="340">340W</option>
                        <option value="460">460W</option>
                        <option value="560">560W</option>
                    </select>
                </div>
                <h5 class="aviso">*Todos os valores são aproximados e sujeitos a uma margem de erro.*</h5>
                <button type="submit" class="btn-simulacao">Calcular</button>
            </form>
        </div>
    </section>
    <script>
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

            function formatCurrency(value) {
                return parseFloat(value).toLocaleString('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                });
            }

            window.updateTextInput = function (value) {
                document.getElementById('rangeValue').value = formatCurrency(value);
            }

            window.updateTarifaDisplay = function (value) {
                document.getElementById('tarifa-display').value = formatCurrency(value);
            }

            // Initial formatting
            updateTextInput(document.getElementById('valor-conta').value);
            updateTarifaDisplay(document.getElementById('valor-tarifa').value);
        });

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
<footer class="footer">
    Copyright &copy; <?php echo date('Y'); ?> <a href="lading-page.html">Lumiener</a>. All rights reserved.
</footer>

</html>