# Performance Checklist - PTMS

Daftar periksa optimasi query dan performa basis data pada PTMS.

---

## 1. Database Query Efficiency ($N+1$ Prevention)
- [x] **Eager Loading pada Daftar Tugas**:
  - `Task::whereHas('project', ...)->with(['project' => fn($q) => $q->withTrashed()])` untuk mencegah query relasi individual per baris tugas.
- [x] **Eager Loading pada Riwayat Audit**:
  - `ActivityLog::where('user_id', ...)->with(['user', 'subject'])` untuk memuat data subjek polymorphic (Project / Task) secara batch.
- [x] **Agregasi Metrik Dashboard Berbasis Single Query**:
  - Menggunakan conditional count / subquery alih-alih me-looping `tasks()->count()` per proyek.
  - Ambang batas aman pada test automated: $<= 12$ query untuk 10 proyek dan puluhan tugas.
- [x] **Halaman Detail Proyek Berskala Besar**:
  - Pemuatan 25+ tugas dan riwayat aktivitas tetap konstan di bawah ambang batas $<= 10$ query total.

---

## 2. Pengindeksan Database (Indexing Strategy)
- [x] **Foreign Keys Index**:
  - Kolom `project_id` pada tabel `tasks`.
  - Kolom `user_id` pada tabel `projects`.
- [x] **Polymorphic Index**:
  - Kolom gabungan `subject_type` dan `subject_id` pada tabel `activity_logs`.
- [x] **Filtering Columns Index**:
  - Index pada kolom `status` dan `due_date` di tabel `tasks` untuk akselerasi pencarian dan deteksi overdue.
  - Index pada kolom `action` dan `created_at` di tabel `activity_logs`.

---

## 3. Mekanisme Concurrency & Locking
- [x] **Pessimistic Locking**:
  - Penggunaan `Task::where('id', $id)->lockForUpdate()` dalam transaksi pembaruan status untuk mencegah *lost updates* pada concurrent requests.
- [x] **In-Memory Cache & Session**:
  - Dukungan cache array driver pada automated test suite guna memangkas latensi eksekusi test (< 3 detik untuk puluhan test).

---

## 4. Evaluasi Hasil Automated Performance Test
```bash
php artisan test tests/Performance
```
- `DashboardQueryCountTest`: **PASS** (query teragregasi efisien).
- `ProjectDetailQueryCountTest`: **PASS** (query konstan $O(1)$).
- `ActivityLogQueryCountTest`: **PASS** (polymorphic eager loading aktif).
