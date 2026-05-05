<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="<?= base_url('assets/css/output.css') ?>" rel="stylesheet">
    <link rel="shortcut icon" type="image/png" href="<?= base_url('assets/gambar/LOGO_BPS.png') ?>">

</head>
<body class="min-h-screen bg-gray-50">
    <div class="min-h-screen flex">
        <!-- Left Side - Form -->
        <div class="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
            <div class="mx-auto w-full max-w-sm lg:w-96 login-form">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Login Mitra</h1>
                    <p class="text-gray-600">Masukkan Sobat Id dan Password untuk Login!</p>
                </div>


                <!-- Divider -->
                <div class="relative mb-6">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-50 text-gray-500"></span>
                    </div>
                </div>

                <!-- Login Form -->
                <form class="space-y-6" action="<?= base_url('auth/login') ?>" method="POST">
    <?= csrf_field() ?>

    <div>
        <label for="email" class="block text-sm font-medium text-gray-900 mb-2">
            Email<span class="text-red-500">*</span>
        </label>
        <input id="email" name="email" type="email" required
               class="input-field w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
               placeholder="example@email.com" value="<?= old('email') ?>">
        <?php if (session('errors.email')): ?>
            <p class="mt-1 text-sm text-red-600"><?= session('errors.email') ?></p>
        <?php endif; ?>
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-900 mb-2">
            Password<span class="text-red-500">*</span>
        </label>
        <input id="password" name="password" type="password" required
               class="input-field w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 placeholder-gray-400"
               placeholder="Enter your password">
        <?php if (session('errors.password')): ?>
            <p class="mt-1 text-sm text-red-600"><?= session('errors.password') ?></p>
        <?php endif; ?>
    </div>

    <!-- Error Message -->
    <?php if (session('error')): ?>
        <div class="flash-message bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-600"><?= session('error') ?></p>
        </div>
    <?php endif; ?>

    <!-- Success Message -->
    <?php if (session('success')): ?>
        <div class="flash-message bg-green-50 border border-green-200 rounded-lg p-3">
            <p class="text-sm text-green-600"><?= session('success') ?></p>
        </div>
    <?php endif; ?>

    <button type="submit"
            class="btn-primary w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
        Login
    </button>
</form>


            </div>
        </div>

        <!-- Right Side - Brand & Background -->
        <div class="hidden lg:block relative w-0 flex-1 gradient-bg pattern-bg bg-blue-700">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center text-white brand-section">
                    <div class="flex items-center justify-center mb-6">
                        
                        <div class="w-12 h-12 bg-white bg-opacity-20 rounded-lg flex items-center justify-center mr-4">
                            <img src="<?= base_url('assets/gambar/LOGO_BPS.png') ?>" alt="Logo BPS" class="w-12 h-12 object-contain"/>

                        </div>
                        <h2 class="text-3xl font-bold">SiPantau</h2>
                    </div>
                    <p class="text-lg text-white text-opacity-90 max-w-md">
                        Sistem Pelaporan Kegiatan Lapangan BPS Provinsi Riau
                    </p>
                </div>
            </div>
            
            <!-- Decorative elements -->
            <div class="decorative-box floating absolute top-10 left-10 w-20 h-20 bg-white bg-opacity-10 rounded-lg"></div>
            <div class="decorative-box floating absolute top-32 right-20 w-16 h-16 bg-white bg-opacity-10 rounded-lg"></div>
            <div class="decorative-box floating absolute bottom-20 left-20 w-24 h-24 bg-white bg-opacity-10 rounded-lg"></div>
            <div class="decorative-box floating absolute bottom-40 right-10 w-12 h-12 bg-white bg-opacity-10 rounded-lg"></div>
        </div>
    </div>

    <script src="<?= base_url('assets/js/login.js') ?>"></script>
</body>
</html>