function openModal(id) {
  document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
  document.getElementById(id).style.display = 'none';
}

function editarPlan(btn) {
    // Llena el formulario del modal con los datos del plan
    document.getElementById('paciente_id').value = btn.getAttribute('data-paciente_id');
    document.getElementById('calorias_diarias').value = btn.getAttribute('data-calorias');
    document.getElementById('porcentaje_proteina').value = btn.getAttribute('data-proteinas');
    document.getElementById('porcentaje_grasa').value = btn.getAttribute('data-grasas');
    document.getElementById('porcentaje_carbos').value = btn.getAttribute('data-carbos');

    // Crea o actualiza el campo oculto para el id del plan
    let idInput = document.getElementById('plan_id');
    if (!idInput) {
        idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'plan_id';
        idInput.id = 'plan_id';
        document.getElementById('nuevoPlanForm').appendChild(idInput);
    }
    idInput.value = btn.getAttribute('data-id');

    openModal('planModal');
}

function eliminarPlan(id) {
    if (confirm('¿Estás seguro de que deseas eliminar este plan?')) {
        // Redirige a un archivo PHP para eliminar el plan
        window.location.href = 'procesar_plan.php?action=delete&id=' + id;
    }
}
