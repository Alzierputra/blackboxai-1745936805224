<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

// Get user data
$query = "SELECT * FROM users WHERE id = :id";
$stmt = $functions->db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'];
    $telepon = $_POST['telepon'];
    $alamat = $_POST['alamat'];
    $email = $_POST['email'];
    $password = !empty($_POST['password']) ? $_POST['password'] : $user['password'];

    try {
        $query = "UPDATE users SET 
                  nama = :nama,
                  telepon = :telepon,
                  alamat = :alamat,
                  email = :email,
                  password = :password
                  WHERE id = :id";
        
        $stmt = $functions->db->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':telepon', $telepon);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':id', $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            $_SESSION['user_name'] = $nama;
            $success = "Profil berhasil diperbarui!";
            
            // Refresh user data
            $stmt = $functions->db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $_SESSION['user_id']);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $error = "Gagal memperbarui profil. Silakan coba lagi.";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan. Silakan coba lagi.";
    }
}
?>

<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="md:grid md:grid-cols-3 md:gap-6">
        <div class="md:col-span-1">
            <div class="px-4 sm:px-0">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Profil Saya</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Informasi profil Anda yang digunakan untuk pemesanan lapangan.
                </p>
            </div>
        </div>

        <div class="mt-5 md:mt-0 md:col-span-2">
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

            <form action="profil.php" method="POST">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                    <div class="px-4 py-5 bg-white space-y-6 sm:p-6">
                        <!-- Name -->
                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">
                                Nama Lengkap
                            </label>
                            <input type="text" name="nama" id="nama" required
                                   value="<?php echo htmlspecialchars($user['nama']); ?>"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="telepon" class="block text-sm font-medium text-gray-700">
                                Nomor Telepon (WhatsApp)
                            </label>
                            <input type="tel" name="telepon" id="telepon" required
                                   value="<?php echo htmlspecialchars($user['telepon']); ?>"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="alamat" class="block text-sm font-medium text-gray-700">
                                Alamat
                            </label>
                            <textarea name="alamat" id="alamat" rows="3" required
                                      class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email
                            </label>
                            <input type="email" name="email" id="email" required
                                   value="<?php echo htmlspecialchars($user['email']); ?>"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password Baru (kosongkan jika tidak ingin mengubah)
                            </label>
                            <input type="password" name="password" id="password"
                                   class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>

                        <!-- Account Status -->
                        <div class="rounded-md bg-gray-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <?php if ($user['status_verifikasi']): ?>
                                        <i class="fas fa-check-circle text-green-400"></i>
                                    <?php else: ?>
                                        <i class="fas fa-exclamation-circle text-yellow-400"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-800">
                                        Status Akun
                                    </h3>
                                    <div class="mt-2 text-sm text-gray-700">
                                        <?php if ($user['status_verifikasi']): ?>
                                            <p>Akun Anda telah terverifikasi</p>
                                        <?php else: ?>
                                            <p>Akun Anda belum terverifikasi. 
                                               <a href="/auth/verifikasi.php" class="text-blue-600 hover:text-blue-500">
                                                   Verifikasi sekarang
                                               </a>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                        <button type="submit"
                                class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
