# Project & Task Management System (PTMS)

Aplikasi manajemen proyek dan tugas berbasis **Laravel 13** dan **PHP 8.3** yang dirancang dengan sistem otorisasi ketat (*Policy-based Authorization*), *Audit Trail Activity Logging* otomatis, pencegahan *race conditions* (pessimistic locking), transisi status berbasis *finite state*, REST API ber-rate limiting, serta dukungan kontainerisasi dengan Docker Compose.

---

## 🌟 Fitur Utama

### 1. Manajemen Proyek & Tugas (*Multi-table Transaction*)
- **Pembuatan Atomic**: Menyimpan data proyek beserta tugas-tugas awal dalam satu transaksi database (`DB::transaction`).
- **Dashboard Ringkasan Agregasi**: Menampilkan statistik total tugas, tugas berstatus *pending*, *in progress*, dan *completed* per proyek secara real-time.
- **Pencarian & Filter Tugas**:
  - Pencarian berdasarkan judul tugas.
  - Filter berdasarkan status (*pending*, *in_progress*, *completed*).
  - Deteksi otomatis tugas yang telah melewati batas waktu (*overdue*) dan belum selesai.
- **State Transition Tugas yang Terkendali**:
  - Alur status hanya dapat maju satu tahap (`pending` ➔ `in_progress` ➔ `completed`).
  - Pencegahan pembaruan status ganda secara konkuren menggunakan *pessimistic locking* (`lockForUpdate`).

### 2. Keamanan & Otorisasi (*Policy-Enforced RBAC*)
- **ProjectPolicy & TaskPolicy**: Pengguna hanya dapat melihat, mengubah, dan menghapus proyek serta tugas miliknya sendiri.
- **ActivityLogPolicy**: Catatan riwayat audit bersifat rahasia dan hanya dapat diakses oleh pemilik akun terkait.
- **Halaman Penolakan Akses Kustom (Context-Aware 403)**:
  - Deteksi cerdas konteks penolakan (Proyek, Tugas, atau Riwayat Audit).
  - Tampilan khusus informatif berbahasa Indonesia lengkap dengan alasan keamanan dan navigasi kembali yang relevan.

### 3. Audit Trail & Riwayat Aktivitas
- Pencatatan otomatis ke tabel `activity_logs` saat:
  - Proyek dibuat (`PROJECT_CREATED`) atau dihapus (`PROJECT_DELETED`).
  - Tugas dibuat (`TASK_CREATED`) atau diperbarui statusnya (`TASK_STATUS_UPDATED` beserta *old* dan *new* value).
- **Halaman Riwayat Aktivitas Pengguna**:
  - Dilengkapi paginasi dan filter berdasarkan jenis aksi serta rentang tanggal (*start date* s/d *end date*).
  - Halaman detail individual log aktivitas.

### 4. REST API & Rate Limiting
- **Endpoint Update Status Tugas**: `PATCH /api/tasks/{task}/status` (format respon JSON standar).
- **Rate Limiting**: Dibatasi maksimal 10 request per menit per pengguna/IP (mengembalikan respon `429 Too Many Requests` jika melampaui ambang batas).
- Autentikasi API via Laravel Sanctum.

### 5. Dukungan Kontainerisasi Docker Compose
- Lingkungan terisolasi siap pakai:
  - **Web**: Nginx Alpine (`:8000`)
  - **App**: PHP 8.3-FPM Alpine (Composer, PDO MySQL, ekstensi Laravel lengkap)
  - **Database**: MySQL 8.0 (`:3307` di host, port `:3306` internal)
- Skrip *entrypoint* otomatis untuk pengecekan kesiapan database, generasi app key, dan eksekusi migrasi tabel.

---

## 🛠️ Prasyarat Sistem

Jika dijalankan langsung di lingkungan lokal:
- PHP >= 8.3 (dengan ekstensi `pdo_mysql`, `mbstring`, `intl`, `bcmath`, `curl`, `zip`)
- Composer >= 2.x
- MySQL >= 8.0 atau MariaDB (misal: XAMPP)
- Node.js & NPM (opsional, untuk build frontend jika diperlukan)

Atau cukup gunakan **Docker & Docker Compose**.

---

## 🚀 Panduan Instalasi & Menjalankan

### Opsi A: Menggunakan Docker Compose (Direkomendasikan)

1. **Salin environment Docker:**
   ```bash
   cp .env.docker.example .env
   ```

2. **Jalankan kontainer:**
   ```bash
   docker compose up -d --build
   ```

3. **Buka aplikasi:**
   Akses di browser melalui **[http://localhost:8000](http://localhost:8000)**.
   *(Database dimigrasikan otomatis saat kontainer dijalankan).*

---

### Opsi B: Instalasi dari repositori github https://github.com/sggcute2/PTMS.git

1. **Clone repositori dari GitHub dan masuk ke direktori proyek:**
   ```bash
   git clone https://github.com/sggcute2/PTMS.git
   cd PTMS/app
   ```

2. **Pasang dependensi PHP:**
   ```bash
   composer install
   ```

3. **Siapkan berkas environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database di file `.env`:**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=ptms
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Jalankan migrasi database:**
   ```bash
   php artisan migrate
   ```

6. **Jalankan server pengembangan:**
   ```bash
   php artisan serve
   ```
   Akses aplikasi di **[http://127.0.0.1:8000](http://127.0.0.1:8000)**.

---

## 🧪 Pengujian Otomatis (Automated Tests)

Aplikasi PTMS dilengkapi dengan rangkaian pengujian otomatis komprehensif (Unit, Feature, Integration, Concurrency, Performance, dan Security) menggunakan **PHPUnit** / **Pest**:

### Menjalankan Seluruh Pengujian:
```bash
php artisan test
```

### Menjalankan Berdasarkan Kategori / Test Suite:
```bash
# 1. Unit Tests (Services, Actions, Domain Logic)
php artisan test --testsuite=Unit

# 2. Feature Tests (Auth, Authorization, CRUD, Logs, Dashboard, API)
php artisan test --testsuite=Feature

# 3. Integration Tests (Database Transactions, Soft Deletes & Integrity)
php artisan test tests/Integration

# 4. Concurrency Tests (Race Condition & LockForUpdate)
php artisan test tests/Concurrency

# 5. Performance Tests (N+1 Query & Scaling Checks)
php artisan test tests/Performance

# 6. Security Tests (IDOR, Mass Assignment, CSRF, SQLi, XSS)
php artisan test tests/Security
```

---

### Struktur & Cakupan Pengujian:

```
tests/
├── Unit/
│   ├── Services/               # ProjectServiceTest, TaskServiceTest, ActivityLogServiceTest
│   ├── Actions/                # Create/Update Project & Task, UpdateTaskStatusActionTest
│   └── Domain/                 # TaskStatusTransitionTest (FSM validation)
│
├── Feature/
│   ├── Authentication/         # RegisterTest, LoginTest, LogoutTest
│   ├── Authorization/          # ProjectPolicyTest, TaskPolicyTest, ActivityLogPolicyTest (403 Context-Aware)
│   ├── Projects/               # CreateProjectTest, ViewProjectTest, DeleteProjectTest
│   ├── Tasks/                  # CreateTaskTest, DeleteTaskTest, SearchTaskTest, FilterTaskTest, OverdueTaskTest
│   ├── ActivityLogs/           # ActivityLogListingTest, ActivityLogFilterTest, ActivityLogPaginationTest
│   ├── Dashboard/              # DashboardSummaryTest (Aggregated project-task metrics)
│   ├── HealthCheck/            # BasicApplicationHealthTest (/up endpoint)
│   └── Api/                    # UpdateTaskStatusApiTest (JSON response & rate limiting 429)
│
├── Integration/
│   ├── TaskStatusTransactionTest.php       # Rollback atomicity on failure
│   ├── ActivityLogTransactionTest.php      # Audit log rollback consistency
│   ├── SoftDeleteIntegrationTest.php       # Cascading soft delete & restore
│   └── DatabaseIntegrityTest.php           # Foreign key & unique constraints
│
├── Concurrency/
│   ├── UpdateTaskStatusRaceConditionTest.php # Double submit & race condition mitigation
│   └── LockForUpdateTest.php                 # Pessimistic locking (lockForUpdate) verification
│
├── Performance/
│   ├── DashboardQueryCountTest.php         # N+1 query prevention on dashboard
│   ├── ProjectDetailQueryCountTest.php     # Constant query count on high-volume tasks
│   └── ActivityLogQueryCountTest.php       # Polymorphic eager loading verification
│
└── Security/
    ├── IdorTest.php                        # Insecure Direct Object References prevention
    ├── MassAssignmentTest.php              # Guarded attributes protection
    ├── CsrfProtectionTest.php              # ValidateCsrfToken enforcement
    ├── SqlInjectionTest.php                # PDO parameter binding resilience
    └── XssProtectionTest.php               # HTML entities escaping on Blade templates
```

### 📋 Daftar Skenario Uji (Test Scenarios Matrix)

| ID Skenario | Kategori | Skenario Uji | Ekspektasi Hasil |
| :--- | :--- | :--- | :--- |
| **TS-AUTH-001** | Autentikasi | Registrasi akun baru dengan data valid | Akun tersimpan, user login otomatis, redirect ke dashboard |
| **TS-AUTH-002** | Autentikasi | Registrasi gagal jika konfirmasi sandi tidak sesuai | Menampilkan pesan error validasi input |
| **TS-AUTH-003** | Autentikasi | Login dengan kredensial yang tepat | Autentikasi berhasil, redirect ke dashboard |
| **TS-AUTH-004** | Autentikasi | Login dengan kata sandi salah | Ditolak, muncul error kredensial pada session |
| **TS-AUTH-005** | Autentikasi | Logout pengguna | Sesi dan token CSRF di-regenerate, kembali ke login |
| **TS-AUTH-006** | Autentikasi | Tamu mengakses root URL (`/`) | Diredirect ke halaman login (HTTP 302) |
| **TS-AUTH-007** | Autentikasi | User terotentikasi mengakses root URL (`/`) | Menampilkan dashboard utama (HTTP 200) |
| **TS-AUTH-008** | Health Check | Uji endpoint status aplikasi (`/up`) | Mengembalikan status HTTP 200 OK |
| **TS-AUTH-009** | Otorisasi | Akses proyek milik pengguna lain | Ditolak HTTP 403, judul *"Akses ke Proyek Dibatasi"* |
| **TS-AUTH-010** | Otorisasi | Akses tugas milik pengguna lain | Ditolak HTTP 403, judul *"Akses ke Tugas Dibatasi"* |
| **TS-AUTH-011** | Otorisasi | Akses riwayat audit pengguna lain | Ditolak HTTP 403, judul *"Akses ke Riwayat Audit Dibatasi"* |
| **TS-PROJ-001** | Proyek | Pembuatan proyek & initial tasks secara atomik | Data tersimpan utuh dalam 1 transaksi DB (`DB::transaction`) |
| **TS-PROJ-002** | Proyek | Soft delete proyek dan tugas-tugas terkait | Proyek & seluruh task ter-soft delete kaskade |
| **TS-PROJ-003** | Proyek | Pemulihan (*restore*) proyek terhapus | Proyek & tugas-tugasnya kembali aktif |
| **TS-TASK-001** | Tugas | Transisi status satu tahap: `pending` ➔ `in_progress` | Status berhasil berubah & log audit tercatat |
| **TS-TASK-002** | Tugas | Transisi status satu tahap: `in_progress` ➔ `completed` | Status berhasil berubah & log audit tercatat |
| **TS-TASK-003** | Tugas | Penolakan lompat status (misal `pending` ➔ `completed`) | Melempar `ValidationException`, status tidak berubah |
| **TS-TASK-004** | Tugas | Penolakan perubahan status dari tahap akhir | Ditolak, status `completed` tidak dapat dimajukan lagi |
| **TS-TASK-005** | Tugas | Pencarian tugas berdasarkan judul | Menampilkan daftar tugas yang sesuai kata kunci |
| **TS-TASK-006** | Tugas | Filter tugas berdasarkan status | Menampilkan hanya tugas dengan status yang dipilih |
| **TS-TASK-007** | Tugas | Filter tugas terlewat jatuh tempo (*overdue*) | Menampilkan tugas belum selesai dengan `due_date < today` |
| **TS-LOG-001** | Audit Trail | Pencatatan otomatis lifecycle data | Event Model mencatat log ke tabel `activity_logs` |
| **TS-LOG-002** | Audit Trail | Filter log berdasarkan aksi dan rentang tanggal | Menampilkan baris riwayat aktivitas yang sesuai filter |
| **TS-LOG-003** | Audit Trail | Paginasi riwayat aktivitas (10 data/halaman) | Menampilkan paginasi dengan mempertahankan query string |
| **TS-DASH-001** | Dashboard | Agregasi metrik tugas per proyek | Total, pending, in progress, dan completed terhitung akurat |
| **TS-API-001** | REST API | Perubahan status tugas via API JSON | Respon JSON status sukses dengan data tugas terbaru |
| **TS-API-002** | REST API | Pembatasan frekuensi request (*rate limiting*) | Request melebihi 10 req/menit mengembalikan HTTP 429 |
| **TS-CONC-001** | Concurrency | Simulasi double-submit status secara konkuren | Hanya 1 request yang berhasil, request kedua ditolak |
| **TS-CONC-002** | Concurrency | Eksekusi pessimistic lock `lockForUpdate()` | Menjamin konsistensi pembacaan sekuensial dalam transaksi |
| **TS-PERF-001** | Performance | Pencegahan query $N+1$ pada Dashboard | Query agregasi dijalankan efisien dalam satu query batch |
| **TS-PERF-002** | Performance | Uji beban query pada Detail Proyek banyak tugas | Jumlah query tetap konstan ($O(1)$) berkat eager loading |
| **TS-SEC-001** | Security | Uji manipulasi ID langsung (*IDOR*) | Akses parameter ID pihak ketiga digagalkan oleh Policy (403) |
| **TS-SEC-002** | Security | Uji Mass Assignment vulnerability | Kolom `user_id` tidak dapat dibajak via payload formulir |
| **TS-SEC-003** | Security | Uji proteksi CSRF | POST request tanpa token valid ditolak (HTTP 419) |
| **TS-SEC-004** | Security | Uji proteksi SQL Injection | Parameter filter/search aman dari injeksi SQL via PDO binding |
| **TS-SEC-005** | Security | Uji proteksi Cross-Site Scripting (*XSS*) | Tag skrip berbahaya di-escape menjadi HTML entities aman |

---

> 📖 **Dokumentasi Pengujian Terperinci:**
> Informasi lebih lanjut mengenai matriks skenario, dataset uji, dan checklist dapat dilihat pada folder [`docs/testing/`](docs/testing/):
> - [`TEST_PLAN.md`](docs/testing/TEST_PLAN.md): Rencana dan strategi pengujian.
> - [`TEST_SCENARIO_MASTER.md`](docs/testing/TEST_SCENARIO_MASTER.md): Matriks seluruh ID skenario uji.
> - [`TEST_DATA.md`](docs/testing/TEST_DATA.md): Strategi fixture dan payload pengujian.
> - [`ACCEPTANCE_CRITERIA.md`](docs/testing/ACCEPTANCE_CRITERIA.md): Kriteria penerimaan sistem.
> - [`PERFORMANCE_CHECKLIST.md`](docs/testing/PERFORMANCE_CHECKLIST.md): Checklist evaluasi performa & query.
> - [`SECURITY_CHECKLIST.md`](docs/testing/SECURITY_CHECKLIST.md): Checklist keamanan OWASP.

---

## 📂 Struktur Direktori Utama

```text
app/
├── app/
│   ├── Actions/                  # Single Responsibility Action classes (Create/Update Project/Task, Status)
│   ├── Domain/                   # Domain Logic murni & Finite State Machine (TaskStatusTransition)
│   ├── Http/
│   │   ├── Controllers/          # Controller Web (Project, Task, Auth, Dashboard, ActivityLog)
│   │   │   └── Api/              # Controller REST API JSON (TaskController ber-rate limiting)
│   │   └── Requests/             # Form Request validation classes
│   ├── Models/                   # Eloquent Models (User, Project, Task, ActivityLog)
│   ├── Policies/                 # Policy Otorisasi (ProjectPolicy, TaskPolicy, ActivityLogPolicy)
│   ├── Providers/                # Service Providers & Rate Limiter bootstrap
│   ├── Services/                 # Business Logic (ProjectService, TaskService, ActivityLogService)
│   └── Traits/                   # LogsActivity (Audit Trail model events observer)
│
├── bootstrap/                    # Framework bootstrap, routing registration & statefulApi middleware
├── config/                       # Konfigurasi aplikasi (app, database, sanctum, session, dll.)
├── database/
│   ├── factories/                # Model Factories untuk seeding dan automated tests
│   ├── migrations/               # Skema migrasi tabel database (users, projects, tasks, activity_logs)
│   └── seeders/                  # Database Seeders
│
├── docs/
│   └── testing/                  # Dokumentasi pengujian komprehensif (Plan, Scenarios, Checklist)
│
├── docker/
│   ├── nginx/                    # Konfigurasi Virtual Host reverse proxy Nginx
│   └── entrypoint.sh             # Skrip otomatisasi inisialisasi kontainer, migrasi & key generator
│
├── resources/views/
│   ├── activity_logs/            # Antarmuka riwayat aktivitas pengguna & detail audit log
│   ├── errors/
│   │   └── 403.blade.php         # Halaman khusus Akses Ditolak context-aware (Proyek, Tugas, Audit)
│   ├── layouts/                  # Layout utama aplikasi (TailwindCSS v4 CDN & Inter Font)
│   ├── projects/                 # Antarmuka daftar, tambah, edit & detail proyek (Ajax enabled)
│   └── tasks/                    # Antarmuka daftar tugas, pencarian, filter status & penanda overdue
│
├── routes/
│   ├── api.php                   # Rute REST API ber-rate limiting (JSON endpoint)
│   ├── console.php               # Rute Artisan CLI commands
│   └── web.php                   # Rute antarmuka Web aplikasi
│
├── tests/                        # Rangkaian pengujian otomatis (Unit, Feature, Integration, Concurrency, Perf, Sec)
├── .env.docker.example           # Contoh template environment untuk Docker Compose
├── .env.example                  # Contoh template environment lokal
├── docker-compose.yml            # Konfigurasi multi-container Docker (Nginx, PHP 8.3 FPM, MySQL 8.0)
├── Dockerfile                    # Docker build file PHP 8.3-FPM Alpine
└── README.md                     # Dokumentasi utama proyek PTMS
```

---

## 📄 Lisensi

Aplikasi ini dilisensikan di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
