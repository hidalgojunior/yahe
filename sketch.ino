#include <Wire.h>
#include <MPU6050.h>

// Definição dos códigos de erro
enum ErrorCode {
  NO_ERROR = 0,
  MPU_CONNECTION_ERROR = 1,
  ACCELERATION_OUT_OF_RANGE = 2,
  SAMPLE_BUFFER_OVERFLOW = 3,
  I2C_ERROR = 4,
  INVALID_MEASUREMENT = 5
};

// Classe para gerenciamento de erros
class ErrorManager {
  private:
    static const int MAX_ERRORS = 10;
    ErrorCode errorLog[MAX_ERRORS];
    int errorCount;
    bool systemHalted;

  public:
    ErrorManager() : errorCount(0), systemHalted(false) {}

    bool logError(ErrorCode error) {
      if (errorCount < MAX_ERRORS) {
        errorLog[errorCount++] = error;
        printError(error);
        return true;
      }
      return false;
    }

    void printError(ErrorCode error) {
      Serial.print("ERRO: ");
      switch (error) {
        case MPU_CONNECTION_ERROR:
          Serial.println("Falha na conexão com MPU6050");
          break;
        case ACCELERATION_OUT_OF_RANGE:
          Serial.println("Aceleração fora do intervalo válido");
          break;
        case SAMPLE_BUFFER_OVERFLOW:
          Serial.println("Buffer de amostras estourou");
          break;
        case I2C_ERROR:
          Serial.println("Erro na comunicação I2C");
          break;
        case INVALID_MEASUREMENT:
          Serial.println("Medição inválida detectada");
          break;
        default:
          Serial.println("Erro desconhecido");
      }
    }

    void haltSystem() {
      systemHalted = true;
      Serial.println("Sistema interrompido devido a erros críticos!");
    }

    bool isSystemHalted() {
      return systemHalted;
    }

    void clearErrors() {
      errorCount = 0;
      systemHalted = false;
    }
};

// Classe para validação de dados
class DataValidator {
  private:
    const float MAX_VALID_ACCELERATION = 20.0; // em g
    const float MIN_VALID_ACCELERATION = -20.0; // em g

  public:
    bool isValidAcceleration(float acceleration) {
      return acceleration >= MIN_VALID_ACCELERATION && 
             acceleration <= MAX_VALID_ACCELERATION;
    }

    bool isValidSampleIndex(int index, int maxSamples) {
      return index >= 0 && index < maxSamples;
    }

    bool isValidMPUData(int16_t ax, int16_t ay, int16_t az) {
      // Verifica se os valores estão dentro do intervalo esperado do sensor
      const int16_t MAX_VALUE = 32767;
      const int16_t MIN_VALUE = -32768;
      
      return (ax > MIN_VALUE && ax < MAX_VALUE) &&
             (ay > MIN_VALUE && ay < MAX_VALUE) &&
             (az > MIN_VALUE && az < MAX_VALUE);
    }
};

// Adicione estas variáveis globais junto com as outras
ErrorManager errorManager;
DataValidator dataValidator;

// Função para verificar a integridade do hardware
bool checkHardware() {
  Wire.beginTransmission(0x68); // Endereço I2C do MPU6050
  byte error = Wire.endTransmission();
  
  if (error != 0) {
    errorManager.logError(I2C_ERROR);
    return false;
  }
  
  if (!mpu.testConnection()) {
    errorManager.logError(MPU_CONNECTION_ERROR);
    return false;
  }
  
  return true;
}

// Função para verificar a validade dos dados
bool validateMeasurement(int16_t ax, int16_t ay, int16_t az, float acceleration) {
  if (!dataValidator.isValidMPUData(ax, ay, az)) {
    errorManager.logError(INVALID_MEASUREMENT);
    return false;
  }

  if (!dataValidator.isValidAcceleration(acceleration)) {
    errorManager.logError(ACCELERATION_OUT_OF_RANGE);
    return false;
  }

  return true;
}

// Modifique a função setup para incluir as verificações
void setup() {
  Serial.begin(115200);
  Wire.begin(21, 22);
  
  // Verificação inicial do hardware
  if (!checkHardware()) {
    errorManager.haltSystem();
    while (1) {
      Serial.println("Falha na inicialização. Verifique as conexões.");
      delay(5000);
    }
  }

  mpu.initialize();
  Serial.println("Iniciando a detecção de quedas...");
}

// Modifique o loop principal para incluir as verificações
void loop() {
  if (errorManager.isSystemHalted()) {
    Serial.println("Sistema em modo de segurança. Reinicie o dispositivo.");
    delay(5000);
    return;
  }

  for (int i = 0; i < NUM_SAMPLES; i++) {
    delayMicroseconds(SAMPLE_INTERVAL);
    
    // Verifica se o índice é válido
    if (!dataValidator.isValidSampleIndex(i, NUM_SAMPLES)) {
      errorManager.logError(SAMPLE_BUFFER_OVERFLOW);
      resetDetection();
      break;
    }

    // Leitura dos dados com verificação de erro
    mpu.getAcceleration(&ax, &ay, &az);
    float acceleration = sqrt(ax * ax + ay * ay + az * az) / GRAVITY_SCALE;

    // Valida os dados obtidos
    if (!validateMeasurement(ax, ay, az, acceleration)) {
      continue; // Pula esta amostra se for inválida
    }

    // Armazena as amostras com verificação
    try {
      ax_samples[i] = ax;
      ay_samples[i] = ay;
      az_samples[i] = az;
    } catch (...) {
      errorManager.logError(SAMPLE_BUFFER_OVERFLOW);
      resetDetection();
      break;
    }

    // Resto do código do loop continua aqui...
    // [mantenha o código existente do loop]
  }
}

// Modifique a função verifica_eixo para incluir validações
void verifica_eixo() {
  if (errorManager.isSystemHalted()) {
    return;
  }

  long mediaX = 0, mediaY = 0, mediaZ = 0;
  int amostrasValidas = 0;

  for (int i = NUM_SAMPLES - AMOSTRAS_DIRECAO; i < NUM_SAMPLES; i++) {
    if (dataValidator.isValidSampleIndex(i, NUM_SAMPLES)) {
      if (dataValidator.isValidMPUData(ax_samples[i], ay_samples[i], az_samples[i])) {
        mediaX += ax_samples[i];
        mediaY += ay_samples[i];
        mediaZ += az_samples[i];
        amostrasValidas++;
      }
    }
  }

  if (amostrasValidas == 0) {
    errorManager.logError(INVALID_MEASUREMENT);
    return;
  }

  // Calcula as médias usando apenas amostras válidas
  mediaX /= amostrasValidas;
  mediaY /= amostrasValidas;
  mediaZ /= amostrasValidas;

  // Resto do código da função verifica_eixo continua aqui...
  // [mantenha o código existente da função]
} 