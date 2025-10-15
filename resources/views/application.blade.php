<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" href="{{ asset('favicon.ico') }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portal de Códigos - Ventas</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('loader.css') }}" />
    @vite(['resources/js/main.js'])
</head>

<body>
    <div id="app">
        <div id="loading-bg">
            <div class="loading-logo">
                <!-- svg logo -->
                <svg xmlns="http://www.w3.org/2000/svg" width="86" height="86" viewBox="0 0 40 40"
                    fill="none">
                    <!-- Fondo circular degradado -->
                    <circle cx="20" cy="20" r="20" fill="url(#grad1)" />

                    <!-- Letra P -->
                    <path d="M12 10 L12 30 M12 10 L20 10 Q24 10 24 15 Q24 20 20 20 L12 20" stroke="white"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />

                    <!-- Letra C -->
                    <path d="M32 14 Q28 10 24 10 Q20 10 20 15 L20 25 Q20 30 24 30 Q28 30 32 26" stroke="white"
                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" fill="none" />

                    <defs>
                        <linearGradient id="grad1" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#6366F1;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#8B5CF6;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>

            </div>
            <div class=" loading">
                <div class="effect-1 effects"></div>
                <div class="effect-2 effects"></div>
                <div class="effect-3 effects"></div>
            </div>
        </div>
    </div>

    <script>
        const loaderColor = localStorage.getItem('materialize-initial-loader-bg') || '#FFFFFF'
        const primaryColor = localStorage.getItem('materialize-initial-loader-color') || '#666CFF'

        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)
        if (loaderColor)
            document.documentElement.style.setProperty('--initial-loader-bg', loaderColor)

        if (primaryColor)
            document.documentElement.style.setProperty('--initial-loader-color', primaryColor)
    </script>
</body>

</html>
