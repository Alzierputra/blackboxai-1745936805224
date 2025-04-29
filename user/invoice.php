<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

// Get booking ID from URL
$pemesanan_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$pemesanan_id) {
    header('Location: /user/pemesanan.php');
    exit;
}

// Get booking details
$query = "SELECT p.*, l.nama as nama_lapangan, l.harga_per_jam, u.nama as nama_user, u.telepon, u.alamat
          FROM pemesanan p 
          JOIN lapangan l ON p.lapangan_id = l.id 
          JOIN users u ON p.user_id = u.id
          WHERE p.id = :id AND p.user_id = :user_id";
$stmt = $functions->db->prepare($query);
$stmt->bindParam(':id', $pemesanan_id);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    header('Location: /user/pemesanan.php');
    exit;
}

// Get bank information
$query = "SELECT * FROM informasi_bank LIMIT 1";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$bank = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <!-- Invoice Header -->
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Invoice</h1>
                    <p class="mt-1 text-sm text-gray-500">
                        #INV-<?php echo str_pad($booking['id'], 6, '0', STR_PAD_LEFT); ?>
                    </p>
                </div>
                <div class="text-right">
                    <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-print mr-2"></i> Cetak
                    </button>
                </div>
            </div>
        </div>

        <!-- Invoice Content -->
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-2 gap-4">
                <!-- Company Info -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900">FutsalKita</h3>
                    <div class="mt-2 text-sm text-gray-500">
                        <p>Jl. Futsal No. 123</p>
                        <p>Kota, Indonesia</p>
                        <p>Telp: +62 123 4567 890</p>
                        <p>Email: info@futsalkita.com</p>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="text-right">
                    <h3 class="text-lg font-medium text-gray-900">Pelanggan</h3>
                    <div class="mt-2 text-sm text-gray-500">
                        <p><?php echo htmlspecialchars($booking['nama_user']); ?></p>
                        <p><?php echo htmlspecialchars($booking['telepon']); ?></p>
                        <p><?php echo htmlspecialchars($booking['alamat']); ?></p>
                    </div>
                </div>
            </div>

            <!-- Booking Details -->
            <div class="mt-8">
                <div class="border-t border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Deskripsi
                                </th>
                                <th scope="col" class="px-6 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Jumlah
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div class="font-medium"><?php echo $booking['nama_lapangan']; ?></div>
                                    <div class="text-gray-500">
                                        <?php 
                                        echo date('d M Y', strtotime($booking['waktu_mulai'])) . '<br>' .
                                             date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . 
                                             date('H:i', strtotime($booking['waktu_selesai']));
                                        ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php if ($booking['diskon'] > 0): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    Diskon
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                                    - Rp <?php echo number_format($booking['diskon'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <tr class="bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    Total
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                    Rp <?php echo number_format($booking['total_harga'] - $booking['diskon'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Payment Info -->
            <div class="mt-8">
                <h4 class="text-lg font-medium text-gray-900">Informasi Pembayaran</h4>
                <div class="mt-4 bg-gray-50 rounded-lg p-4">
                    <div class="text-sm text-gray-700">
                        <p class="font-medium">Metode Pembayaran: <?php echo ucfirst($booking['metode_pembayaran']); ?></p>
                        <p class="mt-2">Status: 
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php echo $booking['status_pembayaran'] === 'dibayar' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo ucfirst($booking['status_pembayaran']); ?>
                            </span>
                        </p>
                        <?php if ($booking['metode_pembayaran'] === 'transfer'): ?>
                        <div class="mt-4">
                            <p class="font-medium">Pembayaran Transfer:</p>
                            <p><?php echo $bank['nama_bank']; ?></p>
                            <p class="font-medium"><?php echo $bank['nomor_rekening']; ?></p>
                            <p>a.n. <?php echo $bank['nama_pemilik']; ?></p>
                        </div>
                        <?php endif; ?>
                        <p class="mt-4 text-sm text-gray-500">
                            Batas waktu pembayaran: <?php echo date('d M Y H:i', strtotime($booking['batas_pembayaran'])); ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Terms -->
            <div class="mt-8 text-sm text-gray-500">
                <p class="font-medium">Syarat dan Ketentuan:</p>
                <ul class="mt-2 list-disc list-inside space-y-1">
                    <li>Pembayaran harus dilakukan maksimal 30 menit sebelum jadwal main</li>
                    <li>Pembatalan hanya dapat dilakukan maksimal 10 menit sebelum jadwal main</li>
                    <li>Tidak ada pengembalian dana untuk pembatalan yang dilakukan kurang dari 10 menit sebelum jadwal main</li>
                    <li>Harap tiba 15 menit sebelum jadwal main</li>
                    <li>Invoice ini adalah bukti sah pemesanan lapangan</li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="mt-8 text-center text-sm text-gray-500">
                <p>Terima kasih telah menggunakan layanan FutsalKita</p>
                <p class="mt-1">Untuk bantuan hubungi: +62 123 4567 890</p>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style type="text/css" media="print">
@page {
    size: auto;
    margin: 0mm;
}

body {
    background-color: #ffffff;
}

.max-w-4xl {
    max-width: none;
    margin: 0;
    padding: 20px;
}

button {
    display: none;
}

.shadow {
    box-shadow: none;
}

.border {
    border: 1px solid #000;
}
</style>

<?php require_once '../includes/footer.php'; ?>
