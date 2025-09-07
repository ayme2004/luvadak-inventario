    <?php
    session_start();
    if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
        header("Location: login.php");
        exit();
    }

    include("conexion.php");

    $id_producto = $_POST['id_producto'];
    $tipo = $_POST['tipo_movimiento']; 
    $cantidad = intval($_POST['cantidad']);
    $obs = $_POST['observaciones'];
    $id_usuario = $_SESSION['id_usuario']; 

    if ($cantidad <= 0) {
        echo "<script>alert('La cantidad debe ser mayor que cero'); window.location.href='registrar_movimiento.php';</script>";
        exit();
    }

    if ($tipo === 'salida') {
        $verificar = $conexion->prepare("SELECT stock FROM productos WHERE id_producto = ?");
        $verificar->bind_param("i", $id_producto);
        $verificar->execute();
        $res = $verificar->get_result();
        $producto = $res->fetch_assoc();

        if ($producto['stock'] < $cantidad) {
            echo "<script>alert('No hay suficiente stock disponible. Stock actual: {$producto['stock']}'); window.location.href='registrar_movimiento.php';</script>";
            exit();
        }
    }

    $stmt = $conexion->prepare("INSERT INTO movimientosinventario (id_producto, tipo_movimiento, cantidad, observaciones, id_usuario) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isisi", $id_producto, $tipo, $cantidad, $obs, $id_usuario);
    $stmt->execute();

    $signo = ($tipo === 'entrada') ? '+' : '-';
    $conexion->query("UPDATE productos SET stock = stock $signo $cantidad WHERE id_producto = $id_producto");
    echo "<script>alert('✅ Movimiento registrado correctamente'); window.location.href='ver_movimientos.php';</script>";
    ?>
