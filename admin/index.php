<?php
// Cargamos la base de datos y el esqueleto (que ya incluye la sesión y seguridad)
include '../config/db.php';
include 'includes/admin_header.php'; 

// Estadísticas básicas
$total_prod = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$total_pedidos = $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();

// Estadísticas de DINERO (Solo para el Admin)
$ingresos = 0;
if ($_SESSION['usuario_rol'] === 'admin') {
    $ingresos = $pdo->query("SELECT SUM(total) FROM pedidos")->fetchColumn() ?: 0;
}
?>

<script>document.getElementById('page-title').innerText = 'Dashboard Principal';</script>

<style>
    .dashboard-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 25px; }
    .dash-card { background: white; padding: 25px; border-radius: 15px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: transform 0.3s; }
    .dash-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px rgba(0,0,0,0.05); }
    .dash-icon { width: 65px; height: 65px; border-radius: 15px; display: flex; align-items: center; justify-content: center; font-size: 2em; }
    .icon-blue { background: #eff6ff; color: #3b82f6; }
    .icon-green { background: #f0fdf4; color: #22c55e; }
    .icon-purple { background: #faf5ff; color: #a855f7; }
    .dash-info h3 { margin: 0; font-size: 0.9em; color: #64748b; text-transform: uppercase; font-weight: 700; }
    .dash-info p { margin: 5px 0 0 0; font-size: 2.2em; font-weight: 800; color: #0f172a; }
</style>

<div class="dashboard-grid">
    <div class="dash-card">
        <div class="dash-icon icon-blue"><i class="fas fa-dolly"></i></div>
        <div class="dash-info"><h3>Productos en Base</h3><p><?php echo $total_prod; ?></p></div>
    </div>
    <div class="dash-card">
        <div class="dash-icon icon-purple"><i class="fas fa-shopping-bag"></i></div>
        <div class="dash-info"><h3>Total Pedidos</h3><p><?php echo $total_pedidos; ?></p></div>
    </div>
    <?php if($_SESSION['usuario_rol'] === 'admin'): ?>
    <div class="dash-card" style="border-color: #bbf7d0;">
        <div class="dash-icon icon-green"><i class="fas fa-hand-holding-usd"></i></div>
        <div class="dash-info"><h3 style="color: #16a34a;">Ingresos Totales</h3><p style="color: #15803d;"><?php echo number_format($ingresos, 2); ?> €</p></div>
    </div>
    <?php endif; ?>
</div>

</main>
</body>
</html>