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

        /* ===== Social Share Section ===== */
        .share-section-box {
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid #f4f4f5;
        }
        .share-box-title {
            font-size: 14px;
            font-weight: 700;
            color: #09090b;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .share-buttons-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .share-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid transparent;
            cursor: pointer;
            line-height: 1;
        }
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .share-btn-wa { background: #25D366; color: #ffffff; }
        .share-btn-wa:hover { background: #20bd5a; color: #ffffff; }
        .share-btn-x { background: #000000; color: #ffffff; }
        .share-btn-x:hover { background: #18181b; color: #ffffff; }
        .share-btn-fb { background: #1877F2; color: #ffffff; }
        .share-btn-fb:hover { background: #0f62cc; color: #ffffff; }
        .share-btn-linkedin { background: #0A66C2; color: #ffffff; }
        .share-btn-linkedin:hover { background: #084e96; color: #ffffff; }
        .share-btn-copy { background: #f4f4f5; color: #18181b; border-color: #e4e4e7; }
        .share-btn-copy:hover { background: #e4e4e7; }
        .share-btn-copy.copied { background: #dcfce7; color: #15803d; border-color: #86efac; }
        .share-btn-native { background: #f4f4f5; color: #18181b; border-color: #e4e4e7; display: none; }
        .share-btn-native:hover { background: #e4e4e7; }
        .share-btn svg { width: 16px; height: 16px; fill: currentColor; flex-shrink: 0; }

        /* ===== Comment Section ===== */
        .comments-section-box {
            margin-top: 40px;
            padding-top: 32px;
            border-top: 1px solid #f4f4f5;
        }
        .comments-header-title {
            font-family: 'Fraunces', serif;
            font-size: 24px;
            font-weight: 600;
            color: #09090b;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .comments-header-badge {
            background: #f4f4f5;
            color: #71717a;
            font-size: 13px;
            font-weight: 700;
            padding: 2px 10px;
            border-radius: 999px;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .comment-form-card {
            background: #fafafa;
            border: 1px solid #f4f4f5;
            border-radius: 16px;
            padding: 24px;
            margin-bottom: 32px;
        }
        .comment-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }
        .comment-input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #3f3f46;
            margin-bottom: 6px;
        }
        .comment-input-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 14px;
            border-radius: 10px;
            border: 1px solid #e4e4e7;
            background: #ffffff;
            color: #18181b;
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s ease;
        }
        .comment-input-control:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15);
        }
        .comment-submit-btn {
            background: var(--orange, #f97316);
            color: #ffffff;
            font-weight: 700;
            font-size: 14px;
            padding: 10px 24px;
            border-radius: 999px;
            border: none;
            cursor: pointer;
            transition: background 0.2s ease;
        }
        .comment-submit-btn:hover {
            background: #ea580c;
        }
        .comment-item-card {
            display: flex;
            gap: 16px;
            padding: 20px 0;
            border-bottom: 1px solid #f4f4f5;
        }
        .comment-item-card:last-child {
            border-bottom: none;
        }
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            background: #e4e4e7;
            color: #3f3f46;
            font-weight: 700;
            font-size: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .comment-meta-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 6px;
        }
        .comment-author-name {
            font-size: 14px;
            font-weight: 700;
            color: #09090b;
        }
        .comment-date {
            font-size: 12px;
            color: #a1a1aa;
        }
        .comment-body-text {
            font-size: 14px;
            line-height: 1.6;
            color: #3f3f46;
        }
        .comment-alert-success {
            background: #dcfce7;
            border: 1px solid #86efac;
            color: #15803d;
            padding: 12px 18px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 24px;
        }

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

                    {{-- ===== Social Media Share Section ===== --}}
                    @php
                        $shareUrl = route('blog.show', $post);
                        $shareTitle = $post->title;
                        $encodedUrl = rawurlencode($shareUrl);
                        $encodedTitle = rawurlencode($shareTitle);

                        $waUrl = "https://api.whatsapp.com/send?text=" . rawurlencode($shareTitle . ' ' . $shareUrl . '?utm_source=whatsapp&utm_medium=social_share');
                        $xUrl = "https://twitter.com/intent/tweet?text=" . $encodedTitle . "&url=" . rawurlencode($shareUrl . '?utm_source=twitter&utm_medium=social_share');
                        $fbUrl = "https://www.facebook.com/sharer/sharer.php?u=" . rawurlencode($shareUrl . '?utm_source=facebook&utm_medium=social_share');
                        $liUrl = "https://www.linkedin.com/sharing/share-offsite/?url=" . rawurlencode($shareUrl . '?utm_source=linkedin&utm_medium=social_share');
                    @endphp

                    <div class="share-section-box">
                        <div class="share-box-title">
                            <svg viewBox="0 0 24 24" style="width:18px;height:18px;fill:none;stroke:currentColor;stroke-width:2;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                            <span>Bagikan Artikel Ini:</span>
                        </div>
                        <div class="share-buttons-grid">
                            <a href="{{ $waUrl }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn-wa">
                                <svg viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>WhatsApp</span>
                            </a>
                            <a href="{{ $xUrl }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn-x">
                                <svg viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                <span>X (Twitter)</span>
                            </a>
                            <a href="{{ $fbUrl }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn-fb">
                                <svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                <span>Facebook</span>
                            </a>
                            <a href="{{ $liUrl }}" target="_blank" rel="noopener noreferrer" class="share-btn share-btn-linkedin">
                                <svg viewBox="0 0 24 24"><path d="M19 3a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h14m-.5 15.5v-5.3a3.26 3.26 0 0 0-3.26-3.26c-.85 0-1.84.52-2.28 1.3v-1.11h-2.79v8.37h2.79v-4.93c0-.77.62-1.4 1.39-1.4a1.4 1.4 0 0 1 1.4 1.4v4.93h2.75M6.46 10.9v8.37H9.25V10.9H6.46M7.86 6.78a1.68 1.68 0 1 0 0 3.36 1.68 1.68 0 0 0 0-3.36z"/></svg>
                                <span>LinkedIn</span>
                            </a>
                            <button type="button" id="copyShareLinkBtn" data-url="{{ $shareUrl }}" class="share-btn share-btn-copy">
                                <svg viewBox="0 0 24 24" style="fill:none;stroke:currentColor;stroke-width:2;"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                                <span id="copyBtnText">Salin Tautan</span>
                            </button>
                            <button type="button" id="nativeShareBtn" data-title="{{ $post->title }}" data-url="{{ $shareUrl }}" class="share-btn share-btn-native">
                                <svg viewBox="0 0 24 24" style="fill:none;stroke:currentColor;stroke-width:2;"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                                <span>Bagikan Lainnya...</span>
                            </button>
                        </div>
                    </div>

                    {{-- ===== Comments Section ===== --}}
                    <div class="comments-section-box" id="comments">
                        <h3 class="comments-header-title">
                            Diskusi & Komentar
                            <span class="comments-header-badge">{{ $post->comments->count() }}</span>
                        </h3>

                        @if (session('success_comment'))
                            <div class="comment-alert-success">
                                {{ session('success_comment') }}
                            </div>
                        @endif

                        {{-- Comment Form --}}
                        <div class="comment-form-card">
                            <form action="{{ route('blog.comments.store', $post) }}" method="POST">
                                @csrf
                                @guest
                                    <div class="comment-form-grid">
                                        <div class="comment-input-group">
                                            <label for="comment_name">Nama Lengkap *</label>
                                            <input type="text" id="comment_name" name="name" class="comment-input-control" value="{{ old('name') }}" placeholder="Masukkan nama Anda" required>
                                            @error('name')
                                                <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                        <div class="comment-input-group">
                                            <label for="comment_email">Alamat Email *</label>
                                            <input type="email" id="comment_email" name="email" class="comment-input-control" value="{{ old('email') }}" placeholder="nama@email.com" required>
                                            @error('email')
                                                <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @else
                                    <div style="font-size:13px; color:#52525b; margin-bottom:14px;">
                                        Tulis komentar sebagai <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
                                        <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                                        <input type="hidden" name="email" value="{{ auth()->user()->email }}">
                                    </div>
                                @endguest

                                <div class="comment-input-group" style="margin-bottom:16px;">
                                    <label for="comment_body">Isi Komentar </label>
                                    <textarea id="comment_body" name="comment" rows="4" class="comment-input-control" placeholder="Tuliskan komentar atau tanggapan Anda mengenai artikel ini..." required>{{ old('comment') }}</textarea>
                                    @error('comment')
                                        <span style="color:#ef4444; font-size:12px; margin-top:4px; display:block;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <button type="submit" class="comment-submit-btn">Kirim Komentar</button>
                            </form>
                        </div>

                        {{-- Comments List --}}
                        <div class="comments-list">
                            @forelse ($post->comments as $comment)
                                <div class="comment-item-card">
                                    <div class="comment-avatar">
                                        {{ strtoupper(substr($comment->name, 0, 1)) }}
                                    </div>
                                    <div style="flex:1;">
                                        <div class="comment-meta-row">
                                            <span class="comment-author-name">{{ $comment->name }}</span>
                                            @if ($comment->user_id && $comment->user_id === $post->user_id)
                                                <span class="badge-chip" style="font-size:10px; padding:2px 8px; background:#ffedd5; color:#c2410c;">Author</span>
                                            @endif
                                            &middot;
                                            <span class="comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="comment-body-text">
                                            {!! nl2br(e($comment->comment)) !!}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align:center; padding:32px 0; color:#71717a; font-size:14px;">
                                    Belum ada komentar. Jadilah yang pertama memberikan tanggapan!
                                </div>
                            @endforelse
                        </div>
                    </div>
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

            // Copy Share Link logic
            var copyBtn = document.getElementById('copyShareLinkBtn');
            var copyText = document.getElementById('copyBtnText');
            if (copyBtn) {
                copyBtn.addEventListener('click', function () {
                    var url = this.getAttribute('data-url');
                    navigator.clipboard.writeText(url).then(function () {
                        copyBtn.classList.add('copied');
                        copyText.textContent = 'Link Tersalin!';
                        setTimeout(function () {
                            copyBtn.classList.remove('copied');
                            copyText.textContent = 'Salin Tautan';
                        }, 2000);
                    });
                });
            }

            // Web Share API (Native Share for Mobile)
            var nativeBtn = document.getElementById('nativeShareBtn');
            if (nativeBtn && navigator.share) {
                nativeBtn.style.display = 'inline-flex';
                nativeBtn.addEventListener('click', function () {
                    navigator.share({
                        title: this.getAttribute('data-title'),
                        url: this.getAttribute('data-url')
                    }).catch(function (err) {
                        console.log('Share canceled or failed', err);
                    });
                });
            }
        });
    </script>
@endsection
