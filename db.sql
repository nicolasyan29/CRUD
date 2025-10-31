CREATE DATABASE IF NOT EXISTS gerenciamento_tarefas;
USE gerenciamento_tarefas;




CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE
);




CREATE TABLE tarefas (
    id_tarefa INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    descricao TEXT NOT NULL,
    nome_setor VARCHAR(255) NOT NULL,
    prioridade ENUM('baixa', 'media', 'alta') NOT NULL,
    data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('a fazer', 'fazendo', 'pronto') DEFAULT 'a fazer',
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);




INSERT INTO usuarios (nome, email) VALUES
('João Silva', 'joao@example.com'),
('Maria Oliveira', 'maria@example.com');




INSERT INTO tarefas (id_usuario, descricao, nome_setor, prioridade, status) VALUES
(1, 'Revisar relatórios financeiros', 'Financeiro', 'alta', 'a fazer'),
(1, 'Atualizar sistema de backup', 'TI', 'media', 'fazendo'),
(2, 'Organizar reunião de equipe', 'RH', 'baixa', 'pronto');