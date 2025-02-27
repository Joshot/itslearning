<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-screen flex">
<div class="flex w-full h-full flex-col md:flex-row">
    <div class="w-full md:w-3/4 bg-cover bg-center hidden md:block" style="background-image: url('/images/login_motto.jpg');"></div>
    <div class="w-full md:w-1/4 bg-white flex flex-col justify-center items-center p-8">
        <img src="/images/login_kecil_warna.png" alt="Logo" class="mb-4">
        <hr class="w-full border-gray-300 mb-4">
        <h1 style="color: #54606b" class="text-2xl mb-6">Sign In Regular S1/D3</h1>

        <!-- Error Notification -->
        @if(session('error'))
        <div id="error-message" class="w-full bg-red-500 text-white text-center p-2 rounded mb-4">
            {{ session('error') }}
        </div>
        @endif


        <form action="/login" method="GET" class="w-full" onsubmit="return validateForm()">
            @csrf
            <div class="mb-4">
                <label for="email" style="color: #54606b" class="block">Email Student</label>
                <input type="email" name="email" id="email" class="w-full p-2 border border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
                <label for="password" style="color: #54606b" class="block">Password</label>
                <input type="password" name="password" id="password" class="w-full p-2 border border-gray-300 rounded mt-1" required>
            </div>
            <button type="submit" class="w-full text-white py-2 rounded hover:bg-blue-600" style="background: #173967">Login</button>
        </form>
    </div>
</div>

<script>
    function validateForm() {
        var email = document.getElementById("email").value;
        var password = document.getElementById("password").value;
        var errorMessage = document.getElementById("error-message");

        if (email === "" || password === "") {
            errorMessage.innerText = "Email dan Password tidak boleh kosong!";
            errorMessage.classList.remove("hidden");
            return false;
        }
        return true;
    }
</script>
</body>
</html>
