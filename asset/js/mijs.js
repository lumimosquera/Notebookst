document.addEventListener('DOMContentLoaded', function () {
    var img = document.getElementById('profile-picture');
    var colorThief = new ColorThief();

    img.onload = function () {
        // Obtener los colores dominantes
        var dominantColor = colorThief.getColor(img);
        var palette = colorThief.getPalette(img, 2);
        var secondaryColor = palette[1] || dominantColor;

        // Crear el gradiente
        var gradient = `linear-gradient(to right, rgb(${dominantColor.join(',')}), rgb(${secondaryColor.join(',')}))`;

        // Aplicar el gradiente al banner
        document.querySelector('.mini-banner').style.background = gradient;
    };

    // Asegúrate de que la imagen esté cargada
    if (img.complete) {
        img.onload();
    }
});
