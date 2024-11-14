
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

// Buscar pets do usuário
$pets_query = "SELECT * FROM Pets WHERE owner_id = ?";
$stmt = $conn->prepare($pets_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$pets_result = $stmt->get_result();

$success_message = '';
$error_message = '';

// Processar formulário de atualização de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $address = $_POST['address'];

    $update_query = "UPDATE Users SET full_name = ?, email = ?, phone_number = ?, address = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssi", $full_name, $email, $phone_number, $address, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Perfil atualizado com sucesso!";
        // Atualizar as informações do usuário na sessão
        $user['full_name'] = $full_name;
        $user['email'] = $email;
        $user['phone_number'] = $phone_number;
        $user['address'] = $address;
    } else {
        $error_message = "Erro ao atualizar o perfil. Tente novamente.";
    }
}

// Processar formulário de adição de pet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $pet_name = $_POST['pet_name'];
    $pet_species = $_POST['pet_species'];
    $pet_age = $_POST['pet_age'];
    $pet_description = $_POST['pet_description'];

// Processar o upload da imagem (se fornecida)
    $image_url = null;
    if ($_FILES["pet_image"]["size"] > 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["pet_image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

        // Verificar se o arquivo é uma imagem real
        $check = getimagesize($_FILES["pet_image"]["tmp_name"]);
        if($check === false) {
            $error_message = "O arquivo não é uma imagem.";
            $uploadOk = 0;
        }

        // Verificar o tamanho do arquivo
        if ($_FILES["pet_image"]["size"] > 500000) {
            $error_message = "Desculpe, seu arquivo é muito grande.";
            $uploadOk = 0;
        }

        // Permitir apenas certos formatos de arquivo
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            $error_message = "Desculpe, apenas arquivos JPG, JPEG, PNG e GIF são permitidos.";
            $uploadOk = 0;
        }

        // Se tudo estiver ok, tenta fazer o upload
        if ($uploadOk == 1) {
            if (move_uploaded_file($_FILES["pet_image"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            } else {
                $error_message = "Desculpe, houve um erro ao fazer o upload do arquivo.";
            }
        }
    }

    // Adicionar o pet ao banco de dados
    if (empty($error_message)) {
        $add_pet_query = "INSERT INTO Pets (owner_id, name, species, age, description, image_url) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($add_pet_query);
        $stmt->bind_param("ississ", $user_id, $pet_name, $pet_species, $pet_age, $pet_description, $image_url);
        
        if ($stmt->execute()) {
            $success_message = "Pet adicionado com sucesso!";
            // Atualizar a lista de pets
            $stmt = $conn->prepare($pets_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $pets_result = $stmt->get_result();
        } else {
            $error_message = "Erro ao adicionar o pet. Tente novamente.";
        }
    }
}

// Processar remoção de pet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_pet'])) {
    $pet_id = $_POST['pet_id'];
    
    $remove_pet_query = "DELETE FROM Pets WHERE id = ? AND owner_id = ?";
    $stmt = $conn->prepare($remove_pet_query);
    $stmt->bind_param("ii", $pet_id, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Pet removido com sucesso!";
        // Atualizar a lista de pets
        $stmt = $conn->prepare($pets_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $pets_result = $stmt->get_result();
    } else {
        $error_message = "Erro ao remover o pet. Tente novamente.";
    }
}

// Processar edição de pet
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_pet'])) {
    $pet_id = $_POST['pet_id'];
    $pet_name = $_POST['pet_name'];
    $pet_species = $_POST['pet_species'];
    $pet_age = $_POST['pet_age'];
    $pet_description = $_POST['pet_description'];
    
    $edit_pet_query = "UPDATE Pets SET name = ?, species = ?, age = ?, description = ? WHERE id = ? AND owner_id = ?";
    $stmt = $conn->prepare($edit_pet_query);
    $stmt->bind_param("ssissi", $pet_name, $pet_species, $pet_age, $pet_description, $pet_id, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Pet atualizado com sucesso!";
        // Atualizar a lista de pets
        $stmt = $conn->prepare($pets_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $pets_result = $stmt->get_result();
    } else {
        $error_message = "Erro ao atualizar o pet. Tente novamente.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário - PetMatch</title>
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
    </li>
            </ul>
            <div class="mt-auto pt-4">
                <div class="flex items-center">
                    <img src="https://via.placeholder.com/40" alt="Foto de perfil" class="w-10 h-10 rounded-full mr-2">
                    <span><?php echo $user['username']; ?></span>
                </div>
            </div>
        </div>

        <!-- Conteúdo principal -->
        <div class="flex-1 p-10">
            <h2 class="text-3xl font-bold mb-6">Perfil do Usuário</h2>
            
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
                <h3 class="text-xl font-bold mb-4">Informações do Perfil</h3>
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
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="phone_number">
                            Telefone
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone_number" type="tel" name="phone_number" value="<?php echo $user['phone_number']; ?>">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="address">
                            Endereço
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="address" name="address"><?php echo $user['address']; ?></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="update_profile">
                            Atualizar Perfil
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4">Meus Pets para Adoção</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php while ($pet = $pets_result->fetch_assoc()): ?>
                        <div class="bg-gray-100 p-4 rounded-lg">
                            <h4 class="font-bold"><?php echo $pet['name']; ?></h4>
                            <p>Espécie: <?php echo $pet['species']; ?></p>
                            <p>Idade: <?php echo $pet['age']; ?> anos</p>
                            <p>Descrição: <?php echo $pet['description']; ?></p>
                            <?php if (isset($pet['image_url']) && !empty($pet['image_url'])): ?>
                                <img src="<?php echo $pet['image_url']; ?>" alt="<?php echo $pet['name']; ?>" class="w-full h-32 object-cover mt-2 rounded">
                            <?php else: ?>
                                <div class="w-full h-32 bg-gray-200 flex items-center justify-center mt-2 rounded">
                                    <span class="text-gray-500">Sem imagem</span>
                                </div>
                            <?php endif; ?>
                            <div class="mt-2">
                                <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($pet)); ?>)" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-1 px-2 rounded text-sm">Editar</button>
                                <form method="POST" action="" class="inline">
                                    <input type="hidden" name="pet_id" value="<?php echo $pet['id']; ?>">
                                    <button type="submit" name="remove_pet" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded text-sm" onclick="return confirm('Tem certeza que deseja remover este pet?')">Remover</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <h4 class="text-lg font-bold mt-6 mb-4">Adicionar Novo Pet</h4>
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="pet_name">
                            Nome do Pet
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="pet_name" type="text" name="pet_name" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="pet_species">
                            Espécie
                        </label>
                        <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="pet_species" name="pet_species" required>
                            <option value="Cachorro">Cachorro</option>
                            <option value="Gato">Gato</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="pet_age">
                            Idade
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="pet_age" type="number" name="pet_age" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="pet_description">
                            Descrição
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="pet_description" name="pet_description" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="pet_image">
                            Imagem do Pet
                        </label>
                        <input type="file" name="pet_image" id="pet_image" accept="image/*">
                    </div>
                    <div class="flex items-center justify-between">
                        <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit" name="add_pet">
                            Adicionar Pet
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h3 class="text-xl font-bold mb-4 text-red-600">Excluir Conta</h3>
                <p class="mb-4">Atenção: Esta ação é irreversível e todos os seus dados serão perdidos.</p>
                <button class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="confirmDeleteAccount()">
                    Excluir minha conta
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de edição de pet -->
    <div id="editPetModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="editPetForm" method="POST" action="">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">Editar Pet</h3>
                        <input type="hidden" name="pet_id" id="edit_pet_id">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_pet_name">Nome do Pet</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="edit_pet_name" type="text" name="pet_name" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_pet_species">Espécie</label>
                            <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="edit_pet_species" name="pet_species" required>
                                <option value="Cachorro">Cachorro</option>
                                <option value="Gato">Gato</option>
                                <option value="Outro">Outro</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_pet_age">Idade</label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="edit_pet_age" type="number" name="pet_age" required>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="edit_pet_description">Descrição</label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="edit_pet_description" name="pet_description" required></textarea>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" name="edit_pet" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Salvar Alterações
                        </button>
                        <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    function confirmDeleteAccount() {
        if (confirm("Tem certeza que deseja excluir sua conta? Esta ação é irreversível.")) {
            // Aqui você pode adicionar a lógica para excluir a conta
            // Por exemplo, redirecionar para uma página de exclusão de conta
            window.location.href = "delete_account.php";
        }
    }

    function openEditModal(pet) {
        document.getElementById('edit_pet_id').value = pet.id;
        document.getElementById('edit_pet_name').value = pet.name;
        document.getElementById('edit_pet_species').value = pet.species;
        document.getElementById('edit_pet_age').value = pet.age;
        document.getElementById('edit_pet_description').value = pet.description;
        document.getElementById('editPetModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editPetModal').classList.add('hidden');
    }
    </script>
</body>
</html>
