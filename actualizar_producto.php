<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
  header("Location: login.php");
  exit();
}

require_once "conexion.php";

/* ===== 1) Validaciones básicas ===== */
$required = ['id_producto','nombre_producto','talla','color','precio'];
foreach ($required as $k) {
  if (!isset($_POST[$k]) || $_POST[$k] === '') {
    header("Location: ver_productos.php?err=faltan_campos");
    exit();
  }
}

$id          = (int)$_POST['id_producto'];
$nombre      = trim($_POST['nombre_producto']);
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
$talla       = trim($_POST['talla']);
$color       = trim($_POST['color']);
$precio      = (float)$_POST['precio'];

/* Categoría opcional: si viene vacía => NULL */
$id_categoria = (isset($_POST['id_categoria']) && $_POST['id_categoria'] !== '')
  ? (int)$_POST['id_categoria']
  : null;

/* No aceptamos editar stock por aquí */
if (isset($_POST['stock'])) {
  // Ignorar silenciosamente o podrías loguearlo si quieres.
  unset($_POST['stock']);
}

/* Precio no negativo */
if ($precio < 0) {
  header("Location: editar_producto.php?id={$id}&err=precio");
  exit();
}

/* ===== 2) Verificar duplicado (por UNIQUE nombre+talla+color) ===== */
$dupSql = "SELECT id_producto
           FROM productos
           WHERE nombre_producto = ? AND talla = ? AND color = ? AND id_producto <> ?
           LIMIT 1";
$dup = $conexion->prepare($dupSql);
$dup->bind_param("sssi", $nombre, $talla, $color, $id);
$dup->execute();
$dupRes = $dup->get_result();
if ($dupRes->num_rows > 0) {
  $dup->close();
  header("Location: editar_producto.php?id={$id}&err=duplicado");
  exit();
}
$dup->close();

/* ===== 3) UPDATE sin stock ===== */
$sql = "UPDATE productos
        SET nombre_producto = ?, descripcion = ?, talla = ?, color = ?, precio = ?, id_categoria = ?
        WHERE id_producto = ?";

$stmt = $conexion->prepare($sql);
/*
 tipos: s = string, d = double, i = integer
 descripcion puede ser NULL y id_categoria puede ser NULL; mysqli enviará NULL si la variable es null
*/
$stmt->bind_param("ssssddi",
  $nombre,
  $descripcion,
  $talla,
  $color,
  $precio,
  $id_categoria,
  $id
);

$ok = $stmt->execute();
$err = $stmt->error;
$stmt->close();
$conexion->close();

/* ===== 4) Redirección con mensaje ===== */
if ($ok) {
  header("Location: editar_producto.php?id={$id}&ok=1");
} else {
  // Opcional: loguear $err
  header("Location: editar_producto.php?id={$id}&err=bd");
}
