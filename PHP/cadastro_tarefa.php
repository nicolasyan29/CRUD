<?php
include 'config.php';
$conn = conectar();


$mensagem = '';
$tipo_mensagem = '';


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
        $id_usuario = $_POST['id_usuario'];
        $descricao = trim($_POST['descricao']);
        $nome_setor = trim($_POST['nome_setor']);
        $prioridade = $_POST['prioridade'];


       
        if (empty($id_usuario) || empty($descricao) || empty($nome_setor) || empty($prioridade)) {
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
        } else {
           
            $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
            $stmt_check->bind_param("i", $id_usuario);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows == 0) {
                $mensagem = 'Usuário não encontrado.';
                $tipo_mensagem = 'erro';
            } else {
                $stmt = $conn->prepare("INSERT INTO tarefas (id_usuario, descricao, nome_setor, prioridade) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $id_usuario, $descricao, $nome_setor, $prioridade);
                if ($stmt->execute()) {
                    $mensagem = 'Tarefa cadastrada com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao cadastrar tarefa.';
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
    <title>Cadastro de Tarefa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Cadastro de Tarefa</h1>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
        <label for="id_usuario">Usuário:</label>
        <select id="id_usuario" name="id_usuario" required>
            <option value="">Selecione um usuário</option>
            <?php foreach ($usuarios as $usuario): ?>
                <option value="<?php echo $usuario['id']; ?>"><?php echo htmlspecialchars($usuario['nome']); ?></option>
            <?php endforeach; ?>
        </select><br><br>
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" required maxlength="65535"></textarea><br><br>
        <label for="nome_setor">Setor:</label>
        <input type="text" id="nome_setor" name="nome_setor" required maxlength="255"><br><br>
        <label for="prioridade">Prioridade:</label>
        <select id="prioridade" name="prioridade" required>
            <option value="baixa">Baixa</option>
            <option value="media">Média</option>
            <option value="alta">Alta</option>
        </select><br><br>
        <button type="submit">Cadastrar</button>
    </form>
    <?php if ($mensagem): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>
    <a href="index.php">Voltar ao Menu</a>
</body>
</html>