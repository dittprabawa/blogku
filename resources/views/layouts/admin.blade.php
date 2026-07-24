<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Admin')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;1,9..144,500&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        :root {
            --paper: #fbf8f3;
            --ink: #1c1917;
            --teal: #0f766e;
            --teal-deep: #0b5a54;
            --sand: #ede7dc;
            --muted: #78716c;
        }
        body {
            font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
            margin: 0;
            background: var(--paper);
            color: var(--ink);
        }
        .layout { display: flex; min-height: 100vh; }
        nav.sidebar {
            width: 230px;
            background: var(--ink);
            color: #fff;
            padding: 26px 0;
            flex-shrink: 0;
        }
        nav.sidebar .brand {
            padding: 0 24px 20px;
            font-family: 'Fraunces', serif;
            font-weight: 500;
            font-style: italic;
            font-size: 20px;
            border-bottom: 1px solid #3f3f3f;
            margin-bottom: 14px;
        }
        nav.sidebar a {
            display: block;
            margin: 2px 14px;
            padding: 10px 14px;
            border-radius: 8px;
            color: #d6d3d1;
            text-decoration: none;
            font-size: 14px;
        }
        nav.sidebar a:hover { background: #292524; color: #fff; }
        nav.sidebar a.active { background: var(--teal); color: #fff; font-weight: 500; }
        main { flex: 1; padding: 36px 40px; }
        .card {
            background: #fff;
            border: 1px solid var(--sand);
            border-radius: 14px;
            padding: 26px;
            box-shadow: 0 1px 2px rgba(28, 25, 23, 0.04);
        }
        h1 {
            font-family: 'Fraunces', serif;
            font-size: 25px;
            font-weight: 500;
            margin: 0 0 22px;
            letter-spacing: -0.01em;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 11px 12px; border-bottom: 1px solid var(--sand); font-size: 14px; }
        th {
            color: var(--muted);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }
        .btn {
            display: inline-block;
            padding: 9px 18px;
            border-radius: 8px;
            border: none;
            background: var(--ink);
            color: #fff;
            font-size: 14px;
            font-family: 'Space Grotesk', sans-serif;
            cursor: pointer;
            text-decoration: none;
        }
        .btn:hover { background: #000; }
        .btn-sm { padding: 6px 12px; font-size: 12px; border-radius: 6px; }
        .btn-danger { background: #b91c1c; }
        .btn-danger:hover { background: #991b1b; }
        .btn-secondary { background: var(--sand); color: var(--ink); }
        .btn-secondary:hover { background: #e2d9c8; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #44403c; }
        .field input, .field select, .field textarea {
            width: 100%;
            padding: 9px 12px;
            border: 1px solid var(--sand);
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Space Grotesk', sans-serif;
            background: #fff;
        }
        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none;
            border-color: var(--teal);
        }
        .field textarea { min-height: 150px; }
        .error { color: #b91c1c; font-size: 13px; margin-top: 4px; }
        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 11px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
            padding: 11px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            background: var(--sand);
        }
        .badge-published { background: #d1fae5; color: #065f46; }
        .badge-draft { background: #fef3c7; color: #92400e; }
        .sidebar-footer {
            margin-top: 26px;
            padding-top: 16px;
            border-top: 1px solid #3f3f3f;
        }
        .sidebar-footer form { margin: 0; }
        .sidebar-footer a, .sidebar-footer button {
            display: block;
            margin: 2px 14px;
            padding: 10px 14px;
        }
        .sidebar-footer button {
            background: none;
            border: none;
            color: #d6d3d1;
            font-size: 14px;
            cursor: pointer;
            font-family: inherit;
            width: calc(100% - 28px);
            text-align: left;
            border-radius: 8px;
        }
        .sidebar-footer button:hover { background: #292524; color: #fff; }
    </style>
</head>
<body>
    <div class="layout">
        <nav class="sidebar">
            <div class="brand">CMS Admin</div>
            <a href="{{ route('admin.posts.index') }}" class="{{ request()->routeIs('admin.posts.*') ? 'active' : '' }}">Posts</a>
            @if (in_array(auth()->user()->role, ['admin', 'editor']))
                <a href="{{ route('admin.categories.index') }}" class="{{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">Categories</a>
                <a href="{{ route('admin.tags.index') }}" class="{{ request()->routeIs('admin.tags.*') ? 'active' : '' }}">Tags</a>
            @endif

            <div class="sidebar-footer">
                <a href="{{ route('blog.index') }}" target="_blank">Lihat Blog ↗</a>
                <a href="{{ route('profile.edit') }}">Profil ({{ auth()->user()->name }})</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </nav>
        <main>
            @if (session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>