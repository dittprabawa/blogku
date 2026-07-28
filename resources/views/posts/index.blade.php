@extends('layouts.blog')

@section('meta_title', config('app.name', 'Veloflex.'))
@section('meta_description', 'Kumpulan artikel dan tulisan terbaru seputar teknologi dan gaya hidup.')

@php
    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => config('app.name'),
        'url' => route('blog.index'),
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => route('blog.index') . '?q={search_term_string}',
            'query-input' => 'required name=search_term_string',
        ],
    ];
@endphp

@section('structured_data')
{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
@endsection

@section('content')
    @php
        $featured = $posts->take(3);
        $rest = $posts->slice(3);
        $hasFilter = request()->hasAny(['q', 'category', 'tag']);
    @endphp

    <style>
        .catalog-container { display: flex; flex-direction: column; gap: 44px; }

        /* ===== Section Title ===== */
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .section-title-main {
            font-family: 'Fraunces', serif;
            font-size: 32px;
            font-weight: 500;
            color: #fff;
            margin: 0;
            letter-spacing: -0.02em;
        }

        /* ===== Trending Articles Grid ===== */
        .trending-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }
        .trending-card {
            position: relative;
            background: #18191e;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            overflow: hidden;
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 280px;
            transition: transform 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .trending-card:hover {
            transform: translateY(-5px);
            border-color: rgba(255,255,255,0.2);
            box-shadow: 0 16px 32px rgba(0,0,0,0.4);
        }
        .trending-bg-img {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.35;
            transition: opacity 0.3s ease, transform 0.4s ease;
        }
        .trending-card:hover .trending-bg-img {
            opacity: 0.5;
            transform: scale(1.04);
        }
        .trending-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15,16,19,0.2) 0%, rgba(15,16,19,0.92) 100%);
        }
        .trending-content {
            position: relative;
            z-index: 2;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .trending-cat-pill {
            align-self: flex-start;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 12px;
            border-radius: 999px;
            border: 1px solid rgba(255,255,255,0.15);
        }
        .trending-bottom h3 {
            font-family: 'Fraunces', serif;
            font-size: 20px;
            font-weight: 500;
            line-height: 1.3;
            margin: 0 0 10px;
            color: #fff;
        }
        .trending-meta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #a1a1aa;
        }
        .btn-read-pill {
            background: #fff;
            color: #0f1013;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .trending-card:hover .btn-read-pill {
            background: var(--orange);
            color: #fff;
        }

        /* ===== Category Filter Bar (Pills Navigation) ===== */
        .filter-nav-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: #18191e;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 999px;
            padding: 8px 12px;
            overflow-x: auto;
        }
        .pills-group {
            display: flex;
            align-items: center;
            gap: 8px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .pills-group::-webkit-scrollbar { display: none; }
        .pill-item {
            background: transparent;
            color: #a1a1aa;
            font-size: 13px;
            font-weight: 500;
            padding: 7px 18px;
            border-radius: 999px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .pill-item:hover {
            color: #fff;
            background: rgba(255,255,255,0.06);
        }
        .pill-item.active {
            background: #fff;
            color: #0f1013;
            font-weight: 700;
        }
        .filter-selectors {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .filter-select {
            background: #0f1013;
            border: 1px solid rgba(255,255,255,0.12);
            color: #f4f4f5;
            padding: 6px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-family: inherit;
            outline: none;
        }

        /* ===== Main Article Grid ===== */
        .article-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }
        .grid-card {
            background: #18191e;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            overflow: hidden;
            text-decoration: none;
            color: #fff;
            display: flex;
            flex-direction: column;
            transition: transform 0.25s ease, border-color 0.25s ease;
        }
        .grid-card:hover {
            transform: translateY(-4px);
            border-color: rgba(255,255,255,0.18);
        }
        .card-thumb-wrap {
            position: relative;
            height: 190px;
            background: #27272a;
            overflow: hidden;
        }
        .card-thumb-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .grid-card:hover .card-thumb-wrap img {
            transform: scale(1.05);
        }
        .card-thumb-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #27272a, #18191e);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #71717a;
            font-size: 13px;
        }
        .grid-card-body {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .grid-card-tag {
            font-size: 11px;
            font-weight: 600;
            color: var(--orange);
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }
        .grid-card-title {
            font-family: 'Fraunces', serif;
            font-size: 18px;
            font-weight: 500;
            line-height: 1.35;
            margin: 0 0 10px;
            color: #fff;
        }
        .grid-card-excerpt {
            font-size: 13px;
            color: #a1a1aa;
            margin-bottom: 16px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .grid-card-footer {
            margin-top: auto;
            padding-top: 14px;
            border-top: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            color: #71717a;
        }
        .author-mini {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #a1a1aa;
        }

        .pagination-bar {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        @media (max-width: 1024px) {
            .trending-grid, .article-grid { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 640px) {
            .trending-grid, .article-grid { grid-template-columns: 1fr; }
        }
    </style>

    <div class="wrap catalog-container">

        {{-- ===== 1. Trending Articles Section ===== --}}
        @if ($featured->count() && !request('category') && !request('q') && !request('tag'))
            <section>
                <div class="section-header">
                    <h2 class="section-title-main">Trending Articles</h2>
                </div>

                <div class="trending-grid">
                    @foreach ($featured as $post)
                        <a href="{{ route('blog.show', $post) }}" class="trending-card">
                            @if ($post->featured_image)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" class="trending-bg-img">
                            @endif
                            <div class="trending-overlay"></div>
                            <div class="trending-content">
                                <span class="trending-cat-pill">{{ $post->category->name }}</span>

                                <div class="trending-bottom">
                                    <h3>{{ $post->title }}</h3>
                                    <div class="trending-meta-row">
                                        <span>{{ $post->published_at?->translatedFormat('M d') ?? 'Recent' }} &middot; {{ $post->reading_time }} min read</span>
                                        <span class="btn-read-pill">Baca</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ===== 2. Category Filter Pills Navigation ===== --}}
        <div class="filter-nav-bar">
            <div class="pills-group">
                <a href="{{ route('blog.index') }}" class="pill-item {{ !request('category') ? 'active' : '' }}">
                    Semua ({{ $posts->total() }})
                </a>
                @foreach ($categories as $category)
                    <a href="{{ route('blog.index', ['category' => $category->slug]) }}"
                       class="pill-item {{ request('category') === $category->slug ? 'active' : '' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>

            @if ($tags->isNotEmpty())
                <form action="{{ route('blog.index') }}" method="GET" class="filter-selectors">
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    @if (request('q'))
                        <input type="hidden" name="q" value="{{ request('q') }}">
                    @endif
                    <select name="tag" onchange="this.form.submit()" class="filter-select">
                        <option value="">Semua Tag</option>
                        @foreach ($tags as $tag)
                            <option value="{{ $tag->slug }}" @selected(request('tag') === $tag->slug)>#{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
        </div>

        {{-- ===== 3. Main Articles Grid ===== --}}
        <section>
            @if ($hasFilter)
                <div style="margin-bottom: 20px; color: #a1a1aa; font-size: 14px;">
                    Menampilkan hasil filter. <a href="{{ route('blog.index') }}" style="color:var(--orange); text-decoration:underline;">Reset Filter</a>
                </div>
            @endif

            <div class="article-grid">
                @forelse ($posts as $post)
                    <a href="{{ route('blog.show', $post) }}" class="grid-card">
                        <div class="card-thumb-wrap">
                            @if ($post->featured_image)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
                            @else
                                <div class="card-thumb-placeholder">Veloflex Article</div>
                            @endif
                        </div>
                        <div class="grid-card-body">
                            <span class="grid-card-tag">{{ $post->category->name }}</span>
                            <h3 class="grid-card-title">{{ $post->title }}</h3>
                            <p class="grid-card-excerpt">{{ $post->excerpt ?: strip_tags($post->body) }}</p>

                            <div class="grid-card-footer">
                                <div class="author-mini">
                                    <span>{{ $post->user->name }}</span>
                                </div>
                                <span>{{ $post->published_at?->translatedFormat('d M Y') }} &middot; {{ $post->reading_time }}m</span>
                            </div>
                        </div>
                    </a>
                @empty
                    <div style="grid-column: 1 / -1; text-align: center; padding: 60px 0; color: #71717a;">
                        Tidak ada artikel yang ditemukan.
                    </div>
                @endforelse
            </div>

            <div class="pagination-bar">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        </section>

    </div>
@endsection