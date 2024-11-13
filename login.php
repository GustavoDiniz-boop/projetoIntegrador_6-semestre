<?php
require_once 'db_connect.php';
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, email, password_hash FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (isset($user['password_hash'])) {
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email'];
                header("Location: tela_principal.php");
                exit();
            } else {
                $error_message = "Invalid email or password";
            }
        } else {
            $error_message = "User data is incomplete. Please contact support.";
            error_log("Login error: Password field missing for user " . $user['email']);
        }
    } else {
        $error_message = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-96">
        <div class="text-center mb-8">
            <i class="fas fa-paw text-4xl text-blue-500"></i>
            <h2 class="text-2xl font-bold mt-2">PetMatch</h2>
        </div>
        <?php
        if (!empty($error_message)) {
            echo "<p class='text-red-500 mb-4'>$error_message</p>";
        }
        ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Entrar
            </button>
        </form>
        <div class="mt-4 text-center">
            <a href="#" class="text-sm text-blue-500 hover:underline">Esqueceu a senha?</a>
        </div>
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">Não tem uma conta? <a href="register.php" class="text-blue-500 hover:underline">Registre-se</a></p>
        </div>
    </div>
</body>
</html>
