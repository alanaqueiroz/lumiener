<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
  echo 'error';
  exit;
}

if (isset($_GET['id'])) {
  $id_simulacao = intval($_GET['id']);
  $id_usuario = $_SESSION['id_usuario'];

  $host = 'localhost';
  $banco = 'banco';
  $usuario = 'root';
  $senhaBanco = '';

  $conexao = new mysqli($host, $usuario, $senhaBanco, $banco);

  if ($conexao->connect_error) {
    echo 'error';
    exit;
  }

  // Verificar se a simulação pertence ao usuário
  $stmt = $conexao->prepare("SELECT id FROM simulacoes WHERE id = ? AND id_usuario = ?");
  $stmt->bind_param("ii", $id_simulacao, $id_usuario);
  $stmt->execute();
  $stmt->store_result();

  if ($stmt->num_rows > 0) {
    // Excluir resultados primeiro
    $stmt = $conexao->prepare("DELETE FROM resultados WHERE id_simulacao = ?");
    $stmt->bind_param("i", $id_simulacao);
    $stmt->execute();

    // Excluir simulação
    $stmt = $conexao->prepare("DELETE FROM simulacoes WHERE id = ?");
    $stmt->bind_param("i", $id_simulacao);
    $stmt->execute();

    echo 'success';
  } else {
    echo 'error';
  }

  $stmt->close();
  $conexao->close();
} else {
  echo 'error';
}
?>
