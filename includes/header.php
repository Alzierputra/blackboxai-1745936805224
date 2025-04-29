<?php
session_start();
require_once __DIR__ . '/functions.php';
$functions = new Functions();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sewa Lapangan Futsal</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <a href="/" class="text-xl font-bold text-gray-800">
                            Futsal<span class="text-blue-600">Kita</span>
                        </a>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="/" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Beranda
                        </a>
                        <a href="/jadwal.php" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Jadwal
                        </a>
                        <a href="/harga.php" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Harga
                        </a>
                        <a href="/kontak.php" class="inline-flex items-center px-1 pt-1 text-gray-700 hover:text-blue-600">
                            Kontak
                        </a>
                    </div>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <div class="ml-3 relative group">
                            <button class="flex items-center space-x-2 text-gray-700 hover:text-blue-600">
                                <span><?php echo $_SESSION['user_name']; ?></span>
                                <i class="fas fa-chevron-down text-sm"></i>
                            </button>
                            <div class="hidden group-hover:block absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5">
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <a href="/admin/dashboard.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Dashboard Admin
                                    </a>
                                <?php else: ?>
                                    <a href="/user/profil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Profil Saya
                                    </a>
                                    <a href="/user/pemesanan.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Pemesanan Saya
                                    </a>
                                <?php endif; ?>
                                <a href="/auth/logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    Keluar
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <a href="/auth/login.php" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            Masuk
                        </a>
                        <a href="/auth/register.php" class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-gray-50">
                            Daftar
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu button -->
        <div class="md:hidden">
            <button type="button" class="mobile-menu-button p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                <span class="sr-only">Buka menu</span>
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Navigation Menu -->
    <div class="hidden md:hidden mobile-menu">
        <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="/" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                Beranda
            </a>
            <a href="/jadwal.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                Jadwal
            </a>
            <a href="/harga.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                Harga
            </a>
            <a href="/kontak.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50">
                Kontak
            </a>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
