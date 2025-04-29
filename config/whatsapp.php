<?php
class WhatsAppConfig {
    private $apiKey = "YOUR_WHATSAPP_API_KEY";
    private $apiUrl = "https://api.whatsapp.com/v1/messages";

    public function sendMessage($phone, $message) {
        // Implementation will depend on the WhatsApp API service you choose
        // This is a placeholder for the actual implementation
        $data = [
            'phone' => $phone,
            'message' => $message
        ];

        // TODO: Implement actual WhatsApp API integration
        return true;
    }

    public function sendVerificationCode($phone, $code) {
        $message = "Kode verifikasi Anda adalah: $code\n" .
                  "Kode ini berlaku selama 5 menit.\n" .
                  "Jangan bagikan kode ini kepada siapapun.";
        
        return $this->sendMessage($phone, $message);
    }

    public function sendBookingConfirmation($phone, $booking) {
        $message = "Pemesanan lapangan berhasil!\n\n" .
                  "Detail Pemesanan:\n" .
                  "Lapangan: {$booking['nama_lapangan']}\n" .
                  "Tanggal: {$booking['tanggal']}\n" .
                  "Waktu: {$booking['waktu_mulai']} - {$booking['waktu_selesai']}\n" .
                  "Total: Rp {$booking['total_harga']}\n\n" .
                  "Silakan lakukan pembayaran sebelum {$booking['batas_pembayaran']}";
        
        return $this->sendMessage($phone, $message);
    }

    public function sendPaymentReminder($phone, $booking) {
        $message = "Pengingat Pembayaran!\n\n" .
                  "Segera lakukan pembayaran untuk pemesanan:\n" .
                  "Lapangan: {$booking['nama_lapangan']}\n" .
                  "Tanggal: {$booking['tanggal']}\n" .
                  "Batas pembayaran: {$booking['batas_pembayaran']}\n\n" .
                  "Pemesanan akan otomatis dibatalkan jika melewati batas waktu.";
        
        return $this->sendMessage($phone, $message);
    }

    public function sendPlayReminder($phone, $booking) {
        $message = "Pengingat Jadwal Main!\n\n" .
                  "Detail jadwal Anda:\n" .
                  "Lapangan: {$booking['nama_lapangan']}\n" .
                  "Tanggal: {$booking['tanggal']}\n" .
                  "Waktu: {$booking['waktu_mulai']} - {$booking['waktu_selesai']}\n\n" .
                  "Selamat bermain!";
        
        return $this->sendMessage($phone, $message);
    }

    public function sendCancellationNotification($phone, $booking) {
        $message = "Pembatalan Pemesanan\n\n" .
                  "Pemesanan Anda telah dibatalkan:\n" .
                  "Lapangan: {$booking['nama_lapangan']}\n" .
                  "Tanggal: {$booking['tanggal']}\n" .
                  "Waktu: {$booking['waktu_mulai']} - {$booking['waktu_selesai']}\n\n" .
                  "Status Pengembalian: {$booking['status_pengembalian']}";
        
        return $this->sendMessage($phone, $message);
    }

    public function sendReviewReminder($phone, $booking) {
        $message = "Bagaimana pengalaman bermain Anda?\n\n" .
                  "Berikan ulasan untuk:\n" .
                  "Lapangan: {$booking['nama_lapangan']}\n" .
                  "Tanggal: {$booking['tanggal']}\n\n" .
                  "Klik link berikut untuk memberikan ulasan:\n" .
                  "{$booking['review_link']}";
        
        return $this->sendMessage($phone, $message);
    }
}
?>
