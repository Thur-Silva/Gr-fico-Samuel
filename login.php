<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grafico";

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

// Verificação de campos vazios
if (empty($usuario) || empty($senha)) {
    $mensagem = "Preencha todos os campos";
    exibirModal($mensagem);
    exit;
}

// Conexão com o banco
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $mensagem = "Erro de conexão com o banco de dados";
    exibirModal($mensagem);
    exit;
}

// Corrigido: campo correto agora é 'nome'
$sql = "SELECT * FROM usuarios WHERE nome = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();

    // Se estiver usando senha sem hash, troque para: if ($senha === $row['senha'])
    if (password_verify($senha, $row['senha'])) {
        session_start();
        $_SESSION['usuario'] = $usuario;
        header("Location: principal.php");
        exit;
    } else {
        $mensagem = "Senha incorreta";
        exibirModal($mensagem);
    }
} else {
    $mensagem = "Usuário não encontrado";
    exibirModal($mensagem);
}

$conn->close();

// Função para exibir modal de erro
function exibirModal($mensagem) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Erro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <!-- Modal -->
  <div class="modal fade show" id="erroModal" tabindex="-1" style="display: block;" aria-labelledby="erroModalLabel" aria-modal="true" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="erroModalLabel">Erro</h5>
        </div>
        <div class="modal-body">
          $mensagem
        </div>
        <div class="modal-footer">
          <a href="index.php" class="btn btn-secondary">Fechar</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    setTimeout(() => {
      window.location.href = 'index.php';
    }, 4000);
  </script>
</body>
</html>
HTML;
}
?>
