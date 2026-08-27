# HRIS API — Panduan Development & Deployment

Development memakai **Docker**. Production **tidak memakai Docker** — aplikasi di-deploy langsung
ke hosting dan connect ke server database terpisah. Karena itu setiap nilai `.env` yang khusus
Docker sudah diberi penanda `=== LOKAL (Docker) — WAJIB DIUBAH SAAT DEPLOY KE HOSTING ===`.

---

## 1. Development (Docker)

### Menjalankan

```bash
cp .env.example .env          # hanya sekali, saat pertama clone
docker compose up -d
docker compose exec web php artisan key:generate
docker compose exec web php artisan migrate
docker compose exec web php artisan db:seed     # role, permission, data contoh
```

### Port

| Service | URL / Alamat | Keterangan |
|---|---|---|
| Aplikasi (FrankenPHP) | http://localhost:8000 | `APP_PORT` |
| phpmyadmin | http://localhost:8081 | `FORWARD_PHPMYADMIN_PORT` |
| MySQL (dari host) | `127.0.0.1:3307` | `FORWARD_DB_PORT` — untuk DBeaver/TablePlus |
| Redis | `127.0.0.1:6379` | `FORWARD_REDIS_PORT` |
| Meilisearch | http://localhost:7700 | `FORWARD_MEILISEARCH_PORT` |

Kredensial DB: user `admin`, password `secret123`, database `hris_api`.

### Menjalankan artisan

Selalu **dari dalam container**, bukan dari PowerShell Windows:

```bash
docker compose exec web php artisan <perintah>
```

`php artisan` langsung di Windows **tidak akan bisa connect ke DB**, karena `.env` memakai
`DB_HOST=mysql` — nama service yang hanya dikenal di dalam jaringan Docker. Kalau memang perlu
menjalankan artisan dari host, ubah sementara `DB_HOST=127.0.0.1` dan `DB_PORT=3307`, lalu
kembalikan sebelum menjalankan container lagi.

### Kalau port di host bentrok

Gejalanya container berstatus `Created` dan tidak pernah `Up` (Docker gagal bind port).
Ubah variabel `FORWARD_*` di `.env` ke port lain, lalu `docker compose up -d`.

Contoh yang sudah terjadi di project ini: port 8080 dipakai service Windows sehingga phpmyadmin
tidak mau start, dan port 3306 dipakai container project lain — keduanya diselesaikan lewat
`FORWARD_PHPMYADMIN_PORT=8081` dan `FORWARD_DB_PORT=3307`.

### Gotcha: `vendor` dan `storage` adalah named volume

Di `docker-compose.yml`, `vendor/` dan `storage/` dipasang sebagai named volume
(`laravel-vendor`, `laravel-storage`) yang menutupi isi image. Artinya `composer install`
saat build **tidak terlihat** setelah volume pertama kali terisi.

Kalau dependency di `composer.json` berubah:

```bash
docker compose exec web composer install     # cara cepat
# atau bersihkan total:
docker compose down -v && docker compose up -d --build
```

> `down -v` juga menghapus volume MySQL — data akan hilang dan perlu `migrate` + `db:seed` ulang.

---

## 2. Production (hosting, tanpa Docker)

### Checklist `.env`

Yang **wajib** diubah dari nilai development:

| Variabel | Development (Docker) | Production |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | **`false`** |
| `APP_KEY` | hasil `key:generate` | generate baru di server, jangan menyalin punya lokal |
| `APP_URL` | `http://localhost` | domain asli, mis. `https://api.domain.com` |
| `DB_HOST` | `mysql` | host / IP server MySQL |
| `DB_PORT` | `3306` | port server MySQL |
| `DB_DATABASE` / `DB_USERNAME` / `DB_PASSWORD` | kredensial dev | kredensial server |
| `MAIL_*` | Mailtrap sandbox | SMTP asli |
| `MEILISEARCH_HOST` | `http://meilisearch:7700` | URL server Meilisearch, atau ganti `SCOUT_DRIVER` |

Yang bisa **dihapus / diabaikan** (khusus Docker, tidak dibaca Laravel):
`FORWARD_DB_PORT`, `FORWARD_REDIS_PORT`, `FORWARD_PHPMYADMIN_PORT`,
`FORWARD_MEILISEARCH_PORT`, `DB_ROOT_PASSWORD`, `APP_PORT`, `MYSQL_VERSION`.

### Soal Redis

`CACHE_STORE=database` dipilih justru supaya deploy tanpa Docker tidak bermasalah — driver ini
hanya butuh tabel `cache` (sudah termasuk migrasi), tidak butuh service Redis maupun ekstensi PHP
apa pun. Blok `REDIS_*` boleh dibiarkan atau dihapus.

Kalau hosting Anda **tidak** punya ekstensi `phpredis`, itu tetap aman: `config/database.php`
sudah di-guard dengan `extension_loaded('redis')` sebelum menyentuh konstanta `\Redis::`.
Tanpa guard tersebut, `php artisan` apa pun akan langsung fatal `Class "Redis" not found`
di server tanpa ekstensi redis.

### Langkah deploy

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate          # hanya sekali, saat setup awal
php artisan migrate --force       # --force wajib di APP_ENV=production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Setelah `config:cache`, perubahan `.env` **tidak akan terbaca** sampai dijalankan
`php artisan config:clear` (atau `config:cache` lagi).

### Queue worker

Di Docker, queue dijalankan supervisor (`.docker/etc/supervisor.d/supervisord.conf`,
program `queue-runner`). Di hosting non-Docker, siapkan padanannya — supervisor, systemd, atau
cron — untuk menjalankan:

```bash
php artisan queue:work --tries=3 --timeout=90 --sleep=3
```

`QUEUE_CONNECTION=database`, jadi tidak perlu Redis; hanya butuh tabel `jobs` dari migrasi.

### Telescope

`laravel/telescope` terpasang dan rutenya aktif di `/telescope`. Pastikan aksesnya dibatasi di
production (lewat `TelescopeServiceProvider::gate()`) atau nonaktifkan dengan
`TELESCOPE_ENABLED=false`, karena Telescope merekam request, query, dan payload job.
