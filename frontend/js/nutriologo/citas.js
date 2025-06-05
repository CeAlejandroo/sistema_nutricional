// Funciones para el manejo de citas

function showTab(tabName) {
  // Ocultar todos los tabs
  const tabContents = document.querySelectorAll(".tab-content")
  tabContents.forEach((tab) => tab.classList.remove("active"))

  // Remover clase active de todos los botones
  const tabBtns = document.querySelectorAll(".tab-btn")
  tabBtns.forEach((btn) => btn.classList.remove("active"))

  // Mostrar el tab seleccionado
  document.getElementById(tabName).classList.add("active")
  event.target.classList.add("active")
}

function confirmarCita(citaId) {
  if (confirm("¿Confirmar esta cita?")) {
    // Aquí implementarías la lógica para confirmar la cita
    window.location.href = `procesar_cita.php?action=confirmar&id=${citaId}`
  }
}

function cancelarCita(citaId) {
  if (confirm("¿Cancelar esta cita?")) {
    // Aquí implementarías la lógica para cancelar la cita
    window.location.href = `procesar_cita.php?action=cancelar&id=${citaId}`
  }
}

function editarCita(citaId) {
  // Aquí implementarías la lógica para editar una cita
  alert("Función de editar cita en desarrollo. ID: " + citaId)
}

function openModal(modalId) {
  document.getElementById(modalId).style.display = "block"
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none"
}

// Variables para el calendario
let currentMonth = new Date().getMonth()
let currentYear = new Date().getFullYear()

function previousMonth() {
  currentMonth--
  if (currentMonth < 0) {
    currentMonth = 11
    currentYear--
  }
  updateCalendar()
}

function nextMonth() {
  currentMonth++
  if (currentMonth > 11) {
    currentMonth = 0
    currentYear++
  }
  updateCalendar()
}

function updateCalendar() {
  const monthNames = [
    "Enero",
    "Febrero",
    "Marzo",
    "Abril",
    "Mayo",
    "Junio",
    "Julio",
    "Agosto",
    "Septiembre",
    "Octubre",
    "Noviembre",
    "Diciembre",
  ]

  document.getElementById("currentMonth").textContent = `${monthNames[currentMonth]} ${currentYear}`
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
