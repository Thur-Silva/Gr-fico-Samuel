<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WebSocket ON/OFF</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    :root {
      --dark-blue: #222;
      --action-color: #90feb5;
    }
    body {
      font-family: Arial, sans-serif;
      background: #f8f9fa;
      width: 100%;
      height: 100%;
      --color: rgba(114, 114, 114, 0.3);
      background-color: #191a1a;
      background-image:
        linear-gradient(0deg, transparent 24%, var(--color) 25%, var(--color) 26%, transparent 27%, transparent 74%, var(--color) 75%, var(--color) 76%, transparent 77%, transparent),
        linear-gradient(90deg, transparent 24%, var(--color) 25%, var(--color) 26%, transparent 27%, transparent 74%, var(--color) 75%, var(--color) 76%, transparent 77%, transparent);
      background-size: 55px 55px;
    }
    .card {
      background: #fff;
      border-radius: .5rem;
    }
    .goo { position:absolute; visibility:hidden; width:1px; height:1px; }

    .button {
      background-color: var(--dark-blue);
      border: none;
      color: #fff;
      font-family: 'Montserrat', sans-serif;
      font-size: 14px;
      font-weight: 100;
      letter-spacing: 1px;
      padding: 20px 40px;
      text-transform: uppercase;
      transition: all 0.1s ease-out;
      cursor: pointer;
      position: relative;
      z-index: 2;
    }
  
    /* ==== LANTERNA ==== */
    #lanterna {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      width: 30px;
      height: 80px;
      background: #333;
      border-radius: 15px 15px 5px 5px;
      box-shadow: inset -2px -4px 6px rgba(0,0,0,0.7);
      cursor: pointer;
      transition: background 0.2s;
      z-index: 10;
    }
    #lanterna::after {
      content: "";
      position: absolute;
      top: -10px;
      left: 50%;
      transform: translateX(-50%);
      width: 20px;
      height: 10px;
      background: #666;
      border-radius: 3px 3px 0 0;
    }
    #lanterna.off::before {
      content: "";
      display: none;
    }
    #lanterna.on::before {
      content: "";
      position: absolute;
      top: -300px;
      left: 50%;
      transform: translateX(-50%);
      width: 400px;
      height: 400px;
      background: radial-gradient(ellipse at top, rgba(255, 255, 0, 0.59) 0%, rgba(0,0,0,0) 70%);
      pointer-events: none;
    }
    #lanterna.on {
      background: #242424;
      
    }

    .gauge-container {
  position: relative;
  width: 100%;
  padding-bottom: 56%;
}
.gauge-container canvas {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
.gauge-legend {
  position: absolute;
  bottom: 10%;
  width: 100%;
  text-align: center;
  font-size: 1rem;
  color: #333;
  font-weight: bold;
}

  </style>
</head>
<body class="d-flex justify-content-center align-items-start min-vh-100">

  <svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="goo">
    <defs>
      <filter id="goo">
        <feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"/>
        <feColorMatrix in="blur" mode="matrix"
          values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 19 -9"
          result="goo"/>
        <feComposite in="SourceGraphic" in2="goo"/>
      </filter>
    </defs>
  </svg>

  <!-- Lanterna -->
  <div id="lanterna" class="lanterna off"></div>

  <div class="card p-4 shadow-lg text-center mt-5" style="width:90%; max-width:500px;">
    <h2 class="mb-3">Controle WebSocket - IoT - Senai</h2>

    <div class="input-group mb-3">
      <canvas id="GraficoTeste" width="300" height="300"></canvas>
    </div>

            <div class="input-group mb-3">
            <span class="input-group-text">Status Sensor</span>
            <input type="text" readonly id="txtStatusSensor" class="form-control" placeholder="Aguardando dados...">
        </div>

    <label for="button">Controle do LED físico</label>
    <button class="button" id="button" onclick="alternarEstado('button')">OFF</button>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.20.3/TweenMax.min.js"></script>
  <script>

    // WebSocket e gráfico originais
    let ultimoDado = null;
    let LampTipo = 'null';
    let LampStatus = 'null';
    let control = false;
    const socket = new WebSocket('ws://localhost:8888');
    socket.onopen = () => console.log("Conectado");
    socket.onerror = e => console.error(e);
    socket.onmessage = e => {
          try {
            const dados = JSON.parse(event.data);
            if ('Tipo' in dados){
              LampTipo = dados.Tipo;
              LampStatus = dados.Status;
              console.log("O estado da "+LampTipo+" é: "+ LampStatus + "   Horário: "+ new Date().toLocaleTimeString());
            }   
            //LAMPADA 1
            if(LampTipo ==="LAMP1" && LampStatus === "ON") {
              document.getElementById("lanterna").classList.remove("off");
              document.getElementById("lanterna").classList.add("on");

              registrarAcao("LAMP1","ON");
            }
             if(LampTipo ==="LAMP1" && LampStatus === "OFF") {
              document.getElementById("lanterna").classList.remove("on");
              document.getElementById("lanterna").classList.add("off");
              registrarAcao("LAMP1","OFF");
            }

            //LAMPADA 2
            if(LampTipo ==="LAMP2" && LampStatus === "ON" && !control) {
              document.getElementById("button").innerText = "ON";
              control = true;
              registrarAcao("LAMP2","ON");
            }
              if(LampTipo ==="LAMP2" && LampStatus === "OFF" && control) {
              document.getElementById("button").innerText = "OFF";
             control = false;
              registrarAcao("LAMP2","OFF");
            }


          } catch (error) {
            console.error(error);
          }
    };

  function alternarEstado(btn) {
    const botao = document.getElementById("button");
    const textoAtual = botao.innerText;
    let novoEstado;
    
    if (textoAtual === "ON") {
        botao.innerText = "OFF";
        novoEstado = "OFF";
    } else {
        botao.innerText = "ON";
        novoEstado = "ON";
    }
    
    // Prepara o payload em JSON
    const estadoBotao = {
        Tipo: "BUTTON",     
        Status: novoEstado     
    };
    
    if (socket.readyState === WebSocket.OPEN) {
        botao.innerText = novoEstado;
        socket.send(JSON.stringify(estadoBotao));
        console.log("Enviando estado de botão: "+JSON.stringify(estadoBotao));
    } else {
        console.error("WebSocket não está conectado");
    }
}

    const ctx = document.getElementById('GraficoTeste').getContext('2d');
    const dados = { labels: [], datasets: [{ label: '', data: [], borderColor: 'blue', fill: false }] };
    const cfg = {
      type: 'line', data: dados, options: {
        responsive: true, animation: false,
        scales: {
          x: { title: { display: true, text: 'Tempo' } },
          y: { suggestedMin: 0, suggestedMax: 100 }
        }
      }
    };
    const Grafico = new Chart(ctx, cfg);
    function incrementa() {
      const h = new Date().toLocaleTimeString();
      if (dados.labels.length > 20) {
        dados.labels.shift();
        dados.datasets[0].data.shift();
      }
      dados.labels.push(h);
      dados.datasets[0].data.push(ultimoDado);
      Grafico.update();
    }
    setInterval(incrementa, 250);

    // LANTERNA ON/OFF
    const lanterna = document.getElementById('lanterna');
    lanterna.addEventListener('click', () => {
      lanterna.classList.toggle('on');
      lanterna.classList.toggle('off');
    });

        function formatarDataMySQL() {
      const d = new Date();
      const ano      = d.getFullYear();
      const mes      = String(d.getMonth()+1).padStart(2,'0');
      const dia      = String(d.getDate()).padStart(2,'0');
      const hh       = String(d.getHours()).padStart(2,'0');
      const mm       = String(d.getMinutes()).padStart(2,'0');
      const ss       = String(d.getSeconds()).padStart(2,'0');
      return `${ano}-${mes}-${dia} ${hh}:${mm}:${ss}`;
    }

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
    
  </script>
</body>
</html>
