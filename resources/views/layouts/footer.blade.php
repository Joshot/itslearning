<style>
    footer {
        background: #106587;
        color: white;
        padding: 2rem 1rem;
        width: 100%;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    footer .grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        max-width: 1200px;
        width: 100%;
        justify-items: center;
        text-align: center;
    }
    footer h2 {
        font-size: 1.125rem;
        margin-bottom: 0.5rem;
        font-weight: bold;
    }
    footer p, footer a {
        font-size: 0.875rem;
        margin: 0.25rem 0;
    }
    footer a:hover {
        text-decoration: underline;
    }
    footer img {
        max-width: 12rem;
    }
    footer .border-t {
        border-color: rgba(255, 255, 255, 0.3);
        width: 100%;
        max-width: 1200px;
        margin: 1rem 0;
    }
    footer .copyright {
        text-align: center;
        width: 100%;
    }

    @media (max-width: 1100px) {
        footer .grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        footer img {
            display: none;
        }
    }

    @media (max-width: 480px) {
        footer {
            padding: 1rem;
        }
        footer p, footer a {
            font-size: 0.75rem;
        }
        footer h2 {
            font-size: 1rem;
        }
    }
</style>

<footer class="text-white">
    <div class="grid">
        <!-- Helpdesk -->
        <div>
            <h2>Helpdesk</h2>
            <p>Jalan Sempoyongan Blok A900 No 76</p>
            <p>Perumahan Elite, Tangerang</p>
            <p>Banten 11111</p>
        </div>

        <!-- Contact -->
        <div>
            <h2>Contact</h2>
            <p>Phone: (021) 6712 2832</p>
            <p>Email: elearningku.edu</p>
            <p>Whatsapp: 081202836128</p>
        </div>

        <!-- Quick Links -->
        <div>
            <h2>Social Media</h2>
            <ul class="space-y-2">
                <li><a href="#" class="hover:underline">Instagram</a></li>
                <li><a href="#" class="hover:underline">LinkedIn</a></li>
                <li><a href="#" class="hover:underline">Facebook</a></li>
                <li><a href="#" class="hover:underline">Tiktok</a></li>
                <li><a href="#" class="hover:underline">X</a></li>
            </ul>
        </div>

        <!-- Gambar -->
        <div>
            <img src="/images/logo_besar.png" alt="Footer Image">
        </div>
    </div>

    <!-- Garis -->
    <div class="border-t"></div>

    <!-- Copyright -->
    <p class="copyright">© 2025 E-LearningKu Joshua Ho</p>
</footer>
