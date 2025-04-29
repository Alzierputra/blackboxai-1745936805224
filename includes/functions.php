<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/whatsapp.php';

class Functions {
    protected $db;
    protected $whatsapp;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->whatsapp = new WhatsAppConfig();
    }

    public function getDb() {
        return $this->db;
    }

    // User Management Functions
    public function generateVerificationCode() {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function registerUser($nama, $telepon, $alamat, $email, $password) {
        try {
            $kode_verifikasi = $this->generateVerificationCode();
            
            $query = "INSERT INTO users (nama, telepon, alamat, email, password, kode_verifikasi) 
                     VALUES (:nama, :telepon, :alamat, :email, :password, :kode_verifikasi)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":nama", $nama);
            $stmt->bindParam(":telepon", $telepon);
            $stmt->bindParam(":alamat", $alamat);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $password);
            $stmt->bindParam(":kode_verifikasi", $kode_verifikasi);
            
            if($stmt->execute()) {
                // Send verification code via WhatsApp
                $this->whatsapp->sendVerificationCode($telepon, $kode_verifikasi);
                return true;
            }
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function verifyUser($telepon, $kode) {
        try {
            $query = "UPDATE users SET status_verifikasi = TRUE 
                     WHERE telepon = :telepon AND kode_verifikasi = :kode";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":telepon", $telepon);
            $stmt->bindParam(":kode", $kode);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    // Booking Functions
    public function createBooking($user_id, $lapangan_id, $jenis_pemesanan, $waktu_mulai, $waktu_selesai, $total_harga, $metode_pembayaran) {
        try {
            // Set batas pembayaran 30 menit sebelum jadwal main
            $batas_pembayaran = date('Y-m-d H:i:s', strtotime($waktu_mulai . ' -30 minutes'));
            
            $query = "INSERT INTO pemesanan (user_id, lapangan_id, jenis_pemesanan, waktu_mulai, waktu_selesai, 
                     total_harga, metode_pembayaran, batas_pembayaran) 
                     VALUES (:user_id, :lapangan_id, :jenis_pemesanan, :waktu_mulai, :waktu_selesai, 
                     :total_harga, :metode_pembayaran, :batas_pembayaran)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":lapangan_id", $lapangan_id);
            $stmt->bindParam(":jenis_pemesanan", $jenis_pemesanan);
            $stmt->bindParam(":waktu_mulai", $waktu_mulai);
            $stmt->bindParam(":waktu_selesai", $waktu_selesai);
            $stmt->bindParam(":total_harga", $total_harga);
            $stmt->bindParam(":metode_pembayaran", $metode_pembayaran);
            $stmt->bindParam(":batas_pembayaran", $batas_pembayaran);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function cancelBooking($pemesanan_id, $alasan) {
        try {
            $query = "SELECT * FROM pemesanan WHERE id = :pemesanan_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":pemesanan_id", $pemesanan_id);
            $stmt->execute();
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            // Check if cancellation is within 10 minutes before start time
            $waktu_mulai = strtotime($booking['waktu_mulai']);
            $batas_pembatalan = $waktu_mulai - (10 * 60); // 10 minutes before
            
            if(time() > $batas_pembatalan) {
                return false;
            }

            // Begin transaction
            $this->db->beginTransaction();

            // Update booking status
            $query = "UPDATE pemesanan SET status_pemesanan = 'dibatalkan' 
                     WHERE id = :pemesanan_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":pemesanan_id", $pemesanan_id);
            $stmt->execute();

            // Create cancellation record
            $query = "INSERT INTO pembatalan (pemesanan_id, alasan_pembatalan) 
                     VALUES (:pemesanan_id, :alasan)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":pemesanan_id", $pemesanan_id);
            $stmt->bindParam(":alasan", $alasan);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch(PDOException $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // Review Functions
    public function addReview($pemesanan_id, $user_id, $rating, $komentar) {
        try {
            $query = "INSERT INTO ulasan (pemesanan_id, user_id, rating, komentar) 
                     VALUES (:pemesanan_id, :user_id, :rating, :komentar)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":pemesanan_id", $pemesanan_id);
            $stmt->bindParam(":user_id", $user_id);
            $stmt->bindParam(":rating", $rating);
            $stmt->bindParam(":komentar", $komentar);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    public function addReviewReply($ulasan_id, $admin_id, $balasan) {
        try {
            $query = "INSERT INTO balasan_ulasan (ulasan_id, admin_id, balasan) 
                     VALUES (:ulasan_id, :admin_id, :balasan)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":ulasan_id", $ulasan_id);
            $stmt->bindParam(":admin_id", $admin_id);
            $stmt->bindParam(":balasan", $balasan);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            return false;
        }
    }

    // Utility Functions
    public function checkAvailability($lapangan_id, $waktu_mulai, $waktu_selesai) {
        try {
            $query = "SELECT COUNT(*) as count FROM pemesanan 
                     WHERE lapangan_id = :lapangan_id 
                     AND status_pemesanan != 'dibatalkan'
                     AND ((waktu_mulai BETWEEN :waktu_mulai AND :waktu_selesai) 
                     OR (waktu_selesai BETWEEN :waktu_mulai AND :waktu_selesai))";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(":lapangan_id", $lapangan_id);
            $stmt->bindParam(":waktu_mulai", $waktu_mulai);
            $stmt->bindParam(":waktu_selesai", $waktu_selesai);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] == 0;
        } catch(PDOException $e) {
            return false;
        }
    }

    public function formatCurrency($amount) {
        return number_format($amount, 0, ',', '.');
    }

    public function generateInvoiceNumber() {
        return 'INV-' . date('Ymd') . '-' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    // Query Functions
    public function query($sql, $params = []) {
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            return false;
        }
    }
}
?>
