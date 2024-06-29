CREATE DATABASE banco;

USE banco;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  senha VARCHAR(200) NOT NULL,
  cep VARCHAR(9) NOT NULL,
  localizacao VARCHAR(200) NOT NULL
);

CREATE TABLE simulacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  data_simulacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  mes INT NOT NULL,
  ano INT NOT NULL,
  tipo VARCHAR(20) NOT NULL, -- Novo campo para tipo (residencial ou empresarial)
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE resultados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_simulacao INT NOT NULL,
  valor_conta DECIMAL(10, 2) NOT NULL,
  potencia_placa INT NOT NULL,
  consumo_mensal_kwh FLOAT NOT NULL,
  placas_necessarias INT NOT NULL,
  economia_anual DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id)
);

CREATE TABLE tarifas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_simulacao INT NOT NULL,
  tarifa DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id)
);

CREATE TABLE incidencia_solar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  latitude DECIMAL(10, 8) NOT NULL,
  longitude DECIMAL(11, 8) NOT NULL,
  incidencia DECIMAL(5, 2) NOT NULL,
  data_obtida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE(latitude, longitude)
);




