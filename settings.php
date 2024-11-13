<?php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - Configurações</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div id="sidebar" class="bg-blue-600 text-white w-64 flex flex-col justify-between py-7 px-2 absolute inset-y-0 left-0 transform transition-all duration-300 ease-in-out" data-expanded="true">
            <div>
                <a href="#" class="text-white flex items-center space-x-2 px-4">
                    <i class="fas fa-paw text-2xl"></i>
                    <span class="text-2xl font-extrabold sidebar-text">PetMatch</span>
                </a>
                <nav class="mt-8">
                    <a href="tela_principal.html" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-home mr-2 text-xl"></i><span class="sidebar-text">Página Principal</span>
                    </a>
                    <a href="user_profile.html" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-user mr-2 text-xl"></i><span class="sidebar-text">Perfil</span>
                    </a>
                    <a href="settings.html" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-cog mr-2 text-xl"></i><span class="sidebar-text">Configurações</span>
                    </a>
                    <a href="login.html" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-blue-700 hover:text-white">
                        <i class="fas fa-sign-out-alt mr-2 text-xl"></i><span class="sidebar-text">Sair</span>
                    </a>
                </nav>
            </div>
            <div class="flex items-center space-x-4 mt-auto px-4 py-2">
                <img id="sidebarProfilePic" src="https://via.placeholder.com/40" alt="Profile" class="w-10 h-10 rounded-full object-cover">
                <span id="sidebarUsername" class="text-sm font-semibold sidebar-text">Username</span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top bar -->
            <header class="flex justify-between items-center p-4 bg-white border-b">
                <div class="flex items-center">
                    <button id="sidebarToggle" class="text-gray-500 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
                <h1 class="text-2xl font-semibold">Configurações</h1>
                <div></div> <!-- Placeholder for right side of header -->
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-200 p-6">
                <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Configurações da Conta</h2>
                    <form>
                        <div class="mb-4">
                            <label for="email-notifications" class="flex items-center">
                                <input type="checkbox" id="email-notifications" class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Receber notificações por e-mail</span>
                            </label>
                        </div>
                        <div class="mb-4">
                            <label for="privacy" class="block text-gray-700 font-bold mb-2">Privacidade do Perfil</label>
                            <select id="privacy" class="form-select w-full px-3 py-2 border rounded-lg">
                                <option>Público</option>
                                <option>Privado</option>
                                <option>Apenas Amigos</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="language" class="block text-gray-700 font-bold mb-2">Idioma</label>
                            <select id="language" class="form-select w-full px-3 py-2 border rounded-lg">
                                <option>Português</option>
                                <option>English</option>
                                <option>Español</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="theme" class="block text-gray-700 font-bold mb-2">Tema</label>
                            <select id="theme" class="form-select w-full px-3 py-2 border rounded-lg">
                                <option value="light">Claro</option>
                                <option value="dark">Escuro</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Salvar Alterações</button>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            // Here you would typically send the form data to your server
            alert('Configurações salvas com sucesso!');
        });
        // Toggle sidebar
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const content = document.querySelector('.flex-1');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');

        sidebarToggle.addEventListener('click', function() {
            const isExpanded = sidebar.dataset.expanded === 'true';
            sidebar.dataset.expanded = !isExpanded;
            sidebar.classList.toggle('w-64');
            sidebar.classList.toggle('w-16');
            content.classList.toggle('md:ml-64');
            content.classList.toggle('md:ml-16');
            sidebarTexts.forEach(text => text.classList.toggle('hidden'));
        });

        // Update sidebar user info
        document.addEventListener('DOMContentLoaded', function() {
            // This is a placeholder. In a real application, you would fetch the user data from your backend or local storage.
            const user = {
                name: "João Silva",
                profilePicture: "https://i.pravatar.cc/150?img=68"
            };
            document.getElementById('sidebarProfilePic').src = user.profilePicture;
            document.getElementById('sidebarUsername').textContent = user.name;
        });

        // Function to update user info (call this when user info changes)
        function updateUserInfo(name, profilePicture) {
            document.getElementById('sidebarProfilePic').src = profilePicture;
            document.getElementById('sidebarUsername').textContent = name;
        }
    </script>
</body>
</html>
