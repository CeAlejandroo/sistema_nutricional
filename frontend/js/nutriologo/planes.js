// Funciones para el manejo de planes nutricionales

function verPlan(planId) {
  // Aquí implementarías la lógica para ver los detalles del plan
  alert("Ver detalles del plan ID: " + planId)
}

function editarPlan(planId) {
  // Aquí implementarías la lógica para editar un plan
  alert("Función de editar plan en desarrollo. ID: " + planId)
}

function eliminarPlan(planId) {
  if (confirm("¿Estás seguro de que deseas eliminar este plan nutricional?")) {
    // Aquí implementarías la lógica para eliminar un plan
    window.location.href = `procesar_plan.php?action=delete&id=${planId}`
  }
}

function openModal(modalId) {
  document.getElementById(modalId).style.display = "block"
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none"
}

// Cerrar modal al hacer clic fuera de él
window.onclick = (event) => {
  const modals = document.getElementsByClassName("modal")
  for (let i = 0; i < modals.length; i++) {
    if (event.target === modals[i]) {
      modals[i].style.display = "none"
    }
  }
}

// Función para generar plan automático
function generarPlanAutomatico() {
  alert("Función de generación automática de planes en desarrollo")
}
