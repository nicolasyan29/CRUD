<?php
include 'config.php';
$conn = conectar();


$mensagem = '';
$tipo_mensagem = '';
$tarefa = null;


if (isset($_GET['id'])) {
    $id_tarefa = $_GET['id'];
    if (!is_numeric($id_tarefa)) {
        die('ID inválido.');
    }
    $stmt = $conn->prepare("SELECT * FROM tarefas WHERE id_tarefa = ?");
    $stmt->bind_param("i", $id_tarefa);
    $stmt->execute();
    $result = $stmt->get_result();
    $tarefa = $result->fetch_assoc();
    $stmt->close();
    if (!$tarefa) {
        die('Tarefa não encontrada.');
    }
}


$usuarios = [];
$result = $conn->query("SELECT id, nome FROM usuarios");
while ($row = $result->fetch_assoc()) {
    $usuarios[] = $row;
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
        $mensagem = 'Erro de segurança. Tente novamente.';
        $tipo_mensagem = 'erro';
    } else {
        $id_tarefa = $_POST['id_tarefa'];
        $id_usuario = $_POST['id_usuario'];
        $descricao = trim($_POST['descricao']);
        $nome_setor = trim($_POST['nome_setor']);
        $prioridade = $_POST['prioridade'];
        $status = $_POST['status'];


       
        if (empty($id_usuario) || empty($descricao) || empty($nome_setor) || empty($prioridade) || empty($status)) {
            $mensagem = 'Todos os campos são obrigatórios.';
            $tipo_mensagem = 'erro';
        } elseif (!is_numeric($id_usuario)) {
            $mensagem = 'Usuário inválido.';
            $tipo_mensagem = 'erro';
        } elseif (strlen($descricao) > 65535) {
            $mensagem = 'Descrição deve ter no máximo 65.535 caracteres.';
            $tipo_mensagem = 'erro';
        } elseif (strlen($nome_setor) > 255) {
            $mensagem = 'Setor deve ter no máximo 255 caracteres.';
            $tipo_mensagem = 'erro';
        } elseif (!in_array($prioridade, ['baixa', 'media', 'alta'])) {
            $mensagem = 'Prioridade inválida.';
            $tipo_mensagem = 'erro';
        } elseif (!in_array($status, ['a fazer', 'fazendo', 'pronto'])) {
            $mensagem = 'Status inválido.';
            $tipo_mensagem = 'erro';
        } else {
           
            $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt_check->bind_param("i", $id_usuario);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows == 0) {
                $mensagem = 'Usuário não encontrado.';
                $tipo_mensagem = 'erro';
            } else {
                $stmt = $conn->prepare("UPDATE tarefas SET id_usuario = ?, descricao = ?, nome_setor = ?, prioridade = ?, status = ? WHERE id_tarefa = ?");
                $stmt->bind_param("issssi", $id_usuario, $descricao, $nome_setor, $prioridade, $status, $id_tarefa);
                if ($stmt->execute()) {
                    $mensagem = 'Tarefa atualizada com sucesso!';
                    $tipo_mensagem = 'sucesso';
                    header("Location: gerenciamento.php");
                    exit;
                } else {
                    $mensagem = 'Erro ao atualizar tarefa.';
                    $tipo_mensagem = 'erro';
                }
                $stmt->close();
            }
            $stmt_check->close();
        }
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Editar Tarefa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Editar Tarefa</h1>
    <?php if ($tarefa): ?>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
            <input type="hidden" name="id_tarefa" value="<?php echo $tarefa['id_tarefa']; ?>">
            <label for="id_usuario">Usuário:</label>
            <select id="id_usuario" name="id_usuario" required>
                <?php foreach ($usuarios as $usuario): ?>
                    <option value="<?php echo $usuario['id']; ?>" <?php if ($usuario['id'] == $tarefa['id_usuario']) echo 'selected'; ?>><?php echo htmlspecialchars($usuario['nome']); ?></option>
                <?php endforeach; ?>
            </select><br><br>
            <label for="descricao">Descrição:</label>
            <textarea id="descricao" name="descricao" required maxlength="65535"><?php echo htmlspecialchars($tarefa['descricao']); ?></textarea><br><br>
            <label for="nome_setor">Setor:</label>
            <input type="text" id="nome_setor" name="nome_setor" value="<?php echo htmlspecialchars($tarefa['nome_setor']); ?>" required maxlength="255"><br><br>
            <label for="prioridade">Prioridade:</label>
            <select id="prioridade" name="prioridade" required>
                <option value="baixa" <?php if ($tarefa['prioridade'] == 'baixa') echo 'selected'; ?>>Baixa</option>
                <option value="media" <?php if ($tarefa['prioridade'] == 'media') echo 'selected'; ?>>Média</option>
                <option value="alta" <?php if ($tarefa['prioridade'] == 'alta') echo 'selected'; ?>>Alta</option>
            </select><br><br>
            <label for="status">Status:</label>
            <select id="status" name="status" required>
                <option value="a fazer" <?php if ($tarefa['status'] == 'a fazer') echo 'selected'; ?>>A Fazer</option>
                <option value="fazendo" <?php if ($tarefa['status'] == 'fazendo') echo 'selected'; ?>>Fazendo</option>
                <option value="pronto" <?php if ($tarefa['status'] == 'pronto') echo 'selected'; ?>>Pronto</option>
            </select><br><br>
            <button type="submit">Atualizar</button>
        </form>
    <?php else: ?>
        <p>Tarefa não encontrada.</p>
    <?php endif; ?>
    <?php if ($mensagem): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>
    <a href="gerenciamento.php">Voltar ao Gerenciamento</a>
</body>
</html>