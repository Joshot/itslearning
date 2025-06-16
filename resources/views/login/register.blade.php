<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen flex">
<div class="flex w-full h-full flex-col md:flex-row">
    <div class="w-full md:w-1/2 md:h-auto bg-cover bg-center hidden md:block" style="background-image: url('/images/login_motto.jpg'); background-size: cover; background-position: top;"></div>
    <div class="w-full md:w-1/2 bg-white flex flex-col justify-center items-center p-4 sm:p-6 md:p-8 min-h-screen">
        <img src="/images/login_kecil_warna.png" alt="Logo" class="mb-4 w-32 sm:w-40 md:w-48">
        <hr class="w-full border-gray-300 mb-4">
        <h1 style="color: #54606b" class="font-bold text-xl sm:text-2xl md:text-3xl mb-6">Register Regular S1/D3</h1>

        <!-- Error Notification -->
        @if (session('error') || $errors->any())
        <div id="error-message" class="w-full max-w-xs sm:max-w-sm md:max-w-md bg-red-500 text-white text-center p-2 rounded mb-4 text-sm sm:text-base">
            {{ session('error') ?: $errors->first() }}
        </div>
        @endif

        <!-- Success Notification -->
        @if (session('success'))
        <div id="success-message" class="w-full max-w-xs sm:max-w-sm md:max-w-md bg-green-500 text-white text-center p-2 rounded mb-4 text-sm sm:text-base">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('register.process') }}" method="POST" class="w-full max-w-xs sm:max-w-sm md:max-w-md" onsubmit="return validateForm()">
            @csrf
            <div class="mb-4">
                <label for="nim" style="color: #54606b" class="block text-sm sm:text-base">NIM</label>
                <input type="text" name="nim" id="nim" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" value="{{ old('nim') }}" required>
            </div>
            <div class="mb-4">
                <label for="name" style="color: #54606b" class="block text-sm sm:text-base">Name</label>
                <input type="text" name="name" id="name" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" value="{{ old('name') }}" required>
            </div>
            <div class="mb-4">
                <label for="email" style="color: #54606b" class="block text-sm sm:text-base">Email Student</label>
                <input type="email" name="email" id="email" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" value="{{ old('email') }}" required>
            </div>
            <div class="mb-4">
                <label for="password" style="color: #54606b" class="block text-sm sm:text-base">Password</label>
                <input type="password" name="password" id="password" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" required>
            </div>
            <div class="mb-4">
                <label for="password_confirmation" style="color: #54606b" class="block text-sm sm:text-base">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" required>
            </div>
            <div class="mb-4">
                <label for="major" style="color: #54606b" class="block text-sm sm:text-base">Major</label>
                <select name="major" id="major" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" required>
                    <option value="" disabled selected>Pilih Jurusan</option>
                    <option value="Informatika" {{ old('major') == 'Informatika' ? 'selected' : '' }}>Informatika</option>
                    <option value="Pertanian" {{ old('major') == 'Pertanian' ? 'selected' : '' }}>Pertanian</option>
                    <option value="Sistem Informasi" {{ old('major') == 'Sistem Informasi' ? 'selected' : '' }}>Sistem Informasi</option>
                    <option value="Teknik Komputer" {{ old('major') == 'Teknik Komputer' ? 'selected' : '' }}>Teknik Komputer</option>
                    <option value="Biologi" {{ old('major') == 'Biologi' ? 'selected' : '' }}>Biologi</option>
                    <option value="Kedokteran" {{ old('major') == 'Kedokteran' ? 'selected' : '' }}>Kedokteran</option>
                    <option value="Ilmu Komunikasi" {{ old('major') == 'Ilmu Komunikasi' ? 'selected' : '' }}>Ilmu Komunikasi</option>
                    <option value="Manajemen" {{ old('major') == 'Manajemen' ? 'selected' : '' }}>Manajemen</option>
                    <option value="Film" {{ old('major') == 'Film' ? 'selected' : '' }}>Film</option>
                    <option value="DKV" {{ old('major') == 'DKV' ? 'selected' : '' }}>DKV</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="angkatan" style="color: #54606b" class="block text-sm sm:text-base">Angkatan</label>
                <input type="number" name="angkatan" id="angkatan" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" value="{{ old('angkatan') }}" required>
            </div>
            <button type="submit" class="w-full text-white py-2 rounded hover:bg-blue-600 text-sm sm:text-base" style="background: #106587">Register</button>
        </form>
        <p class="text-center mt-4 text-sm sm:text-base">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Already have an account? Login</a>
        </p>
        <p class="text-center mt-2 text-sm sm:text-base">
            <a href="{{ route('lecturer.login') }}" class="text-blue-600 hover:underline">Login as Lecturer</a>
        </p>
    </div>
</div>

<script>
    function validateForm() {
        const nim = document.getElementById("nim").value.trim();
        const name = document.getElementById("name").value.trim();
        const email = document.getElementById("email").value.toLowerCase().trim();
        const password = document.getElementById("password").value;
        const passwordConfirmation = document.getElementById("password_confirmation").value;
        const major = document.getElementById("major").value;
        const angkatan = document.getElementById("angkatan").value;
        const errorMessage = document.getElementById("error-message");

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!nim) {
            errorMessage.innerText = "NIM tidak boleh kosong!";
            return false;
        }
        if (!name) {
            errorMessage.innerText = "Nama tidak boleh kosong!";
            return false;
        }
        if (!email) {
            errorMessage.innerText = "Email tidak boleh kosong!";
            return false;
        }
        if (!emailRegex.test(email)) {
            errorMessage.innerText = "Format email tidak valid!";
            return false;
        }
        if (!password) {
            errorMessage.innerText = "Password tidak boleh kosong!";
            return false;
        }
        if (password.length < 8) {
            errorMessage.innerText = "Password minimal 8 karakter!";
            return false;
        }
        if (password !== passwordConfirmation) {
            errorMessage.innerText = "Konfirmasi password tidak cocok!";
            return false;
        }
        if (!major) {
            errorMessage.innerText = "Jurusan tidak boleh kosong!";
            return false;
        }
        if (!angkatan || angkatan.length !== 4) {
            errorMessage.innerText = "Angkatan harus 4 digit!";
            return false;
        }
        return true;
    }
</script>
</body>
</html>
