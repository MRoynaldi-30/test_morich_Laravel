# Test Project PT Morich Indo Fashion

## Cara Instalasi

### 1. Clone repository dari GitHub

```bash
git https://github.com/MRoynaldi-30/test_morich_Laravel.git
cd nama-repository
```

### 2. Install dependency PHP

```bash
composer install
```

### 3. Install dependency frontend

```bash
npm install
```

### 4. Salin file environment

Windows (CMD):

```cmd
copy .env.example .env
```

### 6. Konfigurasi database

Buat database baru terlebih dahulu (contoh: `nama_database`), lalu buka file `.env` dan sesuaikan:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=root
DB_PASSWORD=
```

### 7. Jalankan migrasi database

```bash
php artisan migrate
```

### 9. Build asset frontend

Mode development (hot reload):

```bash
npm run dev
```

Mode production:

```bash
npm run build
```

### 10. Jalankan aplikasi

```bash
php artisan serve
```


