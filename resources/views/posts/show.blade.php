@extends('layouts.blog')

@section('meta_title', $post->meta_title ?: $post->title)
@section('meta_description', $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->body), 155))
@section('og_type', 'article')
@section('canonical', route('blog.show', $post))
@if ($post->featured_image)
    @section('og_image', $post->featured_image_url)
@endif

@php
    $structuredData = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'BlogPosting',
                'headline' => $post->title,
                'description' => $post->meta_description ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt ?: $post->body), 155),
                'datePublished' => optional($post->published_at)->toIso8601String(),
                'dateModified' => $post->updated_at->toIso8601String(),
                'image' => $post->featured_image ? [$post->featured_image_url] : [asset('images/og-default.png')],
                'author' => [
                    '@type' => 'Person',
                    'name' => $post->user->name,
                ],
                'publisher' => [
                    '@type' => 'Organization',
                    'name' => config('app.name'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/icon-512.png'),
                    ],
                ],
                'mainEntityOfPage' => [
                    '@type' => 'WebPage',
                    '@id' => route('blog.show', $post),
                ],
            ],
            [
                '@type' => 'BreadcrumbList',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => 'Beranda', 'item' => route('blog.index')],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $post->category->name, 'item' => route('blog.index', ['category' => $post->category->slug])],
                    ['@type' => 'ListItem', 'position' => 3, 'name' => $post->title, 'item' => route('blog.show', $post)],
                ],
            ],
        ],
    ];
@endphp

@section('structured_data')
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}
@endsection

@section('content')
    <style>
        /* ===== Clean Light Theme for Article Detail Page ===== */
        .article-detail-wrap {
            background: #ffffff;
            color: #18181b;
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }

        .article-layout-grid {
            display: grid;
            grid-template-columns: 1fr 340px;
            gap: 48px;
            align-items: start;
        }

        .back-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #71717a;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 20px;
            transition: color 0.2s ease;
        }
        .back-nav-link:hover { color: var(--orange); }

        .detail-meta-top {
            font-size: 13px;
            color: #71717a;
            font-weight: 500;
            margin-bottom: 12px;
        }

        .detail-title {
            font-family: 'Fraunces', serif;
            font-size: 40px;
            font-weight: 600;
            line-height: 1.18;
            color: #09090b;
            margin: 0 0 20px;
            letter-spacing: -0.02em;
        }

        /* ===== Author Row ===== */
        .author-badge-row {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            padding-bottom: 24px;
            border-bottom: 1px solid #f4f4f5;
            flex-wrap: wrap;
        }
        .author-avatar-img {
            width: 42px;
            height: 42px;
            border-radius: 999px;
            background: var(--orange);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 16px;
        }
        .author-name {
            font-size: 14px;
            font-weight: 700;
            color: #09090b;
        }
        .badge-chip {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            background: #f4f4f5;
            color: #52525b;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .detail-hero-img-wrap {
            width: 100%;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 32px;
            background: #f4f4f5;
        }
        .detail-hero-img-wrap img {
            width: 100%;
            max-height: 480px;
            object-fit: cover;
            display: block;
        }

        /* ===== Markdown Body Typography ===== */
        .detail-prose-body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 17px;
            line-height: 1.8;
            color: #27272a;
        }
        .detail-prose-body p { margin-bottom: 24px; }
        .detail-prose-body h2 {
            font-family: 'Fraunces', serif;
            font-size: 26px;
            font-weight: 600;
            color: #09090b;
            margin: 40px 0 16px;
        }
        .detail-prose-body h3 {
            font-family: 'Fraunces', serif;
            font-size: 22px;
            font-weight: 600;
            color: #09090b;
            margin: 32px 0 14px;
        }
        .detail-prose-body blockquote {
            border-left: 4px solid var(--orange);
            background: #fafafa;
            padding: 14px 20px;
            border-radius: 0 12px 12px 0;
            margin: 0 0 24px;
            color: #52525b;
            font-style: italic;
        }
        .detail-prose-body pre {
            background: #0f1013;
            color: #f4f4f5;
            padding: 20px;
            border-radius: 14px;
            overflow-x: auto;
            position: relative;
        }
        .detail-prose-body code {
            font-family: 'JetBrains Mono', monospace;
            font-size: 14px;
        }
        .copy-btn {
            position: absolute;
            top: 12px;
            right: 12px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: #fff;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
        }
        .copy-btn.copied { color: #4ade80; border-color: #4ade80; }

        .tag-chips-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid #f4f4f5;
        }
        .tag-pill {
            background: #f4f4f5;
            color: #09090b;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 999px;
            text-decoration: none;
            transition: background 0.2s ease;
        }
        .tag-pill:hover { background: var(--orange); color: #fff; }

        /* ===== Right Sidebar (Latest Posts) ===== */
        .sidebar-card-panel {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 24px;
        }
        .sidebar-panel-title {
            font-family: 'Fraunces', serif;
            font-size: 20px;
            font-weight: 600;
            color: #09090b;
            margin: 0 0 20px;
        }
        .sidebar-post-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .sidebar-post-item {
            display: flex;
            gap: 14px;
            text-decoration: none;
            color: #09090b;
        }
        .sidebar-post-thumb {
            width: 80px;
            height: 70px;
            border-radius: 12px;
            overflow: hidden;
            background: #cbd5e1;
            flex-shrink: 0;
        }
        .sidebar-post-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sidebar-post-info h4 {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.35;
            margin: 0 0 4px;
            color: #09090b;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .sidebar-post-item:hover h4 { color: var(--orange); }
        .sidebar-post-read {
            font-size: 11px;
            color: var(--orange);
            font-weight: 700;
        }

        @media (max-width: 960px) {
            .article-layout-grid { grid-template-columns: 1fr; }
            .article-detail-wrap { padding: 24px; }
            .detail-title { font-size: 30px; }
        }
    </style>

    <div class="wrap">
        <a href="{{ route('blog.index') }}" class="back-nav-link">&larr; Kembali ke katalog artikel</a>

        <div class="article-detail-wrap">
            <div class="article-layout-grid">

                {{-- ===== Left Column: Main Article Body ===== --}}
                <div>
                    <div class="detail-meta-top">
                        <span>{{ $post->category->name }}</span> &middot;
                        <span>{{ $post->published_at?->translatedFormat('d M Y') ?? 'Recent' }}</span> &middot;
                        <span>{{ $post->reading_time }} min read</span>
                    </div>

                    <h1 class="detail-title">{{ $post->title }}</h1>

                    {{-- Author Badge Row --}}
                    <div class="author-badge-row">
                        <div class="author-avatar-img">
                            {{ strtoupper(substr($post->user->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="author-name">{{ $post->user->name }}</div>
                            <div style="font-size:12px; color:#71717a;">Penulis Konten</div>
                        </div>
                        <span class="badge-chip">⭐ 4.9 (Editor Choice)</span>
                        <span class="badge-chip">👤 Author</span>
                    </div>

                    @if ($post->featured_image)
                        <div class="detail-hero-img-wrap">
                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">
                        </div>
                    @endif

                    <div class="detail-prose-body">
                        {!! $post->body_html !!}
                    </div>

                    @if ($post->tags->isNotEmpty())
                        <div class="tag-chips-row">
                            @foreach ($post->tags as $tag)
                                <a href="{{ route('blog.index', ['tag' => $tag->slug]) }}" class="tag-pill">#{{ $tag->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ===== Right Column: Sidebar (Latest Posts) ===== --}}
                <aside>
                    <div class="sidebar-card-panel">
                        <h3 class="sidebar-panel-title">Post Terbaru:</h3>

                        <div class="sidebar-post-list">
                            @forelse ($related as $item)
                                <a href="{{ route('blog.show', $item) }}" class="sidebar-post-item">
                                    <div class="sidebar-post-thumb">
                                        @if ($item->featured_image)
                                            <img src="{{ $item->featured_image_url }}" alt="{{ $item->title }}">
                                        @endif
                                    </div>
                                    <div class="sidebar-post-info">
                                        <h4>{{ $item->title }}</h4>
                                        <span class="sidebar-post-read">Baca selengkapnya &rarr;</span>
                                    </div>
                                </a>
                            @empty
                                <div style="font-size:13px; color:#71717a;">Belum ada post lainnya.</div>
                            @endforelse
                        </div>
                    </div>
                </aside>

            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.detail-prose-body pre code').forEach(function (block) {
                hljs.highlightElement(block);
            });

            document.querySelectorAll('.detail-prose-body pre').forEach(function (pre) {
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'copy-btn';
                btn.textContent = 'Copy';
                btn.addEventListener('click', function () {
                    navigator.clipboard.writeText(pre.innerText).then(function () {
                        btn.textContent = 'Copied!';
                        btn.classList.add('copied');
                        setTimeout(function () {
                            btn.textContent = 'Copy';
                            btn.classList.remove('copied');
                        }, 1500);
                    });
                });
                pre.appendChild(btn);
            });
        });
    </script>
@endsection
