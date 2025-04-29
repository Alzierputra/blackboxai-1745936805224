<?php
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT 
            p.id,
            p.waktu_mulai,
            p.waktu_selesai,
            p.total_harga,
            p.diskon,
            p.metode_pembayaran,
            p.status_pembayaran,
            p.status_pemesanan,
            p.created_at,
            l.nama as nama_lapangan,
            u.nama as nama_user,
            u.telepon,
            u.alamat
          FROM pemesanan p 
          JOIN lapangan l ON p.lapangan_id = l.id 
          JOIN users u ON p.user_id = u.id
          WHERE 1=1";

if ($status) {
    $query .= " AND p.status_pemesanan = :status";
}
if ($date) {
    $query .= " AND DATE(p.waktu_mulai) = :date";
}
if ($search) {
    $query .= " AND (u.nama LIKE :search OR u.telepon LIKE :search OR l.nama LIKE :search)";
}

$query .= " ORDER BY p.waktu_mulai DESC";

$stmt = $functions->db->prepare($query);

if ($status) {
    $stmt->bindParam(':status', $status);
}
if ($date) {
    $stmt->bindParam(':date', $date);
}
if ($search) {
    $search = "%$search%";
    $stmt->bindParam(':search', $search);
}

$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="laporan_pemesanan_' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="13" style="text-align: center; font-size: 16px; font-weight: bold;">
                Laporan Pemesanan Lapangan Futsal
            </th>
        </tr>
        <tr>
            <th colspan="13" style="text-align: center;">
                Tanggal: <?php echo date('d M Y', strtotime($date)); ?>
            </th>
        </tr>
        <tr>
            <th>No.</th>
            <th>ID Pemesanan</th>
            <th>Tanggal</th>
            <th>Waktu</th>
            <th>Lapangan</th>
            <th>Nama Pemesan</th>
            <th>Telepon</th>
            <th>Alamat</th>
            <th>Total Harga</th>
            <th>Diskon</th>
            <th>Metode Pembayaran</th>
            <th>Status Pembayaran</th>
            <th>Status Pemesanan</th>
        </tr>
    </thead>
    <tbody>
        <?php 
        $no = 1;
        $total_pendapatan = 0;
        foreach ($bookings as $booking): 
            $total_pendapatan += ($booking['status_pembayaran'] === 'dibayar') ? $booking['total_harga'] - $booking['diskon'] : 0;
        ?>
        <tr>
            <td><?php echo $no++; ?></td>
            <td><?php echo 'INV-' . str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?></td>
            <td><?php echo date('d/m/Y', strtotime($booking['waktu_mulai'])); ?></td>
            <td><?php echo date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . 
                         date('H:i', strtotime($booking['waktu_selesai'])); ?></td>
            <td><?php echo $booking['nama_lapangan']; ?></td>
            <td><?php echo $booking['nama_user']; ?></td>
            <td><?php echo $booking['telepon']; ?></td>
            <td><?php echo $booking['alamat']; ?></td>
            <td><?php echo number_format($booking['total_harga'], 0, ',', '.'); ?></td>
            <td><?php echo number_format($booking['diskon'], 0, ',', '.'); ?></td>
            <td><?php echo ucfirst($booking['metode_pembayaran']); ?></td>
            <td><?php echo ucfirst($booking['status_pembayaran']); ?></td>
            <td><?php echo ucfirst($booking['status_pemesanan']); ?></td>
        </tr>
        <?php endforeach; ?>
        
        <?php if (empty($bookings)): ?>
        <tr>
            <td colspan="13" style="text-align: center;">Tidak ada data pemesanan</td>
        </tr>
        <?php endif; ?>
        
        <!-- Summary -->
        <tr>
            <td colspan="13">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="8" style="text-align: right; font-weight: bold;">Total Pendapatan:</td>
            <td colspan="5" style="font-weight: bold;">
                Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?>
            </td>
        </tr>
    </tbody>
</table>

<!-- Statistics -->
<?php
// Calculate statistics
$total_bookings = count($bookings);
$completed_bookings = array_filter($bookings, function($b) { return $b['status_pemesanan'] === 'selesai'; });
$cancelled_bookings = array_filter($bookings, function($b) { return $b['status_pemesanan'] === 'dibatalkan'; });
$pending_payments = array_filter($bookings, function($b) { return $b['status_pembayaran'] === 'menunggu'; });
?>

<table border="1" style="margin-top: 20px;">
    <thead>
        <tr>
            <th colspan="2" style="text-align: center; font-weight: bold;">Statistik</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total Pemesanan</td>
            <td><?php echo $total_bookings; ?></td>
        </tr>
        <tr>
            <td>Pemesanan Selesai</td>
            <td><?php echo count($completed_bookings); ?></td>
        </tr>
        <tr>
            <td>Pemesanan Dibatalkan</td>
            <td><?php echo count($cancelled_bookings); ?></td>
        </tr>
        <tr>
            <td>Menunggu Pembayaran</td>
            <td><?php echo count($pending_payments); ?></td>
        </tr>
        <tr>
            <td>Total Pendapatan</td>
            <td>Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></td>
        </tr>
    </tbody>
</table>

<!-- Payment Method Statistics -->
<?php
$payment_methods = array_count_values(array_column($bookings, 'metode_pembayaran'));
?>

<table border="1" style="margin-top: 20px;">
    <thead>
        <tr>
            <th colspan="2" style="text-align: center; font-weight: bold;">Metode Pembayaran</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Transfer Bank</td>
            <td><?php echo isset($payment_methods['transfer']) ? $payment_methods['transfer'] : 0; ?></td>
        </tr>
        <tr>
            <td>QRIS</td>
            <td><?php echo isset($payment_methods['qris']) ? $payment_methods['qris'] : 0; ?></td>
        </tr>
        <tr>
            <td>Bayar di Tempat</td>
            <td><?php echo isset($payment_methods['cod']) ? $payment_methods['cod'] : 0; ?></td>
        </tr>
    </tbody>
</table>

<!-- Export Information -->
<table style="margin-top: 20px;">
    <tr>
        <td>Tanggal Export:</td>
        <td><?php echo date('d/m/Y H:i:s'); ?></td>
    </tr>
    <tr>
        <td>Export oleh:</td>
        <td><?php echo $_SESSION['user_name']; ?></td>
    </tr>
</table>
