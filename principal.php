<?php
// Verifica se o usuário está logado
session_start();

if (!isset($_SESSION['usuario'])) {
    // Se não estiver logado, redireciona para o login
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

        <button id="toggleButton" class="btn btn-primary btn-lg mb-3" onclick="toggle()">ON</button>

        <div class="input-group mb-3">
            <input type="text" id="messageInput" class="form-control" placeholder="Digite uma mensagem">
            <button class="btn btn-success" onclick="sendMessage()">Enviar</button>
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text">Status Sensor</span>
            <input type="text" readonly id="txtStatusSensor" class="form-control" placeholder="Aguardando dados...">
        </div>

        <div class="input-group mb-3">
            <span class="input-group-text">Contagem</span>
            <input type="text" readonly id="txtContagem" class="form-control" placeholder="Aguardando dados...">
        </div>

        <div class="input-group mb-3">
            <canvas id="GraficoTeste" width="300" height="300"></canvas>
        </div>
        
        <div id="serverMessages" class="border rounded p-3 bg-white" style="height: 200px; overflow-y: auto;">
            <p class="text-muted">Mensagens do servidor aparecerão aqui...</p>
        </div>

        <br>
          <!-- Botão de logoff -->
          <button id="logoutButton" class="btn btn-danger btn-lg mb-3" onclick="logout()">Logoff</button>

    </div>

    <script>

        function logout() {
            window.location.href = 'logout.php';
        }

        let ultimoDado = null;
        const socket = new WebSocket('ws://192.168.30.222:8080');
        let isOn = false;

        socket.onopen = function() {
            console.log("Conectado ao WebSocket");
        };

        socket.onerror = function(error) {
            console.error("Erro no WebSocket", error);
        };

        socket.onmessage = function(event) {
            console.log("Mensagem recebida:", event.data);

        try {

             const dados = JSON.parse(event.data);

             if ('status_sensor' in dados && 'contagem' in dados && 'canal_analogico' in dados){
                document.getElementById("txtStatusSensor").value = dados.status_sensor ? "Ativo" : "Inativo";
                document.getElementById("txtContagem").value = dados.contagem;
                ultimoDado = dados.canal_analogico;
                // Cores visuais para o status
                document.getElementById("txtStatusSensor").style.color = dados.status_sensor ? "green" : "red";
            } else {
                displayMessage("JSON inválido: campos esperados não encontrados.");
            }

        } catch (e) {
            console.error("Erro ao processar JSON:", e);
            displayMessage("Erro ao interpretar mensagem: " + event.data);
        }

           
          //  if(event.data != "True" && event.data != "False")
         //   displayMessage(event.data);
         //   inputTextMessage(event.data);
            
            /*if(event.data == "True" || event.data == "False")
            {
                inputTextMessage(event.data);

                const statusInput = document.getElementById("txtDadosServer");
            
                if (event.data == "True") {
                    
                    statusInput.style.color = "green";
                } else {
                    
                    statusInput.style.color = "red";
                }
            }
                */
        };

        function toggle() {
        
            isOn = !isOn;

            const message = isOn ? "ON" : "OFF"; // Quando isOn é true, a mensagem enviada é "ON"
            const button = document.getElementById("toggleButton");

            button.textContent = isOn ? "OFF" : "ON"; // O texto do botão mostra o estado contrário para indicar a ação a ser tomada
            button.classList.toggle("btn-primary", !isOn);
            button.classList.toggle("btn-danger", isOn);

            if (socket.readyState === WebSocket.OPEN) {
                socket.send(message);
            } else {
                 console.error("WebSocket não está conectado");
            }
        }

        function sendMessage() {
            const input = document.getElementById("messageInput");
            const message = input.value.trim();

            if (!message) {
                console.warn("Campo de mensagem está vazio!");
                return;
            }

            if (socket.readyState === WebSocket.OPEN) {
                socket.send(message);
                displayMessage("Você: " + message); // Mostra mensagem enviada pelo usuário
                input.value = ""; // Limpa o campo após enviar
            } else {
                console.error("WebSocket não está conectado");
            }
        }

        function inputTextMessage(message) {

            document.getElementById("txtDadosServer").value = message;
        }

        function displayMessage(message) {
            const messageContainer = document.getElementById("serverMessages");
            const newMessage = document.createElement("p");
            newMessage.textContent = message;
            newMessage.classList.add("mb-1");
            messageContainer.appendChild(newMessage);
            messageContainer.scrollTop = messageContainer.scrollHeight; // Rola para a última mensagem
        }
        
    </script>

    <script>

    const constGraph = document.getElementById('GraficoTeste').getContext('2d');

    const dados_grafico = {
        labels: [], // Tempo (x)
        datasets: [{
            label: 'Valores Aleatórios',
            data: [], // Valores (y)
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
            x: {
                title: { display: true, text: 'Tempo' }
            },
            y: {
                //title: { display: true, text: 'Valores Aleatórios' },
                suggestedMin: 0,
                suggestedMax: 100
            }
            }
        }
    };

    const Grafico = new Chart(constGraph, configuracao);

    function incrementaValores() {
        
        const HoraAgora = new Date().toLocaleTimeString();
       // const valor = Math.floor(Math.random() * 100);
   

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
