<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grafico";

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($usuario) || empty($senha)) {
        exibirModal("Preencha todos os campos");
        exit;
    }

    // Conecta ao banco
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        exibirModal("Erro de conexão com o banco de dados");
        exit;
    }

    // Busca apenas pelo nome
    $stmt = $conn->prepare("SELECT * FROM admin WHERE nome = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
       

        // Verifica a senha usando password_verify
        if (password_verify($senha, $row['senha'])) {
            session_start();
            $_SESSION['admin'] = true;
            header("Location: CRUD.php");
            exit;
        }else{
            exibirModal("Usuário ou senha incorretos");
             $conn->close();
        }
    }
    
}

function exibirModal($mensagem) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Erro</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="modal fade show" style="display:block;" aria-modal="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white"><h5 class="modal-title">Erro</h5></div>
      <div class="modal-body">$mensagem</div>
      <div class="modal-footer"><a href="login.php" class="btn btn-secondary">Fechar</a></div>
    </div>
  </div>
</div>
<script>setTimeout(() => { window.location.href = 'login.php'; }, 3000);</script>
</body>
</html>
HTML;
}
?>

<!-- HTML da página de login -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
  body {      
     font-family: Arial, sans-serif;
      background: #f8f9fa;
        width: 100%;
        height: 100%;
        --color: rgba(114, 114, 114, 0.3);
        background-color: #191a1a;
        background-image: linear-gradient(0deg, transparent 24%, var(--color) 25%, var(--color) 26%, transparent 27%,transparent 74%, var(--color) 75%, var(--color) 76%, transparent 77%,transparent),
            linear-gradient(90deg, transparent 24%, var(--color) 25%, var(--color) 26%, transparent 27%,transparent 74%, var(--color) 75%, var(--color) 76%, transparent 77%,transparent);
        background-size: 55px 55px;}

        h2.login-admin {
  color: #ffffff;
}

  </style>
</head>
<body class="">
<div class="container mt-5">
<h2 class="text-center mb-4 login-admin">Login do Administrador</h2>

  <form method="POST" class="card p-4 mx-auto" style="max-width: 400px;">
    <div class="mb-3">
      <label class="form-label">Usuário</label>
      <input type="text" name="usuario" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Senha</label>
      <input type="password" name="senha" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Entrar</button>
  </form>
</div>
</body>
</html>
