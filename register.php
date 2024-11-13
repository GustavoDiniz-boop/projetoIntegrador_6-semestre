<?php
require_once 'db_connect.php';
session_start();

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm-password'];

    // Debug information
    error_log("Submitted username: " . $username);
    error_log("Submitted email: " . $email);

    if ($password !== $confirm_password) {
        $error_message = "Passwords do not match";
    } else {
        // Check if email already exists
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email already exists";
        } else {
            // Insert new user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_user = "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insert_user);
            $stmt->bind_param("sss", $username, $email, $hashed_password);

            // Debug information
            error_log("SQL Query: " . $insert_user);
            error_log("Bound username: " . $username);
            error_log("Bound email: " . $email);

            if ($stmt->execute()) {
                // Debug information
                error_log("Inserted user ID: " . $stmt->insert_id);
                error_log("Affected rows: " . $stmt->affected_rows);
                header("Location: tela_principal.php");
                exit();
                // Debug information
                error_log("User registered successfully");
            } else {
                $error_message = "Error occurred during registration. Please try again.";
                // Debug information
                error_log("Registration error: " . $stmt->error);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - Registro</title>
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
        if (!empty($success_message)) {
            echo "<p class='text-green-500 mb-4'>$success_message</p>";
        }
        ?>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" id="username" name="username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
                <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Senha</label>
                <input type="password" id="password" name="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div class="mb-6">
                <label for="confirm-password" class="block text-gray-700 text-sm font-bold mb-2">Confirmar Senha</label>
                <input type="password" id="confirm-password" name="confirm-password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                Registrar
            </button>
        </form>
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">Já tem uma conta? <a href="login.php" class="text-blue-500 hover:underline">Faça login</a></p>
        </div>
    </div>
</body>
</html>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm-password').value;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('As senhas não coincidem');
        }
    });
</script>
</body>
</html>
