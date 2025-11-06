import serial
import time

# --- Configuração ---
# MUDE AQUI para a porta do seu Arduino (se for outra)
ARDUINO_PORT = "COM11"
BAUD_RATE = 9600
STATUS_FILE = "status.txt"
STATUS_PREFIX = "VISOR:"   # O prefixo que definimos no Arduino
# --------------------

print(f"Iniciando ponte na porta {ARDUINO_PORT}...")
print("Pressione CTRL+C para parar.")

while True:
    try:
        ser = serial.Serial(ARDUINO_PORT, BAUD_RATE, timeout=1)
        print(f"Conectado ao Arduino em {ARDUINO_PORT}.")

        while True:
            if ser.in_waiting > 0:
                try:
                    line = ser.readline().decode('utf-8').strip()

                    if line:
                        # Exibe toda a saída do Arduino (para debug)
                        print(f"Arduino -> {line}")
                        
                        # Só escreve no arquivo se a linha começar com o prefixo
                        if line.startswith(STATUS_PREFIX):
                            # Remove o prefixo antes de salvar
                            status_message = line.replace(STATUS_PREFIX, "").strip()
                            
                            print(f"=== ATUALIZANDO VISOR WEB: {status_message} ===")
                            
                            with open(STATUS_FILE, "w", encoding='utf-8') as f:
                                f.write(status_message)
                                f.flush() # <-- A MODIFICAÇÃO ESTÁ AQUI
                                
                except UnicodeDecodeError:
                    print("Erro de decodificação. Ignorando linha.")
                except Exception as e:
                    print(f"Erro ao ler linha: {e}")

    except serial.SerialException:
        print(f"Arduino não encontrado em {ARDUINO_PORT}. Tentando novamente em 5s...")
        time.sleep(5)
    except KeyboardInterrupt:
        print("\nEncerrando a ponte.")
        if 'ser' in locals() and ser.is_open:
            ser.close()
        break
    except Exception as e:
        print(f"Erro inesperado: {e}")
        time.sleep(5)
