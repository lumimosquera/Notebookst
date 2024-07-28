
<!DOCTYPE html>
<html>
<head>
<title>Formulario de actualización de datos</title>
<style>
body {
  font-family: sans-serif;
  margin: 0;
  padding: 0;
}

form {
  width: 100%;
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  background-color: #fff;
  border: 1px solid #ccc;
}

input {
  width: 100%;
  padding: 10px;
  margin-bottom: 10px;
  border: 1px solid #ccc;
  outline: none;
}

button {
  width: 100%;
  background-color: #000;
  color: #fff;
  padding: 10px;
  margin-bottom: 10px;
  border: none;
  cursor: pointer;
}

img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: none;
}

.image-preview {
  width: 100%;
  height: 100%;
  border: 1px solid #ccc;
  margin-bottom: 10px;
}
</style>
</head>
<body>

<form action="/update-data" method="post" enctype="multipart/form-data">
  <input type="text" name="name" placeholder="Nombre">
  <input type="text" name="username" placeholder="Usuario">
  <input type="email" name="email" placeholder="Correo electrónico">
  <input type="file" name="profile_image" id="profile_image">
  <button type="submit">Actualizar</button>

  <div class="image-preview">
    <img src="" alt="Imagen de perfil">
  </div>

</form>

<script>
$(document).ready(function() {
  $('#profile_image').on('change', function() {
    // Get the image file
    var file = this.files[0];

    // Check if the file is an image
    if (!file.type.match('image.*')) {
      // Not an image
      return;
    }

    // Create a new image element
    var img = document.createElement('img');

    // Set the image source to the file
    img.src = window.URL.createObjectURL(file);

    // Set the height and width of the image
    img.height = 100;
    img.width = 100;

    // Add the image to the image preview
    $('.image-preview').html(img);
  });
});
</script>

</body>
</html>
