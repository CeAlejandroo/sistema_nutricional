function showTab(tabName, event) {
    // Oculta todos los tabs
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
    // Remueve active de todos los botones
    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
    // Muestra el tab seleccionado
    const tab = document.getElementById(tabName);
    tab.classList.add('active');
    // Marca el botón como activo
    if(event) event.target.classList.add('active');
    // Desplaza suavemente a la sección seleccionada
    tab.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'block';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}

// Cerrar modal al hacer clic fuera
window.onclick = function(event) {
    const modal = document.getElementById('citaModal');
    if (modal && event.target === modal) {
        closeModal('citaModal');
    }
};

function editarCita(id) {
    // Puedes abrir el modal y rellenar los datos con AJAX
    fetch(`get_cita.php?id=${id}`)
        .then(res => res.json())
        .then(cita => {
            openModal('citaModal');
            document.querySelector('#citaModal input[name="cliente"]').value = cita.cliente_nombre;
            document.querySelector('#citaModal input[name="fecha"]').value = cita.fecha;
            document.querySelector('#citaModal input[name="hora"]').value = cita.hora;
            document.querySelector('#citaModal input[name="tipo"]').value = cita.tipo;
            document.querySelector('#citaModal textarea[name="notas"]').value = cita.notas || '';
            document.querySelector('#citaModal form').onsubmit = function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('id', id);
                formData.append('accion', 'editar');
                fetch('acciones_cita.php', {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                })
                .then(res => res.text())
                .then(res => {
                    if (res === "ok") location.reload();
                    else alert('Error al editar la cita');
                });
            };
        });
}

function confirmarCita(id) {
    if (confirm('¿Confirmar esta cita?')) {
        fetch('acciones_cita.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&accion=confirmar`
        })
        .then(res => res.text())
        .then(res => {
            if (res === "ok") location.reload();
            else alert('Error al confirmar la cita');
        });
    }
}

function cancelarCita(id) {
    if (confirm('¿Cancelar esta cita?')) {
        fetch('acciones_cita.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `id=${id}&accion=cancelar`
        })
        .then(res => res.text())
        .then(res => {
            if (res === "ok") location.reload();
            else alert('Error al cancelar la cita');
        });
    }
}

function marcarCita(id, btn) {
    btn.classList.toggle('marcada');
    btn.querySelector('i').classList.toggle('marcada');
    // Aquí puedes agregar lógica para guardar el marcador en la base de datos si lo deseas
}