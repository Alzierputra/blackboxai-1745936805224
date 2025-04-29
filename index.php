<?php
require_once 'includes/header.php';

// Get available fields
$stmt = $functions->query("SELECT * FROM lapangan WHERE status = 'tersedia'");
$lapangan = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Get recent reviews
$stmt = $functions->query(
    "SELECT u.nama, ul.rating, ul.komentar, ul.created_at 
     FROM ulasan ul 
     JOIN users u ON ul.user_id = u.id 
     ORDER BY ul.created_at DESC LIMIT 3"
);
$testimonials = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
?>

<!-- Hero Section -->
<div class="relative bg-blue-600">
    <div class="absolute inset-0">
        <img class="w-full h-full object-cover" src="https://images.unsplash.com/photo-1552667466-07770ae110d0?ixlib=rb-1.2.1&auto=format&fit=crop&w=1950&q=80" alt="Futsal Field">
        <div class="absolute inset-0 bg-blue-600 mix-blend-multiply"></div>
    </div>
    <div class="relative max-w-7xl mx-auto py-24 px-4 sm:py-32 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">iFutsal Sayan Bekasi</h1>
        <p class="mt-6 text-xl text-white max-w-3xl">
            Sewa lapangan futsal berkualitas di Bekasi dengan harga terjangkau. Tersedia 3 lapangan dengan fasilitas lengkap untuk main reguler atau turnamen. Lokasi strategis dan mudah dijangkau.
        </p>
        <div class="mt-10">
            <a href="/jadwal.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                Pesan Sekarang
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="py-12 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Fasilitas</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Mengapa Memilih Kami?
            </p>
        </div>

        <div class="mt-10">
            <div class="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                <!-- Feature 1 -->
                <div class="relative">
                    <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-futbol"></i>
                    </div>
                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Lapangan Berkualitas</p>
                    <p class="mt-2 ml-16 text-base text-gray-500">
                        3 lapangan dengan rumput sintetis berkualitas tinggi dan perawatan rutin.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="relative">
                    <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Booking Mudah</p>
                    <p class="mt-2 ml-16 text-base text-gray-500">
                        Sistem booking online 24 jam dengan konfirmasi cepat via WhatsApp.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="relative">
                    <div class="absolute flex items-center justify-center h-12 w-12 rounded-md bg-blue-600 text-white">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <p class="ml-16 text-lg leading-6 font-medium text-gray-900">Pembayaran Fleksibel</p>
                    <p class="mt-2 ml-16 text-base text-gray-500">
                        Pilihan pembayaran via transfer bank, QRIS, atau bayar di tempat.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fields Section -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Lapangan</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Pilihan Lapangan
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <?php foreach($lapangan as $field): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <?php if ($field['foto']): ?>
                    <img class="w-full h-48 object-cover" src="<?php echo $field['foto']; ?>" alt="<?php echo $field['nama']; ?>">
                <?php else: ?>
                    <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                        <i class="fas fa-image text-4xl text-gray-400"></i>
                    </div>
                <?php endif; ?>
                <div class="p-6">
                    <h3 class="text-xl font-semibold text-gray-900"><?php echo $field['nama']; ?></h3>
                    <p class="mt-2 text-gray-600"><?php echo $field['deskripsi']; ?></p>
                    <div class="mt-4">
                        <span class="text-blue-600 font-semibold">
                            Rp <?php echo number_format($field['harga_per_jam'], 0, ',', '.'); ?> / jam
                        </span>
                    </div>
                    <a href="/booking.php?id=<?php echo $field['id']; ?>" class="mt-4 w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                        Pesan Sekarang
                        <i class="fas fa-arrow-right ml-2"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Testimonials Section -->
<div class="bg-white py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Testimonial</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Apa Kata Mereka?
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
            <?php foreach($testimonials as $testimonial): ?>
            <div class="bg-gray-50 rounded-lg p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0">
                        <div class="h-12 w-12 rounded-full bg-blue-600 flex items-center justify-center">
                            <span class="text-white text-xl font-bold">
                                <?php echo substr($testimonial['nama'], 0, 1); ?>
                            </span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-lg font-semibold text-gray-900"><?php echo $testimonial['nama']; ?></h4>
                        <div class="flex items-center">
                            <?php for($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i < $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                <p class="text-gray-600"><?php echo $testimonial['komentar']; ?></p>
                <p class="mt-4 text-sm text-gray-500">
                    <?php echo date('d M Y', strtotime($testimonial['created_at'])); ?>
                </p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Contact Section -->
<div class="bg-gray-50 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center mb-12">
            <h2 class="text-base text-blue-600 font-semibold tracking-wide uppercase">Kontak</h2>
            <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-gray-900 sm:text-4xl">
                Hubungi Kami
            </p>
        </div>

        <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
            <div>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kontak</h3>
                    <ul class="space-y-4">
                        <li class="flex items-start space-x-3">
                            <i class="fas fa-map-marker-alt mt-1 text-gray-400"></i>
                            <span class="text-gray-600">Jl. Sayan, Bekasi</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-phone text-gray-400"></i>
                            <span class="text-gray-600">+62 123 4567 890</span>
                        </li>
                        <li class="flex items-center space-x-3">
                            <i class="fas fa-envelope text-gray-400"></i>
                            <span class="text-gray-600">info@ifutsalsayan.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div>
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Jam Operasional</h3>
                    <ul class="space-y-2">
                        <li class="flex justify-between">
                            <span class="text-gray-600">Senin - Jumat</span>
                            <span class="text-gray-900">08:00 - 23:00</span>
                        </li>
                        <li class="flex justify-between">
                            <span class="text-gray-600">Sabtu - Minggu</span>
                            <span class="text-gray-900">08:00 - 23:00</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Section -->
<div class="bg-blue-600">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="lg:text-center">
            <h2 class="text-3xl font-extrabold text-white sm:text-4xl">
                Siap Untuk Bermain?
            </h2>
            <p class="mt-4 text-lg text-blue-100">
                Booking lapangan sekarang dan nikmati pengalaman bermain futsal terbaik di Bekasi.
            </p>
            <div class="mt-8 flex justify-center">
                <a href="/jadwal.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                    Pesan Sekarang
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
