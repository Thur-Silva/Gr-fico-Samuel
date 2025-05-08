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
    .button:hover { background-color: var(--action-color); }
    .button:active { transform: scale(0.95); }
    .button--bubble { background: none; padding: 0; }
    .button--bubble__container {
      position: relative;
      display: inline-block;
      margin: 0 0.5rem;
    }
    .button--bubble__effect-container {
      position: absolute;
      width: 200%; height: 400%;
      top: -150%; left: -130%;
      filter: url("#goo");
      transition: all 0.1s ease-out;
      pointer-events: none;
    }
    .button--bubble__effect-container .circle {
      position: absolute;
      width: 25px; height: 25px;
      border-radius: 15px;
      background: var(--dark-blue);
      transition: background 0.1s ease-out;
    }
    .circle.top-left { top: 40%; left: 27%; }
    .circle.bottom-right { bottom: 40%; right: 27%; }
    .button--bubble__effect-container .effect-button {
      position: absolute;
      width: 50%; height: 25%;
      top: 50%; left: 25%;
      z-index: 1;
      transform: translateY(-50%);
      background: var(--dark-blue);
      transition: background 0.1s ease-out;
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
      background: radial-gradient(ellipse at top, rgba(255,255,200,0.4) 0%, rgba(0,0,0,0) 70%);
      pointer-events: none;
    }
    #lanterna.on {
      background: #555;
      box-shadow: 0 0 8px rgba(255,255,200,0.6);
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

    <div class="d-flex justify-content-around">
      <span class="button--bubble__container">
        <button id="botao1" class="button button--bubble" onclick="alternarEstado(this)">OFF</button>
        <span class="button--bubble__effect-container">
          <span class="circle top-left"></span>
          <span class="circle top-left"></span>
          <span class="circle top-left"></span>
          <span class="button effect-button"></span>
          <span class="circle bottom-right"></span>
          <span class="circle bottom-right"></span>
          <span class="circle bottom-right"></span>
        </span>
      </span>
      <span class="button--bubble__container">
        <button id="botao2" class="button button--bubble" onclick="alternarEstado(this)">OFF</button>
        <span class="button--bubble__effect-container">
          <span class="circle top-left"></span>
          <span class="circle top-left"></span>
          <span class="circle top-left"></span>
          <span class="button effect-button"></span>
          <span class="circle bottom-right"></span>
          <span class="circle bottom-right"></span>
          <span class="circle bottom-right"></span>
        </span>
      </span>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/1.20.3/TweenMax.min.js"></script>
  <script>
    // animação bubble
    $('.button--bubble').each(function() {
      var $tl = $(this).parent().find('.circle.top-left'),
          $br = $(this).parent().find('.circle.bottom-right'),
          t1 = new TimelineLite(),
          t2 = new TimelineLite(),
          bt = new TimelineLite({ paused: true });

      t1.to($tl,1.2,{x:-25,y:-25,scaleY:2,ease:SlowMo.ease.config(0.1,0.7,false)})
        .to($tl.eq(0),.1,{scale:0.2,x:'+=6',y:'-=2'})
        .to($tl.eq(1),.1,{scaleX:1,scaleY:0.8,x:'-=10',y:'-=7'},'-=0.1')
        .to($tl.eq(2),.1,{scale:0.2,x:'-=15',y:'+=6'},'-=0.1')
        .to($tl,1,{scale:0,opacity:0,stagger:0.1});
      t2.to($br,1.1,{x:30,y:30,ease:SlowMo.ease.config(0.1,0.7,false)})
        .to($br.eq(0),.1,{scale:0.2,x:'-=6',y:'+=3'})
        .to($br.eq(1),.1,{scale:0.8,x:'+=7',y:'+=3'},'-=0.1')
        .to($br.eq(2),.1,{scale:0.2,x:'+=15',y:'-=6'},'-=0.2')
        .to($br,1,{scale:0,opacity:0,stagger:0.1});
      bt.add(t1)
        .to($(this).parent().find('.effect-button'),0.8,{scaleY:1.1},0.1)
        .add(t2,0.2)
        .to($(this).parent().find('.effect-button'),1.8,{scale:1,ease:Elastic.easeOut.config(1.2,0.4)},1.2)
        .timeScale(2.6);
      $(this).on('mouseover',()=>bt.restart());
    });

    // WebSocket e gráfico originais
    let ultimoDado = null;
    const socket = new WebSocket('ws://192.168.30.226:8080');
    socket.onopen = () => console.log("Conectado");
    socket.onerror = e => console.error(e);
    socket.onmessage = e => {
      try {
        const d = JSON.parse(e.data);
        if ('canal_analogico' in d) ultimoDado = d.canal_analogico;
      } catch {}
    };

    function alternarEstado(btn) {
      const novo = btn.textContent === 'ON' ? 'OFF' : 'ON';
      btn.textContent = novo;
      if (socket.readyState === WebSocket.OPEN)
        socket.send(`Botao-${btn.id}: ${novo}`);
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
  </script>
</body>
</html>
