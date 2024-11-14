
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

// Buscar informações do pet
$pet_query = "SELECT p.*, u.username as owner_name
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

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pet['name']; ?> - PetMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="md:flex">
                <div class="md:flex-shrink-0">
                    <?php if ($pet['image_url']): ?>
                        <img class="h-48 w-full object-cover md:w-48" src="<?php echo $pet['image_url']; ?>" alt="<?php echo $pet['name']; ?>">
                    <?php else: ?>
                        <div class="h-48 w-full md:w-48 bg-gray-300 flex items-center justify-center">
                            <span class="text-gray-500 text-lg">Sem imagem</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="p-8">
                    <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold"><?php echo $pet['species']; ?></div>
                    <h2 class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl"><?php echo $pet['name']; ?></h2>
                    <p class="mt-2 text-gray-500"><?php echo $pet['age']; ?> anos</p>
                    <p class="mt-4 text-gray-500">Dono: <?php echo $pet['owner_name']; ?></p>
                </div>
            </div>
            <div class="px-8 py-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Descrição</h3>
                <p class="mt-2 text-gray-600"><?php echo $pet['description']; ?></p>
            </div>
            <div class="px-8 py-6 bg-gray-50 flex justify-between items-center">
                <a href="tela_principal.php" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-2"></i>Voltar
                </a>
                <div>
                    <button onclick="skipPet()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mr-2">
                        <i class="fas fa-times mr-2"></i>Pular
                    </button>
                    <a href="adoption_application.php?pet_id=<?php echo $pet_id; ?>" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mr-2">
                        <i class="fas fa-paw mr-2"></i>Adotar
                    </a>
                    <button onclick="reportPet(<?php echo $pet_id; ?>)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        <i class="fas fa-flag mr-2"></i>Reportar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function reportPet(petId) {
            if (confirm("Tem certeza que deseja reportar este pet?")) {
                // Aqui você pode implementar a lógica para reportar o pet
                alert("Pet reportado com sucesso!");
            }
        }
    </script>
</body>
</html>
