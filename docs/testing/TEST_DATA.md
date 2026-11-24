# Test Data Strategy & Fixtures - PTMS

Dokumen ini menguraikan dataset standar, factory, dan aturan fixture yang digunakan dalam proses pengujian otomatis dan manual PTMS.

## 1. Aturan Pengelolaan Data Uji
- **Isolasi Penuh**: Setiap test case menggunakan trait `RefreshDatabase` yang menjalankan migrasi baru pada SQLite in-memory, menjamin ketiadaan data residual antar-test.
- **Factory-Driven**: Pembuatan entitas pengguna memanfaatkan `User::factory()` dengan kredensial hash terstandarisasi.
- **Data Konkurensi**: Dibuat secara eksplisit dalam blok transaksi untuk mensimulasikan kondisi balapan nyata.

---

## 2. Definisi Model Factory & Seed Data

### A. Pengguna (User)
```php
// User Pemilik (Authorized)
$user = User::factory()->create([
    'name' => 'Owner Account',
    'email' => 'owner@example.com',
    'password' => bcrypt('password123'),
]);

// User Lain (Attacker / Unauthorized)
$attacker = User::factory()->create([
    'name' => 'Attacker Account',
    'email' => 'attacker@example.com',
    'password' => bcrypt('password123'),
]);
```

### B. Proyek (Project)
```php
$project = Project::create([
    'user_id' => $user->id,
    'name' => 'Implementasi Sistem ERP',
    'description' => 'Proyek integrasi modul inventori dan keuangan',
]);
```

### C. Tugas (Task) dengan Ragam Status
```php
// 1. Tugas Pending (Normal)
Task::create([
    'project_id' => $project->id,
    'title' => 'Analisis Kebutuhan Sistem',
    'status' => 'pending',
    'due_date' => Carbon::tomorrow(),
]);

// 2. Tugas In Progress
Task::create([
    'project_id' => $project->id,
    'title' => 'Pengembangan Backend API',
    'status' => 'in_progress',
    'due_date' => Carbon::now()->addDays(3),
]);

// 3. Tugas Completed
Task::create([
    'project_id' => $project->id,
    'title' => 'Setup Server & Docker',
    'status' => 'completed',
    'due_date' => Carbon::yesterday(),
]);

// 4. Tugas Overdue (Jatuh tempo terlewat & belum selesai)
Task::create([
    'project_id' => $project->id,
    'title' => 'Review Arsitektur Database',
    'status' => 'pending', // atau in_progress
    'due_date' => Carbon::yesterday(), // is_overdue == true
]);
```

### D. Riwayat Aktivitas (ActivityLog)
```php
ActivityLog::create([
    'user_id' => $user->id,
    'action' => 'TASK_STATUS_UPDATED',
    'subject_type' => Task::class,
    'subject_id' => $task->id,
    'payload' => [
        'field' => 'status',
        'old' => 'pending',
        'new' => 'in_progress',
    ],
    'created_at' => Carbon::now(),
]);
```

---

## 3. Payload Pengujian Keamanan (Security Payloads)

### SQL Injection Payloads:
- `' OR '1'='1`
- `admin' --`
- `1'; DROP TABLE tasks; --`
- `UNION SELECT null, null, null--`

### XSS (Cross-Site Scripting) Payloads:
- `<script>alert("XSS")</script>`
- `<img src=x onerror=alert(1)>`
- `<svg/onload=alert("task")>`
