-- =====================================================================
-- SISTEMA DE CHAMADOS CORPORATIVO - SCRIPT DE BANCO DE DADOS
-- Banco: MySQL 8+
-- Charset: utf8mb4
-- =====================================================================

CREATE DATABASE IF NOT EXISTS helpdesk CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE helpdesk;

-- ---------------------------------------------------------------------
-- Tabela: setores
-- ---------------------------------------------------------------------
CREATE TABLE setores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao VARCHAR(255) DEFAULT NULL,
    sla_horas INT NOT NULL DEFAULT 24 COMMENT 'Prazo padrao de atendimento em horas',
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: usuarios
-- ---------------------------------------------------------------------
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha_hash VARCHAR(255) NOT NULL,
    perfil ENUM('administrador','atendente','colaborador') NOT NULL DEFAULT 'colaborador',
    setor_id INT DEFAULT NULL,
    telefone VARCHAR(30) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login DATETIME DEFAULT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_usuarios_setor FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: categorias
-- ---------------------------------------------------------------------
CREATE TABLE categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    setor_id INT DEFAULT NULL,
    ativo TINYINT(1) NOT NULL DEFAULT 1,
    CONSTRAINT fk_categorias_setor FOREIGN KEY (setor_id) REFERENCES setores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: chamados
-- ---------------------------------------------------------------------
CREATE TABLE chamados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE COMMENT 'Ex: CH-2026-00001',
    titulo VARCHAR(200) NOT NULL,
    descricao TEXT NOT NULL,
    categoria_id INT DEFAULT NULL,
    prioridade ENUM('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
    status ENUM('novo','em_andamento','em_espera','fechado','cancelado') NOT NULL DEFAULT 'novo',
    solicitante_id INT NOT NULL,
    responsavel_id INT DEFAULT NULL,
    setor_id INT NOT NULL,
    sla_previsto DATETIME DEFAULT NULL COMMENT 'Data limite calculada a partir do SLA do setor',
    resolucao TEXT DEFAULT NULL COMMENT 'Campo obrigatorio: como foi resolvido',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    fechado_em DATETIME DEFAULT NULL,
    CONSTRAINT fk_chamados_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL,
    CONSTRAINT fk_chamados_solicitante FOREIGN KEY (solicitante_id) REFERENCES usuarios(id),
    CONSTRAINT fk_chamados_responsavel FOREIGN KEY (responsavel_id) REFERENCES usuarios(id) ON DELETE SET NULL,
    CONSTRAINT fk_chamados_setor FOREIGN KEY (setor_id) REFERENCES setores(id),
    INDEX idx_status (status),
    INDEX idx_prioridade (prioridade),
    INDEX idx_setor (setor_id),
    INDEX idx_criado_em (criado_em)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: comentarios
-- ---------------------------------------------------------------------
CREATE TABLE comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT NOT NULL,
    usuario_id INT NOT NULL,
    mensagem TEXT NOT NULL,
    interno TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Comentario visivel apenas para equipe interna',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_comentarios_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_comentarios_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: historicos (log imutavel de eventos do chamado)
-- ---------------------------------------------------------------------
CREATE TABLE historicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT NOT NULL,
    usuario_id INT NOT NULL,
    acao VARCHAR(100) NOT NULL COMMENT 'Ex: criado, status_alterado, encaminhado, comentario, anexo',
    descricao VARCHAR(500) NOT NULL,
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historicos_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_historicos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- Tabela: anexos
-- ---------------------------------------------------------------------
CREATE TABLE anexos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chamado_id INT NOT NULL,
    comentario_id INT DEFAULT NULL,
    usuario_id INT NOT NULL,
    nome_original VARCHAR(255) NOT NULL,
    nome_armazenado VARCHAR(255) NOT NULL,
    tipo_mime VARCHAR(100) NOT NULL,
    tamanho INT NOT NULL COMMENT 'Bytes',
    criado_em DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_anexos_chamado FOREIGN KEY (chamado_id) REFERENCES chamados(id) ON DELETE CASCADE,
    CONSTRAINT fk_anexos_comentario FOREIGN KEY (comentario_id) REFERENCES comentarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_anexos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB;

-- =====================================================================
-- DADOS INICIAIS (SEED)
-- =====================================================================

INSERT INTO setores (nome, descricao, sla_horas) VALUES
('TI', 'Tecnologia da Informacao', 8),
('Comercial', 'Vendas e Relacionamento com Cliente', 24),
('Juridico', 'Departamento Juridico', 48),
('Financeiro', 'Contas a Pagar/Receber', 24),
('Recursos Humanos', 'Gestao de Pessoas', 24);

-- Senha padrao para todos os usuarios de seed: "Senha@123"
-- Hash gerado com password_hash('Senha@123', PASSWORD_BCRYPT)
INSERT INTO usuarios (nome, email, senha_hash, perfil, setor_id) VALUES
('Administrador do Sistema', 'admin@empresa.com', '$2y$10$WQGwk6gyfn9RN3O7Aal24umgHPJPLDgNTxuDjme7wht5xPZgyb2MK', 'administrador', 1),
('Atendente TI', 'atendente.ti@empresa.com', '$2y$10$WQGwk6gyfn9RN3O7Aal24umgHPJPLDgNTxuDjme7wht5xPZgyb2MK', 'atendente', 1),
('Colaborador Exemplo', 'colaborador@empresa.com', '$2y$10$WQGwk6gyfn9RN3O7Aal24umgHPJPLDgNTxuDjme7wht5xPZgyb2MK', 'colaborador', 2);

INSERT INTO categorias (nome, setor_id) VALUES
('Hardware', 1), ('Software', 1), ('Rede/Internet', 1), ('Acesso e Senhas', 1),
('Proposta Comercial', 2), ('Contrato de Cliente', 2),
('Consulta Juridica', 3), ('Contrato', 3),
('Pagamento', 4), ('Reembolso', 4),
('Ferias e Beneficios', 5), ('Admissao/Demissao', 5);
