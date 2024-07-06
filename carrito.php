<?php
session_start();

// Credenciales de la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "carrito";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Mensaje de error inicialmente vacío
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['btnAccion'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
        $precio = filter_input(INPUT_POST, 'precio', FILTER_VALIDATE_FLOAT);
        $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);
        $iva = filter_input(INPUT_POST, 'iva', FILTER_VALIDATE_INT);
        $imagen = filter_input(INPUT_POST, 'imagen', FILTER_SANITIZE_URL);
        $redirect_url = filter_input(INPUT_POST, 'redirect_url', FILTER_SANITIZE_URL);

        if ($id !== false && $nombre !== false && $precio !== false && $cantidad !== false && $iva !== false) {
            $producto_existente = false;
            if (isset($_SESSION['carrito'])) {
                foreach ($_SESSION['carrito'] as &$producto) {
                    if (isset($producto['id']) && $producto['id'] == $id) {
                        $producto['cantidad'] += $cantidad;
                        $producto_existente = true;
                        break;
                    }
                }
            }

            if (!$producto_existente) {
                $producto = array(
                    'id' => $id,
                    'nombre' => $nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'iva' => $iva,
                    'imagen' => $imagen
                );

                if (!isset($_SESSION['carrito'])) {
                    $_SESSION['carrito'] = array();
                }

                $_SESSION['carrito'][] = $producto;
            }

            $_SESSION['redirect_url'] = $redirect_url;
            $_SESSION['message'] = 'El producto ha sido añadido al carrito de compras.';

            header('Location: carrito.php');
            exit();
        } else {
            $error_message = 'Datos del producto inválidos.';
        }
    }

    if (isset($_POST['btnEliminar'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if ($id !== false && isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $index => $producto) {
                if (isset($producto['id']) && $producto['id'] == $id) {
                    unset($_SESSION['carrito'][$index]);
                    $_SESSION['carrito'] = array_values($_SESSION['carrito']);
                    $_SESSION['message'] = 'El producto ha sido eliminado del carrito de compras.';
                    break;
                }
            }
        }
        header('Location: carrito.php');
        exit();
    }

    if (isset($_POST['btnActualizar'])) {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $cantidad = filter_input(INPUT_POST, 'cantidad', FILTER_VALIDATE_INT);

        if ($id !== false && $cantidad !== false && isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as &$producto) {
                if (isset($producto['id']) && $producto['id'] == $id) {
                    $producto['cantidad'] = $cantidad;
                    $_SESSION['message'] = 'La cantidad del producto ha sido actualizada.';
                    break;
                }
            }
        }
        header('Location: carrito.php');
        exit();
    }

    if (isset($_POST['btnPagar'])) {
        // Redirigir a la página de pago (checkout)
        header('Location: checkout.php');
        exit();
    }

    if (isset($_POST['btnConfirmarPago'])) {
        // Lógica para confirmar el pago
        // Obtener la información del pedido y guardarla en la base de datos
        // Eliminar variables de sesión y redirigir a la página principal
    }
}

// Calcular el total de productos en el carrito
$total_productos = 0;
if (isset($_SESSION['carrito'])) {
    foreach ($_SESSION['carrito'] as $producto) {
        $total_productos += $producto['cantidad'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        .container {
            max-width: 1000px;
            margin: 50px auto;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: .25rem;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .producto-azul {
            color: blue;
            font-weight: bold;
        }
        .table {
            margin-top: 1rem;
        }
        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }
        .table th:first-child,
        .table td:first-child {
            text-align: left;
        }
        .table th {
            font-weight: bold;
            background-color: #f8f9fa;
            border-bottom-width: 2px;
        }
        .table-bordered th,
        .table-bordered td {
            border: 1px solid #dee2e6;
        }
        .table-bordered thead th,
        .table-bordered thead td {
            border-bottom-width: 2px;
        }
        .form-control {
            display: inline-block;
            width: auto;
        }
        .btn-primary,
        .btn-danger {
            padding: 0.375rem 0.75rem;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-primary:hover,
        .btn-danger:hover {
            opacity: 0.85;
        }
        .row {
            display: flex;
        }
        .col-left {
            flex: 0 0 70%;
            max-width: 70%;
            padding-right: 15px;
        }
        .col-right {
            flex: 0 0 30%;
            max-width: 30%;
            padding-left: 15px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="text-center">Carrito de Compras</h1>
        <?php if (!empty($error_message)) : ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['message'])) : ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-left">
                <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) : ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Imagen</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subtotal_iva_15 = 0;
                                $subtotal_iva_0 = 0;
                                $iva_15 = 0;
                                $total = 0;
                                foreach ($_SESSION['carrito'] as $producto) {
                                    $productoID = isset($producto['id']) ? $producto['id'] : 0;

                                    // Consulta para obtener información del producto, incluyendo la ruta de la imagen
                                    $sql = "SELECT Nombre, Precio, RutaImagenThumb FROM producto WHERE ProductoID = ?";
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bind_param("i", $productoID);
                                    $stmt->execute();
                                    $result = $stmt->get_result();

                                    if ($result->num_rows > 0) {
                                        $fila = $result->fetch_assoc();
                                        $nombre = $fila['Nombre'];
                                        $precio = $fila['Precio'];
                                        $imagen = $fila['RutaImagenThumb'];
                                        $cantidad = isset($producto['cantidad']) ? $producto['cantidad'] : 1;
                                        $iva = isset($producto['iva']) ? $producto['iva'] : 0;

                                        $total_producto = $precio * $cantidad;
                                        if ($iva == 15) {
                                            $subtotal_iva_15 += $total_producto;
                                            $iva_15 += $total_producto * 0.15;
                                        } else {
                                            $subtotal_iva_0 += $total_producto;
                                        }
                                        $total += $total_producto + ($iva == 15 ? $total_producto * 0.15 : 0);

                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($nombre) . '</td>';
                                        echo '<td><img src="' . htmlspecialchars($imagen) . '" alt="Imagen de ' . htmlspecialchars($nombre) . '" style="width: 100px;"></td>';
                                        echo '<td>' . number_format($precio, 2) . '</td>';
                                        echo '<td>
                                            <form action="carrito.php" method="post">
                                                <input type="hidden" name="id" value="' . htmlspecialchars($productoID) . '">
                                                <input type="number" name="cantidad" value="' . htmlspecialchars($cantidad) . '" min="1" class="form-control">
                                                <button class="btn btn-primary" name="btnActualizar" type="submit">Actualizar</button>
                                            </form>
                                        </td>';
                                        echo '<td>' . number_format($total_producto, 2) . '</td>';
                                        echo '<td>
                                            <form action="carrito.php" method="post">
                                                <input type="hidden" name="id" value="' . htmlspecialchars($productoID) . '">
                                                <button class="btn btn-danger" name="btnEliminar" type="submit">Eliminar</button>
                                            </form>
                                        </td>';
                                        echo '</tr>';
                                    } else {
                                        echo "<tr><td colspan='6'>Producto no encontrado</td></tr>";
                                    }

                                    $stmt->close();
                                }
                                ?>
                                <tr>
                                    <td colspan="4" class="text-right">Subtotal IVA 15%:</td>
                                    <td><?php echo number_format($subtotal_iva_15, 2); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right">Subtotal IVA 0%:</td>
                                    <td><?php echo number_format($subtotal_iva_0, 2); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right">IVA 15%:</td>
                                    <td><?php echo number_format($iva_15, 2); ?></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right">Total:</td>
                                    <td><?php echo number_format($total, 2); ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <p>No hay productos en el carrito.</p>
                <?php endif; ?>
                <div class="text-center">
                    <a href="<?php echo isset($_SESSION['redirect_url']) ? $_SESSION['redirect_url'] : 'index.php'; ?>" class="btn btn-primary">Regresar a tienda</a>
                    <?php if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) : ?>
                        <form action="carrito.php" method="post">
                            <button class="btn btn-success" name="btnPagar" type="submit">Pagar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-right">
                <div class="summary-box">
                    <h3>Resumen</h3>
                    <p><strong>Subtotal IVA 15%:</strong> <?php echo number_format($subtotal_iva_15, 2); ?></p>
                    <p><strong>Subtotal IVA 0%:</strong> <?php echo number_format($subtotal_iva_0, 2); ?></p>
                    <p><strong>IVA 15%:</strong> <?php echo number_format($iva_15, 2); ?></p>
                    <p><strong>Total:</strong> <?php echo number_format($total, 2); ?></p>
                </div>
                <?php if (isset($_POST['btnPagar']) || isset($_POST['btnConfirmarPago'])) : ?>
                    <form action="carrito.php" method="post">
                        <h4>Detalles del pago</h4>
                        <div class="form-group">
                            <label for="lugar_entrega">Lugar de entrega</label>
                            <input type="text" class="form-control" id="lugar_entrega" name="lugar_entrega" required>
                        </div>
                        <div class="form-group">
                            <label for="costo_entrega">Costo de entrega</label>
                            <input type="number" class="form-control" id="costo_entrega" name="costo_entrega" required>
                        </div>
                        <div class="form-group">
                            <label for="datos_cliente">Datos del cliente para facturación</label>
                            <textarea class="form-control" id="datos_cliente" name="datos_cliente" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="forma_pago">Forma de pago</label>
                            <select class="form-control" id="forma_pago" name="forma_pago" required>
                                <option value="">Seleccione...</option>
                                <option value="tarjeta">Tarjeta de crédito</option>
                                <option value="efectivo">Efectivo</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        <button class="btn btn-primary" name="btnConfirmarPago" type="submit">Confirmar pago</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

<?php
$conn->close();
?>
