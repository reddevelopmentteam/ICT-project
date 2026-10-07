<!DOCTYPE html>
@php
    $siteSettings = $siteSettings ?? [];
    $siteLogo = \App\Models\Setting::resolveAssetUrl($siteSettings['logo'] ?? null, asset('assets/img/logo_smkn_2.png'));
    $siteName = $siteSettings['nama_website'] ?? config('app.name');
    $browserRefreshVersion = $siteSettings['system.browser_refresh_version'] ?? '';
    // Same server-readable state as the sidebar partial: used as Alpine's default
    // so client init agrees with the server-rendered markup (no post-init flip).
    $isSidebarCollapsed = request()->cookie('sidebar-collapsed') === '1';
@endphp

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Read persisted sidebar state BEFORE first paint so the server-rendered
             (always expanded) sidebar never flashes on load or wire:navigate.
             Key must match Alpine $persist(...).as('sidebar-collapsed') on <body>,
             which stores the value JSON-encoded. -->
        <script>
            try {
                if (JSON.parse(localStorage.getItem('sidebar-collapsed')) === true) {
                    document.documentElement.setAttribute('data-sidebar-collapsed', 'true');
                }
            } catch (e) {}
        </script>

        <title>{{ $title ?? $siteName }}</title>

        <!-- favicon -->
        <link rel="shortcut icon" href="{{ $siteLogo }}" type="image/x-icon">

        <!-- Fonts loaded via app.css @import -->

        <!-- Register Iconify before the sidebar is parsed to prevent late icon pop-in. -->
        <link rel="preconnect" href="https://code.iconify.design" crossorigin>
        <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
        
        {!! ToastMagic::styles() !!}

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>

    <body class="cursor-auto bg-blue-50 overflow-hidden" x-data="{ openside: false, isSidebarCollapsed: $persist({{ $isSidebarCollapsed ? 'true' : 'false' }}).as('sidebar-collapsed'), sidebarGroupExpand(e) { const btn = e.target && e.target.closest ? e.target.closest('#app-sidebar .sidebar-group-btn') : null; if (!btn || !this.isSidebarCollapsed) return; this.isSidebarCollapsed = false; if (window.matchMedia('(min-width: 768px)').matches) e.stopPropagation(); } }" x-init="$watch('isSidebarCollapsed', (v) => { try { document.cookie = 'sidebar-collapsed=' + (v ? '1' : '0') + '; path=/; max-age=31536000; SameSite=Lax'; if (v) { document.documentElement.setAttribute('data-sidebar-collapsed', 'true'); } else { document.documentElement.removeAttribute('data-sidebar-collapsed'); } } catch (e) {} })" @click.capture="sidebarGroupExpand($event)">
        <main class="h-screen flex justify-start min-w-0" >
            <livewire:components.sidebar :key="'layout-sidebar'" />
            <div class="flex-1 flex flex-col min-h-0 min-w-0 overflow-y-auto overflow-x-clip" >
                <div class="p-4">
                    <livewire:components.header :key="'layout-header'" />
                    {{ $slot }}
                </div>
            </div>
        </main>
        @if (app()->environment('local'))
            <livewire:components.test-time :key="'layout-test-time-app'" />
        @endif

        @livewireScripts

        {!! ToastMagic::scripts() !!}

        <!-- Keep pre-paint sidebar state across Livewire SPA navigations.
             replaceHtmlAttributes() strips data-sidebar-collapsed from <html> on every
             swap (server HTML never has it), so re-assert it from the same persisted
             key synchronously via onSwap — same task as the DOM swap, before paint.
             data-navigate-once: register this document-level listener exactly once. -->
        <script data-navigate-once>
            document.addEventListener('alpine:navigating', (event) => {
                event.detail.onSwap(() => {
                    try {
                        if (JSON.parse(localStorage.getItem('sidebar-collapsed')) === true) {
                            document.documentElement.setAttribute('data-sidebar-collapsed', 'true');
                        } else {
                            document.documentElement.removeAttribute('data-sidebar-collapsed');
                        }
                    } catch (e) {}
                });
            });
        </script>

        @auth
            <x-browser-refresh-listener :version="$browserRefreshVersion" />
        @endauth
    </body>
</html>
