 const userModal = document.getElementById('userModal');
    const modalTitle = document.getElementById('modalTitle');
    const userIdInput = document.getElementById('userId');
    const userNameInput = document.getElementById('userName');
    const userEmailInput = document.getElementById('userEmail');
    const userPermissionSelect = document.getElementById('userPermission');
    const userPasswordInput = document.getElementById('userPassword');
    const userImagePreview = document.getElementById('userImagePreview');

    function openUserModal(userData = null) {
        if (userData) {
            // Modo Edição
            modalTitle.textContent = 'Editar Usuário';
            userIdInput.value = userData.id;
            userNameInput.value = userData.name;
            userEmailInput.value = userData.email;
            userPermissionSelect.value = userData.permission;
            userImagePreview.src = `https://placehold.co/128x128/E2E8F0/475569?text=${userData.name.substring(0,2)}`;
        } else {
            // Modo Adicionar
            modalTitle.textContent = 'Adicionar Novo Usuário';
            userIdInput.value = '';
            userNameInput.value = '';
            userEmailInput.value = '';
            userPermissionSelect.value = 'operador';
            userImagePreview.src = 'https://placehold.co/128x128/E2E8F0/475569?text=Foto';
        }
        userPasswordInput.value = ''; // Limpa o campo de senha sempre
        userModal.classList.remove('hidden');
    }

    function closeUserModal() {
        userModal.classList.add('hidden');
    }

    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            userImagePreview.src = reader.result;
        }
        reader.readAsDataURL(event.target.files[0]);
    }

    // Fecha o modal se clicar fora dele
    userModal.addEventListener('click', function(event) {
        if (event.target === userModal) {
            closeUserModal();
        }
    });