<?php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PetMatch - Formulário de Adoção</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <button onclick="goBack()" class="bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                <i class="fas fa-arrow-left mr-2"></i>Voltar
            </button>
            <h1 class="text-3xl font-bold text-center">Formulário de Adoção</h1>
            <div class="w-24"></div> <!-- Placeholder for alignment -->
        </div>
        <div id="petInfo" class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto mb-8">
            <!-- Pet information will be inserted here -->
        </div>
        <div class="bg-white rounded-lg shadow-md p-6 max-w-2xl mx-auto">
            <form id="adoptionForm">
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-bold mb-2">Nome Completo</label>
                    <input type="text" id="name" name="name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                    <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="phone" class="block text-gray-700 font-bold mb-2">Telefone</label>
                    <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-gray-700 font-bold mb-2">Endereço</label>
                    <textarea id="address" name="address" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="living-situation" class="block text-gray-700 font-bold mb-2">Situação de Moradia</label>
                    <select id="living-situation" name="living-situation" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <option value="">Selecione uma opção</option>
                        <option value="house">Casa</option>
                        <option value="apartment">Apartamento</option>
                        <option value="other">Outro</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="experience" class="block text-gray-700 font-bold mb-2">Experiência com Pets</label>
                    <textarea id="experience" name="experience" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <div class="mb-4">
                    <label for="reason" class="block text-gray-700 font-bold mb-2">Por que você quer adotar este pet?</label>
                    <textarea id="reason" name="reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Enviar Solicitação de Adoção
                </button>
            </form>
        </div>
    </div>

    <script>
        // Sample pet data (replace with actual data fetching in a real application)
        const pets = [
            { id: 0, name: "Laika", breed: "Samoyeda", age: "2 meses", image: "https://placedog.net/500/280?id=1" },
            { id: 1, name: "Max", breed: "Labrador", age: "1 ano", image: "https://placedog.net/500/280?id=2" },
            { id: 2, name: "Luna", breed: "Siamês", age: "3 meses", image: "https://placekitten.com/500/280?image=1" }
        ];

        // Mock user data (replace with actual user authentication in a real application)
        const currentUser = {
            name: "João Silva",
            email: "joao.silva@email.com",
            phone: "11987654321",
            address: "Rua das Flores, 123, São Paulo - SP",
        };

        function displayPetInfo(pet) {
            const petInfoDiv = document.getElementById('petInfo');
            petInfoDiv.innerHTML = `
                <div class="flex items-center">
                    <img src="${pet.image}" alt="${pet.name}" class="w-24 h-24 rounded-full object-cover mr-4">
                    <div>
                        <h2 class="text-2xl font-bold">${pet.name}</h2>
                        <p class="text-gray-600">${pet.breed}, ${pet.age}</p>
                    </div>
                </div>
            `;
        }

        function prefillForm() {
            document.getElementById('name').value = currentUser.name;
            document.getElementById('email').value = currentUser.email;
            document.getElementById('phone').value = currentUser.phone;
            document.getElementById('address').value = currentUser.address;
        }

        document.getElementById('adoptionForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (validateForm()) {
                const submitButton = this.querySelector('button[type="submit"]');
                const originalButtonText = submitButton.innerHTML;
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Enviando...';

                try {
                    const formData = new FormData(this);
                    const formObject = Object.fromEntries(formData.entries());
                    const response = await submitAdoptionApplication(formObject);

                    if (response.success) {
                        showFeedback('success', 'Sua solicitação de adoção foi enviada com sucesso! Entraremos em contato em breve.');
                        setTimeout(() => {
                            window.location.href = 'tela_principal.html';
                        }, 3000);
                    } else {
                        throw new Error(response.message || 'Falha ao enviar a solicitação');
                    }
                } catch (error) {
                    showFeedback('error', `Erro ao enviar a solicitação: ${error.message}`);
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            }
        });

        function showFeedback(type, message) {
            const feedbackDiv = document.createElement('div');
            feedbackDiv.className = `fixed top-0 left-0 right-0 p-4 ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white text-center`;
            feedbackDiv.textContent = message;
            document.body.appendChild(feedbackDiv);
            setTimeout(() => {
                feedbackDiv.remove();
            }, 5000);
        }

        function validateForm() {
            const fields = [
                { id: 'name', label: 'Nome Completo', validator: (value) => value.trim() !== '' },
                { id: 'email', label: 'Email', validator: isValidEmail },
                { id: 'phone', label: 'Telefone', validator: isValidPhone },
                { id: 'address', label: 'Endereço', validator: (value) => value.trim() !== '' },
                { id: 'living-situation', label: 'Situação de Moradia', validator: (value) => value !== '' },
                { id: 'experience', label: 'Experiência com Pets', validator: (value) => value.trim() !== '' },
                { id: 'reason', label: 'Razão para Adoção', validator: (value) => value.trim() !== '' }
            ];

            let isValid = true;
            fields.forEach(field => {
                const element = document.getElementById(field.id);
                const errorElement = document.getElementById(`${field.id}-error`);
                if (!field.validator(element.value)) {
                    isValid = false;
                    element.classList.add('border-red-500');
                    errorElement.textContent = `Por favor, preencha ${field.label} corretamente.`;
                    errorElement.classList.remove('hidden');
                } else {
                    element.classList.remove('border-red-500');
                    errorElement.classList.add('hidden');
                }
            });

            return isValid;
        }

        function isValidEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function isValidPhone(phone) {
            const re = /^[0-9]{10,11}$/;
            return re.test(phone.replace(/\D/g, ''));
        }

        function goBack() {
            window.history.back();
        }

        // Get the pet ID from the URL query parameter and display pet info
        const urlParams = new URLSearchParams(window.location.search);
        const petId = parseInt(urlParams.get('petId'));
        console.log('Adoption application for pet ID:', petId);

        const selectedPet = pets.find(pet => pet.id === petId);
        if (selectedPet) {
            displayPetInfo(selectedPet);
            prefillForm(); // Pre-fill the form with user data
        } else {
            console.error('Pet not found');
            document.getElementById('petInfo').innerHTML = '<p class="text-red-500">Pet não encontrado. Por favor, volte e tente novamente.</p>';
        }

        // Simulating API interaction
        async function submitAdoptionApplication(formData) {
            // In a real application, this would be an API call
            return new Promise((resolve, reject) => {
                setTimeout(() => {
                    console.log('Adoption application submitted to API:', formData);
                    // Simulating a 90% success rate
                    if (Math.random() < 0.9) {
                        resolve({ success: true, message: 'Application submitted successfully' });
                    } else {
                        reject(new Error('Failed to submit application. Please try again.'));
                    }
                }, 2000); // Simulating network delay
            });
        }
    </script>
</body>
</html>
