<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Search functionality
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    $search_condition = "AND (p.name LIKE '%$search%' OR p.species LIKE '%$search%' OR p.breed LIKE '%$search%')";
}

// Fetch pets from the database
$sql = "SELECT p.*, COALESCE(pi.image_url, 'https://via.placeholder.com/300x200.png?text=No+Image') as image_url,
               u.username as owner_name
        FROM pets p 
        LEFT JOIN (
            SELECT pet_id, MIN(id) as min_id
            FROM PetImages
            GROUP BY pet_id
        ) pim ON p.id = pim.pet_id
        LEFT JOIN PetImages pi ON pim.pet_id = pi.pet_id AND pim.min_id = pi.id
        LEFT JOIN Users u ON p.owner_id = u.id
        WHERE p.status = 'available' $search_condition
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);

if (!$result) {
    die("Error fetching pets: " . $conn->error);
}

$pets = $result->fetch_all(MYSQLI_ASSOC);

// Count total pets for pagination
$count_sql = "SELECT COUNT(*) as total FROM pets p WHERE p.status = 'available' $search_condition";
$count_result = $conn->query($count_sql);
$total_pets = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_pets / $limit);

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
        <!-- Sidebar -->
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
                <span id="sidebarUsername" class="text-sm font-semibold"><?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="flex justify-between items-center p-4 bg-white border-b">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="text-gray-500 focus:outline-none md:hidden">
                        <i class="fas fa-bars"></i>
                    </button>
                </div>
                <form action="" method="GET" class="w-1/2">
                    <input type="search" name="search" placeholder="Buscar pets..." value="<?php echo htmlspecialchars($search); ?>" class="w-full px-4 py-2 rounded-full bg-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </form>
                <div></div>
            </header>

            <!-- Ad Space -->
            <div class="bg-white p-4 border-b">
                <div id="adSpace" class="h-40 bg-gray-200 flex items-center justify-center">
                    <p class="text-gray-500">Espaço para anúncios</p>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($pets as $pet): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-48 object-cover">
                            <div class="p-4">
                                <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($pet['name']); ?></h2>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($pet['species']); ?> - <?php echo htmlspecialchars($pet['breed']); ?></p>
                                <p class="text-gray-600 mb-2"><?php echo htmlspecialchars($pet['age']); ?> anos</p>
                                <p class="text-gray-600 mb-4">Dono: <?php echo htmlspecialchars($pet['owner_name']); ?></p>
                                <a href="pet_details.php?id=<?php echo $pet['id']; ?>" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Ver Detalhes</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <div class="mt-8 flex justify-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="mx-1 px-3 py-2 bg-white text-blue-500 rounded-md <?php echo $page === $i ? 'font-bold' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-translate-x-full');
        });
    </script>
</body>
</html>
