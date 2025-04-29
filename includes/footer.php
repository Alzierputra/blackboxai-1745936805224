</main>

    <!-- Footer -->
    <footer class="bg-white shadow-inner mt-8">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">iFutsal Sayan Bekasi</h3>
                    <p class="text-gray-600 mb-4">
                        Tempat bermain futsal terbaik dengan fasilitas lengkap dan pelayanan profesional.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <i class="fab fa-facebook fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <i class="fab fa-instagram fa-lg"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-gray-500">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Link Cepat</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="/" class="text-gray-600 hover:text-blue-600">Beranda</a>
                        </li>
                        <li>
                            <a href="/jadwal.php" class="text-gray-600 hover:text-blue-600">Jadwal</a>
                        </li>
                        <li>
                            <a href="/harga.php" class="text-gray-600 hover:text-blue-600">Harga</a>
                        </li>
                        <li>
                            <a href="/kontak.php" class="text-gray-600 hover:text-blue-600">Kontak</a>
                        </li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Kontak</h3>
                    <ul class="space-y-2">
                        <li class="flex items-start space-x-2">
                            <i class="fas fa-map-marker-alt mt-1 text-gray-400"></i>
                            <span class="text-gray-600">Jl. Sayan, Bekasi</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-phone text-gray-400"></i>
                            <span class="text-gray-600">+62 123 4567 890</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fas fa-envelope text-gray-400"></i>
                            <span class="text-gray-600">info@ifutsalsayan.com</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Payment Methods -->
            <div class="border-t border-gray-200 mt-8 pt-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Metode Pembayaran</h3>
                <div class="flex space-x-4 items-center">
                    <span class="text-gray-600">
                        <i class="fas fa-money-bill-wave fa-lg"></i> Transfer Bank
                    </span>
                    <span class="text-gray-600">
                        <i class="fas fa-qrcode fa-lg"></i> QRIS
                    </span>
                    <span class="text-gray-600">
                        <i class="fas fa-wallet fa-lg"></i> Bayar di Tempat
                    </span>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-200 mt-8 pt-8">
                <p class="text-center text-gray-500">
                    &copy; <?php echo date('Y'); ?> iFutsal Sayan Bekasi. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu JavaScript -->
    <script>
        document.querySelector('.mobile-menu-button').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });
    </script>

    <!-- Custom JavaScript -->
    <script>
        // Flash Messages
        const flashMessages = document.querySelectorAll('.flash-message');
        flashMessages.forEach(message => {
            setTimeout(() => {
                message.style.opacity = '0';
                setTimeout(() => {
                    message.remove();
                }, 300);
            }, 3000);
        });
    </script>
</body>
</html>
