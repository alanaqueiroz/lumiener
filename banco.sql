CREATE DATABASE banco;

USE banco;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(100) NOT NULL,
  senha VARCHAR(200) NOT NULL,
  cep VARCHAR (9) NOT NULL,
  localizacao VARCHAR(200) NOT NULL
);

CREATE TABLE simulacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL,
  data_simulacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

CREATE TABLE resultados (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_simulacao INT NOT NULL,
  valor_conta DECIMAL(10, 2) NOT NULL,
  tarifa DECIMAL(5, 2) NOT NULL,
  potencia_placa INT NOT NULL,
  consumo_mensal_kwh DECIMAL(10, 2) NOT NULL,
  placas_necessarias INT NOT NULL,
  economia_anual DECIMAL(10, 2) NOT NULL,
  FOREIGN KEY (id_simulacao) REFERENCES simulacoes(id)
);


drop database banco;
drop table resultados;
drop table simulacoes;
select * from usuarios;
select * from resultados;
select * from simulacoes;