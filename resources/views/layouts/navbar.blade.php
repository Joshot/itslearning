<style>
    nav {
        background: #106587;
        color: white;
        padding: 0.5rem 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 1000;
        width: 100%;
        box-sizing: border-box;
    }
    nav img {
        height: 3rem;
    }
    .nav-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .hamburger {
        display: none;
        cursor: pointer;
        flex-direction: column;
        justify-content: space-between;
        width: 24px;
        height: 18px;
    }
    .hamburger span {
        background: white;
        height: 3px;
        width: 100%;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    .hamburger.active span:nth-child(1) {
        transform: rotate(45deg) translate(5px, 5px);
    }
    .hamburger.active span:nth-child(2) {
        opacity: 0;
    }
    .hamburger.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }
    nav a, nav button {
        font-size: 0.875rem;
        color: white;
        transition: color 0.2s ease;
    }
    nav a:hover, nav button:hover {
        color: #e2e8f0;
    }
    .nav-links {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    nav svg {
        cursor: pointer;
        transition: transform 0.2s ease;
    }
    nav svg:hover {
        transform: scale(1.1);
    }

    @media (max-width: 1100px) {
        nav {
            padding: 0.5rem 1rem;
        }
        nav img {
            height: 2.5rem;
        }
        .hamburger {
            display: flex;
        }
        .nav-links {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #106587;
            flex-direction: column;
            padding: 1rem;
            width: 100%;
            box-sizing: border-box;
            z-index: 999;
            display: none;
        }
        .nav-links.active {
            display: flex;
        }
        .nav-links a, .nav-links button, .nav-links span {
            font-size: 1rem;
            padding: 0.5rem 0;
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 480px) {
        nav {
            padding: 0.5rem;
        }
        nav img {
            height: 2rem;
        }
        .nav-links a, .nav-links button, .nav-links span {
            font-size: 0.75rem;
        }
    }
</style>

<nav class="text-white p-2 flex justify-between items-center">
    <div>
        <img src="/images/logo_kecil_putih.png" alt="Logo" class="h-12 pl-6">
    </div>
    <div class="nav-menu pr-6">
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav-links">
            <div class="relative">
                <svg class="h-6 w-6 text-white cursor-pointer" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2a7 7 0 00-7 7v3.17a3 3 0 00-.83 2.07V16a3 3 0 003 3h10a3 3 0 003-3v-1.76a3 3 0 00-.83-2.07V9a7 7 0 00-7-7zM9 19a3 3 0 006 0z" />
                </svg>
            </div>
            <div class="relative">
                <svg class="h-6 w-6 text-white cursor-pointer" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M21 6.5a2.5 2.5 0 00-2.5-2.5h-13A2.5 2.5 0 003 6.5v7A2.5 2.5 0 005.5 16H8v3l4-3h6.5A2.5 2.5 0 0021 13.5v-7z" />
                </svg>
            </div>
            @if (Auth::guard('lecturer')->check())
            <span class="text-white font-semibold">{{ Auth::guard('lecturer')->user()->name ?? 'Guest' }}</span>
            <form action="{{ route('lecturer.logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-white">Logout</button>
            </form>
            @elseif (Auth::guard('student')->check())
            <span class="text-white font-semibold">{{ Auth::guard('student')->user()->name ?? 'Guest' }}</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-white">Logout</button>
            </form>
            @else
            <a href="{{ route('login') }}" class="text-white">Student Login</a>
            <a href="{{ route('lecturer.login') }}" class="text-white">Lecturer Login</a>
            @endif
        </div>
    </div>
</nav>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const hamburger = document.querySelector(".hamburger");
        const navLinks = document.querySelector(".nav-links");

        hamburger.addEventListener("click", () => {
            hamburger.classList.toggle("active");
            navLinks.classList.toggle("active");
        });

        // Close menu when clicking outside
        document.addEventListener("click", (event) => {
            if (!hamburger.contains(event.target) && !navLinks.contains(event.target)) {
                hamburger.classList.remove("active");
                navLinks.classList.remove("active");
            }
        });
    });
</script>
