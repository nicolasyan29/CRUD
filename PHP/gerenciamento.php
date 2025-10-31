<?php
include 'config.php';
$conn = conectar();


$tarefas = [
    'a fazer' => [],
    'fazendo' => [],
    'pronto' => []
];


$result = $conn->query("SELECT t.id_tarefa, t.descricao, t.nome_setor, t.prioridade, t.status, u.nome AS usuario_nome FROM tarefas t JOIN usuarios u ON t.id_usuario = u.id ORDER BY t.data_cadastro DESC");
while ($row = $result->fetch_assoc()) {
    $tarefas[$row['status']][] = $row;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['excluir'])) {
   
    if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
        die('Erro de segurança.');
    }
    $id_tarefa = $_POST['id_tarefa_excluir'];
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id_tarefa = ?");
    $stmt->bind_param("i", $id_tarefa);
    $stmt->execute();
    $stmt->close();
    header("Location: gerenciamento.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['alterar_status'])) {
    $id_tarefa = $_POST['id_tarefa'];
    $novo_status = $_POST['novo_status'];
    $stmt = $conn->prepare("UPDATE tarefas SET status = ? WHERE id_tarefa = ?");
    $stmt->bind_param("si", $novo_status, $id_tarefa);
    $stmt->execute();
    $stmt->close();
    header("Location: gerenciamento.php");
    exit;
}


$conn->close();
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
    <div class="kanban">
        <?php foreach ($tarefas as $status => $lista): ?>
            <div class="coluna">
                <h2><?php echo ucfirst($status); ?></h2>
                <?php foreach ($lista as $tarefa): ?>
                    <div class="tarefa">
                        <p><strong>Descrição:</strong> <?php echo htmlspecialchars($tarefa['descricao']); ?></p>
                        <p><strong>Setor:</strong> <?php echo htmlspecialchars($tarefa['nome_setor']); ?></p>
                        <p><strong>Prioridade:</strong> <?php echo htmlspecialchars($tarefa['prioridade']); ?></p>
                        <p><strong>Usuário:</strong> <?php echo htmlspecialchars($tarefa['usuario_nome']); ?></p>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                            <input type="hidden" name="id_tarefa" value="<?php echo $tarefa['id_tarefa']; ?>">
                            <select name="novo_status">
                                <option value="a fazer" <?php if ($status == 'a fazer') echo 'selected'; ?>>A Fazer</option>
                                <option value="fazendo" <?php if ($status == 'fazendo') echo 'selected'; ?>>Fazendo</option>
                                <option value="pronto" <?php if ($status == 'pronto') echo 'selected'; ?>>Pronto</option>
                            </select>
                            <button type="submit" name="alterar_status">Alterar Status</button>
                        </form>
                        <a href="editar_tarefa.php?id=<?php echo $tarefa['id_tarefa']; ?>">Editar</a>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                            <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
                            <input type="hidden" name="id_tarefa_excluir" value="<?php echo $tarefa['id_tarefa']; ?>">
                            <button type="submit" name="excluir">Excluir</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="index.php">Voltar ao Menu</a>
</body>
</html>