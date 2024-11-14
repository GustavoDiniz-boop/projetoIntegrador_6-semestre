
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
$user_query = "SELECT username FROM Users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();

// Configuração da paginação
$pets_per_page = 12;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $pets_per_page;

// Filtro de espécie
$species_filter = isset($_GET['species']) ? $_GET['species'] : '';
$species_condition = '';
if ($species_filter) {
    if ($species_filter === 'outros') {
        $species_condition = "AND species NOT IN ('Cachorro', 'Gato')";
    } else {
        $species_condition = "AND species = '$species_filter'";
    }
}

// Buscar pets disponíveis para adoção
$pets_query = "SELECT p.* FROM Pets p
               WHERE p.status = 'available' $species_condition
               LIMIT ? OFFSET ?";
$stmt = $conn->prepare($pets_query);
$stmt->bind_param("ii", $pets_per_page, $offset);
$stmt->execute();
$pets_result = $stmt->get_result();

// Contar o total de pets para a paginação
$count_query = "SELECT COUNT(*) as total FROM Pets WHERE status = 'available' $species_condition";
$count_result = $conn->query($count_query);
$total_pets = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_pets / $pets_per_page);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PetMatch - Página Principal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.theme.default.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="bg-blue-600 text-white w-64 flex flex-col">
            <div class="p-4">
                <h1 class="text-2xl font-bold mb-4">PetMatch</h1>
                <ul>
                    <li class="mb-2"><a href="tela_principal.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-home mr-2"></i>Página Principal</a></li>
                    <li class="mb-2"><a href="user_profile.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user mr-2"></i>Perfil</a></li>
                    <li class="mb-2"><a href="settings.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-cog mr-2"></i>Configurações</a></li>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?><li class="mb-2"><a href="admin_dashboard.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a><?php if ($_SESSION['user_role'] === 'admin'): ?><li class="mb-2"><a href="admin_dashboard.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a><?php endif; ?>
                    <li class="mb-2"><a href="logout.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-sign-out-alt mr-2"></i>Sair</a></li>
                </ul>
            </div>
            <div class="mt-auto p-4 border-t border-blue-500">
                <div class="flex items-center">
                    <img src="https://via.placeholder.com/40" alt="Foto de perfil" class="w-10 h-10 rounded-full mr-2">
                    <span><?php echo $user['username']; ?></span>
                </div>
            </div>
        </div>

        <!-- Conteúdo principal -->
        <div class="flex-1 overflow-y-auto">
            <div class="p-6">
                <h2 class="text-3xl font-bold mb-6">Pets Disponíveis para Adoção</h2>

                <!-- Filtros e Anúncios -->
                <div class="bg-white rounded-lg shadow-md p-4 mb-6">
                    <div class="mb-4">
                        <h3 class="text-xl font-bold mb-2">Filtrar por espécie:</h3>
                        <div class="flex flex-wrap gap-2">
                            <a href="?species=Cachorro" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Cachorros</a>
                            <a href="?species=Gato" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Gatos</a>
                            <a href="?species=outros" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Outros Animais</a>
                            <a href="?" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Limpar Filtros</a>
                        </div>
                    </div>

                    <!-- Carrossel de anúncios -->
                    <div class="owl-carousel owl-theme">
                        <div class="item"><img src="https://via.placeholder.com/800x150?text=Anúncio+1" alt="Anúncio 1" class="w-full h-24 object-cover rounded"></div>
                        <div class="item"><img src="https://via.placeholder.com/800x150?text=Anúncio+2" alt="Anúncio 2" class="w-full h-24 object-cover rounded"></div>
                        <div class="item"><img src="https://via.placeholder.com/800x150?text=Anúncio+3" alt="Anúncio 3" class="w-full h-24 object-cover rounded"></div>
                    </div>
                </div>

                <!-- Grid de pets -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php while ($pet = $pets_result->fetch_assoc()): ?>
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'https://via.placeholder.com/300x200?text=' . $pet['name']; ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full h-40 object-cover">
                            <div class="p-4">
                                <h3 class="text-lg font-bold mb-2"><?php echo htmlspecialchars($pet['name']); ?></h3>
                                <p class="text-gray-600 text-sm mb-2"><?php echo htmlspecialchars($pet['species']); ?>, <?php echo $pet['age']; ?> anos</p>
                                <a href="tela_rolagem.php?pet_id=<?php echo $pet['id']; ?>" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm block text-center">Ver Detalhes</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <!-- Paginação -->
                <div class="mt-6 flex justify-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo $species_filter ? '&species=' . $species_filter : ''; ?>" class="mx-1 px-3 py-2 bg-blue-500 text-white rounded hover:bg-blue-700">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function(){
            $(".owl-carousel").owlCarousel({
                items: 1,
                loop: true,
                autoplay: true,
                autoplayTimeout: 5000,
                autoplayHoverPause: true
            });
        });
    </script>
</body>
</html>
