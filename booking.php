<?php 
require_once 'includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: /auth/login.php');
    exit;
}

// Get field ID from URL
$lapangan_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$lapangan_id) {
    header('Location: /');
    exit;
}

// Get field details
$query = "SELECT * FROM lapangan WHERE id = :id";
$stmt = $functions->db->prepare($query);
$stmt->bindParam(':id', $lapangan_id);
$stmt->execute();
$lapangan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lapangan) {
    header('Location: /');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = $_POST['tanggal'];
    $waktu_mulai = $_POST['waktu_mulai'];
    $durasi = $_POST['durasi'];
    $jenis_pemesanan = $_POST['jenis_pemesanan'];
    $metode_pembayaran = $_POST['metode_pembayaran'];

    // Calculate end time and total price
    $waktu_selesai = date('Y-m-d H:i:s', strtotime($tanggal . ' ' . $waktu_mulai . ' + ' . $durasi . ' hours'));
    $total_harga = $lapangan['harga_per_jam'] * $durasi;

    // Apply discount for tournament bookings
    if ($jenis_pemesanan === 'turnamen') {
        $total_harga = $total_harga * 0.9; // 10% discount for tournaments
    }

    // Check availability
    if ($functions->checkAvailability($lapangan_id, $tanggal . ' ' . $waktu_mulai, $waktu_selesai)) {
        // Create booking
        if ($functions->createBooking(
            $_SESSION['user_id'],
            $lapangan_id,
            $jenis_pemesanan,
            $tanggal . ' ' . $waktu_mulai,
            $waktu_selesai,
            $total_harga,
            $metode_pembayaran
        )) {
            header('Location: /user/pemesanan.php?success=1');
            exit;
        } else {
            $error = "Terjadi kesalahan saat membuat pemesanan. Silakan coba lagi.";
        }
    } else {
        $error = "Maaf, lapangan sudah dipesan untuk waktu tersebut.";
    }
}

// Get bank information
$query = "SELECT * FROM informasi_bank LIMIT 1";
$stmt = $functions->db->prepare($query);
$stmt->execute();
$bank = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <!-- Field Information -->
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Informasi Lapangan</h3>
                <div class="mt-4">
                    <?php if ($lapangan['foto']): ?>
                        <img src="<?php echo $lapangan['foto']; ?>" 
                             alt="<?php echo $lapangan['nama']; ?>"
                             class="w-full h-48 object-cover rounded-lg">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 flex items-center justify-center rounded-lg">
                            <i class="fas fa-image text-4xl text-gray-400"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="mt-4">
                    <h4 class="text-xl font-semibold"><?php echo $lapangan['nama']; ?></h4>
                    <p class="mt-1 text-gray-600"><?php echo $lapangan['deskripsi']; ?></p>
                    <p class="mt-2 text-lg font-semibold text-blue-600">
                        Rp <?php echo number_format($lapangan['harga_per_jam'], 0, ',', '.'); ?> / jam
                    </p>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="mt-5 md:mt-0 md:col-span-2">
            <form action="booking.php?id=<?php echo $lapangan_id; ?>" method="POST">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <?php if (isset($error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline"><?php echo $error; ?></span>
                            </div>
                        <?php endif; ?>

                        <!-- Date and Time -->
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="tanggal" class="block text-sm font-medium text-gray-700">
                                    Tanggal
                                </label>
                                <input type="date" name="tanggal" id="tanggal" required
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="waktu_mulai" class="block text-sm font-medium text-gray-700">
                                    Waktu Mulai
                                </label>
                                <select name="waktu_mulai" id="waktu_mulai" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php
                                    // Generate time slots (8 AM to 11 PM)
                                    for ($hour = 8; $hour <= 23; $hour++) {
                                        $time = sprintf("%02d:00", $hour);
                                        echo "<option value=\"$time\">$time</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Duration and Booking Type -->
                        <div class="grid grid-cols-6 gap-6">
                            <div class="col-span-6 sm:col-span-3">
                                <label for="durasi" class="block text-sm font-medium text-gray-700">
                                    Durasi (Jam)
                                </label>
                                <select name="durasi" id="durasi" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo "<option value=\"$i\">$i Jam</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="col-span-6 sm:col-span-3">
                                <label for="jenis_pemesanan" class="block text-sm font-medium text-gray-700">
                                    Jenis Pemesanan
                                </label>
                                <select name="jenis_pemesanan" id="jenis_pemesanan" required
                                        class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="reguler">Regular</option>
                                    <option value="turnamen">Turnamen (Diskon 10%)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">
                                Metode Pembayaran
                            </label>
                            <div class="mt-4 space-y-4">
                                <div class="flex items-center">
                                    <input id="transfer" name="metode_pembayaran" type="radio" value="transfer" required
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="transfer" class="ml-3 block text-sm font-medium text-gray-700">
                                        Transfer Bank (<?php echo $bank['nama_bank']; ?>)
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="qris" name="metode_pembayaran" type="radio" value="qris" required
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="qris" class="ml-3 block text-sm font-medium text-gray-700">
                                        QRIS
                                    </label>
                                </div>
                                <div class="flex items-center">
                                    <input id="cod" name="metode_pembayaran" type="radio" value="cod" required
                                           class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300">
                                    <label for="cod" class="ml-3 block text-sm font-medium text-gray-700">
                                        Bayar di Tempat
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="rounded-md bg-blue-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-info-circle text-blue-400"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-blue-800">
                                        Informasi Pembayaran
                                    </h3>
                                    <div class="mt-2 text-sm text-blue-700">
                                        <ul class="list-disc pl-5 space-y-1">
                                            <li>Pembayaran harus dilakukan maksimal 30 menit sebelum jadwal main</li>
                                            <li>Booking otomatis dibatalkan jika pembayaran tidak dilakukan</li>
                                            <li>Untuk pembayaran transfer, gunakan rekening:</li>
                                            <li class="font-semibold"><?php echo $bank['nama_bank']; ?> <?php echo $bank['nomor_rekening']; ?></li>
                                            <li class="font-semibold">a.n. <?php echo $bank['nama_pemilik']; ?></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Pesan Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Disable past dates in date picker
const today = new Date().toISOString().split('T')[0];
document.getElementById('tanggal').setAttribute('min', today);

// Calculate and display total price
const calculateTotal = () => {
    const durasi = document.getElementById('durasi').value;
    const jenisPemesanan = document.getElementById('jenis_pemesanan').value;
    const hargaPerJam = <?php echo $lapangan['harga_per_jam']; ?>;
    
    let total = durasi * hargaPerJam;
    
    if (jenisPemesanan === 'turnamen') {
        total = total * 0.9; // 10% discount
    }
    
    // Update total price display if it exists
    const totalDisplay = document.getElementById('total_harga');
    if (totalDisplay) {
        totalDisplay.textContent = total.toLocaleString('id-ID');
    }
};

// Add event listeners
document.getElementById('durasi').addEventListener('change', calculateTotal);
document.getElementById('jenis_pemesanan').addEventListener('change', calculateTotal);

// Initial calculation
calculateTotal();
</script>

<?php require_once 'includes/footer.php'; ?>
