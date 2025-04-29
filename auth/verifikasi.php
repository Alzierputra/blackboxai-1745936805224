<?php
require_once '../includes/header.php';

// Redirect if no phone number in session
if (!isset($_SESSION['verification_phone'])) {
    header('Location: register.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode = $_POST['kode'];
    $telepon = $_SESSION['verification_phone'];
    
    if ($functions->verifyUser($telepon, $kode)) {
        // Get user data after verification
        $query = "SELECT id, nama, role FROM users WHERE telepon = :telepon AND status_verifikasi = TRUE";
        $stmt = $functions->db->prepare($query);
        $stmt->bindParam(':telepon', $telepon);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['nama'];
            $_SESSION['role'] = $user['role'];
            
            // Clear verification session
            unset($_SESSION['verification_phone']);
            
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard.php');
            } else {
                header('Location: /');
            }
            exit;
        }
    } else {
        $error = "Kode verifikasi tidak valid. Silakan coba lagi.";
    }
}

// Function to mask phone number
function maskPhoneNumber($phone) {
    $length = strlen($phone);
    $visibleDigits = 4;
    $maskedLength = $length - $visibleDigits;
    return str_repeat('*', $maskedLength) . substr($phone, -$visibleDigits);
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Verifikasi WhatsApp
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Masukkan kode verifikasi yang telah dikirim ke WhatsApp
                <span class="font-medium text-blue-600">
                    <?php echo maskPhoneNumber($_SESSION['verification_phone']); ?>
                </span>
            </p>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo $error; ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" action="verifikasi.php" method="POST">
            <div>
                <label for="kode" class="sr-only">Kode Verifikasi</label>
                <input id="kode" name="kode" type="text" required maxlength="6"
                       class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm text-center tracking-widest"
                       placeholder="Masukkan 6 digit kode"
                       pattern="[0-9]{6}"
                       title="Masukkan 6 digit angka">
            </div>

            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-check-circle"></i>
                    </span>
                    Verifikasi
                </button>
            </div>
        </form>

        <div class="flex items-center justify-center">
            <div class="text-sm">
                <button id="resendCode" class="font-medium text-blue-600 hover:text-blue-500" disabled>
                    Kirim Ulang Kode
                    <span id="countdown" class="text-gray-500">(60)</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Countdown timer for resend code
let timeLeft = 60;
const countdownDisplay = document.getElementById('countdown');
const resendButton = document.getElementById('resendCode');

function updateCountdown() {
    if (timeLeft > 0) {
        timeLeft--;
        countdownDisplay.textContent = `(${timeLeft})`;
        setTimeout(updateCountdown, 1000);
    } else {
        countdownDisplay.textContent = '';
        resendButton.disabled = false;
        resendButton.classList.remove('text-gray-400');
        resendButton.classList.add('text-blue-600', 'hover:text-blue-500');
    }
}

// Format verification code input
const codeInput = document.getElementById('kode');
codeInput.addEventListener('input', function(e) {
    // Remove non-numeric characters
    this.value = this.value.replace(/[^0-9]/g, '');
    
    // Limit to 6 digits
    if (this.value.length > 6) {
        this.value = this.value.slice(0, 6);
    }
});

// Start countdown
updateCountdown();

// Handle resend code
resendButton.addEventListener('click', function() {
    if (!this.disabled) {
        // Reset countdown
        timeLeft = 60;
        this.disabled = true;
        this.classList.remove('text-blue-600', 'hover:text-blue-500');
        this.classList.add('text-gray-400');
        updateCountdown();

        // Send AJAX request to resend code
        fetch('resend-code.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                phone: '<?php echo $_SESSION['verification_phone']; ?>'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Kode verifikasi baru telah dikirim!');
            } else {
                alert('Gagal mengirim kode verifikasi. Silakan coba lagi.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        });
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
