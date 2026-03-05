<?php
// dist/facturacion/facturas.php
session_start();
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
  header("location: ../login.php");
  exit;
}

include("../../config/db.php");
include("../../config/conexion.php");

$q = mysqli_query($con, "SELECT ff.*, c.nombre_cliente
  FROM fact_facturas ff
  LEFT JOIN clientes c ON c.id_cliente = ff.id_cliente
  ORDER BY ff.id_fact_facturas DESC
  LIMIT 200
");

?>
<!doctype html>
<html lang="es">
<head>
<?php
include '../header.php';
?>
</head>
<body class="class="layout-fixed sidebar-expand-lg sidebar-mini sidebar-collapse sidebar-dark-info  bg-body-tertiary"">
 <div class="app-wrapper">
  <?php
include '../navbar.php';
include '../aside_menu.php';
    ?>
  <!-- <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="m-0">Facturación CFDI</h3>
    <a class="btn btn-primary" href="nueva_factura.php">Nueva factura</a>
  </div> -->
<main class="app-main">
        <!--begin::App Content Header-->
  <div class="app-content-header">
		
  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Serie/Folio</th>
          <th>Cliente</th>
          <th>Total</th>
          <th>Status</th>
          <th>UUID</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php while($r = mysqli_fetch_assoc($q)): ?>
          <tr>
            <td><?php echo (int)$r['id_fact_facturas']; ?></td>
            <td><?php echo htmlspecialchars(($r['serie']??'')." ".($r['folio']??'')); ?></td>
            <td><?php echo htmlspecialchars($r['nombre_cliente'] ?? ''); ?></td>
            <td>$ <?php echo number_format((float)($r['total'] ?? $r['total_factura']), 2); ?></td>
            <td><?php echo (int)$r['status_factura']; ?></td>
            <td style="font-size:12px"><?php echo htmlspecialchars($r['uuid'] ?? ''); ?></td>
            <td>
              <a class="btn btn-sm btn-outline-primary" href="nueva_factura.php?id=<?php echo (int)$r['id_fact_facturas']; ?>">Abrir</a>
              <?php if((int)$r['status_factura'] === 2 && !empty($r['uuid'])): ?>
                <a class="btn btn-sm btn-outline-success" href="pagos.php?id=<?php echo (int)$r['id_fact_facturas']; ?>">Pagos</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
