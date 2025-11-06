<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Visor do Cadeado</title>
    <style>
        /* ... (Todo o seu CSS anterior) ... */
        body, html {
            margin: 0; padding: 0; width: 100%; height: 100%;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            box-sizing: border-box;
        }
        body {
            display: grid; place-items: center; background: #1e1e1e; color: #f0f0f0;
        }
        #cadeado-painel {
            width: 360px; background: linear-gradient(145deg, #3a3a3a, #2c2c2c);
            border-radius: 20px; padding: 30px;
            box-shadow: 10px 10px 30px rgba(0, 0, 0, 0.5), -10px -10px 30px rgba(65, 65, 65, 0.1);
            border: 1px solid #444; transition: all 0.3s ease;
        }
        .padlock-container { width: 120px; height: 120px; margin: 0 auto 25px auto; position: relative; }
        .padlock-svg {
            width: 100%; height: 100%; position: absolute; top: 0; left: 0;
            stroke: #e0e0e0; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round;
            fill: none; transition: opacity 0.3s ease, transform 0.4s ease;
        }
        #cadeado-painel.state-locked #svg-unlocked,
        #cadeado-painel.state-typing #svg-unlocked,
        #cadeado-painel.state-error #svg-unlocked { opacity: 0; }
        #cadeado-painel.state-locked #svg-locked,
        #cadeado-painel.state-typing #svg-locked,
        #cadeado-painel.state-error #svg-locked { opacity: 1; }
        #cadeado-painel.state-unlocked #svg-locked { opacity: 0; transform: scale(0.9); }
        #cadeado-painel.state-unlocked #svg-unlocked { opacity: 1; transform: rotate(-15deg) scale(1.1); }
        @keyframes jiggle {
            0% { transform: translateX(0); } 20% { transform: translateX(-4px) rotate(-3deg); }
            40% { transform: translateX(4px) rotate(3deg); } 60% { transform: translateX(-4px) rotate(-3deg); }
            80% { transform: translateX(4px) rotate(3deg); } 100% { transform: translateX(0); }
        }
        #cadeado-painel.state-error .padlock-container { animation: jiggle 0.35s ease-in-out; }
        #visor-texto {
            background: #222; border: 2px solid #111; padding: 15px 20px;
            font-family: 'Courier New', Courier, monospace; font-size: 24px; font-weight: bold;
            text-align: center; color: #20c20e; border-radius: 8px;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.7); overflow: hidden; white-space: nowrap;
            letter-spacing: 2px; height: 30px; line-height: 30px; margin-bottom: 25px;
        }
        #status-luz {
            width: 20px; height: 20px; margin: 0 auto; border-radius: 50%;
            background: #444; border: 2px solid #111; transition: all 0.3s ease;
        }
        #cadeado-painel.state-locked #status-luz { background: #444; box-shadow: none; }
        #cadeado-painel.state-typing #status-luz { background: #f39c12; box-shadow: 0 0 15px #f39c12; }
        #cadeado-painel.state-error #status-luz { background: #e74c3c; box-shadow: 0 0 15px #e74c3c; }
        #cadeado-painel.state-unlocked #status-luz { background: #2ecc71; box-shadow: 0 0 15px #2ecc71; }

        /* --- NOVO: Estilo do Botão de Som --- */
        #sound-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 100px;
            padding: 10px;
            font-size: 14px;
            font-weight: bold;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 3px 10px rgba(0,0,0,0.3);
            border: 1px solid #000;
        }

        #sound-toggle.state-off {
            background-color: #e74c3c; /* Vermelho */
        }
        #sound-toggle.state-off:hover {
            background-color: #c0392b;
        }
        #sound-toggle.state-on {
            background-color: #2ecc71; /* Verde */
        }
        #sound-toggle.state-on:hover {
            background-color: #27ae60;
        }

    </style>
</head>
<body>

    <button id="sound-toggle" class="state-off">Sound: OFF</button>

    <div id="cadeado-painel" class="cadeado-painel state-locked">
        <div class="padlock-container">
            <svg id="svg-locked" class="padlock-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            <svg id="svg-unlocked" class="padlock-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 9.9-1"></path></svg>
        </div>
        <div id="visor-texto">Iniciando...</div>
        <div id="status-luz"></div>
    </div>

    <script>
        // Pega os elementos principais
        const painel = document.getElementById('cadeado-painel');
        const visor = document.getElementById('visor-texto');
        const soundToggle = document.getElementById('sound-toggle'); // NOVO

        // Pré-carregamento dos arquivos de áudio
        const somSucesso = new Audio('sounds/success.mp3');
        const somErro = new Audio('sounds/error.mp3');
        const somBip = new Audio('sounds/blip.mp3');

        // Variáveis de controle de estado
        let ultimoStatus = '';
        let isSoundEnabled = false;   // NOVO: Controla se o som deve tocar
        let isAudioUnlocked = false;  // NOVO: Controla se o áudio foi "acordado"

        // --- NOVO: Lógica do botão de Mudo ---
        soundToggle.addEventListener('click', () => {
            isSoundEnabled = !isSoundEnabled; // Inverte o estado

            if (isSoundEnabled) {
                // Atualiza o botão
                soundToggle.textContent = 'Sound: ON';
                soundToggle.classList.remove('state-off');
                soundToggle.classList.add('state-on');

                // *** TRUQUE DE AUTOPLAY ***
                // Se for o PRIMEIRO clique, "acorda" o áudio
                if (!isAudioUnlocked) {
                    console.log('Contexto de áudio desbloqueado pelo usuário.');
                    somBip.play().catch(e => console.warn("Áudio 'play' inicial bloqueado, mas a interação deve ter resolvido."));
                    somBip.pause();
                    somBip.currentTime = 0;
                    isAudioUnlocked = true; // Só precisa fazer isso uma vez
                }
            } else {
                // Atualiza o botão
                soundToggle.textContent = 'Sound: OFF';
                soundToggle.classList.remove('state-on');
                soundToggle.classList.add('state-off');
            }
        });


        // Função assíncrona para buscar o status
        async function fetchStatus() {
            try {
                const response = await fetch('get_status.php?' + new Date().getTime());
                const status = await response.text();
                
                if (status === ultimoStatus) {
                    return; 
                }
                ultimoStatus = status; 
                
                visor.textContent = status;

                // Lógica de Interface
                if (!painel.classList.contains('state-error')) {
                    painel.classList.remove('state-unlocked', 'state-typing', 'state-locked');
                }

                switch (true) {
                    case status.startsWith('Codigo Correto'):
                        painel.classList.add('state-unlocked');
                        if (isSoundEnabled) somSucesso.play(); // NOVO: Verifica se o som está ligado
                        break;
                    
                    case status.startsWith('Codigo Errado'):
                        painel.classList.add('state-error');
                        if (isSoundEnabled) somErro.play(); // NOVO: Verifica se o som está ligado
                        
                        setTimeout(() => {
                            painel.classList.remove('state-error');
                            painel.classList.add('state-locked');
                        }, 500); 
                        break;
                    
                    case status.startsWith('*'):
                        painel.classList.add('state-typing');
                        if (isSoundEnabled) somBip.play(); // NOVO: Verifica se o som está ligado
                        break;
                    
                    default: 
                        painel.classList.add('state-locked');
                }

            } catch (error) {
                console.error('Erro ao buscar status:', error);
                visor.textContent = 'Desconectado';
                painel.className = 'cadeado-painel state-error';
                if (isSoundEnabled) somErro.play(); // NOVO: Verifica se o som está ligado
                ultimoStatus = 'Desconectado';
            }
        }
        
        // --- Inicia o loop imediatamente ao carregar a página ---
        fetchStatus();
        setInterval(fetchStatus, 150);
        
    </script>
</body>
</html>