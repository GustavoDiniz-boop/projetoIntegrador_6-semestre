
<?php
session_start();
require_once 'db_connect.php';

// Verificar se o usuário está logado e é um admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Funções de administração

// Remover usuário e seus pets
function removeUser($conn, $user_id_to_remove) {
    $conn->begin_transaction();
    try {
        // Remover pets do usuário
        $delete_pets_query = "DELETE FROM Pets WHERE owner_id = ?";
        $stmt = $conn->prepare($delete_pets_query);
        $stmt->bind_param("i", $user_id_to_remove);
        $stmt->execute();

        // Remover o usuário
        $delete_user_query = "DELETE FROM Users WHERE id = ?";
        $stmt = $conn->prepare($delete_user_query);
        $stmt->bind_param("i", $user_id_to_remove);
        $stmt->execute();

        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        return false;
    }
}

// Remover pet
function removePet($conn, $pet_id) {
    $delete_pet_query = "DELETE FROM Pets WHERE id = ?";
    $stmt = $conn->prepare($delete_pet_query);
    $stmt->bind_param("i", $pet_id);
    return $stmt->execute();
}

// Gerenciar anúncios
function addAdvertisement($conn, $image_url, $link_url) {
    $insert_ad_query = "INSERT INTO Advertisements (image_url, link_url) VALUES (?, ?)";
    $stmt = $conn->prepare($insert_ad_query);
    $stmt->bind_param("ss", $image_url, $link_url);
    return $stmt->execute();
}

function removeAdvertisement($conn, $ad_id) {
    $delete_ad_query = "DELETE FROM Advertisements WHERE id = ?";
    $stmt = $conn->prepare($delete_ad_query);
    $stmt->bind_param("i", $ad_id);
    return $stmt->execute();
}

$success_message = '';
$error_message = '';

// Processar ações do admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['remove_user'])) {
        $user_id_to_remove = $_POST['user_id'];
        if (removeUser($conn, $user_id_to_remove)) {
            $success_message = "Usuário e seus pets removidos com sucesso.";
        } else {
            $error_message = "Erro ao remover o usuário.";
        }
    } elseif (isset($_POST['remove_pet'])) {
        $pet_id_to_remove = $_POST['pet_id'];
        if (removePet($conn, $pet_id_to_remove)) {
            $success_message = "Pet removido com sucesso.";
        } else {
            $error_message = "Erro ao remover o pet.";
        }
    } elseif (isset($_POST['add_advertisement'])) {
        $image_url = $_POST['image_url'];
        $link_url = $_POST['link_url'];
        if (addAdvertisement($conn, $image_url, $link_url)) {
            $success_message = "Anúncio adicionado com sucesso.";
        } else {
            $error_message = "Erro ao adicionar o anúncio.";
        }
    } elseif (isset($_POST['remove_advertisement'])) {
        $ad_id_to_remove = $_POST['ad_id'];
        if (removeAdvertisement($conn, $ad_id_to_remove)) {
            $success_message = "Anúncio removido com sucesso.";
        } else {
            $error_message = "Erro ao remover o anúncio.";
        }
    }
}

// Buscar usuários
$users_query = "SELECT id, username, email, role FROM Users WHERE id != ?";
$stmt = $conn->prepare($users_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$users_result = $stmt->get_result();

// Buscar pets
$pets_query = "SELECT p.id, p.name, p.species, u.username as owner_name FROM Pets p JOIN Users u ON p.owner_id = u.id";
$pets_result = $conn->query($pets_query);

// Buscar anúncios
$ads_query = "SELECT * FROM Advertisements";
$ads_result = $conn->query($ads_query);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PetMatch</title>
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
                <li class="mb-2"><a href="admin_dashboard.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-user-shield mr-2"></i>Admin Dashboard</a></li>
                <li class="mb-2"><a href="logout.php" class="block p-2 hover:bg-blue-700 rounded"><i class="fas fa-sign-out-alt mr-2"></i>Sair</a></li>
            </ul>
        </div>

        <!-- Conteúdo principal -->
        <div class="flex-1 p-10">
            <h2 class="text-3xl font-bold mb-6">Admin Dashboard</h2>
            
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

            <!-- Gerenciar Usuários -->
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4">Gerenciar Usuários</h3>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Username</th>
                            <th class="text-left">Email</th>
                            <th class="text-left">Role</th>
                            <th class="text-left">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $user['username']; ?></td>
                                <td><?php echo $user['email']; ?></td>
                                <td><?php echo $user['role']; ?></td>
                                <td>
                                    <form method="POST" action="" onsubmit="return confirm('Tem certeza que deseja remover este usuário e todos os seus pets?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="remove_user" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Gerenciar Pets -->
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4">Gerenciar Pets</h3>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Nome</th>
                            <th class="text-left">Espécie</th>
                            <th class="text-left">Dono</th>
                            <th class="text-left">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pet = $pets_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $pet['name']; ?></td>
                                <td><?php echo $pet['species']; ?></td>
                                <td><?php echo $pet['owner_name']; ?></td>
                                <td>
                                    <form method="POST" action="" onsubmit="return confirm('Tem certeza que deseja remover este pet?');">
                                        <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                        <button type="submit" name="remove_pet" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Gerenciar Anúncios -->
            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4">Gerenciar Anúncios</h3>
                <form method="POST" action="" class="mb-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="image_url">
                            URL da Imagem
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="image_url" type="url" name="image_url" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="link_url">
                            URL do Link
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="link_url" type="url" name="link_url" required>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="add_advertisement">
                            Adicionar Anúncio
                        </button>
                    </div>
                </form>
                <table class="w-full">
                    <thead>
                        <tr>
                            <th class="text-left">Imagem</th>
                            <th class="text-left">Link</th>
                            <th class="text-left">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ad = $ads_result->fetch_assoc()): ?>
                            <tr>
                                <td><img src="<?php echo $ad['image_url']; ?>" alt="Anúncio" class="w-20 h-20 object-cover"></td>
                                <td><a href="<?php echo $ad['link_url']; ?>" target="_blank"><?php echo $ad['link_url']; ?></a></td>
                                <td>
                                    <form method="POST" action="" onsubmit="return confirm('Tem certeza que deseja remover este anúncio?');">
                                        <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                        <button type="submit" name="remove_advertisement" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-xs">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
