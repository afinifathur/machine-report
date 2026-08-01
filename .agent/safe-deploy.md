---
description: Safe deployment workflow - push code to production server without breaking database
---

# 🔒 Aturan Emas: Safe Production Deployment

// turbo-all

## Prinsip Utama
1. **JANGAN PERNAH** jalankan `migrate:fresh` atau `migrate:rollback` di production
2. **SELALU** backup database sebelum deploy jika ada migration baru
3. **HANYA** jalankan `migrate` (tanpa flag) untuk migration baru di production
4. Perubahan PHP/Blade biasa **TIDAK PERLU** migration

---

## Langkah Deployment

### STEP 1: Commit & Push dari Local (Laragon)

```powershell
cd c:\laragon\www\Warehouse-System-Sparepart

# Cek perubahan
git status

# Add semua perubahan
git add -A

# Commit dengan pesan deskriptif
git commit -m "feat: deskripsi singkat perubahan"

# Push ke production remote (biasanya prod)
git push prod main
```

### STEP 2: Backup Database di Server (WAJIB jika ada migration baru)

SSH ke server, lalu jalankan:

```bash
cd /srv/docker/apps/Warehouse-System-SP

# Backup database sebelum pull
# Password database: wh_sys_k8q2pL9zX_prod
sudo docker compose exec warehouse-db mysqldump -u warehouse_system_user -p[PASSWORD] warehouse_system > /home/peroniks/backups/warehouse_backup_$(date +%Y%m%d_%H%M%S).sql
```
### STEP 3: Pull & Update di Server

```bash
cd /srv/docker/apps/Machine-Report

# Pastikan repository bersih sebelum pull
sudo git status

# Pull kode terbaru
sudo git pull origin main

# (Opsional) Pastikan file migration terbaru sudah ada di host
ls database/migrations | tail

# Rebuild image dari source terbaru
# WAJIB jika ada perubahan source code (PHP, Blade, Migration, Composer, Dockerfile)
sudo docker compose build --no-cache

# Jalankan container terbaru
sudo docker compose up -d

# Pastikan migration terbaru benar-benar sudah masuk ke dalam container
sudo docker compose exec app ls /var/www/html/database/migrations | tail

# Pastikan storage symlink tersedia
sudo docker compose exec app php artisan storage:link

# Bersihkan cache Laravel
sudo docker compose exec app php artisan optimize:clear

# Cek status migration
sudo docker compose exec app php artisan migrate:status

# Jalankan migration HANYA jika ada migration baru
# (JANGAN PERNAH menggunakan migrate:fresh di production)
sudo docker compose exec app php artisan migrate --force

# Rebuild cache production
sudo docker compose exec app php artisan config:cache
sudo docker compose exec app php artisan route:cache
sudo docker compose exec app php artisan view:cache
```

---

### STEP 4: Verifikasi

#### A. Verifikasi Docker

```bash
sudo docker compose ps
```

Pastikan seluruh container berstatus **Up**.

---

#### B. Verifikasi Laravel

```bash
sudo docker compose exec app php artisan about
```

```bash
sudo docker compose exec app php artisan migrate:status
```

Pastikan:

- Tidak ada migration yang masih **Pending**
- Tidak ada error saat startup

---

#### C. Verifikasi Log

```bash
sudo docker compose exec app tail -n 50 storage/logs/laravel.log
```

Pastikan tidak ada error baru setelah proses deploy.

---

#### D. Verifikasi Aplikasi

Buka aplikasi di browser.

Periksa:

- ✅ Login berhasil
- ✅ Dashboard tampil normal
- ✅ Fitur yang baru di-deploy berjalan
- ✅ Data lama masih ada
- ✅ Tidak ada error 500
- ✅ Tidak ada warning pada halaman

---

## ⚠️ PERINTAH BERBAHAYA - JANGAN DIGUNAKAN DI PRODUCTION

```bash
# ❌ MENGHAPUS SELURUH DATABASE
php artisan migrate:fresh

# ❌ MENGHAPUS DATABASE + SEED
php artisan migrate:fresh --seed

# ❌ Rollback dapat menghapus struktur/data production
php artisan migrate:rollback

# ❌ Menghapus seluruh database
php artisan db:wipe

# ❌ Jangan melakukan reset database production
php artisan migrate:reset
```

---

## Checklist Sebelum Deploy

- [ ] Sudah test di Local (Laragon)?
- [ ] Repository Local sudah bersih (`git status`)?
- [ ] Ada migration baru?
- [ ] Backup database sudah tersedia?
- [ ] Commit message sudah jelas?
- [ ] Push ke remote `prod`?
- [ ] Sudah memberi tahu user jika deployment berpotensi mengganggu akses?

---

## Recovery Jika Terjadi Masalah

### Restore Database

```bash
sudo docker compose exec -T warehouse-db \
mysql -u warehouse_system_user -p[PASSWORD] warehouse_system \
< /home/peroniks/backups/warehouse_backup_YYYYMMDD_HHMMSS.sql
```

### Rollback Source Code

```bash
sudo git reset --hard HEAD~1
```

Kemudian lakukan deploy ulang:

```bash
sudo docker compose build --no-cache
sudo docker compose up -d
sudo docker compose exec app php artisan optimize:clear
sudo docker compose exec app php artisan config:cache
sudo docker compose exec app php artisan route:cache
```

---

## 📌 Catatan Penting (Hasil Incident 2026-07-28)

Project ini menggunakan Dockerfile dengan mekanisme:

```dockerfile
COPY . .
```

Artinya:

- `git pull` **hanya memperbarui source code di host server**.
- Container yang sedang berjalan **tidak otomatis menggunakan source code terbaru**.
- Jika terdapat perubahan PHP, Blade, Migration, Composer, atau Dockerfile, maka **WAJIB** menjalankan:

```bash
docker compose build --no-cache
docker compose up -d
```

Jangan menganggap `git pull` saja sudah cukup untuk meng-update aplikasi yang sedang berjalan.