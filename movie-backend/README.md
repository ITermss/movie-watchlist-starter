# FilmLog Backend API (Laravel)

Layanan RESTful API untuk **FilmLog (Movie Watchlist & Review Platform)** — Tugas Rekrutmen Back-End GDGoC UGM 26/27. Dibangun menggunakan **Laravel 13 (PHP 8.3+)**, autentikasi stateless **JWT (`php-open-source-saver/jwt-auth`)**, **SQLite / PostgreSQL**, dan integrasi **Google Gemini API** dengan strategi *database grounding* berlapis.

---

## 📋 Prasyarat (Prerequisites)

Pastikan perangkat Anda telah terinstal:
- **PHP** >= 8.3 (direkomendasikan PHP 8.3 / 8.4 / 8.5 dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `curl`)
- **Composer** >= 2.x
- **Python 3** (opsional, untuk menjalankan static server `frontend/`)
- **Google Gemini API Key** (dapatkan gratis melalui [Google AI Studio](https://aistudio.google.com/app/apikey))

---

## 🚀 Panduan Instalasi & Setup

1. **Masuk ke direktori backend:**
   ```bash
   cd movie-backend
   ```

2. **Instal dependensi PHP melalui Composer:**
   ```bash
   composer install
   ```

3. **Salin file environment `.env.example` menjadi `.env`:**
   ```bash
   cp .env.example .env
   ```
   *(Di Windows PowerShell: `Copy-Item .env.example .env`)*

4. **Generate Application Key dan JWT Secret:**
   ```bash
   php artisan key:generate
   php artisan jwt:secret
   ```

5. **Atur konfigurasi di dalam file `.env`:**
   Pastikan variabel berikut terisi di `.env`:
   ```env
   DB_CONNECTION=sqlite

   AUTH_GUARD=api
   JWT_TTL=1440

   GEMINI_API_KEY=AIzaSy...isi_api_key_anda_di_sini
   GEMINI_MODEL=gemini-3.5-flash
   ```

6. **Siapkan file database SQLite dan jalankan migrasi beserta seeder (20 film awal):**
   ```bash
   touch database/database.sqlite
   php artisan migrate:fresh --seed
   ```
   *(Di Windows PowerShell jika file belum ada: `New-Item -ItemType File -Force database/database.sqlite`)*

---

## ▶️ Cara Menjalankan Aplikasi

1. **Jalankan server backend Laravel (Port 8000):**
   ```bash
   php artisan serve --port=8000
   ```
   API akan aktif di `http://localhost:8000/api`.

2. **Jalankan Frontend Companion (Port 3000) di terminal terpisah:**
   ```bash
   cd ../frontend
   python -m http.server 3000 --bind 127.0.0.1
   ```
   Buka `http://localhost:3000` di browser untuk menguji seluruh fitur secara visual dan memantau request melalui **API Network Inspector**.

---

## Dokumentasi API (Postman)

- **Dokumentasi online:** https://documenter.getpostman.com/view/58696710/2sBYHYuJEJ
- **File collection (untuk di-import):** [`docs/movie-watchlist-api.postman_collection.json`](docs/movie-watchlist-api.postman_collection.json)

Cara memakai file collection:
1. Postman → **Import** → pilih file tersebut.
2. Pastikan variabel `base_url` mengarah ke server (default `http://localhost:8000/api`).
3. Jalankan **Daftar Akun Baru**, lalu **Masuk dan Dapatkan Token**. Token tersimpan otomatis.
4. Jalankan request lainnya. Tiap request punya deskripsi dan contoh respons sukses maupun error.

## 📡 Daftar Endpoint & Kontrak API

Semua endpoint menggunakan prefix `/api`. Seluruh respons error selalu berbentuk JSON dengan properti `{"message": "..."}`.

### 1. Autentikasi (`Public`)
| Method | Endpoint | Body Request | Status Code | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/auth/register` | `{ "username", "email", "password" }` | `201`, `422` | Registrasi akun baru (`username` maks 50 karakter, `email` unik, `password` min 8 karakter di-hash dengan bcrypt). Mengembalikan `{ message, user }`. |
| `POST` | `/api/auth/login` | `{ "email", "password" }` | `200`, `401`, `422` | Login pengguna dan mengembalikan `{ token, user: { id, username, email } }`. |

### 2. Katalog Film & Rekomendasi AI (`Public`)
| Method | Endpoint | Query / Body | Status Code | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/movies` | `?genre=`, `?search=`, `?random=true` | `200` | Mengembalikan array daftar film beserta filter opsional berdasarkan genre, judul/sutradara, atau urutan acak. |
| `GET` | `/api/movies/{id}` | - | `200`, `404` | Mengembalikan detail satu objek film berdasarkan ID. |
| `POST` | `/api/movies/recommend` | `{ "prompt": "..." }` | `200`, `422`, `429`, `502`, `503`, `504` | Rekomendasi 1 film dari katalog database menggunakan Google Gemini (`{ movie_id, reasoning }`). Dilindungi *rate limiter* `throttle:10,1` (10 req/menit). |

### 3. Watchlist (`Protected` — Header `Authorization: Bearer <token>`)
| Method | Endpoint | Body Request | Status Code | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/watchlist` | - | `200`, `401` | Mengambil daftar film di watchlist milik user yang sedang login (`[{ movie_id, movie, created_at }]`). |
| `POST` | `/api/watchlist` | `{ "movieId": 1 }` | `201`, `401`, `404`, `409`, `422` | Menambahkan film ke watchlist. Mengembalikan `409 Conflict` jika film sudah ada di watchlist user. |
| `DELETE` | `/api/watchlist/{movieId}` | - | `200`, `401`, `404` | Menghapus film dari watchlist user yang sedang login. |

### 4. Watched & Reviews (`Protected` — Header `Authorization: Bearer <token>`)
| Method | Endpoint | Body Request | Status Code | Deskripsi |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/watched` | - | `200`, `401` | Mengambil riwayat film yang sudah ditonton beserta rating & review milik user (`[{ id, movie_id, rating, review, created_at, movie }]`). |
| `POST` | `/api/watched` | `{ "movieId": 1, "rating": 5, "review": "..." }` | `201`, `200`, `401`, `404`, `422` | Mencatat atau memperbarui rating (`1-5`) dan ulasan film (maks 2000 karakter). Mengembalikan `201 Created` untuk ulasan baru atau `200 OK` jika memperbarui ulasan yang sudah ada. |

---

## 🗄️ Skema Database & ERD

File definisi Entity-Relationship Diagram (DBML) tersedia di [`docs/erd.dbml`](docs/erd.dbml) dan dapat divisualisasikan langsung di [dbdiagram.io](https://dbdiagram.io/d).

```mermaid
erDiagram
    users ||--o{ watchlists : "has many"
    users ||--o{ reviews : "writes"
    movies ||--o{ watchlists : "included in"
    movies ||--o{ reviews : "reviewed in"

    users {
        bigint id PK
        varchar username
        varchar email UK
        varchar password
        timestamp created_at
        timestamp updated_at
    }

    movies {
        bigint id PK
        varchar title
        smallint year
        varchar director
        json genres
        decimal rating
        varchar duration
        text synopsis
        text poster_url
        json cast_members
        timestamp created_at
        timestamp updated_at
    }

    watchlists {
        bigint id PK
        bigint user_id FK
        bigint movie_id FK
        timestamp created_at
        timestamp updated_at
    }

    reviews {
        bigint id PK
        bigint user_id FK
        bigint movie_id FK
        tinyint rating
        text review
        timestamp created_at
        timestamp updated_at
    }
```

### Keputusan Desain Utama:
1. **Integritas Relasional & Anti-Duplikasi**: Tabel `watchlists` dan `reviews` menggunakan *foreign key* dengan `ON DELETE CASCADE` serta *composite unique constraint* `UNIQUE(user_id, movie_id)` di level database untuk mencegah duplikasi data sekalipun terjadi *race condition*.
2. **Keamanan & Isolasi Data (*Data Scoping*)**: Seluruh operasi baca/tulis pada `watchlists` dan `reviews` selalu di-*scope* ke `$request->user()->id` dari payload JWT yang terverifikasi. ID pengguna tidak pernah diterima dari body/query request (mencegah celah IDOR).
3. **Strategi *Database Grounding* Gemini**:
   - Menyuntikkan katalog film dari database lokal ke dalam `<movie_catalog>` di `systemInstruction`.
   - Mengisolasi input pengguna di dalam tag `<user_preferences>` sebagai data untuk memitigasi *prompt injection*.
   - Memaksa output JSON terstruktur melalui `responseMimeType: application/json` dan `responseJsonSchema` (`movie_id` integer & `reasoning` string).
   - Melakukan validasi pasca-respons (`in_array($movieId, $validIds, true)`) di kode PHP dengan mekanisme *auto-retry* 1 kali apabila LLM mengembalikan ID di luar katalog.

---

## 🧪 Pengujian Otomatis (Automated Tests)

Jalankan rangkaian *feature test* PHPUnit (menggunakan SQLite `:memory:` sehingga aman bagi data lokal Anda):

```bash
php artisan test
```

Skenario yang diuji di `tests/Feature/WatchlistApiTest.php`:
- Akses rute terproteksi (`/api/watchlist` & `/api/watched`) tanpa token menghasilkan `401 Unauthorized` JSON meskipun tanpa header `Accept: application/json`.
- Menambahkan film duplikat ke watchlist menghasilkan `409 Conflict`.
- Mengirim rating review di luar rentang 1–5 (mis. `9` atau `0`) menghasilkan `422 Unprocessable Content`.
- Isolasi data antar pengguna (User B tidak dapat melihat atau menghapus watchlist User A).
- Validasi pasca-respons dan mekanisme *retry* pada `GeminiRecommender` ketika respons pertama mengembalikan `movie_id` di luar katalog database.
