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

// Verifica se há uma busca por nome
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : "";
if ($busca !== "") {
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE nome LIKE ?");
    $param = "%$busca%";
    $stmt->bind_param("s", $param);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM usuarios");
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>CRUD de Usuários</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #191a1a;
      background-image: 
        linear-gradient(0deg, transparent 24%, rgba(114,114,114,0.3) 25%, rgba(114,114,114,0.3) 26%, transparent 27%,transparent 74%, rgba(114,114,114,0.3) 75%, rgba(114,114,114,0.3) 76%, transparent 77%,transparent),
        linear-gradient(90deg, transparent 24%, rgba(114,114,114,0.3) 25%, rgba(114,114,114,0.3) 26%, transparent 27%,transparent 74%, rgba(114,114,114,0.3) 75%, rgba(114,114,114,0.3) 76%, transparent 77%,transparent);
      background-size: 55px 55px;
    }
    h2.label-titulo { color: #ffffff; }

    /* --- Seu CSS do componente de busca: --- */
    .grid {
      height: 800px;
      width: 800px;
      background-image: linear-gradient(to right, #0f0f10 1px, transparent 1px),
                        linear-gradient(to bottom, #0f0f10 1px, transparent 1px);
      background-size: 1rem 1rem;
      background-position: center center;
      position: absolute;
      z-index: -1;
      filter: blur(1px);
    }
    .white, .border, .darkBorderBg, .glow {
      max-height: 70px;
      max-width: 314px;
      height: 100%;
      width: 100%;
      position: absolute;
      overflow: hidden;
      z-index: -1;
      border-radius: 12px;
      filter: blur(3px);
    }
    .input {
      background-color: #010201;
      border: none;
      width: 301px;
      height: 56px;
      border-radius: 10px;
      color: white;
      padding-inline: 59px;
      font-size: 18px;
    }
    .input::placeholder { color: #c0b9c0; }
    .input:focus { outline: none; }

    #poda { display: flex; align-items: center; justify-content: center; }
    #main-inner { position: relative; }
    #input-mask {
      pointer-events: none;
      width: 100px; height: 20px;
      position: absolute;
      background: linear-gradient(90deg, transparent, black);
      top: 18px; left: 70px;
    }
    #pink-mask {
      pointer-events: none;
      width: 30px; height: 20px;
      position: absolute;
      background: #cf30aa;
      top: 10px; left: 5px;
      filter: blur(20px);
      opacity: 0.8;
      transition: all 2s;
    }
    #poda:hover > #pink-mask { opacity: 0; }
    .white::before, .border::before, .darkBorderBg::before, .glow::before {
      content: "";
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%);
      width: 600px; height: 600px;
      background-repeat: no-repeat;
      background-position: 0 0;
      transition: all 2s;
      z-index: -2;
    }
    /* ajustei apenas o essencial dos seus conic-gradients para funcionar */
    .white::before {
      background-image: conic-gradient(rgba(0,0,0,0) 0%, #a099d8, rgba(0,0,0,0) 8%, rgba(0,0,0,0) 50%, #dfa2da, rgba(0,0,0,0) 58%);
      filter: brightness(1.4);
      transform: translate(-50%, -50%) rotate(83deg);
    }
    .border::before {
      background-image: conic-gradient(#1c191c, #402fb5 5%, #1c191c 14%, #1c191c 50%, #cf30aa 60%, #1c191c 64%);
      filter: brightness(1.3);
      transform: translate(-50%, -50%) rotate(70deg);
    }
    .darkBorderBg::before {
      background-image: conic-gradient(rgba(0,0,0,0), #18116a, rgba(0,0,0,0) 10%, rgba(0,0,0,0) 50%, #6e1b60, rgba(0,0,0,0) 60%);
      transform: translate(-50%, -50%) rotate(82deg);
    }
    .glow::before {
      background-image: conic-gradient(#000, #402fb5 5%, #000 38%, #000 50%, #cf30aa 60%, #000 87%);
      filter: blur(30px);
      opacity: 0.4;
      transform: translate(-50%, -50%) rotate(60deg);
    }
    #poda:hover .white::before   { transform: translate(-50%, -50%) rotate(-97deg); }
    #poda:hover .border::before  { transform: translate(-50%, -50%) rotate(-110deg); }
    #poda:hover .darkBorderBg::before { transform: translate(-50%, -50%) rotate(-98deg); }
    #poda:hover .glow::before    { transform: translate(-50%, -50%) rotate(-120deg); }

    #filter-icon {
      position: absolute;
      top: 7px; right: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      isolation: isolate;
      overflow: hidden;
      border-radius: 10px;
      background: linear-gradient(180deg, #161329, black, #1d1b4b);
      border: 1px solid transparent;
      width: 40px; height: 42px;
      padding: 0; margin: 0;
    }
    .filterBorder {
      position: absolute;
      top: 7px; right: 7px;
      height: 42px; width: 40px;
      overflow: hidden;
      border-radius: 10px;
    }
    .filterBorder::before {
      content: "";
      position: absolute;
      top: 50%; left: 50%;
      transform: translate(-50%, -50%) rotate(90deg);
      width: 600px; height: 600px;
      background-image: conic-gradient(rgba(0,0,0,0), #3d3a4f, rgba(0,0,0,0) 50%, rgba(0,0,0,0) 50%, #3d3a4f, rgba(0,0,0,0) 100%);
      background-repeat: no-repeat;
      background-position: 0 0;
      filter: brightness(1.35);
      animation: rotate 4s linear infinite;
      z-index: -2;
    }

    .home-container {
      position: relative;
      margin-right: 20px;
    }
    .home-button {
      background-color: #010201;
      border: none;
      width: 100px;
      height: 56px;
      border-radius: 10px;
      color: white;
      font-size: 18px;
      text-decoration: none;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
      overflow: hidden;
    }
    .home-button::before {
      content: "";
      position: absolute;
      inset: 0;
      border-radius: 10px;
      padding: 2px;
      background: linear-gradient(45deg, #402fb5, #cf30aa);
      -webkit-mask: 
         linear-gradient(#000 0 0) content-box, 
         linear-gradient(#000 0 0);
      mask: 
         linear-gradient(#000 0 0) content-box, 
         linear-gradient(#000 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
    }
    @keyframes rotate { 100% { transform: translate(-50%, -50%) rotate(450deg); } }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div class="d-flex align-items-center">
        <!-- Botão Home -->
        <div class="home-container">
          <a href="index.php" class="home-button">Home</a>
    </div>
    <h2 class="label-titulo">Gerenciamento de Usuários</h2>
    <!-- Campo de busca estilizado -->
    <form method="GET" style="display:flex; align-items:center; position:relative;">
      <div class="grid"></div>
      <div id="poda">
        <div class="glow"></div>
        <div class="darkBorderBg"></div>
        <div class="darkBorderBg"></div>
        <div class="darkBorderBg"></div>
        <div class="white"></div>
        <div class="border"></div>
        <div id="main-inner">
          <input placeholder="Buscar" type="text" name="busca" class="input" value="<?= htmlspecialchars($busca) ?>" />
          <div id="input-mask"></div>
          <div id="pink-mask"></div>
          <div class="filterBorder"></div>
          <button type="submit" id="filter-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" height="24" fill="none" class="feather feather-search">
              <circle r="8" cy="11" cx="11" stroke="url(#search)"></circle>
              <line y2="16.65" y1="22" x2="16.65" x1="22" stroke="url(#searchl)"></line>
              <defs>
                <linearGradient gradientTransform="rotate(50)" id="search">
                  <stop stop-color="#f8e7f8" offset="0%"></stop>
                  <stop stop-color="#b6a9b7" offset="50%"></stop>
                </linearGradient>
                <linearGradient id="searchl">
                  <stop stop-color="#b6a9b7" offset="0%"></stop>
                  <stop stop-color="#837484" offset="50%"></stop>
                </linearGradient>
              </defs>
            </svg>
          </button>
        </div>
      </div>
    </form>
  </div>

  <?php if ($msg): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>

  <!-- Resto do seu CRUD (formulário de adicionar, tabela, etc.) continua inalterado -->

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
