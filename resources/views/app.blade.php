<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"  @class(['dark' => ($appearance ?? 'system') == 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @php
            $appName = config('app.name', 'Laravel');
            $pageProps = $page['props'] ?? [];
            $seo = $pageProps['seo'] ?? [];
            $appUrl = rtrim((string) ($pageProps['appUrl'] ?? config('app.url', request()->getSchemeAndHttpHost())), '/');

            $seoTitle = (string) ($seo['title'] ?? $appName);
            $seoDescription = (string) ($seo['description'] ?? 'Lets keep it here. Preserve class memories, profiles, and graduation stories in one place.');
            $seoType = (string) ($seo['type'] ?? 'website');
            $seoImage = (string) ($seo['image'] ?? url('/featured.webp'));
            $seoImage = str_starts_with($seoImage, 'http')
                ? $seoImage
                : $appUrl.'/'.ltrim($seoImage, '/');
            $seoUrl = request()->fullUrl();
        @endphp

        <meta name="description" content="{{ $seoDescription }}">
        <meta property="og:site_name" content="{{ $appName }}">
        <meta property="og:type" content="{{ $seoType }}">
        <meta property="og:title" content="{{ $seoTitle }}">
        <meta property="og:description" content="{{ $seoDescription }}">
        <meta property="og:image" content="{{ $seoImage }}">
        <meta property="og:url" content="{{ $seoUrl }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ $seoTitle }}">
        <meta name="twitter:description" content="{{ $seoDescription }}">
        <meta name="twitter:image" content="{{ $seoImage }}">
        <link rel="canonical" href="{{ $seoUrl }}">

        {{-- Inline script to detect system dark mode preference and apply it immediately --}}
        <script>
            (function() {
                const appearance = '{{ $appearance ?? "system" }}';

                if (appearance === 'system') {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

                    if (prefersDark) {
                        document.documentElement.classList.add('dark');
                    }
                }
            })();
        </script>

        {{-- Inline style to set the HTML background color based on our theme in app.css --}}
        <style>
            html {
                background-color: oklch(1 0 0);
            }

            html.dark {
                background-color: oklch(0.145 0 0);
            }
        </style>

        <title inertia>{{ $seoTitle }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.png" type="image/png">
        <link rel="shortcut icon" href="/favicon.ico">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
