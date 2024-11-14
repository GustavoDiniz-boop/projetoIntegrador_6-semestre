
<?php
session_start();
require_once 'db_connect.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar informações do usuário
$user_query = "SELECT * FROM Users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

$success_message = '';
$error_message = '';

// Processar formulário de atualização de informações
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_info'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];

    $update_query = "UPDATE Users SET full_name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $full_name, $email, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Informações atualizadas com sucesso!";
        // Atualizar as informações do usuário na sessão
        $user['full_name'] = $full_name;
        $user['email'] = $email;
    } else {
        $error_message = "Erro ao atualizar as informações. Tente novamente.";
    }
}

// Processar formulário de alteração de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (password_verify($current_password, $user['password_hash'])) {
        if ($new_password === $confirm_password) {
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $update_query = "UPDATE Users SET password_hash = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("si", $new_password_hash, $user_id);
            
            if ($stmt->execute()) {
                $success_message = "Senha alterada com sucesso!";
            } else {
                $error_message = "Erro ao alterar a senha. Tente novamente.";
            }
        } else {
            $error_message = "As senhas não coincidem.";
        }
    } else {
        $error_message = "Senha atual incorreta.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - PetMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="bg-blue-600 text-white w-64 min-h-screen p-4">
            <h1 class="text-2xl font-bold mb-4">PetMatch</h1>
            <ul>
                <li class="mb-2"><a href="tela_principal.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-home mr-2"></i>Página Principal</a></li>
                <li class="mb-2"><a href="user_profile.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user mr-2"></i>Perfil</a></li>
                <li class="mb-2"><a href="settings.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-cog mr-2"></i>Configurações</a></li>
                <?php if ($_SESSION['user_role'] === 'admin'): ?><li class="mb-2"><a href="admin_dashboard.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a><?php if ($_SESSION['user_role'] === 'admin'): ?><li class="mb-2"><a href="admin_dashboard.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a><?php endif; ?>
                <li class="mb-2"><a href="logout.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-sign-out-alt mr-2"></i>Sair</a></li>
            </ul>
        </div>

        <!-- Conteúdo principal -->
        <div class="flex-1 p-10">
            <h2 class="text-3xl font-bold mb-6">Configurações</h2>
            
            <?php if ($success_message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $success_message; ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $error_message; ?></span>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4">Atualizar Informações</h3>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="full_name">
                            Nome Completo
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="full_name" type="text" name="full_name" value="<?php echo $user['full_name']; ?>">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" name="email" value="<?php echo $user['email']; ?>">
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="update_info">
                            Atualizar Informações
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4">Alterar Senha</h3>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="current_password">
                            Senha Atual
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="current_password" type="password" name="current_password" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="new_password">
                            Nova Senha
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="new_password" type="password" name="new_password" required>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                            Confirmar Nova Senha
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="confirm_password" type="password" name="confirm_password" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="change_password">
                            Alterar Senha
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
