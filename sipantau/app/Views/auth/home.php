<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | SiPantau</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">

    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center gap-3 mb-2">
                <img src="<?= base_url('assets/gambar/LOGO_BPS.png') ?>" 
                     alt="Logo SiPantau" 
                     class="h-10 w-10 object-contain">
                <h1 class="text-2xl font-bold text-gray-800">SiPantau</h1>
            </div>
            <p class="text-gray-500 mt-2">Silahkan login sesuai peran Anda</p>
        </div>

        <!-- Button Login Pegawai -->
        <a href="<?= base_url('login/sso') ?>"
           class="w-full flex items-center justify-center gap-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition mb-4">
             Login sebagai Pegawai
        </a>

        <!-- Divider -->
        <div class="flex items-center my-4">
            <div class="flex-grow h-px bg-gray-300"></div>
            <span class="px-3 text-sm text-gray-400">atau</span>
            <div class="flex-grow h-px bg-gray-300"></div>
        </div>

        <!-- Button Login Mitra -->
        <a href="<?= base_url('login') ?>"
           class="w-full flex items-center justify-center gap-3 bg-gray-800 hover:bg-gray-900 text-white font-semibold py-3 rounded-xl transition">
             Login sebagai Mitra
        </a>

        <!-- Footer -->
        <p class="text-center text-xs text-gray-400 mt-8">
            © <?= date('Y') ?> Badan Pusat Statistik Provinsi Riau
        </p>
    </div>

</body>
</html>