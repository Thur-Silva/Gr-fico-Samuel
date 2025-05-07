<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "grafico";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Erro de conexão: " . $conn->connect_error);

$msg = "";

// Ações: adicionar, editar, excluir
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['adicionar'])) {
        $nome = trim($_POST['nome']);
        $senha = trim($_POST['senha']);

        if ($nome && $senha) {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (nome, senha, data) VALUES (?, ?, NOW())");
            $stmt->bind_param("ss", $nome, $senhaHash);
            $stmt->execute();
            $msg = "Usuário adicionado com sucesso.";
        }
    } elseif (isset($_POST['editar'])) {
        $id = $_POST['id'];
        $nome = trim($_POST['nome']);
        $senha = trim($_POST['senha']);

        if ($id && $nome) {
            if ($senha !== "") {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE usuarios SET nome=?, senha=? WHERE id=?");
                $stmt->bind_param("ssi", $nome, $senhaHash, $id);
            } else {
                $stmt = $conn->prepare("UPDATE usuarios SET nome=? WHERE id=?");
                $stmt->bind_param("si", $nome, $id);
            }
            $stmt->execute();
            $msg = "Usuário atualizado com sucesso.";
        }
    } elseif (isset($_POST['excluir'])) {
        $id = $_POST['id'];
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $msg = "Usuário excluído com sucesso.";
        }
    }
}

$result = $conn->query("SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>CRUD de Usuários</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="mb-4">Gerenciamento de Usuários</h2>

  <?php if ($msg): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Formulário para adicionar -->
  <form method="POST" class="card p-3 mb-4 shadow-sm">
    <h5>Adicionar Novo Usuário</h5>
    <div class="row g-2">
      <div class="col-md-4"><input type="text" name="nome" class="form-control" placeholder="Nome" required></div>
      <div class="col-md-4"><input type="text" name="senha" class="form-control" placeholder="Senha" required></div>
      <div class="col-md-4"><button type="submit" name="adicionar" class="btn btn-success w-100">Adicionar</button></div>
    </div>
  </form>

  <!-- Tabela de usuários -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr><th>ID</th><th>Nome</th><th>Nova Senha</th><th>Data de Criação</th><th>Ações</th></tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <form method="POST">
            <td>
              <input type="hidden" name="id" value="<?= $row['id'] ?>">
              <?= $row['id'] ?>
            </td>
            <td><input type="text" name="nome" value="<?= htmlspecialchars($row['nome']) ?>" class="form-control"></td>
            <td><input type="text" name="senha" placeholder="Nova senha" class="form-control"></td>
            <td><?= $row['data'] ?></td>
            <td class="d-flex gap-2">
              <button type="submit" name="editar" class="btn btn-primary btn-sm">Salvar</button>
              <button type="submit" name="excluir" class="btn btn-danger btn-sm" onclick="return confirm('Excluir usuário?')">Excluir</button>
            </td>
          </form>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
