<?php
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Get today's bookings
$query = "SELECT p.*, l.nama as nama_lapangan, u.nama as nama_user, u.telepon
          FROM pemesanan p 
          JOIN lapangan l ON p.lapangan_id = l.id 
          JOIN users u ON p.user_id = u.id
          WHERE DATE(p.waktu_mulai) = CURDATE()
          ORDER BY p.waktu_mulai ASC";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$today_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get pending payments
$query = "SELECT p.*, l.nama as nama_lapangan, u.nama as nama_user
          FROM pemesanan p 
          JOIN lapangan l ON p.lapangan_id = l.id 
          JOIN users u ON p.user_id = u.id
          WHERE p.status_pembayaran = 'menunggu'
          ORDER BY p.batas_pembayaran ASC";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$pending_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent reviews
$query = "SELECT ul.*, u.nama as nama_user, l.nama as nama_lapangan
          FROM ulasan ul
          JOIN pemesanan p ON ul.pemesanan_id = p.id
          JOIN users u ON ul.user_id = u.id
          JOIN lapangan l ON p.lapangan_id = l.id
          ORDER BY ul.created_at DESC
          LIMIT 5";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$recent_reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get booking statistics
$query = "SELECT 
            COUNT(*) as total_bookings,
            SUM(CASE WHEN status_pembayaran = 'dibayar' THEN total_harga ELSE 0 END) as total_revenue,
            COUNT(CASE WHEN status_pembayaran = 'menunggu' THEN 1 END) as pending_payments,
            COUNT(CASE WHEN status_pemesanan = 'dibatalkan' THEN 1 END) as cancelled_bookings
          FROM pemesanan
          WHERE DATE(created_at) = CURDATE()";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Bookings -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-calendar text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Total Pemesanan Hari Ini
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                <?php echo $stats['total_bookings']; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Pendapatan Hari Ini
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                Rp <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payments -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Menunggu Pembayaran
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                <?php echo $stats['pending_payments']; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cancelled Bookings -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <i class="fas fa-ban text-2xl text-red-600"></i>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">
                                Pembatalan Hari Ini
                            </dt>
                            <dd class="text-2xl font-semibold text-gray-900">
                                <?php echo $stats['cancelled_bookings']; ?>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Bookings -->
    <div class="mt-8">
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h2 class="text-lg leading-6 font-medium text-gray-900">
                        Jadwal Hari Ini
                    </h2>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        <?php echo date('d M Y'); ?>
                    </p>
                </div>
                <a href="bookings.php" class="text-sm text-blue-600 hover:text-blue-500">
                    Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>
            <div class="border-t border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Waktu
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Lapangan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pemesan
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pembayaran
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($today_bookings as $booking): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . 
                                             date('H:i', strtotime($booking['waktu_selesai'])); ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo $booking['nama_lapangan']; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900"><?php echo $booking['nama_user']; ?></div>
                                    <div class="text-sm text-gray-500"><?php echo $booking['telepon']; ?></div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php
                                        switch($booking['status_pemesanan']) {
                                            case 'aktif':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'selesai':
                                                echo 'bg-gray-100 text-gray-800';
                                                break;
                                            case 'dibatalkan':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-yellow-100 text-yellow-800';
                                        }
                                        ?>">
                                        <?php echo ucfirst($booking['status_pemesanan']); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php echo $booking['status_pembayaran'] === 'dibayar' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($booking['status_pembayaran']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (empty($today_bookings)): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                    Tidak ada pemesanan untuk hari ini
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Payments and Recent Reviews -->
    <div class="mt-8 grid grid-cols-1 gap-8 lg:grid-cols-2">
        <!-- Pending Payments -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Menunggu Pembayaran
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($pending_payments as $payment): ?>
                    <li class="px-4 py-4">
                        <div class="flex justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $payment['nama_user']; ?>
                                </p>
                                <p class="text-sm text-gray-500">
                                    <?php echo $payment['nama_lapangan']; ?> -
                                    <?php echo date('d M Y H:i', strtotime($payment['waktu_mulai'])); ?>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">
                                    Rp <?php echo number_format($payment['total_harga'], 0, ',', '.'); ?>
                                </p>
                                <p class="text-xs text-gray-500">
                                    Batas: <?php echo date('H:i', strtotime($payment['batas_pembayaran'])); ?>
                                </p>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($pending_payments)): ?>
                    <li class="px-4 py-4 text-center text-sm text-gray-500">
                        Tidak ada pembayaran yang tertunda
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- Recent Reviews -->
        <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    Ulasan Terbaru
                </h3>
            </div>
            <div class="border-t border-gray-200">
                <ul class="divide-y divide-gray-200">
                    <?php foreach ($recent_reviews as $review): ?>
                    <li class="px-4 py-4">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                    <span class="text-white font-medium">
                                        <?php echo substr($review['nama_user'], 0, 1); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo $review['nama_user']; ?>
                                </p>
                                <div class="flex items-center mt-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <p class="mt-1 text-sm text-gray-700">
                                    <?php echo $review['komentar']; ?>
                                </p>
                                <p class="mt-1 text-xs text-gray-500">
                                    <?php echo $review['nama_lapangan']; ?> -
                                    <?php echo date('d M Y', strtotime($review['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($recent_reviews)): ?>
                    <li class="px-4 py-4 text-center text-sm text-gray-500">
                        Belum ada ulasan
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
