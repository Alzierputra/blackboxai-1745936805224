<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

// Handle booking cancellation
if (isset($_POST['cancel_booking'])) {
    $pemesanan_id = $_POST['pemesanan_id'];
    $alasan = $_POST['alasan'];
    
    if ($functions->cancelBooking($pemesanan_id, $alasan)) {
        $success = "Pemesanan berhasil dibatalkan.";
    } else {
        $error = "Tidak dapat membatalkan pemesanan. Pastikan waktu pembatalan masih dalam 10 menit sebelum jadwal main.";
    }
}

// Get user's bookings
$query = "SELECT p.*, l.nama as nama_lapangan, l.harga_per_jam,
          (SELECT COUNT(*) FROM ulasan u WHERE u.pemesanan_id = p.id) as has_review
          FROM pemesanan p 
          JOIN lapangan l ON p.lapangan_id = l.id 
          WHERE p.user_id = :user_id 
          ORDER BY p.waktu_mulai DESC";
$stmt = $functions->db->prepare($query);
$stmt->bindParam(':user_id', $_SESSION['user_id']);
$stmt->execute();
$pemesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Success/Error Messages -->
    <?php if (isset($success)): ?>
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $success; ?></span>
        </div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline"><?php echo $error; ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
                Riwayat Pemesanan
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Daftar pemesanan lapangan Anda
            </p>
        </div>
        
        <ul class="divide-y divide-gray-200">
            <?php foreach ($pemesanan as $booking): ?>
                <li>
                    <div class="px-4 py-4 sm:px-6">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <?php
                                    $statusClass = '';
                                    switch($booking['status_pemesanan']) {
                                        case 'aktif':
                                            $statusClass = 'bg-green-100 text-green-800';
                                            break;
                                        case 'selesai':
                                            $statusClass = 'bg-gray-100 text-gray-800';
                                            break;
                                        case 'dibatalkan':
                                            $statusClass = 'bg-red-100 text-red-800';
                                            break;
                                        case 'kadaluarsa':
                                            $statusClass = 'bg-yellow-100 text-yellow-800';
                                            break;
                                    }
                                    ?>
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($booking['status_pemesanan']); ?>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo $booking['nama_lapangan']; ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php 
                                        echo date('d M Y', strtotime($booking['waktu_mulai'])) . ' | ' . 
                                             date('H:i', strtotime($booking['waktu_mulai'])) . ' - ' . 
                                             date('H:i', strtotime($booking['waktu_selesai'])); 
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4">
                                <div class="text-right">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp <?php echo number_format($booking['total_harga'], 0, ',', '.'); ?>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        <?php echo ucfirst($booking['metode_pembayaran']); ?>
                                    </div>
                                </div>
                                <div class="flex space-x-2">
                                    <?php if ($booking['status_pemesanan'] === 'aktif'): ?>
                                        <?php
                                        $waktu_mulai = strtotime($booking['waktu_mulai']);
                                        $batas_pembatalan = $waktu_mulai - (10 * 60); // 10 minutes before
                                        if (time() <= $batas_pembatalan):
                                        ?>
                                            <button onclick="showCancelModal(<?php echo $booking['id']; ?>)"
                                                    class="inline-flex items-center px-2.5 py-1.5 border border-red-300 text-xs font-medium rounded text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                Batalkan
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if ($booking['status_pemesanan'] === 'selesai' && !$booking['has_review']): ?>
                                        <button onclick="showReviewModal(<?php echo $booking['id']; ?>, '<?php echo $booking['nama_lapangan']; ?>')"
                                                class="inline-flex items-center px-2.5 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Beri Ulasan
                                        </button>
                                    <?php endif; ?>

                                    <a href="invoice.php?id=<?php echo $booking['id']; ?>" target="_blank"
                                       class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                        <i class="fas fa-download mr-1"></i> Invoice
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>

<!-- Cancel Booking Modal -->
<div id="cancelModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="cancelForm" action="pemesanan.php" method="POST">
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
                                    Apakah Anda yakin ingin membatalkan pemesanan ini? Tindakan ini tidak dapat dibatalkan.
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
                        Batalkan Pemesanan
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

<!-- Review Modal -->
<div id="reviewModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="reviewForm" action="review.php" method="POST">
                <input type="hidden" name="pemesanan_id" id="review_pemesanan_id">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="review-modal-title">
                                Beri Ulasan
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Rating</label>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <div class="flex items-center" id="rating">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <button type="button" data-rating="<?php echo $i; ?>" 
                                                        class="rating-star text-gray-300 hover:text-yellow-400 focus:outline-none">
                                                    <i class="fas fa-star text-2xl"></i>
                                                </button>
                                            <?php endfor; ?>
                                        </div>
                                        <input type="hidden" name="rating" id="rating_value" required>
                                    </div>
                                </div>
                                <div>
                                    <label for="komentar" class="block text-sm font-medium text-gray-700">
                                        Komentar
                                    </label>
                                    <textarea name="komentar" id="komentar" rows="4" required
                                              class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Kirim Ulasan
                    </button>
                    <button type="button" onclick="hideReviewModal()"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cancel Modal Functions
function showCancelModal(pemesananId) {
    document.getElementById('cancel_pemesanan_id').value = pemesananId;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function hideCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
    document.getElementById('cancelForm').reset();
}

// Review Modal Functions
function showReviewModal(pemesananId, namaLapangan) {
    document.getElementById('review_pemesanan_id').value = pemesananId;
    document.getElementById('review-modal-title').textContent = `Beri Ulasan - ${namaLapangan}`;
    document.getElementById('reviewModal').classList.remove('hidden');
}

function hideReviewModal() {
    document.getElementById('reviewModal').classList.add('hidden');
    document.getElementById('reviewForm').reset();
    resetStars();
}

// Rating Stars Functions
const stars = document.querySelectorAll('.rating-star');
const ratingInput = document.getElementById('rating_value');

function resetStars() {
    stars.forEach(star => {
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300');
    });
    ratingInput.value = '';
}

stars.forEach(star => {
    star.addEventListener('click', () => {
        const rating = star.dataset.rating;
        ratingInput.value = rating;
        
        // Update stars visual
        stars.forEach(s => {
            const sRating = s.dataset.rating;
            if (sRating <= rating) {
                s.classList.remove('text-gray-300');
                s.classList.add('text-yellow-400');
            } else {
                s.classList.remove('text-yellow-400');
                s.classList.add('text-gray-300');
            }
        });
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>
