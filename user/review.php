<?php
require_once '../includes/header.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pemesanan_id = $_POST['pemesanan_id'];
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'];
    
    // Verify that the booking belongs to the user and is completed
    $query = "SELECT id FROM pemesanan 
              WHERE id = :id 
              AND user_id = :user_id 
              AND status_pemesanan = 'selesai'";
    $stmt = $functions->db->prepare($query);
    $stmt->bindParam(':id', $pemesanan_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    
    if ($stmt->fetch()) {
        // Check if review already exists
        $query = "SELECT id FROM ulasan WHERE pemesanan_id = :pemesanan_id";
        $stmt = $functions->db->prepare($query);
        $stmt->bindParam(':pemesanan_id', $pemesanan_id);
        $stmt->execute();
        
        if (!$stmt->fetch()) {
            // Add review
            if ($functions->addReview($pemesanan_id, $_SESSION['user_id'], $rating, $komentar)) {
                $_SESSION['success'] = "Terima kasih atas ulasan Anda!";
            } else {
                $_SESSION['error'] = "Gagal menambahkan ulasan. Silakan coba lagi.";
            }
        } else {
            $_SESSION['error'] = "Anda sudah memberikan ulasan untuk pemesanan ini.";
        }
    } else {
        $_SESSION['error'] = "Pemesanan tidak valid atau belum selesai.";
    }
    
    header('Location: /user/pemesanan.php');
    exit;
} else {
    // If accessed directly without POST, redirect to booking history
    header('Location: /user/pemesanan.php');
    exit;
}
?>
