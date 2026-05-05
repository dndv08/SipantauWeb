<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - SiPantau</title>
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/gambar/LOGO_BPS.png') ?>">

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .animated-gradient {
            background: linear-gradient(-45deg, #1e40af, #2563eb, #3b82f6, #60a5fa);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="min-h-screen animated-gradient flex items-center justify-center p-4">
    
    <!-- Main Container -->
    <div class="max-w-4xl w-full">
        
        <!-- Card -->
        <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-2xl p-8 md:p-12 lg:p-16 text-center">
            
            <!-- Icon/Illustration -->
            <div class="mb-8 flex justify-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-500 opacity-20 blur-3xl rounded-full pulse-slow"></div>
                    <div class="relative w-32 h-32 md:w-40 md:h-40 bg-gradient-to-br from-blue-600 to-blue-800 rounded-full flex items-center justify-center float-animation shadow-2xl">
                        <i class="fas fa-rocket text-5xl md:text-6xl text-white"></i>
                    </div>
                </div>
            </div>
            
            <!-- Logo BPS -->
            <div class="mb-6 flex justify-center">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-lg flex items-center justify-center">
                        <img src="<?= base_url('assets/gambar/LOGO_BPS.png') ?>" alt="Logo BPS" class="w-12 h-12 object-contain"/>
                    </div>
                    <div class="text-left">
                        <h3 class="text-xl font-bold text-gray-900">SiPantau</h3>
                        <p class="text-xs text-gray-600">Badan Pusat Statistik</p>
                    </div>
                </div>
            </div>
            
            <!-- Main Heading -->
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-gray-900 mb-4">
                Coming Soon!    
            </h1>
            
            <!-- Subheading -->
            <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-2xl mx-auto">
                Halaman ini sedang dalam tahap pengembangan. <br class="hidden sm:block">
                Kami bekerja keras untuk menghadirkan fitur terbaik untuk Anda.
            </p>
            
            <!-- Features Preview -->
            <!-- <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 md:gap-6 mb-10 max-w-3xl mx-auto">
                
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 hover:scale-105">
                    <div class="w-14 h-14 bg-blue-600 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-bolt text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Cepat & Efisien</h3>
                    <p class="text-sm text-gray-600">Akses data lapangan dengan cepat dan mudah</p>
                </div>
                
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 hover:scale-105">
                    <div class="w-14 h-14 bg-blue-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-shield-alt text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Aman & Terpercaya</h3>
                    <p class="text-sm text-gray-600">Keamanan data adalah prioritas kami</p>
                </div>
                
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300 hover:scale-105">
                    <div class="w-14 h-14 bg-blue-800 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-line text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Real-time Monitoring</h3>
                    <p class="text-sm text-gray-600">Pantau progres kegiatan secara real-time</p>
                </div>
                
            </div> -->
            
            <!-- Back Button -->
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="javascript:history.back()" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Dashboard
                </a>
                
                <a href="mailto:support@sipantau.bps.go.id" class="inline-flex items-center px-8 py-4 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-xl hover:border-gray-400 hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-envelope mr-2"></i>
                    Hubungi Tim
                </a>
            </div>
            
            <!-- Footer Info -->
            <div class="mt-10 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    <i class="far fa-clock mr-2"></i>
                    Fitur ini akan segera hadir. Terima kasih atas kesabaran Anda.
                </p>
            </div>
            
        </div>
        
        <!-- Bottom Text -->
        <div class="mt-8 text-center">
            <p class="text-white/90 text-sm drop-shadow-lg">
                &copy; 2025 SiPantau - BPS Provinsi Riau. All rights reserved.
            </p>
        </div>
        
    </div>
    
</body>
</html>