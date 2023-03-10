<!DOCTYPE html>
<html lang="{{ \App::getLocale() }}">
<head>
    <title>Plugin MarketManager</title>
    {{-- Laravel Mix - CSS File --}}
    {{-- <link rel="stylesheet" href="{{ mix('css/market-manager.css') }}"> --}}

    @include('MarketManager::commons.head')
</head>

<body>
    <div class="position-relative">
        @yield('content')

        @include('MarketManager::commons.toast')
    </div>

    {{-- Laravel Mix - JS File --}}
    {{-- <script src="{{ mix('js/market-manager.js') }}"></script> --}}
    @include('MarketManager::commons.bodyjs')
</body>
</html>
