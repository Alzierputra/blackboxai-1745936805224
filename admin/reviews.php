<?php
require_once '../includes/header.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /auth/login.php');
    exit;
}

// Handle review reply
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply'])) {
    $ulasan_id = $_POST['ulasan_id'];
    $balasan = $_POST['balasan'];
    
    if ($functions->addReviewReply($ulasan_id, $_SESSION['user_id'], $balasan)) {
        $_SESSION['success'] = "Balasan berhasil ditambahkan.";
    } else {
        $_SESSION['error'] = "Gagal menambahkan balasan.";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Get all reviews with replies
$query = "SELECT ul.*, u.nama as nama_user, l.nama as nama_lapangan,
          p.waktu_mulai, p.waktu_selesai,
          (SELECT GROUP_CONCAT(CONCAT(admin.nama, '|', br.balasan, '|', br.created_at) SEPARATOR '||')
           FROM balasan_ulasan br
           JOIN users admin ON br.admin_id = admin.id
           WHERE br.ulasan_id = ul.id) as balasan
          FROM ulasan ul
          JOIN pemesanan p ON ul.pemesanan_id = p.id
          JOIN users u ON ul.user_id = u.id
          JOIN lapangan l ON p.lapangan_id = l.id
          ORDER BY ul.created_at DESC";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    <!-- Reviews List -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h2 class="text-lg leading-6 font-medium text-gray-900">
                Ulasan Pelanggan
            </h2>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                Kelola dan balas ulasan dari pelanggan
            </p>
        </div>

        <div class="border-t border-gray-200">
            <ul class="divide-y divide-gray-200">
                <?php foreach ($reviews as $review): ?>
                <li class="p-4">
                    <div class="flex space-x-3">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-600 flex items-center justify-center">
                                <span class="text-white font-medium">
                                    <?php echo substr($review['nama_user'], 0, 1); ?>
                                </span>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">
                                        <?php echo $review['nama_user']; ?>
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        <?php echo $review['nama_lapangan']; ?> -
                                        <?php echo date('d M Y', strtotime($review['waktu_mulai'])); ?>
                                        (<?php echo date('H:i', strtotime($review['waktu_mulai'])) . ' - ' . 
                                                  date('H:i', strtotime($review['waktu_selesai'])); ?>)
                                    </p>
                                </div>
                                <div class="flex items-center">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mt-2 text-sm text-gray-700">
                                <?php echo $review['komentar']; ?>
                            </p>
                            <p class="mt-1 text-xs text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($review['created_at'])); ?>
                            </p>

                            <!-- Admin Replies -->
                            <?php if ($review['balasan']): ?>
                                <?php 
                                $replies = explode('||', $review['balasan']);
                                foreach ($replies as $reply):
                                    list($admin_name, $reply_text, $reply_date) = explode('|', $reply);
                                ?>
                                <div class="mt-4 ml-6 bg-gray-50 rounded-lg p-3">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo $admin_name; ?> <span class="text-gray-500">(Admin)</span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo date('d M Y H:i', strtotime($reply_date)); ?>
                                        </p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-700">
                                        <?php echo $reply_text; ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <!-- Reply Form -->
                            <div class="mt-4">
                                <button onclick="showReplyForm(<?php echo $review['id']; ?>)"
                                        class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <i class="fas fa-reply mr-2"></i> Balas
                                </button>
                                <form id="replyForm_<?php echo $review['id']; ?>" 
                                      action="reviews.php" method="POST" 
                                      class="mt-2 hidden">
                                    <input type="hidden" name="ulasan_id" value="<?php echo $review['id']; ?>">
                                    <textarea name="balasan" rows="2" required
                                              class="block w-full shadow-sm sm:text-sm focus:ring-blue-500 focus:border-blue-500 border-gray-300 rounded-md"
                                              placeholder="Tulis balasan..."></textarea>
                                    <div class="mt-2 flex justify-end space-x-2">
                                        <button type="button" 
                                                onclick="hideReplyForm(<?php echo $review['id']; ?>)"
                                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                            Batal
                                        </button>
                                        <button type="submit" name="reply"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            Kirim Balasan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($reviews)): ?>
                <li class="p-4 text-center text-gray-500">
                    Belum ada ulasan
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script>
function showReplyForm(reviewId) {
    document.getElementById(`replyForm_${reviewId}`).classList.remove('hidden');
}

function hideReplyForm(reviewId) {
    const form = document.getElementById(`replyForm_${reviewId}`);
    form.classList.add('hidden');
    form.reset();
}
</script>

<?php require_once '../includes/footer.php'; ?>
