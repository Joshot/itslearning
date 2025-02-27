<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UMN Portal')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-image: url('/images/background_logo.jpg'); background-size: cover; background-position: center;" class="flex flex-col min-h-screen">

@include('layouts.navbar')

<main class="flex-grow p-8">
    @yield('content')
</main>

@include('layouts.footer')

</body>
</html>
