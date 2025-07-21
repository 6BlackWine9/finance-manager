<!DOCTYPE html>
<html lang="ru" class="h-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Finance Manager - @yield('title')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        /* Предотвращение мерцания при загрузке */
        html {
            visibility: hidden;
            opacity: 0;
        }
        
        html.loaded {
            visibility: visible;
            opacity: 1;
            transition: opacity 0.3s ease-in-out;
        }
        
        /* Sticky Footer Styles */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
        }
        
        /* Custom styles for better appearance */
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        
        .premium-badge {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        /* Исправление выравнивания кнопки премиум в навигации */
        .navbar-nav .nav-item .btn {
            margin-top: 0;
            margin-bottom: 0;
            display: inline-flex;
            align-items: center;
        }
        
        /* Выравнивание элементов навигации */
        .navbar-nav {
            align-items: center;
        }
        
        .navbar-nav .nav-item {
            display: flex;
            align-items: center;
        }
    </style>
    
    @yield('styles')
</head>
<body class="d-flex flex-column h-100">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="fas fa-chart-line me-2"></i>Finance Manager
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                @auth
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <i class="fas fa-tachometer-alt me-1"></i> Дашборд
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active' : '' }}" href="{{ route('transactions.index') }}">
                            <i class="fas fa-exchange-alt me-1"></i> Транзакции
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                            <i class="fas fa-tags me-1"></i> Категории
                        </a>
                    </li>
                    @if(auth()->user()->isPremium())
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('analytics') ? 'active' : '' }}" href="{{ route('analytics') }}">
                            <i class="fas fa-chart-pie me-1"></i> Аналитика
                        </a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-warning premium-badge">
                            <i class="fas fa-crown me-1"></i> Премиум
                        </span>
                    </li>
                    @endif
                </ul>
                <ul class="navbar-nav">
                    @if(!auth()->user()->isPremium())
                    <li class="nav-item me-3">
                        <a class="btn btn-outline-warning btn-sm" href="{{ route('subscription') }}">
                            <i class="fas fa-crown me-1"></i> Получить Премиум
                        </a>
                    </li>
                    @endif
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('profile.show') }}"><i class="fas fa-user me-1"></i> Профиль</a></li>
                            <li><a class="dropdown-item" href="{{ route('subscription') }}"><i class="fas fa-crown me-1"></i> Подписка</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt me-1"></i> Выйти</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
                @else
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}"><i class="fas fa-sign-in-alt me-1"></i> Войти</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('register') }}"><i class="fas fa-user-plus me-1"></i> Регистрация</a>
                    </li>
                </ul>
                @endauth
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @yield('content')
        </div>
    </main>

    <footer class="bg-light text-center py-3 mt-auto">
        <div class="container">
            <p class="mb-0 text-muted">
                <i class="fas fa-chart-line me-2"></i>Finance Manager &copy; {{ date('Y') }} - Система управления личными финансами
            </p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Предотвращение мерцания контента при загрузке
        document.addEventListener('DOMContentLoaded', function() {
            // Показываем контент после полной загрузки
            document.documentElement.classList.add('loaded');
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                setTimeout(function() {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 5000);
            });
        });
        
        // Альтернативный способ для старых браузеров
        window.addEventListener('load', function() {
            document.documentElement.classList.add('loaded');
        });
    </script>
    
    @yield('scripts')
</body>
</html>
