# Docker Compose - Panduan Penggunaan PTMS

Konfigurasi Docker Compose ini memungkinkan seluruh stack aplikasi (Laravel 13 + PHP 8.3-FPM, Web Server Nginx, dan Database MySQL 8.0) dapat langsung dijalankan secara terisolasi tanpa instalasi lokal.

---

## 🚀 Cara Menjalankan

### 1. Salin Environment File
Gunakan file konfigurasi environment khusus Docker:
```bash
cp .env.docker.example .env
```
*(Atau sesuaikan nilai `DB_HOST=db`, `DB_USERNAME=ptms_user`, `DB_PASSWORD=ptms_password`, dan `DB_DATABASE=ptms` pada file `.env` yang ada).*

### 2. Jalankan Docker Compose
Jalankan perintah berikut pada direktori proyek (`app/`):
```bash
docker compose up -d --build
```

Container yang akan berjalan:
- **`ptms_web` (Nginx)**: Port `8000` (akses via browser: [http://localhost:8000](http://localhost:8000))
- **`ptms_app` (PHP 8.3 FPM)**: Tempat runtime Laravel, Composer, dan Artisan.
- **`ptms_db` (MySQL 8.0)**: Port host `3307` (mencegah bentrok dengan MySQL lokal XAMPP di 3306).

> **Otomatisasi Entrypoint:**
> Script `docker/entrypoint.sh` secara otomatis:
> 1. Menunggu database MySQL hingga siap menerima koneksi (healthcheck).
> 2. Menjalankan `composer install` jika folder `vendor` belum terpasang.
> 3. Menjalankan `php artisan key:generate` jika `APP_KEY` belum diset.
> 4. Menjalankan `php artisan migrate --force` untuk migrasi seluruh tabel secara otomatis.

---

## 🛠️ Perintah Berguna

- **Melihat status container:**
  ```bash
  docker compose ps
  ```

- **Melihat log container:**
  ```bash
  docker compose logs -f
  ```

- **Menjalankan artisan command:**
  ```bash
  docker compose exec app php artisan migrate
  docker compose exec app php artisan test
  ```

- **Menghentikan container:**
  ```bash
  docker compose down
  ```

- **Menghentikan dan menghapus data database (volume):**
  ```bash
  docker compose down -v
  ```
