<?php
// dist/facturacion/seleccionar_producto.php
session_start();
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) { header("location: ../login.php"); exit; }

include("../config/db.php");
include("../config/conexion.php");

$id = (int)($_GET['id'] ?? 0);
if($id<=0){ die("Factura inválida"); }

$prods = mysqli_query($con, "SELECT id_producto, referencia, descripcion, precio_producto, id_almacen
  FROM products WHERE estatus=1 ORDER BY descripcion ASC LIMIT 2000");
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>Agregar producto</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head><body class="p-3">
<div class="container">
  <h4>Agregar producto a Factura #<?php echo $id; ?></h4>
  <a class="btn btn-outline-secondary btn-sm mb-2" href="nueva_factura.php?id=<?php echo $id; ?>">Volver</a>

  <div class="table-responsive">
    <table class="table table-sm table-striped">
      <thead><tr><th>Ref</th><th>Descripción</th><th class="text-end">Precio</th><th></th></tr></thead>
      <tbody>
        <?php while($p=mysqli_fetch_assoc($prods)): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['referencia']); ?></td>
          <td><?php echo htmlspecialchars($p['descripcion']); ?></td>
          <td class="text-end">$ <?php echo number_format((float)$p['precio_producto'],2); ?></td>
          <td class="text-end">
            <form method="post" action="ajax/agregar_item.php" class="d-flex gap-2 align-items-center">
              <input type="hidden" name="id" value="<?php echo $id; ?>">
              <input type="hidden" name="id_producto" value="<?php echo (int)$p['id_producto']; ?>">
              <input type="hidden" name="id_almacen" value="<?php echo (int)$p['id_almacen']; ?>">
              <input class="form-control form-control-sm" style="width:120px" name="cantidad" value="1">
              <input class="form-control form-control-sm" style="width:140px" name="precio" value="<?php echo htmlspecialchars($p['precio_producto']); ?>">
              <button class="btn btn-sm btn-primary">Agregar</button>
            </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body></html>
