# Test Plan - Project & Task Management System (PTMS)

## 1. Pendahuluan
Dokumen ini mendefinisikan strategi, cakupan, arsitektur, dan alur pengujian perangkat lunak untuk aplikasi **PTMS (Project & Task Management System)** berbasis **Laravel 13** dan **PHP 8.3**.

## 2. Tujuan Pengujian
- Memastikan integritas data transaksi lintas tabel (multi-table atomic transaction).
- Memverifikasi kepatuhan isolasi data dan otorisasi pengguna berbasis *Policy* (RBAC).
- Menguji ketahanan aplikasi terhadap kondisi balapan (*race conditions*) melalui mekanisme *pessimistic locking*.
- Menjamin efisiensi eksekusi query (mencegah masalah query $N+1$).
- Memvalidasi perlindungan aplikasi terhadap kerentanan keamanan utama (IDOR, SQL Injection, CSRF, Mass Assignment, dan XSS).

## 3. Cakupan Pengujian (Test Scope)

### In-Scope:
1. **Unit Testing (`tests/Unit`)**:
   - Lapisan Layanan (*Services*): `ProjectService`, `TaskService`, `ActivityLogService`.
   - Lapisan Aksi (*Actions*): Create/Update Project & Task, Update Task Status.
   - Aturan Domain (*Domain Logic*): `TaskStatusTransition` finite state machine.
2. **Feature Testing (`tests/Feature`)**:
   - Autentikasi: Registrasi, Login, Logout, dan Session Security.
   - Otorisasi: `ProjectPolicy`, `TaskPolicy`, `ActivityLogPolicy` (HTTP 403 context-aware).
   - Fitur Proyek: Pembuatan atomik, soft delete kaskade, tampilan detail.
   - Fitur Tugas: Filter status, pencarian, deteksi *overdue*.
   - Riwayat Aktivitas (*Audit Trail*): Listing, filter tanggal/aksi, paginasi.
   - Dashboard: Agregasi metrik per proyek per status.
   - API Endpoint: `PATCH /api/tasks/{task}/status` ber-rate limit.
3. **Integration Testing (`tests/Integration`)**:
   - Konsistensi transaksi DB dan *rollback atomicity*.
   - Kaskade *soft delete* dan *restore*.
   - Integritas relasional basis data (*foreign key constraints*).
4. **Concurrency Testing (`tests/Concurrency`)**:
   - Penanganan request simultan (pessimistic locking `lockForUpdate()`).
   - Pencegahan perubahan status ganda (*double-click / race condition*).
5. **Performance Testing (`tests/Performance`)**:
   - Analisis jumlah query ($N+1$ check) pada Dashboard, Detail Proyek, dan Log Audit.
6. **Security Testing (`tests/Security`)**:
   - IDOR, SQL Injection, CSRF Protection, XSS Escaping, dan Mass Assignment.

### Out-of-Scope:
- Pengujian performa beban terdistribusi ekstrim (stress testing multi-server > 10.000 concurrent users).
- Kompatibilitas cross-browser lawas (IE11 / legacy engines).

## 4. Lingkungan Pengujian (Test Environment)
- **Framework**: Laravel 13.x
- **Test Runner**: PHPUnit 12.x / Pest compatible
- **Database Testing**: In-Memory SQLite (`:memory:`) dengan `RefreshDatabase`
- **Database Produksi/Staging**: MySQL 8.0 (Dockerized)
- **Cache / Session Driver**: Array driver saat testing

## 5. Kriteria Keberhasilan (Pass/Fail Criteria)
- **Pass Rate**: 100% lulus (0 failure, 0 error).
- **Regresi**: Tidak ada breaking changes pada endpoint API JSON maupun rute Web Blade.
- **Coverage**: Seluruh logika bisnis inti pada Service dan Action ter-cover oleh automated test.
