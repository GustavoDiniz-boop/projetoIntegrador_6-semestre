// Simulação de dados de pets
const petsData = {
    gato: [
        { nome: 'Miau', idade: 2, sexo: 'Macho', localizacao: 'São Paulo - SP', imagem: 'https://placehold.co/300x200?text=Gato' },
        { nome: 'Fiona', idade: 1, sexo: 'Fêmea', localizacao: 'Campinas - SP', imagem: 'https://placehold.co/300x200?text=Gato' }
    ],
    cachorro: [
        { nome: 'Rex', idade: 3, sexo: 'Macho', localizacao: 'São Paulo - SP', imagem: 'https://placehold.co/300x200?text=Cachorro' },
        { nome: 'Estrela', idade: 4, sexo: 'Fêmea', localizacao: 'Campinas - SP', imagem: 'https://placehold.co/300x200?text=Cachorro' }
    ],
    coelho: [
        { nome: 'Bunny', idade: 2, sexo: 'Macho', localizacao: 'São Paulo - SP', imagem: 'https://placehold.co/300x200?text=Coelho' },
        { nome: 'Coco', idade: 1, sexo: 'Fêmea', localizacao: 'Campinas - SP', imagem: 'https://placehold.co/300x200?text=Coelho' }
    ]
};

// Variável para armazenar a categoria atual
let currentCategory = 'gato';

// Função para atualizar a lista de pets
function updatePetsList(categoria) {
    const petsList = document.getElementById('pets-list');
    petsList.innerHTML = ''; // Limpa a lista anterior

    const pets = petsData[categoria];
    if (pets) {
        pets.forEach(pet => {
            const petCard = `
                <div class="bg-white rounded-lg shadow p-4">
                    <img alt="Imagem de um pet" class="w-full h-40 object-cover rounded-lg mb-2" src="${pet.imagem}"/>
                    <h3 class="text-blue-700 font-bold">${pet.nome}, ${pet.idade} anos</h3>
                    <p>${pet.sexo}</p>
                    <p>${pet.localizacao}</p>
                </div>
            `;
            petsList.innerHTML += petCard; // Adiciona o cartão do pet à lista
        });
    }
}

// Função para filtrar pets pela pesquisa
function filterPets(searchTerm) {
    const petsList = document.getElementById('pets-list');
    petsList.innerHTML = ''; // Limpa a lista anterior

    const pets = petsData[currentCategory]; // Obtém os pets da categoria atual
    if (pets) {
        const filteredPets = pets.filter(pet => pet.nome.toLowerCase().includes(searchTerm.toLowerCase()));
        filteredPets.forEach(pet => {
            const petCard = `
                <div class="bg-white rounded-lg shadow p-4">
                    <img alt="Imagem de um pet" class="w-full h-40 object-cover rounded-lg mb-2" src="${pet.imagem}"/>
                    <h3 class="text-blue-700 font-bold">${pet.nome}, ${pet.idade} anos</h3>
                    <p>${pet.sexo}</p>
                    <p>${pet.localizacao}</p>
                </div>
            `;
            petsList.innerHTML += petCard; // Adiciona o cartão do pet à lista
        });
    }
}

// Função para adicionar/remover classe ativa
function setActiveButton(button) {
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => btn.classList.remove('bg-gray-200', 'font-bold'));
    button.classList.add('bg-gray-200', 'font-bold');
}

// Adiciona eventos de clique aos botões
document.getElementById('btn-gato').addEventListener('click', (event) => {
    currentCategory = 'gato';
    updatePetsList(currentCategory);
    setActiveButton(event.currentTarget); // Adiciona a classe ativa
});
document.getElementById('btn-cachorro').addEventListener('click', (event) => {
    currentCategory = 'cachorro';
    updatePetsList(currentCategory);
    setActiveButton(event.currentTarget);
});
document.getElementById('btn-coelho').addEventListener('click', (event) => {
    currentCategory = 'coelho';
    updatePetsList(currentCategory);
    setActiveButton(event.currentTarget);
});

// Inicializa a lista com a primeira categoria
updatePetsList(currentCategory);

// Adiciona evento de input à barra de pesquisa
const searchInput = document.querySelector('input[placeholder="Search..."]');
searchInput.addEventListener('input', (event) => {
    const searchTerm = event.target.value;
    filterPets(searchTerm);
});