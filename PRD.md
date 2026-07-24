# PRD — Jadi CMS

**Product Requirements Document**
Versi: 1.0 | Status: Released (MVP)
Terakhir diperbarui: Juli 2026

---

## 1. Ringkasan Produk

**Jadi CMS** adalah sistem manajemen konten berbasis web yang dirancang untuk tim editorial kecil hingga menengah dan penulis mandiri (solo blogger). Produk ini menyediakan alur penerbitan artikel yang bersih, aman, dan terstruktur — dari penulisan draft hingga publikasi — dengan antarmuka modern dan API terbuka untuk integrasi pihak ketiga.

### Pernyataan Masalah

Tim editorial yang menggunakan platform generik (WordPress, Notion, Google Docs) sering menghadapi masalah:
- Tidak ada pemisahan peran yang jelas antara penulis dan editor
- Tidak ada kontrol atas visibilitas draft
- Penghapusan konten secara tidak sengaja (cascade delete) tanpa peringatan
- Tidak ada API standar untuk integrasi headless

### Tujuan Produk

1. Memberikan alur kerja editorial yang terstruktur dengan **tiga tingkat peran** yang terdefinisi jelas
2. Menjamin **integritas data** — tidak ada post yang terhapus secara tidak sengaja
3. Menyediakan **API REST** yang dapat digunakan oleh klien frontend lain
4. Menghadirkan **blog publik** yang cepat, SEO-ready, dan readable

---

## 2. Pengguna & Peran

### 2.1 Persona Utama

| Persona | Peran Sistem | Deskripsi |
|---|---|---|
| **Kepala Redaksi** | Admin | Mengelola seluruh sistem — semua post, pengguna, kategori, dan konfigurasi |
| **Editor** | Editor | Meninjau dan mengedit tulisan semua author; mengelola taksonomi |
| **Penulis** | Author | Menulis dan mengelola post miliknya sendiri |
| **Pembaca** | Reader | Mengakses blog publik; tidak punya akses dashboard |

### 2.2 Matriks Hak Akses

| Kemampuan | Admin | Editor | Author | Reader |
|---|:---:|:---:|:---:|:---:|
| Lihat semua post (termasuk draft orang lain) | ✅ | ✅ | ❌ | ❌ |
| Buat post baru | ✅ | ✅ | ✅ | ❌ |
| Edit post sendiri | ✅ | ✅ | ✅ | ❌ |
| Edit post orang lain | ✅ | ✅ | ❌ | ❌ |
| Hapus post | ✅ | ✅ (milik sendiri) | ✅ (milik sendiri) | ❌ |
| Kelola kategori & tag | ✅ | ✅ | ❌ | ❌ |
| Hapus kategori (tanpa post) | ✅ | ✅ | ❌ | ❌ |
| Hapus kategori (ada post) | ❌ (ditolak) | ❌ (ditolak) | ❌ | ❌ |
| Hapus akun sendiri (tanpa post) | ✅ | ✅ | ✅ | ✅ |
| Hapus akun sendiri (ada post) | ❌ (ditolak) | ❌ (ditolak) | ❌ (ditolak) | ❌ |

---

## 3. Fitur & Persyaratan Fungsional

### 3.1 Blog Publik

**FR-PUB-01**: Daftar artikel menampilkan semua post berstatus `published`, diurutkan dari terbaru.

**FR-PUB-02**: Halaman index menampilkan:
- Bagian *Trending Articles* (3 artikel terbaru) dengan gambar latar
- Navigation pills kategori
- Grid artikel dengan filter berdasarkan kategori, tag, dan pencarian teks bebas

**FR-PUB-03**: Halaman detail artikel (`/blog/{slug}`) menampilkan:
- Judul, meta baca, informasi penulis
- Gambar utama (jika ada)
- Isi artikel dalam format Markdown dengan syntax highlighting
- Tag terkait
- Sidebar 3 artikel terkait dari kategori yang sama

**FR-PUB-04**: Post berstatus `draft` mengembalikan HTTP 404 di blog publik.

**FR-PUB-05**: Setiap halaman memiliki meta SEO yang dapat dikustomisasi per artikel (meta title, meta description, Open Graph, Twitter Card, canonical URL).

---

### 3.2 CMS Dashboard (Admin)

**FR-ADMIN-01**: Halaman daftar post menampilkan semua post (Admin/Editor) atau hanya post milik sendiri (Author).

**FR-ADMIN-02**: Setiap post memiliki bidang: judul, slug (auto-generate dari judul), excerpt, body (Markdown), status (draft/published), kategori, tags (multi-select), gambar utama (upload), meta title, meta description.

**FR-ADMIN-03**: Status `published` secara otomatis mengisi `published_at` dengan waktu saat ini jika belum pernah diisi sebelumnya.

**FR-ADMIN-04**: Penghapusan kategori yang masih memiliki post harus **ditolak** — baik di controller maupun di level database constraint (`RESTRICT`), disertai pesan error yang jelas menyebutkan jumlah post terdampak.

**FR-ADMIN-05**: Konfirmasi hapus kategori di UI menampilkan jumlah post terhubung sebelum submit.

**FR-ADMIN-06**: Penghapusan akun user yang masih memiliki post harus **ditolak** dengan pesan error di form modal.

---

### 3.3 Taksonomi

**FR-TAX-01**: Kategori dan tag memiliki `name` dan `slug` (auto-generate). Slug unik.

**FR-TAX-02**: Satu post memiliki tepat satu kategori dan nol atau lebih tag.

**FR-TAX-03**: Blog publik dapat difilter berdasarkan kategori (via URL slug) dan tag (via select dropdown).

---

### 3.4 REST API

**FR-API-01**: API tersedia di `/api` dan dilindungi dengan Laravel Sanctum (Bearer Token).

**FR-API-02**: Endpoint tersedia untuk resource: `posts`, `categories`, `tags`.

**FR-API-03**: Endpoint hapus kategori dan post di API mengikuti aturan yang sama seperti di dashboard (restrict delete bila ada post terkait).

---

### 3.5 RSS & Sitemap

**FR-SEO-01**: RSS 2.0 tersedia di `/feed` — berisi 20 artikel `published` terbaru. Mendukung namespace `atom:link`.

**FR-SEO-02**: Sitemap XML tersedia di `/sitemap.xml` dan mencantumkan semua post `published` beserta `lastmod`.

**FR-SEO-03**: `robots.txt` tersedia di `/robots.txt` dan merujuk ke sitemap.

**FR-SEO-04**: Tag `<link rel="alternate" type="application/rss+xml">` terpasang di `<head>` setiap halaman blog untuk auto-discovery RSS reader.

---

## 4. Persyaratan Non-Fungsional

### 4.1 Keamanan

**NFR-SEC-01**: Semua endpoint admin dilindungi middleware `auth` dan `role`.

**NFR-SEC-02**: Otorisasi aksi menggunakan Laravel Policy (`PostPolicy`, `CategoryPolicy`), bukan pengecekan inline.

**NFR-SEC-03**: Foreign key database menggunakan `RESTRICT ON DELETE` untuk `category_id` dan `user_id` pada tabel `posts` — menghindari cascade delete yang tidak disengaja.

**NFR-SEC-04**: Draft post tidak dapat diakses di blog publik (HTTP 404).

**NFR-SEC-05**: Author tidak dapat melihat draft milik penulis lain di dashboard.

### 4.2 Kualitas Kode & Testing

**NFR-QA-01**: Setiap fitur kritis memiliki feature test yang dapat dijalankan dengan `php artisan test`.

**NFR-QA-02**: Coverage minimal mencakup: autentikasi, otorisasi per role, CRUD post, CRUD kategori (termasuk restrict delete), visibilitas draft, dan delete akun.

**NFR-QA-03**: Test suite harus 100% lolos sebelum merge ke branch utama.

### 4.3 Performa

**NFR-PERF-01**: Query N+1 dicegah dengan eager loading (`with(['user', 'category', 'tags'])`).

**NFR-PERF-02**: Listing post menggunakan paginasi (10 item per halaman, konfigurabel).

### 4.4 SEO & Aksesibilitas

**NFR-SEO-01**: Setiap halaman publik memiliki `<title>`, `<meta name="description">`, dan `<link rel="canonical">` yang unik.

**NFR-SEO-02**: Struktur heading menggunakan `<h1>` tunggal per halaman.

---

## 5. Arsitektur Teknis

### 5.1 Stack

```
Laravel 13 (PHP 8.3+)
├── Auth         : Laravel Breeze + Sanctum
├── Database     : SQLite (dev) / MySQL / PostgreSQL
├── Markdown     : league/commonmark
├── Frontend     : Blade + Vanilla CSS (tanpa framework JS)
└── Testing      : Pest PHP
```

### 5.2 Model Data

```
users
├── id, name, email, password, role (admin|editor|author|reader)

posts
├── id, user_id (→ users RESTRICT), category_id (→ categories RESTRICT)
├── title, slug, excerpt, body, status, published_at
├── featured_image, meta_title, meta_description
└── timestamps

categories
└── id, name, slug, timestamps

tags
└── id, name, slug, timestamps

post_tag (pivot)
└── post_id, tag_id
```

### 5.3 URL Structure

```
/                       → redirect ke /blog
/blog                   → daftar artikel publik
/blog/{slug}            → detail artikel
/feed                   → RSS 2.0
/sitemap.xml            → Sitemap
/robots.txt             → Robots directives

/admin/posts            → CMS: daftar post
/admin/posts/create     → CMS: form buat post baru
/admin/posts/{id}/edit  → CMS: form edit post
/admin/categories       → CMS: daftar & kelola kategori
/admin/tags             → CMS: daftar & kelola tag

/api/posts              → API posts
/api/categories         → API categories
/api/tags               → API tags
```

---

## 6. Batasan & Keputusan Desain

| Keputusan | Alasan |
|---|---|
| Tidak ada soft delete | Menjaga kesederhanaan MVP; restrict delete sudah mencegah kehilangan data tidak disengaja |
| Draft tidak bisa dipindah ke user lain | Di luar scope MVP; solusi: editor dapat mengedit dan mempublish atas nama author |
| Newsletter belum terhubung | Fitur UI sudah ada (placeholder) namun backend pengiriman email belum tersambung |
| Tidak ada WYSIWYG editor | Menggunakan raw Markdown untuk menjaga portabilitas dan kecepatan |
| SQLite sebagai default DB | Mempermudah setup lokal tanpa instalasi database server |

---

## 7. Roadmap (Post-MVP)

| Prioritas | Fitur |
|---|---|
| 🔴 Tinggi | Fitur "Pindahkan Post ke Kategori Lain" saat hapus kategori |
| 🔴 Tinggi | Transfer kepemilikan post saat hapus akun user |
| 🟡 Sedang | Soft delete / Trash (pemulihan artikel yang terhapus) |
| 🟡 Sedang | Bulk action (ubah status / kategori banyak post sekaligus) |
| 🟡 Sedang | Newsletter backend (integrasi Mailchimp / Resend) |
| 🟢 Rendah | Komentar artikel |
| 🟢 Rendah | Statistik kunjungan artikel (tanpa third-party tracking) |
| 🟢 Rendah | Multi-language / i18n |
