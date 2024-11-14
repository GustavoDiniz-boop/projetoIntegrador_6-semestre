
<?php
session_start();
require_once 'db_connect.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Verificar se o ID do pet foi fornecido
if (!isset($_GET['pet_id'])) {
    header("Location: tela_principal.php");
    exit();
}

$pet_id = $_GET['pet_id'];

// Buscar informações do pet e do dono
$pet_query = "SELECT p.*, u.email as owner_email, u.username as owner_name
              FROM Pets p 
              JOIN Users u ON p.owner_id = u.id 
              WHERE p.id = ?";
$stmt = $conn->prepare($pet_query);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$pet_result = $stmt->get_result();
$pet = $pet_result->fetch_assoc();

if (!$pet) {
    header("Location: tela_principal.php");
    exit();
}

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $housing_situation = $_POST['housing_situation'];
    $experience = $_POST['experience'];
    $reason = $_POST['reason'];

    // Validar campos (você pode adicionar mais validações conforme necessário)
    if (empty($full_name) || empty($email) || empty($phone) || empty($address) || empty($housing_situation) || empty($experience) || empty($reason)) {
        $error_message = "Por favor, preencha todos os campos.";
    } else {
        // Inserir a solicitação de adoção no banco de dados
        $insert_query = "INSERT INTO AdoptionApplications (pet_id, applicant_id, full_name, email, phone, address, housing_situation, experience, reason, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iisssssss", $pet_id, $user_id, $full_name, $email, $phone, $address, $housing_situation, $experience, $reason);
        
        if ($stmt->execute()) {
            // Enviar e-mail para o dono do pet
            $to = $pet['owner_email'];
            $subject = "Nova solicitação de adoção para " . $pet['name'];
            $message = "Olá " . $pet['owner_name'] . ",

";
            $message .= "Você recebeu uma nova solicitação de adoção para " . $pet['name'] . ".

";
            $message .= "Detalhes do solicitante:
";
            $message .= "Nome: " . $full_name . "
";
            $message .= "Email: " . $email . "
";
            $message .= "Telefone: " . $phone . "
";
            $message .= "Endereço: " . $address . "
";
            $message .= "Situação de Moradia: " . $housing_situation . "
";
            $message .= "Experiência com pets: " . $experience . "
";
            $message .= "Motivo para adoção: " . $reason . "

";
            $message .= "Por favor, entre em contato com o solicitante para prosseguir com o processo de adoção.";

            $headers = "From: noreply@petmatch.com";

            if (mail($to, $subject, $message, $headers)) {
                $success_message = "Sua solicitação de adoção foi enviada com sucesso! O dono do pet entrará em contato em breve.";
            } else {
                $error_message = "Houve um problema ao enviar o e-mail. Por favor, tente novamente mais tarde.";
            }
        } else {
            $error_message = "Erro ao enviar a solicitação de adoção. Por favor, tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitação de Adoção - <?php echo $pet['name']; ?> - PetMatch</title>
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
                <li class="mb-2"><a href="logout.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-sign-out-alt mr-2"></i>Sair</a></li>
            </ul>
        </div>

        <!-- Conteúdo principal -->
        <div class="flex-1 p-10">
            <h2 class="text-3xl font-bold mb-6">Solicitação de Adoção - <?php echo $pet['name']; ?></h2>
            
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

            <form method="POST" action="" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="full_name">
                        Nome Completo
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="full_name" type="text" name="full_name" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" type="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">
                        Telefone
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" type="tel" name="phone" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                        Endereço
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" name="address" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="housing_situation">
                        Situação de Moradia
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="housing_situation" name="housing_situation" required>
                        <option value="">Selecione</option>
                        <option value="casa">Casa</option>
                        <option value="apartamento">Apartamento</option>
                        <option value="outro">Outro</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="experience">
                        Experiências com pets
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="experience" name="experience" required></textarea>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="reason">
                        Por que você quer adotar este pet?
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="reason" name="reason" required></textarea>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Enviar Solicitação de Adoção
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
