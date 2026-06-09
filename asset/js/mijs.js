function cambiarEstado(tareaId, nuevoEstado) {
    // Realizar la solicitud AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "../controller/cnt_tarea.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.success) {
                // Actualizar la insignia (badge) del estado
                var badge = document.querySelector(`#badge-${tareaId}`);
                badge.textContent = nuevoEstado;
                badge.className = `badge badge-pill badge-${getBadgeClass(nuevoEstado)}`;

                // Mostrar un mensaje de éxito
                alert(response.message);
            } else {
                alert("Error: " + response.message);
            }
        }
    };

    xhr.send("id_tarea=" + encodeURIComponent(tareaId) + "&nuevo_estado=" + encodeURIComponent(nuevoEstado));
}

function getBadgeClass(estado) {
    switch(estado) {
        case 'pendiente':
            return 'warning';
        case 'en_proceso':
            return 'info';
        case 'completada':
            return 'success';
        case 'cancelada':
            return 'danger';
        default:
            return 'secondary';
    }
}
