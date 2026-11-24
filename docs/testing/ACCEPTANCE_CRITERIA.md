# Acceptance Criteria - PTMS

Kriteria penerimaan fitur (*Acceptance Criteria*) yang harus dipenuhi sebelum rilis.

---

## 1. Modul Manajemen Proyek
- [x] **AC-PROJ-01**: Pengguna dapat membuat proyek baru beserta sejumlah tugas awal sekaligus dalam 1 formulir (tersimpan atomik dalam satu transaksi database).
- [x] **AC-PROJ-02**: Jika salah satu validasi tugas awal gagal, seluruh data proyek dibatalkan (rollback).
- [x] **AC-PROJ-03**: Penghapusan proyek menerapkan *soft delete*, yang otomatis memicu *soft delete* pada semua tugas terkait.
- [x] **AC-PROJ-04**: Pemulihan (*restore*) proyek otomatis memulihkan kembali tugas-tugas yang terhapus bersamaan.

---

## 2. Modul Manajemen Tugas & Finite State Machine
- [x] **AC-TASK-01**: Status tugas hanya boleh maju satu tahap secara berurutan: `pending` ➔ `in_progress` ➔ `completed`.
- [x] **AC-TASK-02**: Upaya melompati tahap status (contoh: langsung `pending` ke `completed`) ditolak dengan pesan validasi yang jelas.
- [x] **AC-TASK-03**: Tugas dengan status `completed` tidak dapat dimajukan lagi (*final state*).
- [x] **AC-TASK-04**: Tugas yang memiliki tanggal jatuh tempo (`due_date`) lebih lampau dari hari ini dan statusnya belum `completed` harus ditandai sebagai `is_overdue = true` (badge merah).

---

## 3. Otorisasi & Keamanan Pengguna
- [x] **AC-SEC-01**: Pengguna hanya memiliki hak akses penuh terhadap proyek, tugas, dan riwayat aktivitas milik akunnya sendiri.
- [x] **AC-SEC-02**: Percobaan akses ID pengguna lain (IDOR) pada rute Proyek, Tugas, atau Riwayat Audit wajib mengembalikan respon **HTTP 403 Forbidden**.
- [x] **AC-SEC-03**: Halaman penolakan 403 wajib menyajikan konteks error berbahasa Indonesia (*Context-Aware*):
  - Konteks Tugas: *"Akses ke Tugas Dibatasi"* beserta tombol navigasi ke Daftar Tugas.
  - Konteks Proyek: *"Akses ke Proyek Dibatasi"* beserta tombol navigasi ke Daftar Proyek.
  - Konteks Riwayat Audit: *"Akses ke Riwayat Audit Dibatasi"* beserta tombol navigasi ke Riwayat Aktivitas Saya.
- [x] **AC-SEC-04**: Seluruh output text pada template Blade wajib menggunakan sanitasi entitas HTML (`{{ ... }}`) untuk mencegah serangan XSS.

---

## 4. Audit Trail & Riwayat Aktivitas
- [x] **AC-AUDIT-01**: Setiap aksi `PROJECT_CREATED`, `PROJECT_DELETED`, `TASK_CREATED`, dan `TASK_STATUS_UPDATED` otomatis terekam di tabel `activity_logs`.
- [x] **AC-AUDIT-02**: Khusus perubahan status tugas, log audit harus menyimpan nilai status lama (`old`) dan status baru (`new`).
- [x] **AC-AUDIT-03**: Halaman riwayat audit menyediakan filter pencarian berdasarkan jenis aksi dan filter rentang tanggal (*start date* - *end date*).
- [x] **AC-AUDIT-04**: Paginasi data riwayat audit dibatasi 10 data per halaman dengan mempertahankan filter aktif di query string.

---

## 5. REST API & Performa
- [x] **AC-API-01**: Endpoint `PATCH /api/tasks/{task}/status` mengembalikan respon JSON terstruktur (`status`, `message`, `data`).
- [x] **AC-API-02**: Endpoint API dibatasi *rate limiting* maksimal 10 request per menit per client (HTTP 429 jika melebihi batas).
- [x] **AC-PERF-01**: Tidak boleh ada query $N+1$ saat merender Dashboard ringkasan maupun tabel riwayat aktivitas (harus memanfaatkan *eager loading* dan *conditional aggregation*).
