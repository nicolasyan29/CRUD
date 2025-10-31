<?php
include 'config.php';
$conn = conectar();




?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Gerenciamento de Tarefas</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gerenciamento de Tarefas</h1>
    <nav>
        <a href="cadastro_usuario.php">Cadastrar Usuário</a>
        <a href="cadastro_tarefa.php">Cadastrar Tarefa</a>
        <a href="gerenciamento.php">Gerenciar Tarefas</a>
    </nav>
    <p>Bem-vindo ao sistema de gerenciamento de tarefas. Use o menu acima para navegar.</p>
</body>
</html>