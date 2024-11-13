<?php
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Reportar Pet - PetMatch</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h1 class="text-2xl font-bold mb-4">Reportar Pet</h1>
        <p class="mb-4">Por favor, selecione o motivo do seu relatório:</p>
        <form id="reportForm">
            <div class="space-y-4">
                <div>
                    <input type="radio" id="misleading" name="reportReason" value="misleading" class="mr-2">
                    <label for="misleading">Conteúdo Enganoso</label>
                    <p class="text-sm text-gray-600 ml-6">Imagens ou descrições que não correspondem ao perfil do pet.</p>
                </div>
                <div>
                    <input type="radio" id="wrongInfo" name="reportReason" value="wrongInfo" class="mr-2">
                    <label for="wrongInfo">Informações Incorretas</label>
                    <p class="text-sm text-gray-600 ml-6">O perfil apresenta informações erradas sobre o pet.</p>
                </div>
                <div>
                    <input type="radio" id="inappropriate" name="reportReason" value="inappropriate" class="mr-2">
                    <label for="inappropriate">Conteúdo Inapropriado</label>
                    <p class="text-sm text-gray-600 ml-6">O perfil contém conteúdo ofensivo ou inadequado.</p>
                </div>
                <div>
                    <input type="radio" id="other" name="reportReason" value="other" class="mr-2">
                    <label for="other">Outro</label>
                    <textarea id="otherReason" class="w-full p-2 border rounded mt-2" placeholder="Por favor, especifique o motivo" style="display: none;"></textarea>
                </div>
            </div>
            <button type="submit" class="mt-6 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Enviar Relatório</button>
        </form>
    </div>

    <script>
        document.getElementById('other').addEventListener('change', function() {
            document.getElementById('otherReason').style.display = this.checked ? 'block' : 'none';
        });

        document.getElementById('reportForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const selectedReason = document.querySelector('input[name="reportReason"]:checked');
            if (!selectedReason) {
                alert('Por favor, selecione um motivo para o relatório.');
                return;
            }
            const reason = selectedReason.value;
            const otherReason = document.getElementById('otherReason').value;
            const petId = new URLSearchParams(window.location.search).get('petId');

            // Here you would typically send this data to your server
            console.log('Report submitted:', { petId, reason, otherReason });

            alert('Obrigado pelo seu relatório. Iremos analisar e tomar as medidas necessárias.');
            window.location.href = 'tela_rolagem.html'; // Redirect back to the pet browsing page
        });
    </script>
</body>
</html>
