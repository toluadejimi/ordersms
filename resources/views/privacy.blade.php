<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('images/favicon.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <title>{{ config('app.name') }} - Online SMS Verification</title>
    <link rel="preload" as="style" href="{{ asset('css/app-ChsQTuQo.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app-ChsQTuQo.css') }}">
    <link href="{{ asset('css/aos.css') }}" rel="stylesheet">
    <script src="{{ asset('js/aos.js') }}"></script>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            position: relative;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        footer {
            margin-top: auto;
            margin-bottom: 0;
        }
        * {
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body class="font-manrope min-h-screen flex flex-col m-0 p-0">
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <!-- Rounded Background Container -->
            <div class="bg-[#21014D] rounded-full px-6 py-3 flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center">
    <a href="{{ url('/') }}" class="flex items-center">
        <img src="{{ asset('images/logo.png') }}" alt="chokesms Logo" class="h-8">
    </a>
</div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-6">
                    <a href="/register" class="flex items-center gap-2 text-white text-sm font-medium hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m8-4a4 4 0 100-8 4 4 0 000 8z"></path>
                        </svg>
                        Get Started Now
                    </a>
                    <div class="h-4 w-[1px] bg-white/30"></div>
                    <div class="flex items-center gap-4">
                        <a href="/login" class="flex items-center gap-1 text-white text-sm font-medium hover:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"></path>
                            </svg>
                            Log in
                        </a>
                        <a href="https://www.chokesms.com/disclaimer" class="flex items-center gap-1 text-white text-sm font-medium hover:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Disclaimer
                        </a>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center gap-4">
                    
                    <button class="text-white" id="navbar-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="bg-[#21014D] mx-4 mt-2 rounded-2xl px-6 py-4 shadow-lg">
                <div class="flex justify-between items-center mb-4">

                    <div class="flex flex-col gap-4">
                        <a href="/register" class="flex items-center gap-2 text-white hover:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4m8-4a4 4 0 100-8 4 4 0 000 8z"></path>
                            </svg>
                            Register
                        </a>
                    </div>
                    <button id="close-menu" class="p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 6L6 18M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div class="flex flex-col gap-4">
                    <a href="/login" class="flex items-center gap-2 text-white hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M13.8 12H3"></path>
                        </svg>
                        Log in
                    </a>
                    <a href="" class="flex items-center gap-2 text-white hover:text-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Disclaimer
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Spacer to prevent content from hiding behind fixed navbar -->
    <div class="h-20"></div>

    <script>
        document.getElementById('navbar-toggle').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.remove('hidden');
        });

        document.getElementById('close-menu').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.add('hidden');
        });
    </script>
    
    
    
 <!-- Main Content -->
    <main class="flex-1 py-12 px-4 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-4 text-gray-900">Privacy Policy</h1>
        <p class="text-sm text-gray-500 mb-8">Effective Date: May 28, 2025</p>

        <p class="mb-6">
            Welcome to <strong>ChokeSMS</strong>. Your privacy is important to us. This Privacy Policy explains how we collect, use, and protect your personal information when you use our mobile and web platforms.
        </p>

        <h2 class="text-xl font-semibold mt-8 mb-2">1. Information We Collect</h2>
        <ul class="list-disc list-inside space-y-1 mb-6">
            <li><strong>Personal Information:</strong> Name, email, phone number, referral code.</li>
            <li><strong>Device Information:</strong> IP address, browser type, OS, device ID.</li>
            <li><strong>Usage Data:</strong> Services accessed, transactions, wallet balance.</li>
            <li><strong>Verification Data:</strong> Purchased numbers, SMS codes, selected services.</li>
            <li><strong>Location Data:</strong> Approximate location used for fraud detection.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-8 mb-2">2. How We Use Your Information</h2>
        <ul class="list-disc list-inside space-y-1 mb-6">
            <li>To create and manage your ChokeSMS account.</li>
            <li>To deliver SMS verification and virtual number services.</li>
            <li>To process wallet funding and transactions.</li>
            <li>To improve and optimize platform performance.</li>
            <li>To detect fraud and unauthorized access.</li>
            <li>To communicate updates, offers, and service alerts.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-8 mb-2">3. Sharing Your Information</h2>
        <p class="mb-4">We do not sell your personal data. We may share information with trusted services such as:</p>
        <ul class="list-disc list-inside space-y-1 mb-6">
            <li>Virtual number providers.</li>
            <li>Payment processors (e.g., Paystack, Flutterwave).</li>
            <li>Regulatory agencies when legally required.</li>
            <li>Analytics platforms (e.g., Firebase, Sentry).</li>
        </ul>

        <h2 class="text-xl font-semibold mt-8 mb-2">4. Data Storage and Security</h2>
        <ul class="list-disc list-inside space-y-1 mb-6">
            <li>Data is encrypted and securely stored on our servers.</li>
            <li>We enforce strict access controls and session validation.</li>
            <li>Payment and wallet data is securely handled and never stored in plaintext.</li>
        </ul>

        <h2 class="text-xl font-semibold mt-8 mb-2">5. Your Rights</h2>
        <ul class="list-disc list-inside space-y-1 mb-6">
            <li>View and update your profile information.</li>
            <li>Request account deletion at any time.</li>
            <li>Opt out of promotional messages.</li>
            <li>Withdraw consent where applicable.</li>
        </ul>
        <p class="mb-6">
            To make any request, email us at <a href="mailto:support@chokesms.com" class="text-blue-600 underline">support@chokesms.com</a>.
        </p>

        <h2 class="text-xl font-semibold mt-8 mb-2">6. Cookies and Tracking</h2>
        <p class="mb-6">
            We may use cookies to manage sessions, remember preferences, and analyze site traffic. You can disable cookies in your browser settings.
        </p>

        <h2 class="text-xl font-semibold mt-8 mb-2">7. Children's Privacy</h2>
        <p class="mb-6">
            ChokeSMS is not intended for users under 13. We do not knowingly collect information from minors.
        </p>

        <h2 class="text-xl font-semibold mt-8 mb-2">8. Changes to This Policy</h2>
        <p class="mb-6">
            We may update this Privacy Policy at any time. Significant changes will be communicated via the app or email.
        </p>

        <h2 class="text-xl font-semibold mt-8 mb-2">9. Contact Us</h2>
        <p>
            Email: <a href="mailto:support@chokesms.com" class="text-blue-600 underline">support@chokesms.com</a><br>
            Telegram: <a href="https://t.me/chokesmscc" class="text-blue-600 underline" target="_blank">https://t.me/chokesmscc</a><br>
            Website: <a href="https://chokesms.com" class="text-blue-600 underline" target="_blank">https://chokesms.com</a>
        </p>
    </main>
    
    
    
    
     <footer class="relative w-full mt-auto">
        <!-- Wave Separator -->
        <div class="absolute top-0 left-0 w-full overflow-hidden leading-none rotate-180">
            <svg class="relative w-full h-12 md:h-24" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="fill-[#21014D]/10"></path>
            </svg>
        </div>

        <!-- Main Footer Content -->
        <div class="bg-gradient-to-br from-[#21014D] to-[#21014D]/90 pt-20 pb-6">
            <div class="container mx-auto px-4">
                <!-- Newsletter Section -->
                <div class="relative max-w-3xl mx-auto mb-16 text-center">
                    <div class="absolute inset-0 bg-[#8E63F0]/20 blur-3xl rounded-full"></div>
                    <div class="relative">
                        <h3 class="text-xl md:text-2xl font-bold text-white mb-4">Never Miss an Update</h3>
                        <div class="flex flex-wrap gap-4 justify-center items-center">
                            <a href="http://instagram.com/chokesms" class="group bg-white/10 backdrop-blur-sm px-6 py-3 rounded-xl hover:bg-[#8E63F0] transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 fill-current text-white" viewBox="0 0 24 24">
                                        <path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.897 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.897-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"></path>
                                    </svg>
                                    <span class="text-white text-sm">Follow on Instagram</span>
                                </div>
                            </a>
                            <a href="https://t.me/chokesmscommunity" class="group bg-white/10 backdrop-blur-sm px-6 py-3 rounded-xl hover:bg-[#8E63F0] transition-all duration-300">
                                <div class="flex items-center gap-3">
                                    <svg class="w-5 h-5 fill-current text-white" viewBox="0 0 24 24">
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.894 8.221l-1.97 9.28c-.145.658-.537.818-1.084.508l-3-2.21-1.446 1.394c-.14.18-.357.295-.6.295-.002 0-.003 0l.213-3.054 5.56-5.022c.24-.213-.054-.334-.373-.121l-6.869 4.326-2.96-.924c-.64-.203-.654-.64.135-.954l11.566-4.458c.538-.196 1.006.128.832.941z"></path>
                                    </svg>
                                    <span class="text-white text-sm">Join Telegram</span>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Main Links Section -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8 mb-12">
                    <!-- Logo & About -->
                    <div class="col-span-2 md:col-span-4 text-center mb-8">
    <img src="{{ asset('images/logo.png') }}" alt="chokesms Logo" class="h-8 mx-auto mb-4">
    <p class="text-white text-xs md:text-sm max-w-xl mx-auto">
        Your trusted partner for seamless global connections, providing reliable international numbers to receive verification codes anytime, anywhere
    </p>
</div>

                    <!-- Quick Links Columns -->
                    <div class="space-y-4 text-center">
                        <a href="/" class="block">
                            <span class="text-white text-xs md:text-sm font-medium hover:text-[#8E63F0] transition-colors">Home</span>
                        </a>
                        <a href="/#how-it-works" class="block">
                            <span class="text-white text-xs md:text-sm font-medium hover:text-[#8E63F0] transition-colors">How it Works</span>
                        </a>
                    </div>

                    <!-- Contact Info -->
                    <div class="space-y-4 text-center">
                        <a href="mailto:support@chokesms.com" class="block">
                            <span class="text-white text-xs md:text-sm hover:text-[#8E63F0] transition-colors">support@chokesms.com</span>
                        </a>
                    </div>

                    <!-- Stats -->
                    <div class="space-y-3 text-center">
                        <div>
                            <div class="text-white text-lg md:text-xl font-bold">24/7</div>
                            <div class="text-white text-xs">Support</div>
                        </div>
                    </div>

                    <!-- Trust Badge -->
                    <div class="space-y-3 text-center">
                        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-3 inline-block">
                            <div class="text-white text-lg md:text-xl font-bold">100%</div>
                            <div class="text-white text-xs">Secure</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="relative">
            <!-- Animated Gradient Line -->
            <div class="h-px bg-gradient-to-r from-[#21014D] to-[#21014D]/90 animate-pulse"></div>

        <!-- Copyright -->
            <div class="pt-6 flex flex-col md:flex-row items-center justify-between gap-4 bg-gradient-to-br from-[#21014D] to-[#21014D]/90 px-12">
                <div class="text-[10px] md:text-xs text-white">
                    All rights Reserved
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2 bg-white/5 backdrop-blur-sm px-3 py-1.5 rounded-full">
                        <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                        <span class="text-[10px] md:text-xs text-white">Copyright © 2025 chokesms.</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        AOS.init({
            duration: 1000,
            easing: 'ease-out-cubic',
            once: true
        });
    </script>

    <!-- Add this to your CSS for the marquee animation -->
    <style>
        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        .animate-scroll {
            animation: scroll 30s linear infinite;
        }

        .animate-scroll:hover {
            animation-play-state: paused;
        }
    </style>

    <!-- Remove the carousel dots or any other elements after footer -->
    <script>
        // Remove any carousel dots if they exist
        document.addEventListener('DOMContentLoaded', function() {
            const dots = document.querySelectorAll('.carousel-dots, .slick-dots');
            dots.forEach(dot => dot.remove());
        });
    </script>


</body></html>