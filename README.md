# 🔒 Cofre Eletrônico com Visor Web em Tempo Real

Este é um projeto de IoT (Internet das Coisas) que combina hardware e software para criar um cofre eletrônico (feito com Arduino) monitorado e controlado por uma interface web em tempo real.

O projeto utiliza um teclado matricial para entrada de senha, um servo motor como tranca e um visor web (HTML/PHP/JS) que exibe o status do cofre (Trancado, Erro, Sucesso, Digitando...) instantaneamente.

## 📸 Demonstração

**(RECOMENDADO!)**
*Seria incrível se você gravasse um GIF curto da tela do navegador e do cofre físico funcionando lado a lado e colocasse aqui!*
`![GIF de Demonstração](link_para_seu_gif.gif)`

## 🚀 Funcionalidades Principais

* **Tranca Física:** Um servo motor atua como uma tranca que gira para liberar a tampa.
* **Entrada por Teclado:** Um teclado matricial 4x4 permite digitar e alterar a senha.
* **Visor Web em Tempo Real:** Uma página web moderna exibe o status atual do cofre.
* **Feedback Completo:** A interface web possui feedback visual (animações de cadeado, luzes de status) e sonoro (sucesso, erro, digitação).
* **Arquitetura Híbrida:** Utiliza uma ponte (bridge) em Python para conectar o hardware (Porta Serial) ao servidor web (PHP).

## ⚙️ Como Funciona (Arquitetura)

Este projeto funciona em três partes que se comunicam em cadeia:

1.  **Hardware (Arduino):**
    * Lê o teclado matricial.
    * Compara a senha digitada.
    * Controla o servo motor (a tranca).
    * **Envia todos os status** (ex: `VISOR:Codigo Correto`, `VISOR:***`) pela porta Serial (USB).

2.  **A "Ponte" (Python):**
    * Um script (`bridge.py`) fica rodando no computador.
    * Ele lê constantemente a porta Serial do Arduino.
    * Quando recebe um status (algo que começa com `VISOR:`), ele escreve essa mensagem no arquivo `status.txt`.

3.  **Visor Web (PHP + JavaScript):**
    * Um servidor (XAMPP) hospeda a página `index.php`.
    * O JavaScript na página pede ao `get_status.php` o status mais recente (a cada 150ms).
    * O `get_status.php` lê o arquivo `status.txt` e entrega o conteúdo ao JavaScript, que atualiza a interface.

![Diagrama da Arquitetura](link_para_um_diagrama_simples_se_voce_quiser_fazer.png)

## 🛠️ Hardware Necessário

* 1x Arduino (Uno, Nano, etc.)
* 1x Servo Motor (ex: SG90)
* 1x Teclado Matricial 4x4
* 1x Protoboard e Jumpers

## 💻 Software e Instalação

Para rodar este projeto, você precisará de um ambiente com Arduino, Python e um servidor PHP.

### 1. Arduino
1.  Abra o arquivo `arduino_cofre/arduino_cofre.ino` na IDE do Arduino.
2.  Conecte os componentes seguindo as conexões no código (pinos 2, 3, 4, 5, 6, 7, A4, A5 para o teclado; pino 9 para o servo).
3.  Carregue (Upload) o código para o seu Arduino.

### 2. Servidor Web (XAMPP)
1.  Instale o [XAMPP](https://www.apachefriends.org/index.html).
2.  Copie a pasta `visor_web` para dentro da pasta `htdocs` do XAMPP (ex: `C:\xampp\htdocs\visor_web`).
3.  Inicie os módulos **Apache** no painel de controle do XAMPP.

### 3. A "Ponte" (Python)
1.  Instale o [Python 3](https://www.python.org/).
2.  Instale a biblioteca `pyserial` (necessária para ler a porta COM):
    ```bash
    pip install pyserial
    ```
3.  Abra o arquivo `visor_web/bridge.py` e **edite a porta COM** para a porta do seu Arduino (ex: `ARDUINO_PORT = "COM11"`).
4.  Abra um terminal **(como Administrador)**, navegue até a pasta e execute a ponte:
    ```bash
    # Navegue até a pasta correta!
    cd C:\xampp\htdocs\visor_web

    # Execute o script
    python bridge.py
    ```

### 4. Execução
1.  Com o **Apache rodando** e o **`bridge.py` executando**, abra seu navegador e acesse:
    `http://localhost/visor_web`
2.  Clique no botão "Sound: OFF" para ativar o áudio e começar a usar!
