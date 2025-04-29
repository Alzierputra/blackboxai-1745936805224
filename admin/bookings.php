<?php
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Handle payment confirmation
if (isset($_POST['confirm_payment'])) {
    $pemesanan_id = $_POST['pemesanan_id'];
    
    try {
        $query = "UPDATE pemesanan 
                 SET status_pembayaran = 'dibayar' 
                 WHERE id = :id";
        $stmt = $functions->db->prepare($query);
        $stmt->bindParam(':id', $pemesanan_id);
        
        if ($stmt->execute()) {
            // Get booking details for WhatsApp notification
            $query = "SELECT p.*, u.telepon, l.nama as nama_lapangan 
                     FROM pemesanan p 
                     JOIN users u ON p.user_id = u.id 
                     JOIN lapangan l ON p.lapangan_id = l.id 
                     WHERE p.id = :id";
            $stmt = $functions->db->prepare($query);
            $stmt->bindParam(':id', $pemesanan_id);
            $stmt->execute();
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Send WhatsApp notification
            $message = "Pembayaran Anda telah dikonfirmasi!\n\n" .
                      "Detail Pemesanan:\n" .
                      "Lapangan: {$booking['nama_lapangan']}\n" .
                      "Tanggal: " . date('d M Y', strtotime($booking['waktu_mulai'])) . "\n" .
                      "Waktu: " . date('H:i', strtotime($booking['waktu_mulai'])) . " - " . 
                                 date('H:i', strtotime($booking['waktu_selesai']));
            
            $functions->whatsapp->sendMessage($booking['telepon'], $message);
            
            $_SESSION['success'] = "Pembayaran berhasil dikonfirmasi.";
        } else {
            $_SESSION['error'] = "Gagal mengkonfirmasi pembayaran.";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem.";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $pemesanan_id = $_POST['pemesanan_id'];
    $alasan = $_POST['alasan'];
    
    if ($functions->cancelBooking($pemesanan_id, $alasan)) {
        $_SESSION['success'] = "Pemesanan berhasil dibatalkan.";
    } else {
        $_SESSION['error'] = "Gagal membatalkan pemesanan.";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get filter parameters
$status = isset($_GET['status']) ? $_GET['status'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT p.*, l.nama as nama_lapangan, u.nama as nama_user, u.telepon
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
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['success']; ?></span>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $_SESSION['error']; ?></span>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6 mb-6">
        <form action="bookings.php" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date" name="date" id="date" value="<?php echo $date; ?>"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status"
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                    <option value="">Semua Status</option>
                    <option value="aktif" <?php echo $status === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                    <option value="selesai" <?php echo $status === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                    <option value="dibatalkan" <?php echo $status === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                </select>
            </div>
            
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Cari</label>
                <input type="text" name="search" id="search" value="<?php echo $search; ?>"
                       placeholder="Nama/Telepon/Lapangan"
                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
            </div>
            
            <div class="sm:mt-6">
                <button type="submit"
                        class="w-full inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="bg-white shadow sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
            <h2 class="text-lg leading-6 font-medium text-gray-900">
                Daftar Pemesanan
            </h2>
            <a href="export.php<?php echo $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''; ?>" 
               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                <i class="fas fa-file-excel mr-2"></i> Export Excel
            </a>
        </div>
        
        <div class="border-t border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal & Waktu
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lapangan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pemesan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Total
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pembayaran
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <div><?php echo date('d M Y', strtotime($booking['waktu_mulai'])); ?></div>
                                <div class="text-gray-500">
                                    <?php echo date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . 
                                             date('H:i', strtotime($booking['waktu_selesai'])); ?>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?php echo $booking['nama_lapangan']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo $booking['nama_user']; ?></div>
                                <div class="text-sm text-gray-500"><?php echo $booking['telepon']; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <div class="flex space-x-2">
                                    <?php if ($booking['status_pembayaran'] === 'menunggu'): ?>
                                        <form action="bookings.php" method="POST" class="inline">
                                            <input type="hidden" name="pemesanan_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" name="confirm_payment"
                                                    class="text-green-600 hover:text-green-900"
                                                    onclick="return confirm('Konfirmasi pembayaran ini?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <?php if ($booking['status_pemesanan'] === 'aktif'): ?>
                                        <button onclick="showCancelModal(<?php echo $booking['id']; ?>)"
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                    
                                    <a href="../user/invoice.php?id=<?php echo $booking['id']; ?>" target="_blank"
                                       class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bookings)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                Tidak ada data pemesanan
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="cancelForm" action="bookings.php" method="POST">
                <input type="hidden" name="pemesanan_id" id="cancel_pemesanan_id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Batalkan Pemesanan
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin membatalkan pemesanan ini?
                                </p>
                                <div class="mt-4">
                                    <label for="alasan" class="block text-sm font-medium text-gray-700">
                                        Alasan Pembatalan
                                    </label>
                                    <textarea name="alasan" id="alasan" rows="3" required
                                              class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-red-500 focus:border-red-500 border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" name="cancel_booking"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Batalkan
                    </button>
                    <button type="button" onclick="hideCancelModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showCancelModal(pemesananId) {
    document.getElementById('cancel_pemesanan_id').value = pemesananId;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancelForm').reset();
}
</script>

<?php require_once '../includes/footer.php'; ?>
