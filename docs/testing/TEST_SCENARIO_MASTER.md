# Test Scenario Master - PTMS

Daftar master skenario pengujian otomatis dan manual untuk aplikasi PTMS.

| ID Skenario | Modul / Kategori | Deskripsi Skenario | Expected Result | Jenis Test |
| :--- | :--- | :--- | :--- | :--- |
| **TS-AUTH-001** | Authentication | Akses form registrasi akun baru | HTTP 200, form registrasi tampil | Feature |
| **TS-AUTH-002** | Authentication | Registrasi akun baru dengan data valid | User terdaftar di DB, session login aktif, redirect dashboard | Feature |
| **TS-AUTH-003** | Authentication | Registrasi gagal jika konfirmasi sandi tidak cocok | HTTP 302, error validation session muncul | Feature |
| **TS-AUTH-004** | Authentication | Login dengan kredensial yang tepat | User terautentikasi, redirect ke dashboard | Feature |
| **TS-AUTH-005** | Authentication | Login dengan sandi salah | HTTP 302, muncul error email/password, tetap guest | Feature |
| **TS-AUTH-006** | Authentication | Logout pengguna terotentikasi | Session dihancurkan, token di-regenerate, redirect login | Feature |
| **TS-AUTH-007** | Authentication | Akses root (`/`) oleh tamu (unauthenticated) | Redirect ke `/login` (HTTP 302) | Feature |
| **TS-AUTH-008** | Authentication | Akses root (`/`) oleh user login | Menampilkan Dashboard (HTTP 200) | Feature |
| **TS-AUTH-009** | HealthCheck | Pengecekan endpoint `/up` | HTTP 200 OK | Feature |
| **TS-AUTH-010** | Authorization | Akses melihat proyek milik pengguna lain | Ditolak HTTP 403, pesan khusus proyek | Feature |
| **TS-AUTH-011** | Authorization | Akses mengedit/menghapus tugas pengguna lain | Ditolak HTTP 403, pesan khusus tugas | Feature |
| **TS-AUTH-012** | Authorization | Akses melihat riwayat audit pengguna lain | Ditolak HTTP 403, pesan khusus riwayat audit | Feature |
| **TS-PROJ-001** | Projects | Pembuatan proyek atomik beserta daftar tugas awal | Proyek & tugas tersimpan dalam 1 transaksi DB | Unit/Feature |
| **TS-PROJ-002** | Projects | Soft delete proyek | Proyek dan seluruh tugas di dalamnya berstatus soft deleted | Integration |
| **TS-PROJ-003** | Projects | Restore proyek yang terhapus | Proyek dan tugas-tugasnya kembali aktif | Integration |
| **TS-TASK-001** | Tasks | Transisi status satu tahap: pending ➔ in_progress | Status tugas berubah ke in_progress, log audit dicatat | Unit/Feature |
| **TS-TASK-002** | Tasks | Transisi status satu tahap: in_progress ➔ completed | Status tugas berubah ke completed, log audit dicatat | Unit/Feature |
| **TS-TASK-003** | Tasks | Penolakan lompat status (pending ➔ completed) | Melempar ValidationException, status tidak berubah | Unit |
| **TS-TASK-004** | Tasks | Penolakan transisi mundur atau dari status akhir | Melempar ValidationException | Unit |
| **TS-TASK-005** | Tasks | Pencarian tugas berdasarkan judul | Menampilkan hanya tugas yang sesuai substring | Feature |
| **TS-TASK-006** | Tasks | Filter tugas berdasarkan status | Menampilkan hanya tugas dengan status target | Feature |
| **TS-TASK-007** | Tasks | Filter tugas overdue (terlewat jatuh tempo) | Hanya tugas belum selesai dengan due_date < today | Feature |
| **TS-LOG-001** | Activity Logs | Pencatatan otomatis saat manipulasi data | Event Model mencatat log ke tabel `activity_logs` | Integration |
| **TS-LOG-002** | Activity Logs | Filter log berdasarkan jenis aksi & rentang tanggal | Menampilkan baris log sesuai kriteria | Feature |
| **TS-LOG-003** | Activity Logs | Paginasi log riwayat aktivitas | 10 entri per halaman, query string dipertahankan | Feature |
| **TS-DASH-001** | Dashboard | Agregasi metrik tugas per proyek | Total, pending, in_progress, dan completed terhitung akurat | Feature |
| **TS-API-001** | REST API | Update status tugas via API JSON | Respon JSON status success, status tugas terupdate | Feature |
| **TS-API-002** | REST API | Rate limiting endpoint API (maks 10 req/menit) | Melebihi 10 request menghasilkan HTTP 429 Too Many Requests | Feature |
| **TS-CONC-001** | Concurrency | Simulasi double-submit pembaruan status | Hanya 1 request berhasil, request konkuren kedua ditolak | Concurrency |
| **TS-CONC-002** | Concurrency | Eksekusi `lockForUpdate()` dalam transaksi | Menjamin pembacaan sekuensial konsisten | Concurrency |
| **TS-PERF-001** | Performance | Pemeriksaan query $N+1$ di Dashboard | Query tetap terkontrol (agregat conditional) | Performance |
| **TS-PERF-002** | Performance | Pemeriksaan query pada Detail Proyek banyak tugas | Query konstan berkat eager loading | Performance |
| **TS-SEC-001** | Security | Uji ketahanan IDOR | Akses manipulasi via URL ID milik user lain gagal (403) | Security |
| **TS-SEC-002** | Security | Uji Mass Assignment vulnerability | Field sensitif (user_id/is_admin) tidak dapat ditimpa | Security |
| **TS-SEC-003** | Security | Uji proteksi CSRF | POST tanpa token CSRF valid ditolak (HTTP 419) | Security |
| **TS-SEC-004** | Security | Uji proteksi SQL Injection | Payload SQLi pada input search/filter tidak merusak DB | Security |
| **TS-SEC-005** | Security | Uji proteksi XSS | Script tag dalam input ter-escape secara aman oleh Blade | Security |
