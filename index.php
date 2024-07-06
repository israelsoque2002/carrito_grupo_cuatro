<?php
session_start();

// Ejemplo de adición de productos (ajusta según tu lógica)
$productos = array(
    array(
        'id' => 1,
        'nombre' => 'Snack 1',
        'precio' => 10.50,
        'cantidad' => 1,
        'iva' => 16,
        'categoria' => 'snack' // Indica la categoría del producto
    ),
    array(
        'id' => 2,
        'nombre' => 'Aseo 1',
        'precio' => 20.75,
        'cantidad' => 1,
        'iva' => 16,
        'categoria' => 'aseo' // Indica la categoría del producto
    )
);

// Procesar la solicitud de agregar al carrito
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['btnAgregarCarrito'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
        $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);
        $categoria = filter_input(INPUT_POST, 'categoria', FILTER_SANITIZE_STRING);
        $redirect_url = filter_input(INPUT_POST, 'redirect_url', FILTER_SANITIZE_URL);

        if ($id !== false && $nombre !== false && $precio !== false && $cantidad !== false && $iva !== false && $categoria !== false) {
            // Verificar si el producto ya está en el carrito
            $producto_existente = false;
            if (isset($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as $producto) {
                    if ($producto['id'] == $id) {
                        $producto_existente = true;
                        break;
                    }
                }
            }

            if (!$producto_existente) {
                // Crear el producto
                $producto = array(
                    'id' => $id,
                    'nombre' => $nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'iva' => $iva,
                    'categoria' => $categoria
                );

                // Agregar el producto al carrito
                if (!isset($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = array();
                }

                $_SESSION['carrito'][] = $producto;

                // Guardar la URL de la página anterior en la sesión
                $_SESSION['redirect_url'] = $redirect_url;

                header('Location: viveres.php');
                exit();
            } else {
                // Mensaje de producto ya elegido
                $error_message = 'El producto <span style="color: blue;">' . htmlspecialchars($nombre) . '</span> ya ha sido añadido al carrito.';
            }
        } else {
            $error_message = 'Datos del producto inválidos.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tienda</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .navbar {
            margin-bottom: 20px;
        }

        .carousel-container {
            width: 100%;
            height: 100vh;
            overflow: hidden;
            position: relative;
        }

        .carousel-slide {
            display: flex;
            transition: transform 0.5s ease-in-out;
            height: 100%;
        }

        .carousel-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .categoria-section {
            padding: 40px 0;
            background-color: #f8f9fa;
        }

        .categoria-title {
            margin-bottom: 20px;
        }

        .producto-card {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#">Mi Tienda Online</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
                <a class="nav-link" href="#">HOME</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="viveres.php">PRODUCTOS</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="carrito.php">
                    CARRITO 
                    <img src="images/carrito (2).png" class="img-fluid" alt="Carrito de Compras">
                    <span class="badge badge-light"><?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?></span>
                </a>
            </li>
        </ul>
    </div>
</nav>


    <!-- Carrusel de imágenes -->
    <div class="carousel-container">
        <div class="carousel-slide">
            <img src="Imágenes banner/uno.png" class="img-fluid" alt="Banner 1">
            <img src="Imágenes banner/dos.png" class="img-fluid" alt="Banner 2">
            <img src="Imágenes banner/tres.png" class="img-fluid" alt="Banner 3">
            <img src="Imágenes banner/cuatro.png" class="img-fluid" alt="Banner 4">
        </div>
    </div>

   
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const carouselSlide = document.querySelector(".carousel-slide");
            const carouselImages = document.querySelectorAll(".carousel-slide img");

            // Configuración del carrusel
            let counter = 1;
            const slideWidth = carouselImages[0].clientWidth;

            function slide() {
                carouselSlide.style.transition = "transform 0.5s ease-in-out";
                carouselSlide.style.transform = translateX(${-slideWidth * counter}px);
                counter++;
            }

            setInterval(() => {
                if (counter >= carouselImages.length) {
                    counter = 0;
                }
                slide();
            }, 2000); // Cambiar imagen cada 2 segundos
        });
    </script>
</body>

</html>