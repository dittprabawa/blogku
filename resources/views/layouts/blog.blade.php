<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('meta_title', config('app.name', 'Veloflex.'))</title>
    <meta name="description" content="@yield('meta_description', 'Kumpulan artikel dan tulisan terbaru seputar teknologi dan gaya hidup.')">
    <link rel="canonical" href="@yield('canonical', url()->current())">
    <link rel="manifest" href="/site.webmanifest">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
    <meta name="theme-color" content="#0b0b0f">

    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('meta_title', config('app.name', 'Veloflex.'))">
    <meta property="og:description" content="@yield('meta_description', 'Kumpulan artikel dan tulisan terbaru seputar teknologi dan gaya hidup.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/og-default.png'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:image" content="@yield('og_image', asset('images/og-default.png'))">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', config('app.name', 'Veloflex.'))">
    <meta name="twitter:description" content="@yield('meta_description', 'Kumpulan artikel dan tulisan terbaru seputar teknologi dan gaya hidup.')">
    <link rel="alternate" type="application/rss+xml" title="{{ config('app.name', 'Veloflex.') }} RSS Feed" href="{{ route('feed') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500;1,9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <style>
        * { box-sizing: border-box; }
        html, body { min-height: 100%; margin: 0; }
        body {
            font-family: 'Plus Jakarta Sans', 'Space Grotesk', -apple-system, sans-serif;
            background: #0f1013;
            color: #f4f4f5;
            line-height: 1.6;
            display: flex;
            flex-direction: column;
        }

        :root {
            --bg-dark: #0f1013;
            --card-dark: #18191e;
            --card-border: rgba(255, 255, 255, 0.08);
            --orange: #ff5a26;
            --orange-hover: #e04616;
            --text-main: #f4f4f5;
            --text-sub: #a1a1aa;
            --text-muted: #71717a;
            --radius-lg: 16px;
            --radius-md: 12px;
            --radius-pill: 999px;
        }

        .wrap { max-width: 1240px; margin: 0 auto; padding: 0 28px; width: 100%; }

        /* ===== Header Nav Bar ===== */
        header.v-header {
            background: #0f1013;
            border-bottom: 1px solid var(--card-border);
            padding: 16px 0;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(12px);
        }
        header.v-header .wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }
        .brand-logo {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            font-size: 22px;
            color: #fff;
            text-decoration: none;
            letter-spacing: -0.02em;
        }
        .brand-logo .dot { color: var(--orange); }

        .header-center {
            display: flex;
            align-items: center;
            gap: 16px;
            flex: 1;
            max-width: 580px;
        }
        .nav-dropdown {
            background: #18191e;
            border: 1px solid var(--card-border);
            color: var(--text-sub);
            padding: 8px 14px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            text-decoration: none;
        }
        .nav-dropdown:hover { color: #fff; border-color: rgba(255,255,255,0.18); }

        .header-search {
            position: relative;
            flex: 1;
            display: flex;
            align-items: center;
            background: #18191e;
            border: 1px solid var(--card-border);
            border-radius: var(--radius-pill);
            padding: 3px 4px 3px 14px;
        }
        .header-search input {
            background: transparent;
            border: none;
            outline: none;
            color: #fff;
            font-family: inherit;
            font-size: 13px;
            width: 100%;
        }
        .header-search input::placeholder { color: var(--text-muted); }
        .header-search button {
            background: var(--orange);
            color: #fff;
            border: none;
            padding: 7px 18px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .header-search button:hover { background: var(--orange-hover); }

        .header-right {
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .user-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #18191e;
            border: 1px solid var(--card-border);
            padding: 5px 12px 5px 6px;
            border-radius: var(--radius-pill);
            text-decoration: none;
            color: #fff;
            font-size: 13px;
            font-weight: 500;
        }
        .user-avatar {
            width: 28px;
            height: 28px;
            border-radius: 999px;
            background: var(--orange);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }

        .btn-dash {
            background: #27272a;
            color: #fff;
            padding: 8px 16px;
            border-radius: var(--radius-pill);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }
        .btn-dash:hover { background: #3f3f46; }

        main { flex: 1; padding: 40px 0 60px; }

        /* ===== Footer ===== */
        footer.v-footer {
            background: #090a0c;
            border-top: 1px solid var(--card-border);
            padding: 48px 0 32px;
            color: var(--text-sub);
            font-size: 14px;
        }
        footer.v-footer .footer-brand {
            font-size: 18px;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
        }
        footer.v-footer nav {
            display: flex;
            gap: 20px;
            margin: 16px 0 24px;
            flex-wrap: wrap;
        }
        footer.v-footer nav a {
            color: var(--text-sub);
            text-decoration: none;
            font-size: 14px;
        }
        footer.v-footer nav a:hover { color: #fff; }
        .footer-credit {
            color: var(--text-muted);
            font-size: 13px;
            margin-top: 20px;
            border-top: 1px solid var(--card-border);
            padding-top: 20px;
        }

        @media (max-width: 860px) {
            header.v-header .wrap { flex-wrap: wrap; }
            .header-center { order: 3; max-width: 100%; flex-basis: 100%; margin-top: 6px; }
        }
    </style>
    @hasSection('structured_data')
        <script type="application/ld+json">@yield('structured_data')</script>
    @endif

</head>
<body>
    <header class="v-header">
        <div class="wrap">
            <a href="{{ route('blog.index') }}" class="brand-logo">{{ config('app.name', 'Veloflex') }}<span class="dot">.</span></a>

            <div class="header-center">
                <form action="{{ route('blog.index') }}" method="GET" class="header-search">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari artikel...">
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if (request('tag'))
                        <input type="hidden" name="tag" value="{{ request('tag') }}">
                    @endif
                    <button type="submit">Cari</button>
                </form>
            </div>

            <div class="header-right">
                @auth
                    @if (in_array(auth()->user()->role, ['admin', 'editor', 'author']))
                        <a href="{{ route('admin.posts.index') }}" class="btn-dash">Dashboard</a>
                    @endif
                    <div class="user-badge">
                        <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                        <span>{{ auth()->user()->name }}</span>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-dash">Masuk</a>
                @endauth
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="v-footer">
        <div class="wrap">
            <div class="footer-brand">{{ config('app.name', 'Veloflex') }}<span style="color:var(--orange)">.</span></div>
            <p style="margin:0 0 12px; color:var(--text-muted)">Kumpulan artikel terbaru dan gagasan terbaik seputar teknologi, desain, dan inovasi.</p>

            <nav>
                <a href="{{ route('blog.index') }}">Blog</a>
                <a href="{{ route('feed') }}">RSS Feed</a>
                @auth
                    <a href="{{ route('admin.posts.index') }}">Dashboard Admin</a>
                @else
                    <a href="{{ route('login') }}">Masuk</a>
                @endauth
            </nav>

            <div class="footer-credit">
                Copyright &copy; {{ date('Y') }} {{ config('app.name', 'Veloflex') }}. Seluruh hak cipta dilindungi.
                &middot; Halaman dimuat dalam {{ number_format(microtime(true) - (defined('LARAVEL_START') ? LARAVEL_START : microtime(true)), 3) }} detik.
            </div>
        </div>
    </footer>
</body>
</html>
