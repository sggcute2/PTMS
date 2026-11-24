# Security Checklist - PTMS

Daftar periksa keamanan aplikasi terhadap standar **OWASP Top 10** dan best-practice Laravel Security.

---

## 1. Authentication & Session Management
- [x] **Password Hashing**: Kata sandi di-hash menggunakan algoritma modern `bcrypt` / `argon2id` dengan minimum rounds aman.
- [x] **Session Regeneration**: Panggilan `$request->session()->regenerate()` saat login dan `$request->session()->invalidate()` saat logout untuk mencegah *Session Fixation*.
- [x] **Guest Redirection**: Endpoint halaman utama (`/`) memproteksi pengguna non-otentikasi dan mengalihkan ke `/login`.

---

## 2. Authorization & Access Control (Broken Access Control Prevention)
- [x] **Insecure Direct Object References (IDOR)**:
  - Setiap akses detail, edit, update, dan delete proyek divalidasi via `ProjectPolicy`.
  - Setiap akses tugas divalidasi via `TaskPolicy` (memastikan proyek penaung milik user aktif).
  - Setiap akses entri audit divalidasi via `ActivityLogPolicy`.
- [x] **Context-Aware HTTP 403 Response**:
  - Penolakan hak akses menghasilkan status 403 Forbidden dengan pesan informatif dan tombol navigasi aman tanpa membocorkan data sensitif.

---

## 3. Injection Prevention
- [x] **SQL Injection (SQLi)**:
  - Seluruh query menggunakan parameter binding PDO via Eloquent ORM.
  - Input pencarian `title` dan filter aksi ter-sanitize secara otomatis melalui prepared statements.
- [x] **Cross-Site Scripting (XSS)**:
  - Seluruh output pengguna pada template Blade dirender menggunakan kurung kurawal ganda `{{ $var }}` yang memanggil `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
  - Tag script mentah (`<script>`, `<svg onload>`, `<img onerror>`) ter-escape menjadi entitas teks HTML yang aman.

---

## 4. Request Integrity & CSRF Protection
- [x] **Cross-Site Request Forgery (CSRF)**:
  - Middleware `ValidateCsrfToken` aktif di seluruh grup rute Web.
  - Setiap formulir mutasi (POST/PUT/PATCH/DELETE) menyertakan `@csrf` token yang diverifikasi di sisi server.
- [x] **Mass Assignment Protection**:
  - Kolom sensitif seperti `user_id` tidak diizinkan di-override via form mass-assignment.
  - Penggunaan atribut `#[Fillable]` pada model Eloquent membatasi mutasi hanya pada atribut yang sah.

---

## 5. API Security & Rate Limiting
- [x] **API Rate Limiting**:
  - Rute `PATCH /api/tasks/{task}/status` dilindungi throttle limit 10 request/menit untuk memitigasi serangan brute-force atau DDoS.
- [x] **Stateful Session / Token Authentication**:
  - Route API memvalidasi session cookie yang terenkripsi dan token CSRF / Sanctum.

---

## 6. Evaluasi Hasil Automated Security Test
```bash
php artisan test tests/Security
```
- `IdorTest`: **PASS** (403 saat akses ID pihak ketiga).
- `MassAssignmentTest`: **PASS** (owner bypass tertolak).
- `CsrfProtectionTest`: **PASS** (POST tanpa token valid menghasilkan 419).
- `SqlInjectionTest`: **PASS** (input SQLi aman).
- `XssProtectionTest`: **PASS** (payload script ter-escape).
