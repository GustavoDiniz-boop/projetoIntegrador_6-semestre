
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

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $reason = $_POST['reason'];
    $description = $_POST['description'];

    // Inserir o relatório no banco de dados
    $insert_query = "INSERT INTO PetReports (pet_id, reporter_id, reason, description, status) VALUES (?, ?, ?, ?, 'pending')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiss", $pet_id, $user_id, $reason, $description);
    
    if ($stmt->execute()) {
        $success_message = "Pet reportado com sucesso. Obrigado por nos informar.";
    } else {
        $error_message = "Erro ao reportar o pet. Por favor, tente novamente.";
    }
}

// Buscar informações do pet
$pet_query = "SELECT name FROM Pets WHERE id = ?";
$stmt = $conn->prepare($pet_query);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$pet_result = $stmt->get_result();
$pet = $pet_result->fetch_assoc();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportar Pet - <?php echo $pet['name']; ?> - PetMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Reportar Pet - <?php echo $pet['name']; ?></h2>
                    <a href="tela_rolagem.php?pet_id=<?php echo $pet_id; ?>" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                </div>
                
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

                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="reason">
                            Motivo do Reporte
                        </label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="reason" name="reason" required>
                            <option value="">Selecione um motivo</option>
                            <option value="Informações incorretas">Informações incorretas</option>
                            <option value="Conteúdo inapropriado">Conteúdo inapropriado</option>
                            <option value="Suspeita de fraude">Suspeita de fraude</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                            Descrição detalhada
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="4" required></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Enviar Reporte
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
