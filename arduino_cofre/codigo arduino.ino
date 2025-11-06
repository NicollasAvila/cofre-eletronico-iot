#include <Keypad.h>
#include <Servo.h> // Inclui a biblioteca do servo motor

// Definição do teclado matricial
const byte linhas = 4;
const byte colunas = 4;

char teclas[linhas][colunas] = {
  {'1', '2', '3', 'A'},
  {'4', '5', '6', 'B'},
  {'7', '8', '9', 'C'},
  {'*', '0', '#', 'D'}
};

byte pinosLinhas[linhas] = {7, 6, 5, 4};
byte pinosColunas[colunas] = {2, 3, A5, A4};

// Inicializa o teclado
Keypad teclado = Keypad(makeKeymap(teclas), pinosLinhas, pinosColunas, linhas, colunas);

// Definições do Servom/m
Servo meuServo;             // Cria o objeto 'servo'
int pinoServo = 9;          // Pino de sinal (deve ser PWM, como o 9)
int anguloFechado = 250;      // Posição (em graus) para a tranca fechada
int anguloAberto = -280;      // Posição (em graus) para a tranca aberta

// Variáveis de senha
char codigoAcesso[5] = "123A";
char tentativa[5];
int indice = 0;

String visorWebString = "";
const String STATUS_PREFIX = "VISOR:";

void setup() {
  Serial.begin(9600);
  
  // Inicializa o servo
  meuServo.attach(pinoServo);     // "Avisa" o Arduino que o servo está no pino 9
  meuServo.write(anguloFechado);  // Garante que o cofre comece trancado

  Serial.println("=== Sistema de Senha Iniciado ===");
  Serial.println("Pressione as teclas no teclado...");

  visorWebString = "Digite o codigo";
  Serial.println(STATUS_PREFIX + visorWebString);
}

void loop() {
  char tecla = teclado.getKey();
  if (tecla != NO_KEY) {
    Serial.print("Tecla pressionada: ");
    Serial.println(tecla);
    verificaCodigo(tecla);
  }
}

// ==========================================================
// FUNÇÃO VERIFICACODIGO ATUALIZADA (OPÇÃO 2)
// ==========================================================
void verificaCodigo(char teclaPressionada) {
  // Quando confirma (#)
  if (teclaPressionada == '#') {
    if (verificaIgualdade(codigoAcesso, tentativa)) {
      
      // *** LÓGICA DE SUCESSO ATUALIZADA ***
      Serial.println(STATUS_PREFIX + "Codigo Correto");
      
      meuServo.write(anguloAberto); // Apenas abre o cofre
      
      // O delay de 5s e o comando de fechar foram REMOVIDOS daqui.
      // Deixamos um delay curto só para o status não sumir rápido
      delay(2000);
      
    } else {
      // Lógica de erro (sem mudança)
      Serial.println(STATUS_PREFIX + "Codigo Errado");
      delay(2000);
    }
    
    // Reseta para a próxima tentativa
    Serial.println(STATUS_PREFIX + "Digite o codigo");
    reiniciarTentativa();
  }

  // Quando digita caracteres
  else if (isAlphanumeric(teclaPressionada)) {
    
    // *** NOVA LÓGICA DE FECHAMENTO ***
    // Se for a PRIMEIRA tecla de uma nova tentativa (indice=0),
    // primeiro FECHA O COFRE.
    if (indice == 0) {
      meuServo.write(anguloFechado); // Garante que o cofre está fechado
    }
    // *** FIM DA MUDANÇA ***

    if (indice < 4) {
      tentativa[indice] = teclaPressionada;
      tentativa[indice + 1] = '\0';
      indice++;
      
      if (indice == 1) {
        visorWebString = "";
      }
      visorWebString += "*";
      Serial.println(STATUS_PREFIX + visorWebString);
    }
  }

  // Quando tenta alterar código (*)
  else if (teclaPressionada == '*') {
    
    // *** NOVA LÓGICA DE FECHAMENTO ***
    // Fecha o cofre antes de tentar alterar o código
    meuServo.write(anguloFechado);
    // *** FIM DA MUDANÇA ***

    if (verificaIgualdade(codigoAcesso, tentativa)) {
      Serial.println("Acesso autorizado para mudar o codigo.");
      alteraCodigo();
    } else {
      Serial.println(STATUS_PREFIX + "Codigo Errado");
      delay(2000);
      Serial.println(STATUS_PREFIX + "Digite o codigo");
      reiniciarTentativa();
    }
  }
}

bool isAlphanumeric(char c) {
  return (isDigit(c) || isAlpha(c));
}

bool verificaIgualdade(const char a1[], const char a2[]) {
  for (int i = 0; i < 4; i++) {
    if (a1[i] != a2[i]) return false;
  }
  return true;
}

void reiniciarTentativa() {
  for (int i = 0; i < 5; i++) tentativa[i] = '\0';
  indice = 0;
  visorWebString = "";
}

// ==========================================================
// FUNÇÃO ALTERACODIGO ATUALIZADA (OPÇÃO 2)
// ==========================================================
void alteraCodigo() {
  // *** NOVA LÓGICA DE FECHAMENTO ***
  // Garante que o cofre feche se estava aberto
  meuServo.write(anguloFechado); 
  // *** FIM DA MUDANÇA ***

  visorWebString = "Novo codigo:";
  Serial.println("Digite o novo codigo (4 teclas):");
  Serial.println(STATUS_PREFIX + visorWebString);

  visorWebString = "";
  
  for (int i = 0; i < 4; i++) {
    char tecla;
    do {
      tecla = teclado.getKey();
    } while (tecla == NO_KEY);

    Serial.print("Nova tecla: ");
    Serial.println(tecla);

    if (isAlphanumeric(tecla)) {
      codigoAcesso[i] = tecla;
      visorWebString += "*";
      Serial.println(STATUS_PREFIX + visorWebString);
    } else {
      i--;
    }
  }

  Serial.print("Novo codigo definido: ");
  Serial.println(codigoAcesso);
  Serial.println(STATUS_PREFIX + "Codigo alterado");
  
  delay(2000);
  Serial.println(STATUS_PREFIX + "Digite o codigo");
  reiniciarTentativa();
}