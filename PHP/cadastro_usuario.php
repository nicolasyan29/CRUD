<?php
include 'config.php';
$conn = conectar();


$mensagem = '';
$tipo_mensagem = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    if (!isset($_POST['csrf_token']) || !validarTokenCSRF($_POST['csrf_token'])) {
        $mensagem = 'Erro de segurança. Tente novamente.';
        $tipo_mensagem = 'erro';
    } else {
        $nome = trim($_POST['nome']);
        $email = trim($_POST['email']);


       
        if (empty($nome) || empty($email)) {
            $mensagem = 'Todos os campos são obrigatórios.';
            $tipo_mensagem = 'erro';
        } elseif (strlen($nome) > 255) {
            $mensagem = 'Nome deve ter no máximo 255 caracteres.';
            $tipo_mensagem = 'erro';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $mensagem = 'E-mail inválido.';
            $tipo_mensagem = 'erro';
        } elseif (strlen($email) > 255) {
            $mensagem = 'E-mail deve ter no máximo 255 caracteres.';
            $tipo_mensagem = 'erro';
        } else {
           
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $mensagem = 'E-mail já cadastrado.';
                $tipo_mensagem = 'erro';
            } else {
               
                $stmt = $conn->prepare("INSERT INTO usuarios (nome, email) VALUES (?, ?)");
                $stmt->bind_param("ss", $nome, $email);
                if ($stmt->execute()) {
                    $mensagem = 'Cadastro concluído com sucesso!';
                    $tipo_mensagem = 'sucesso';
                } else {
                    $mensagem = 'Erro ao cadastrar usuário.';
                    $tipo_mensagem = 'erro';
                }
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Usuário</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Cadastro de Usuário</h1>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo gerarTokenCSRF(); ?>">
        <label for="nome">Nome:</label>
        <input type="text" id="nome" name="nome" required maxlength="255"><br><br>
        <label for="email">E-mail:</label>
        <input type="email" id="email" name="email" required maxlength="255"><br><br>
        <button type="submit">Cadastrar</button>
    </form>
    <?php if ($mensagem): ?>
        <div class="mensagem <?php echo $tipo_mensagem; ?>"><?php echo htmlspecialchars($mensagem); ?></div>
    <?php endif; ?>
    <a href="index.php">Voltar ao Menu</a>
</body>
</html>