<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen flex">
<div class="flex w-full h-full flex-col max-[765px]:flex-col md:flex-row">
    <div class="w-full max-[765px]:h-1/5 md:w-1/2 md:h-auto bg-cover bg-center max-[765px]:block md:block hidden" style="background-image: url('/images/login_motto.jpg'); background-size: cover; background-position: top;"></div>
    <div class="w-full max-[765px]:h-4/5 md:w-1/2 md:h-auto bg-white flex flex-col justify-center items-center p-4 sm:p-6 md:p-8 min-h-screen max-[765px]:min-h-0">
        <img src="/images/login_kecil_warna.png" alt="Logo" class="mb-4 w-32 sm:w-40 md:w-48">
        <hr class="w-full border-gray-300 mb-4">
        <h1 style="color: #54606b" class="font-bold text-xl sm:text-2xl md:text-3xl mb-6">Sign In Lecturer</h1>

        <!-- Error Notification -->
        @if (session('error') || $errors->any())
        <div id="error-message" class="w-full bg-red-500 text-white text-center p-2 rounded mb-4 text-sm sm:text-base">
            {{ session('error') ?: $errors->first() }}
        </div>
        @endif

        <form action="{{ route('lecturer.login.process') }}" method="POST" class="w-full max-w-xs sm:max-w-sm md:max-w-md" onsubmit="return validateForm()">
            @csrf
            <div class="mb-4">
                <label for="email" style="color: #54606b" class="block text-sm sm:text-base">Email Lecturer</label>
                <input type="email" name="email" id="email" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" value="{{ old('email') }}" required>
            </div>
            <div class="mb-4">
                <label for="password" style="color: #54606b" class="block text-sm sm:text-base">Password</label>
                <input type="password" name="password" id="password" class="w-full p-2 border border-gray-300 rounded mt-1 text-sm sm:text-base" required>
            </div>
            <button type="submit" class="w-full text-white py-2 rounded hover:bg-blue-600 text-sm sm:text-base" style="background: #106587">Login</button>
        </form>

        <p class="text-center mt-4 text-sm sm:text-base">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Login as Student</a>
        </p>
    </div>
</div>

<script>
    function validateForm() {
        var email = document.getElementById("email").value.toLowerCase().trim();
        var password = document.getElementById("password").value;
        var errorMessage = document.getElementById("error-message");

        // Validasi email kosong atau format salah
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email) {
            errorMessage.innerText = "Email tidak boleh kosong!";
            errorMessage.classList.remove("hidden");
            return false;
        }
        if (!emailRegex.test(email)) {
            errorMessage.innerText = "Format email tidak valid!";
            errorMessage.classList.remove("hidden");
            return false;
        }
        if (!password) {
            errorMessage.innerText = "Password tidak boleh kosong!";
            errorMessage.classList.remove("hidden");
            return false;
        }
        return true;
    }
</script>
</body>
</html>
