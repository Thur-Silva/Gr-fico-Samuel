<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Senai - Login</title>
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/css/bootstrap.min.css" 
    rel="stylesheet"
  />
  <style>
    body {
      font-family: Arial, sans-serif;
      width: 100%; height: 100%;
      --color: rgba(114, 114, 114, 0.3);
      background-color: #191a1a;
      background-image:
        linear-gradient(0deg, transparent 24%, var(--color) 25%, var(--color) 26%, transparent 27%,transparent 74%, var(--color) 75%, var(--color) 76%, transparent 77%,transparent),
        linear-gradient(90deg, transparent 24%, var(--color) 25%, var(--color) 26%, transparent 27%,transparent 74%, var(--color) 75%, var(--color) 76%, transparent 77%,transparent);
      background-size: 55px 55px;
    }
  </style>
</head>
<body class="d-flex align-items-center justify-content-center vh-100">

  <div class="card shadow p-4" style="width:100%; max-width:400px;">
    <h3 class="text-center mb-4">Login - Senai</h3>
    <form id="loginForm" action="login.php" method="post">
      <div class="mb-3">
        <label for="usuario" class="form-label">Usuário</label>
        <input id="usuario" name="usuario" class="form-control" />
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input id="senha" name="senha" type="password" class="form-control" />
      </div>
      <div class="d-grid mb-2">
        <button type="button" class="btn btn-primary" onclick="logar()">
          Logar
        </button>
      </div>
    </form>
    <div class="d-grid">
      <a href="loginAdmin.php" class="btn btn-outline-secondary">
        Login Administrador
      </a>
    </div>
  </div>

  <!-- Modal de erro -->
  <div class="modal fade" id="erroModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Erro</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Por favor, preencha o usuário e a senha.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fechar
        </button>
      </div>
    </div></div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // 1) formata a data para o padrão MySQL (YYYY-MM-DD HH:mm:ss)
    function formatarDataMySQL() {
      const data = new Date();
      const ano      = data.getFullYear();
      const mes      = String(data.getMonth() + 1).padStart(2, '0');
      const dia      = String(data.getDate()).padStart(2, '0');
      const horas    = String(data.getHours()).padStart(2, '0');
      const minutos  = String(data.getMinutes()).padStart(2, '0');
      const segundos = String(data.getSeconds()).padStart(2, '0');
      const dataFormatada = `${ano}-${mes}-${dia} ${horas}:${minutos}:${segundos}`;
      console.log('Data formatada para MySQL:', dataFormatada);
      return dataFormatada;
    }

    // 2) função que registra no backend e retorna uma Promise
    function registrarAcao(user, actionType) {
      const nome   = user;
      const action = actionType;
      const time   = formatarDataMySQL();

      return fetch('http://localhost:3000/dados', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nome, action, time })
      })
      .then(res => {
        if (!res.ok) throw new Error('Status ' + res.status);
        return res.json();
      })
      .then(data => {
        console.log('Ação registrada:', data);
        return data;
      });
    }

    // 3) disparada ao clicar em “Logar”
    function logar() {
      const usuario = document.getElementById('usuario').value.trim();
      const senha   = document.getElementById('senha').value.trim();

      if (!usuario || !senha) {
        new bootstrap.Modal(document.getElementById('erroModal')).show();
        return;
      }

      registrarAcao(usuario, 'Efetuou login básico')
        .then(() => {
          document.getElementById('loginForm').submit();
        })
        .catch(err => {
          console.error('Falha ao registrar ação:', err);
          document.getElementById('loginForm').submit();
        });
    }
  </script>
</body>
</html>
