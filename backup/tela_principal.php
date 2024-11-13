<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch pets from the database

// Check if PetImages table exists
$check_table_sql = "SHOW TABLES LIKE 'PetImages'";
$table_exists = $conn->query($check_table_sql)->num_rows > 0;

if ($table_exists) {
    $sql = "SELECT p.*, COALESCE(pi.image_url, 'https://via.placeholder.com/300x200.png?text=No+Image') as image_url 
            FROM pets p 
            LEFT JOIN (
                SELECT pet_id, MIN(id) as min_id
                FROM PetImages
                GROUP BY pet_id
            ) pim ON p.id = pim.pet_id
            LEFT JOIN PetImages pi ON pim.pet_id = pi.pet_id AND pim.min_id = pi.id
            ORDER BY p.created_at DESC LIMIT 10";
} else {
    $sql = "SELECT *, 'https://via.placeholder.com/300x200.png?text=No+Image' as image_url 
            FROM pets 
            ORDER BY created_at DESC LIMIT 10";
}

$result = $conn->query($sql);
$pets = $result->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - Página Principal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar (keep your existing sidebar code here) -->
        <div id="sidebar" class="bg-blue-600 text-white w-64 flex flex-col justify-between py-7 px-2 absolute inset-y-0 left-0 transform -translate-x-full md:relative md:translate-x-0 transition duration-300 ease-in-out">
            <div>
                <a href="#" class="text-white flex items-center space-x-2 px-4">
                    <i class="fas fa-paw"></i>
                    <span class="text-2xl font-extrabold">PetMatch</span>
                </a>
                <nav class="mt-8">
                    <a href="tela_principal.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-home mr-2"></i>Página Principal
                    </a>
                    <a href="user_profile.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-user mr-2"></i>Perfil
                    </a>
                    <a href="settings.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-cog mr-2"></i>Configurações
                    </a>
                    <a href="logout.php" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-sign-out-alt mr-2"></i>Sair
                    </a>
                </nav>
            </div>
            <div class="flex items-center space-x-4 mt-auto px-4 py-2">
                <img id="sidebarProfilePic" src="https://via.placeholder.com/40" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                <span id="sidebarUsername" class="text-sm font-semibold">Username</span>
            </div>
        </div>
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden ml-64">
            <!-- Top bar (keep your existing top bar code here) -->
            <header class="flex justify-between items-center p-4 bg-white border-b">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="text-gray-500 focus:outline-none md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <div class="w-1/2">
                    <input type="search" placeholder="Buscar pets..." class="w-full px-4 py-2 rounded-full bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
                <div></div> <!-- Placeholder for right side of header -->
            </header>

            <!-- Ad Space -->
            <div class="bg-white p-4 border-b">
                <div id="adSpace" class="h-40 bg-gray-200 flex items-center justify-center">
                    <p class="text-gray-500">Espaço para anúncios</p>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-4">
                <h1 class="text-3xl font-semibold mb-6">Pets Disponíveis para Adoção</h1>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($pets as $pet): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden cursor-pointer transition duration-300 ease-in-out transform hover:scale-105" onclick="window.location.href='tela_rolagem.php?id=<?php echo $pet['id']; ?>'">
                            <img src="<?php echo htmlspecialchars($pet['image_url'] ?? 'https://via.placeholder.com/300x200.png?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h2 class="font-bold text-xl mb-2"><?php echo htmlspecialchars($pet['name']); ?></h2>
                                <p class="text-gray-700 text-base mb-1"><?php echo htmlspecialchars($pet['age']); ?> • <?php echo htmlspecialchars($pet['breed']); ?></p>
                                <p class="text-gray-600 text-sm"><i class="fas fa-map-marker-alt mr-1"></i><?php echo htmlspecialchars($pet['location']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Keep your existing JavaScript code here
    </script>
</body>
</html>
