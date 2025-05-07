<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Senai - Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-SgOJa3DmI69IUzQ2PVdRZhwQ+dy64/BUtbMJw1MZ8t5HZApcHrRKUc4W0kG879m7" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js" integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous"></script>
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
    <h3 class="text-center mb-4">Login - Senai</h3>
    
    <form action="login.php" method="post">
      <div class="mb-3">
        <label for="usuario" class="form-label">Usuário</label>
        <input type="text" class="form-control" name="usuario" id="usuario" placeholder="Digite seu usuário">
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control" id="senha" placeholder="Digite sua senha">
      </div>
      <div class="d-grid mb-2">
        <button type="submit" class="btn btn-primary">Logar</button>
      </div>
    </form>

    <!-- Botão para login admin -->
    <div class="d-grid">
      <a href="loginAdmin.php" class="btn btn-outline-secondary">Login Administrador</a>
    </div>
  </div>

  <!-- Modal de erro -->
  <div class="modal fade" id="erroModal" tabindex="-1" aria-labelledby="erroModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="erroModalLabel">Erro</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          Por favor, preencha o usuário e a senha.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    function logar() {
      const usuario = document.getElementById('usuario').value.trim();
      const senha = document.getElementById('senha').value.trim();

      if (!usuario || !senha) {
        const erroModal = new bootstrap.Modal(document.getElementById('erroModal'));
        erroModal.show();
        return;
      }

      // Aqui você pode seguir com o login real
      console.log("Usuário:", usuario);
      console.log("Senha:", senha);
    }
  </script>

</body>
</html>
