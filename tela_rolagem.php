<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if pet ID is provided
if (!isset($_GET['id'])) {
    header("Location: tela_principal.php");
    exit();
}

$pet_id = $_GET['id'];

// Fetch pet details from the database

$sql = "SELECT p.*, pi.image_url 
        FROM pets p 
        LEFT JOIN pet_images pi ON p.id = pi.pet_id
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();

$pet = null;
$pet_images = array();

while ($row = $result->fetch_assoc()) {
    if (!$pet) {
        $pet = $row;
        unset($pet['image_url']);
    }
    if ($row['image_url']) {
        $pet_images[] = $row['image_url'];
    }
}

if (!$pet) {
    header("Location: tela_principal.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - <?php echo htmlspecialchars($pet['name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar (keep your existing sidebar code here) -->

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden ml-64">
            <!-- Top bar (keep your existing top bar code here) -->

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-4">
                <div id="pet-card" class="bg-white rounded-lg shadow-lg max-w-md w-full mx-auto">
                    <div class="relative">
                        <div class="carousel relative">
                            <?php foreach ($pet_images as $index => $image): ?>
                                <img id="pet-image-<?php echo $index; ?>" src="<?php echo htmlspecialchars($image['image_url']); ?>" alt="<?php echo htmlspecialchars($pet['name']); ?>" class="w-full rounded-t-lg h-64 object-cover <?php echo $index === 0 ? '' : 'hidden'; ?>">
                            <?php endforeach; ?>
                            <button class="absolute top-1/2 left-2 transform -translate-y-1/2 bg-white bg-opacity-50 rounded-full p-2" onclick="changeImage(-1)">
                                <i class="fas fa-chevron-left text-gray-800"></i>
                            </button>
                            <button class="absolute top-1/2 right-2 transform -translate-y-1/2 bg-white bg-opacity-50 rounded-full p-2" onclick="changeImage(1)">
                                <i class="fas fa-chevron-right text-gray-800"></i>
                            </button>
                        </div>
                        <div class="absolute top-0 left-0 p-4">
                            <i class="fas fa-chevron-left text-white text-2xl cursor-pointer" onclick="goBack()"></i>
                        </div>
                        <div class="absolute top-0 right-0 p-4">
                            <i class="fas fa-ellipsis-v text-white text-2xl cursor-pointer" onclick="toggleMenu()"></i>
                        </div>
                        <div id="menu" class="hidden absolute top-12 right-4 bg-white rounded-lg shadow-lg p-2">
                            <button class="block w-full text-left px-4 py-2 hover:bg-gray-100" onclick="reportPet()">Report Pet</button>
                        </div>
                    </div>
                    <div class="p-4">
                        <h2 class="text-blue-600 text-lg font-semibold"><?php echo htmlspecialchars($pet['name']); ?>, <?php echo htmlspecialchars($pet['age']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($pet['breed']); ?></p>
                        <p class="text-gray-500"><?php echo htmlspecialchars($pet['location']); ?></p>
                        <hr class="my-4"/>
                        <p class="text-gray-600"><?php echo htmlspecialchars($pet['description']); ?></p>
                    </div>
                    <div class="flex justify-around p-4">
                        <button class="bg-red-100 p-4 rounded-full" onclick="swipeLeft()">
                            <i class="fas fa-times text-red-500 text-2xl"></i>
                        </button>
                        <button class="bg-green-100 p-4 rounded-full" onclick="swipeRight()">
                            <i class="fas fa-paw text-green-500 text-2xl"></i>
                        </button>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        let currentImageIndex = 0;
        const totalImages = <?php echo count($pet_images); ?>;

        function changeImage(direction) {
            currentImageIndex = (currentImageIndex + direction + totalImages) % totalImages;
            updateImage();
        }

        function updateImage() {
            for (let i = 0; i < totalImages; i++) {
                document.getElementById(`pet-image-${i}`).classList.add('hidden');
            }
            document.getElementById(`pet-image-${currentImageIndex}`).classList.remove('hidden');
        }

        function goBack() {
            window.history.back();
        }

        function toggleMenu() {
            const menu = document.getElementById('menu');
            menu.classList.toggle('hidden');
        }

        function reportPet() {
            window.location.href = `report_pet.php?petId=<?php echo $pet_id; ?>`;
        }

        function swipeLeft() {
            // Implement swipe left functionality (e.g., skip pet)
            window.location.href = 'tela_principal.php';
        }

        function swipeRight() {
            Swal.fire({
                title: 'Adotar',
                text: 'Você tem certeza que deseja adotar este pet?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, adotar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Implement adoption process (e.g., redirect to adoption form)
                    window.location.href = `adoption_application.php?petId=<?php echo $pet_id; ?>`;
                }
            });
        }
    </script>
</body>
</html>
