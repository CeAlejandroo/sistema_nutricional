function openModal(id) {
    document.getElementById(id).style.display = 'block';
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
//Motor
function abrirGenerarExperto() {
    openModal('expertoModal');
}

function generarPlanExperto(event) {
    event.preventDefault();
    const pacienteId = document.getElementById('paciente_experto').value;
    if (!pacienteId) {
        alert('Selecciona un paciente.');
        return false;
    }

    fetch('generar_plan_experto.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'paciente_id=' + encodeURIComponent(pacienteId)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Mostrar resumen en el modal
            const resumenDiv = document.getElementById('resumen_experto');
            resumenDiv.style.display = 'block';
            let resumen = `
                <strong>Resumen del Plan Generado:</strong><br>
                <b>Calorías:</b> ${data.calorias_totales} kcal/día<br>
                <b>Proteínas:</b> ${data.proteinas_totales} g<br>
                <b>Grasas:</b> ${data.grasas_totales} g<br>
                <b>Carbohidratos:</b> ${data.carbohidratos_totales} g<br>
                <hr>
                <b>Comidas sugeridas:</b>
                <table style="width:100%;margin-top:0.5em;font-size:0.98em;">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Alimento ID</th>
                      <th>Cantidad (g)</th>
                      <th>Calorías</th>
                    </tr>
                  </thead>
                  <tbody>
            `;
            data.comidas.forEach(comida => {
                resumen += `
                  <tr>
                    <td>${comida.tipo_comida}</td>
                    <td>${alimentosPorId[comida.alimento_id] || comida.alimento_id}</td>
                    <td>${comida.cantidad_gramos.toFixed(2)}</td>
                    <td>${comida.calorias.toFixed(2)}</td>
                  </tr>
                `;
            });
            resumen += `
                  </tbody>
                </table>
                <button class="btn-primary" style="margin-top:1em;" onclick="aceptarPlanExperto(${pacienteId}, ${data.calorias_totales}, ${data.proteinas_totales}, ${data.grasas_totales}, ${data.carbohidratos_totales})">Usar este plan</button>
            `;
            resumenDiv.innerHTML = resumen;

            // Al generar el plan, guarda las comidas sugeridas en una variable global
            window.ultimaComidasGeneradas = data.comidas;
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(() => alert('Error de conexión con el motor experto'));
    return false;
}

// Al usar el plan, envía también las comidas
function aceptarPlanExperto(pacienteId, calorias, proteinas, grasas, carbos) {
    fetch('procesar_plan.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `paciente_id=${encodeURIComponent(pacienteId)}&calorias_diarias=${encodeURIComponent(calorias)}&porcentaje_proteina=${encodeURIComponent(proteinas)}&porcentaje_grasa=${encodeURIComponent(grasas)}&porcentaje_carbos=${encodeURIComponent(carbos)}&comidas=${encodeURIComponent(JSON.stringify(window.ultimaComidasGeneradas))}`
    })
    .then(res => res.json())
    .then(resp => {
        if (resp.success) {
            alert('¡Plan guardado exitosamente!');
            closeModal('expertoModal');
            location.reload();
        } else {
            alert('Error al guardar el plan: ' + resp.error);
        }
    })
    .catch(() => alert('Error de conexión al guardar el plan'));
}

function verPlan(planId) {
    fetch('ver_plan.php?id=' + planId)
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            let html = `
                <h3>Plan Nutricional</h3>
                <b>Calorías:</b> ${data.plan.calorias_totales} kcal/día<br>
                <b>Proteínas:</b> ${data.plan.proteinas_totales} g<br>
                <b>Grasas:</b> ${data.plan.grasas_totales} g<br>
                <b>Carbohidratos:</b> ${data.plan.carbohidratos_totales} g<br>
                <hr>
                <b>Comidas sugeridas:</b>
                <table style="width:100%;margin-top:0.5em;font-size:0.98em;">
                  <thead>
                    <tr>
                      <th>Tipo</th>
                      <th>Alimento</th>
                      <th>Cantidad (g)</th>
                      <th>Calorías</th>
                    </tr>
                  </thead>
                  <tbody>
            `;
            // Ordena por tipo de comida: desayuno, almuerzo, cena, snack
            const orden = { 'desayuno': 1, 'almuerzo': 2, 'cena': 3, 'snack': 4, 'Snack': 4 };
            data.comidas.sort((a, b) => (orden[a.tipo_comida.toLowerCase()] || 99) - (orden[b.tipo_comida.toLowerCase()] || 99));

            if (data.comidas.length === 0) {
                html += `<tr><td colspan="4" style="text-align:center;">No hay comidas sugeridas para este plan.</td></tr>`;
            } else {
                data.comidas.forEach(comida => {
                    html += `
                      <tr>
                        <td>${comida.tipo_comida}</td>
                        <td>${alimentosPorId[comida.alimento_id] || comida.alimento_id}</td>
                        <td>${parseFloat(comida.cantidad_gramos).toFixed(2)}</td>
                        <td>${parseFloat(comida.calorias).toFixed(2)}</td>
                      </tr>
                    `;
                });
            }
            html += `</tbody></table>`;
            document.getElementById('contenido_ver_plan').innerHTML = html;
            openModal('verPlanModal');
        } else {
            alert('No se pudo cargar el plan');
        }
    });
}