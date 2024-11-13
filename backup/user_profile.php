<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'update_profile') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    $update_sql = "UPDATE users SET full_name = ?, email = ?, phone_number = ?, address = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("ssssi", $full_name, $email, $phone_number, $address, $user_id);

    if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully!";
        // Refresh user data
        $user['full_name'] = $full_name;
        $user['email'] = $email;
        $user['phone_number'] = $phone_number;
        $user['address'] = $address;
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
}

// Handle pet insertion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_pet') {
    $pet_name = $_POST['pet_name'];
    $pet_species = $_POST['pet_species'];
    $pet_breed = $_POST['pet_breed'];
    $pet_age = $_POST['pet_age'];
    $pet_location = $_POST['pet_location'];
    $pet_description = $_POST['pet_description'];
    
    // Handle image upload (single image)
    $image_url = '';
    if (isset($_FILES['pet_images']) && $_FILES['pet_images']['error'][0] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["pet_images"]["name"][0]);
        if (move_uploaded_file($_FILES["pet_images"]["tmp_name"][0], $target_file)) {
            $image_url = $target_file;
        }
    }
    
    // Modify this query based on your actual database structure
    $insert_pet = "INSERT INTO pets (owner_id, name, species, breed, age, location, description) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_pet);
    $stmt->bind_param("isssiss", $user_id, $pet_name, $pet_species, $pet_breed, $pet_age, $pet_location, $pet_description);
    
    if ($stmt->execute()) {
        $success_message = "Pet adicionado com sucesso!";
    } else {
        $error_message = "Erro ao adicionar pet. Por favor, tente novamente.";
    }
}

// Fetch user data
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Fetch user's pets
$sql = "SELECT * FROM pets WHERE owner_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pets_result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - Perfil do Usuário</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <!-- Sidebar -->
        <div class="bg-blue-600 text-white w-64 min-h-screen flex flex-col">
            <div class="p-4">
                <h2 class="text-2xl font-semibold">PetMatch</h2>
            </div>
            <nav class="flex-1">
                <a href="tela_principal.php" class="block py-2 px-4 hover:bg-blue-700">Página Principal</a>
                <a href="user_profile.php" class="block py-2 px-4 hover:bg-blue-700">Perfil</a>
                <a href="settings.php" class="block py-2 px-4 hover:bg-blue-700">Configurações</a>
                <a href="logout.php" class="block py-2 px-4 hover:bg-blue-700">Sair</a>
            </nav>
            <div class="p-4">
                <img src="https://via.placeholder.com/50" alt="Profile" class="w-12 h-12 rounded-full inline-block">
                <span class="ml-2"><?php echo htmlspecialchars($user['username']); ?></span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-10">
            <h1 class="text-3xl font-bold mb-8">Perfil do Usuário</h1>
            
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Informações do Usuário</h2>
                <p><strong>Nome de Usuário:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Nome Completo:</strong> <?php echo htmlspecialchars($user['full_name']); ?></p>
                <p><strong>Telefone:</strong> <?php echo htmlspecialchars($user['phone_number']); ?></p>
                <p><strong>Endereço:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Atualizar Perfil</h2>
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_profile">
                    <div class="mb-4">
                        <label for="full_name" class="block text-gray-700 font-bold mb-2">Nome Completo</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="phone_number" class="block text-gray-700 font-bold mb-2">Telefone</label>
                        <input type="tel" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="address" class="block text-gray-700 font-bold mb-2">Endereço</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Atualizar Perfil</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold mb-4">Adicionar Novo Pet</h2>
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_pet">
                    <div class="mb-4">
                        <label for="pet_name" class="block text-gray-700 font-bold mb-2">Nome do Pet</label>
                        <input type="text" id="pet_name" name="pet_name" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="pet_species" class="block text-gray-700 font-bold mb-2">Espécie</label>
                        <input type="text" id="pet_species" name="pet_species" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="pet_breed" class="block text-gray-700 font-bold mb-2">Raça</label>
                        <input type="text" id="pet_breed" name="pet_breed" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="pet_age" class="block text-gray-700 font-bold mb-2">Idade</label>
                        <input type="number" id="pet_age" name="pet_age" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="pet_location" class="block text-gray-700 font-bold mb-2">Localização</label>
                        <input type="text" id="pet_location" name="pet_location" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="pet_description" class="block text-gray-700 font-bold mb-2">Descrição</label>
                        <textarea id="pet_description" name="pet_description" required class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="pet_images" class="block text-gray-700 font-bold mb-2">Imagens do Pet</label>
                        <input type="file" id="pet_images" name="pet_images[]" accept="image/*" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Adicionar Pet</button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-2xl font-bold mb-4">Seus Pets</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <?php while ($pet = $pets_result->fetch_assoc()): ?>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h3 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($pet['name']); ?></h3>
                            <p><strong>Espécie:</strong> <?php echo htmlspecialchars($pet['species']); ?></p>
                            <p><strong>Raça:</strong> <?php echo htmlspecialchars($pet['breed']); ?></p>
                            <p><strong>Idade:</strong> <?php echo htmlspecialchars($pet['age']); ?> anos</p>
                            <p><strong>Localização:</strong> <?php echo htmlspecialchars($pet['location']); ?></p>
                            <p><strong>Descrição:</strong> <?php echo htmlspecialchars($pet['description']); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
