# BlogKu API Documentation

REST API untuk BlogKu — dipakai untuk integrasi aplikasi mobile atau frontend terpisah. Otentikasi menggunakan token berbasis [Laravel Sanctum](https://laravel.com/docs/sanctum).

**Base URL:** `https://domain-kamu.com/api`

---

## Daftar Isi

- [Autentikasi](#autentikasi)
  - [Register](#register)
  - [Login](#login)
  - [Logout](#logout)
  - [Get Current User](#get-current-user)
- [Posts](#posts)
  - [List Posts](#list-posts)
  - [Get Post Detail](#get-post-detail)
  - [Create Post](#create-post)
  - [Update Post](#update-post)
  - [Delete Post](#delete-post)
- [Categories](#categories)
- [Tags](#tags)
- [Aturan Otorisasi (Role)](#aturan-otorisasi-role)
- [Rate Limiting](#rate-limiting)
- [Format Error](#format-error)

---

## Autentikasi

Semua endpoint di bawah `Posts`, `Categories`, dan `Tags` butuh header:

```
Authorization: Bearer {token}
Accept: application/json
```

Token didapat dari endpoint **Register** atau **Login**.

### Register

Daftar akun baru. Role default akun baru adalah **`reader`** (tanpa akses dashboard admin — cuma bisa baca blog publik). Kalau butuh akses dashboard, akun harus dinaikkan rolenya secara manual oleh admin (jadi `author`, `editor`, atau `admin`).

```
POST /api/register
```

**Body**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `name` | string | ✅ | Maks 255 karakter |
| `email` | string | ✅ | Harus unik |
| `password` | string | ✅ | Minimal 8 karakter |
| `device_name` | string | — | Nama device, default `"mobile-app"`. Berguna kalau user login dari beberapa device sekaligus |

**Contoh request**
```bash
curl -X POST https://domain-kamu.com/api/register \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "password": "password123",
    "device_name": "iphone-budi"
  }'
```

**Response `201 Created`**
```json
{
  "message": "Registrasi berhasil.",
  "user": {
    "id": 5,
    "name": "Budi Santoso",
    "email": "budi@example.com",
    "role": "reader",
    "email_verified_at": null,
    "created_at": "2026-07-26T10:00:00.000000Z",
    "updated_at": "2026-07-26T10:00:00.000000Z"
  },
  "token": "5|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
}
```

Simpan `token` ini — dipakai di header `Authorization: Bearer {token}` untuk semua request berikutnya.

---

### Login

```
POST /api/login
```

**Body**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `email` | string | ✅ | |
| `password` | string | ✅ | |
| `device_name` | string | — | Default `"mobile-app"` |

**Contoh request**
```bash
curl -X POST https://domain-kamu.com/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email": "budi@example.com", "password": "password123"}'
```

**Response `200 OK`** — bentuknya sama seperti register (`message`, `user`, `token`).

**Response gagal `422 Unprocessable Entity`** (email/password salah):
```json
{
  "message": "Email atau password salah.",
  "errors": { "email": ["Email atau password salah."] }
}
```

---

### Logout

Mencabut token yang sedang dipakai request ini saja (device lain tetap login kalau ada).

```
POST /api/logout
```
*Butuh Authorization header.*

**Response `200 OK`**
```json
{ "message": "Logout berhasil." }
```

---

### Get Current User

```
GET /api/user
```
*Butuh Authorization header.*

**Response `200 OK`** — data user yang sedang login (`id`, `name`, `email`, `role`, dll).

---

## Posts

### List Posts

```
GET /api/posts
```
*Butuh Authorization header.*

**Query Parameters** (semua opsional)

| Param | Tipe | Keterangan |
|---|---|---|
| `q` | string | Cari di `title`, `excerpt`, dan `body` |
| `category_id` | integer | Filter berdasarkan ID kategori |
| `tag_id` | integer | Filter berdasarkan ID tag |
| `status` | string | `draft` atau `published` |
| `per_page` | integer | Jumlah item per halaman. Default `10`, maksimal `50` |

**Contoh request**
```bash
curl -H "Authorization: Bearer {token}" \
  "https://domain-kamu.com/api/posts?category_id=2&status=published&per_page=20"
```

**Response `200 OK`**
```json
{
  "data": [
    {
      "id": 12,
      "title": "Membangun Gaya Hidup Sehat di Era Digital",
      "slug": "membangun-gaya-hidup-sehat-di-era-digital",
      "excerpt": "...",
      "body": "...",
      "status": "published",
      "published_at": "2026-07-22T09:56:53.000000Z",
      "featured_image_url": "https://domain-kamu.com/storage/posts/abc123.jpg",
      "meta_title": null,
      "meta_description": null,
      "author": { "id": 3, "name": "Admin" },
      "category": { "id": 2, "name": "Lifestyle", "slug": "lifestyle" },
      "tags": [
        { "id": 1, "name": "Kesehatan", "slug": "kesehatan" }
      ],
      "created_at": "2026-07-22T09:56:53.000000Z",
      "updated_at": "2026-07-22T09:56:53.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

> **Catatan:** endpoint ini menampilkan post **apa saja** (termasuk draft) ke siapapun yang terautentikasi — tidak otomatis difilter berdasarkan kepemilikan. Kalau kamu bikin mobile app untuk pembaca umum (bukan buat penulis), filter manual pakai `?status=published` di sisi klien.

---

### Get Post Detail

```
GET /api/posts/{id}
```
*Butuh Authorization header.*

**Response `200 OK`** — 1 object post, bentuknya sama seperti item di `List Posts`, dibungkus `{ "data": {...} }`.

**Response `404 Not Found`** kalau ID tidak ada.

---

### Create Post

```
POST /api/posts
```
*Butuh Authorization header. Content-Type: `multipart/form-data` kalau menyertakan gambar.*

**Body**

| Field | Tipe | Wajib | Keterangan |
|---|---|---|---|
| `title` | string | ✅ | Maks 255 karakter |
| `body` | string | ✅ | Isi artikel (mendukung Markdown) |
| `category_id` | integer | ✅ | Harus ID kategori yang valid |
| `status` | string | ✅ | `draft` atau `published` |
| `excerpt` | string | — | Maks 500 karakter |
| `tags` | array of integer | — | Contoh: `tags[]=1&tags[]=3` |
| `featured_image` | file (image) | — | Maks 2MB. Otomatis di-resize & dikompresi |
| `meta_title` | string | — | Maks 255 karakter |
| `meta_description` | string | — | Maks 500 karakter |

**Contoh request (dengan gambar)**
```bash
curl -X POST https://domain-kamu.com/api/posts \
  -H "Authorization: Bearer {token}" \
  -H "Accept: application/json" \
  -F "title=Judul Artikel" \
  -F "body=Isi artikel..." \
  -F "category_id=2" \
  -F "status=draft" \
  -F "featured_image=@/path/ke/foto.jpg"
```

**Response `201 Created`** — object post yang baru dibuat, dibungkus `{ "message": "...", "data": {...} }`.

**Response gagal `422 Unprocessable Entity`** — field wajib kosong/tidak valid:
```json
{
  "message": "The title field is required.",
  "errors": {
    "title": ["The title field is required."],
    "category_id": ["The category id field is required."]
  }
}
```

**Response `403 Forbidden`** — role user tidak diizinkan membuat post (lihat [Aturan Otorisasi](#aturan-otorisasi-role)).

---

### Update Post

```
PUT /api/posts/{id}
```
*Field & validasi sama seperti Create Post.*

**Response `200 OK`** — object post yang sudah diperbarui.

**Response `403 Forbidden`** — kalau:
- Role `author` mencoba update post **milik user lain**
- Role tidak diizinkan sama sekali (lihat tabel di bawah)

> Kalau tidak menyertakan `featured_image` baru, gambar lama tetap dipertahankan (tidak terhapus).

---

### Delete Post

```
DELETE /api/posts/{id}
```

**Response `200 OK`**
```json
{ "message": "Post berhasil dihapus." }
```
Featured image (kalau ada) otomatis ikut terhapus dari storage.

**Response `403 Forbidden`** — aturan sama seperti Update Post.

---

## Categories

```
GET    /api/categories        List semua kategori + jumlah post
GET    /api/categories/{id}   Detail 1 kategori
POST   /api/categories        Buat kategori baru
PUT    /api/categories/{id}   Update nama kategori
DELETE /api/categories/{id}   Hapus kategori
```

**Body untuk Create/Update:**

| Field | Tipe | Wajib |
|---|---|---|
| `name` | string, maks 255 | ✅ |

**Response List** (`GET /api/categories`):
```json
{
  "data": [
    { "id": 1, "name": "Teknologi", "slug": "teknologi", "posts_count": 12, "created_at": "...", "updated_at": "..." }
  ]
}
```

**Response `409 Conflict`** saat hapus kategori yang masih punya post:
```json
{ "message": "Kategori tidak bisa dihapus karena masih memiliki post." }
```

**Response `403 Forbidden`** — hanya role `admin` dan `editor` yang boleh create/update/delete. Role `author` cuma bisa `GET` (baca).

---

## Tags

```
GET    /api/tags        List semua tag + jumlah post
GET    /api/tags/{id}   Detail 1 tag
POST   /api/tags        Buat tag baru
PUT    /api/tags/{id}   Update nama tag
DELETE /api/tags/{id}   Hapus tag
```

Struktur request/response, aturan otorisasi, dan format sama persis dengan **Categories** di atas.

---

## Aturan Otorisasi (Role)

BlogKu punya 4 role untuk pengguna terautentikasi: `admin`, `editor`, `author`, `reader`.

| Aksi | Admin | Editor | Author | Reader |
|---|:---:|:---:|:---:|:---:|
| Lihat semua post | ✅ | ✅ | ✅ | ✅ |
| Buat post baru | ✅ | ✅ | ✅ | ❌ `403` |
| Edit/hapus post **milik sendiri** | ✅ | ✅ | ✅ | ❌ `403` |
| Edit/hapus post **milik orang lain** | ✅ | ✅ | ❌ `403` | ❌ `403` |
| Kelola kategori (create/update/delete) | ✅ | ✅ | ❌ `403` | ❌ `403` |
| Kelola tag (create/update/delete) | ✅ | ✅ | ❌ `403` | ❌ `403` |

> Role `reader` adalah default untuk akun yang baru register lewat `/api/register` atau `/register` — cuma bisa baca konten, tidak bisa menulis apapun. Untuk jadi kontributor (`author`), akun harus dinaikkan rolenya manual oleh admin lewat `tinker` atau (nanti) fitur manajemen user di dashboard.

---

## Rate Limiting

| Endpoint | Batas |
|---|---|
| `POST /api/register` | 6 request / menit / IP |
| `POST /api/login` | 6 request / menit / IP |
| Semua endpoint lain (butuh token) | 60 request / menit / user |

Kalau limit terlampaui, response `429 Too Many Requests`:
```json
{ "message": "Too Many Attempts." }
```
Header response juga menyertakan `Retry-After` (detik) dan `X-RateLimit-Remaining`.

---

## Format Error

Semua error validasi (`422`) mengikuti format standar Laravel:
```json
{
  "message": "Ringkasan error",
  "errors": {
    "nama_field": ["Pesan error spesifik untuk field ini"]
  }
}
```

Error otorisasi (`403`) dan tidak ditemukan (`404`) cuma punya field `message`, tanpa `errors`.

---

*Dokumentasi ini dibuat untuk BlogKu — update manual kalau ada endpoint/field yang berubah.*