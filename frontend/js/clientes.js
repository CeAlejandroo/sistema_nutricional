// Función para abrir el modal de nuevo cliente
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
    }
}

// Función para cerrar el modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Cerrar el modal si el usuario hace clic fuera del contenido
window.onclick = function(event) {
    const modal = document.getElementById('clientModal');
    if (modal && event.target === modal) {
        closeModal('clientModal');
    }
}

function editClient(btn) {
    // Obtén los datos del cliente desde los atributos data-*
    document.getElementById('nombre').value = btn.getAttribute('data-nombre');
    document.getElementById('email').value = btn.getAttribute('data-email');
    document.getElementById('telefono').value = btn.getAttribute('data-telefono');
    document.getElementById('edad').value = btn.getAttribute('data-edad');
    document.getElementById('altura').value = btn.getAttribute('data-altura');
    document.getElementById('peso').value = btn.getAttribute('data-peso');
    document.getElementById('objetivo').value = btn.getAttribute('data-objetivo');
    document.getElementById('observaciones').value = btn.getAttribute('data-observaciones');

    // Puedes agregar un campo oculto para el id si lo necesitas para actualizar
    let idInput = document.getElementById('cliente_id');
    if (!idInput) {
        idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'cliente_id';
        idInput.id = 'cliente_id';
        document.getElementById('clientForm').appendChild(idInput);
    }
    idInput.value = btn.getAttribute('data-id');

    // Abre el modal
    openModal('clientModal');
}

function deleteClient(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este cliente?')) {
        window.location.href = 'procesar_cliente.php?action=delete&id=' + id;
    }
}