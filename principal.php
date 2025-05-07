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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSocket ON/OFF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
</head>
<body class="d-flex justify-content-center align-items-start min-vh-100 bg-light">

    <div class="card p-4 shadow-lg text-center" style="width: 90%; max-width: 500px;">
        <h2 class="mb-3">Controle WebSocket - IoT - Senai</h2>

        <div class="input-group mb-3">
            <canvas id="GraficoTeste" width="300" height="300"></canvas>
        </div>

        <div class="d-flex justify-content-around">
            <button id="botao1" class="btn btn-danger btn-lg" onclick="alternarEstado(this)">OFF</button>
            <button id="botao2" class="btn btn-danger btn-lg" onclick="alternarEstado(this)">OFF</button>
        </div>
    </div>

    <script>
        let ultimoDado = null;
        const socket = new WebSocket('ws://192.168.30.222:8080');

        socket.onopen = () => console.log("Conectado ao WebSocket");
        socket.onerror = error => console.error("Erro no WebSocket", error);

        socket.onmessage = function(event) {
            try {
                const dados = JSON.parse(event.data);
                if ('canal_analogico' in dados){
                    ultimoDado = dados.canal_analogico;
                }
            } catch (e) {
                console.error("Erro ao processar JSON:", e);
            }
        };

        function alternarEstado(botao) {
            const estadoAtual = botao.textContent;
            const novoEstado = estadoAtual === "ON" ? "OFF" : "ON";
            botao.textContent = novoEstado;
            botao.classList.toggle("btn-primary", novoEstado === "ON");
            botao.classList.toggle("btn-danger", novoEstado === "OFF");

            if (socket.readyState === WebSocket.OPEN) {
                socket.send(`Botao-${botao.id}: ${novoEstado}`);
            }
        }

        const ctx = document.getElementById('GraficoTeste').getContext('2d');
        const dados_grafico = {
            labels: [],
            datasets: [{
                label: 'Valores Aleatórios',
                data: [],
                borderColor: 'blue',
                fill: false
            }]
        };

        const configuracao = {
            type: 'line',
            data: dados_grafico,
            options: {
                responsive: true,
                animation: false,
                scales: {
                    x: { title: { display: true, text: 'Tempo' }},
                    y: { suggestedMin: 0, suggestedMax: 100 }
                }
            }
        };

        const Grafico = new Chart(ctx, configuracao);

        function incrementaValores() {
            const HoraAgora = new Date().toLocaleTimeString();
            if (dados_grafico.labels.length > 20) {
                dados_grafico.labels.shift();
                dados_grafico.datasets[0].data.shift();
            }
            dados_grafico.labels.push(HoraAgora);
            dados_grafico.datasets[0].data.push(ultimoDado);
            Grafico.update();
        }

        setInterval(incrementaValores, 250);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
