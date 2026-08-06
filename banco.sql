CREATE TABLE IF NOT EXISTS `usuarios` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL UNIQUE,
    `senha` VARCHAR(255) NOT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `clientes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150),
    `telefone` VARCHAR(20),
    `empresa` VARCHAR(100),
    `observacao` TEXT,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `projetos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `cliente_id` INT,
    `nome` VARCHAR(150) NOT NULL,
    `descricao` TEXT,
    `status` ENUM('em_andamento','concluido','pausado','cancelado') DEFAULT 'em_andamento',
    `valor_total` DECIMAL(10,2) DEFAULT 0.00,
    `valor_pago` DECIMAL(10,2) DEFAULT 0.00,
    `data_inicio` DATE,
    `data_previsao` DATE,
    `data_conclusao` DATE,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `tarefas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `projeto_id` INT NOT NULL,
    `titulo` VARCHAR(200) NOT NULL,
    `descricao` TEXT,
    `status` ENUM('pendente','em_andamento','concluida') DEFAULT 'pendente',
    `prioridade` ENUM('baixa','media','alta') DEFAULT 'media',
    `data_prazo` DATE,
    `concluido_em` DATETIME,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`projeto_id`) REFERENCES `projetos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `arquivos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `projeto_id` INT NOT NULL,
    `nome_original` VARCHAR(255) NOT NULL,
    `nome_arquivo` VARCHAR(255) NOT NULL,
    `tamanho` INT,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`projeto_id`) REFERENCES `projetos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `anotacoes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `projeto_id` INT NOT NULL,
    `texto` TEXT NOT NULL,
    `criado_em` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`projeto_id`) REFERENCES `projetos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
