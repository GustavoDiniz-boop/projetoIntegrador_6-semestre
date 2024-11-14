
<?php
session_start();
require_once 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $error = "As senhas não coincidem.";
    } else {
        // Verificar se o email já está em uso
        $check_email = "SELECT id FROM Users WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Este email já está em uso.";
        } else {
            // Criar nova conta de usuário
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_user = "INSERT INTO Users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_user);
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                $success = "Conta criada com sucesso. Faça login para continuar.";
            } else {
                $error = "Erro ao criar conta. Tente novamente.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - PetMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <h2 class="text-2xl font-semibold mb-6 text-center">Registro - PetMatch</h2>
        <?php
        if ($error) {
            echo "<p class='text-red-500 mb-4'>$error</p>";
        }
        if ($success) {
            echo "<p class='text-green-500 mb-4'>$success</p>";
        }
        ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 font-bold mb-2">Nome de usuário</label>
                <input type="text" id="username" name="username" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 font-bold mb-2">Senha</label>
                <input type="password" id="password" name="password" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 font-bold mb-2">Confirmar Senha</label>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:shadow-outline">
                Registrar
            </button>
        </form>
        <p class="mt-4 text-center">
            Já tem uma conta? <a href="login.php" class="text-blue-500 hover:underline">Faça login</a>
        </p>
    </div>
</body>
</html>
