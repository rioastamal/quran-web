# QuranWeb Sync - Cloudflare Worker

Backend serverless berbasis Cloudflare Worker dan Cloudflare KV untuk sinkronisasi preferensi & data `localStorage` pengguna QuranWeb antar-perangkat menggunakan kode unik.

---

## Fitur
- **Tanpa Database Server**: Menggunakan Cloudflare KV (Key-Value) yang serverless, cepat, dan terdistribusi global.
- **Fleksibilitas Kode**:
  - **Auto-generate**: Sistem dapat menghasilkan kode acak 6 digit unik (contoh: `K7H2X9`).
  - **Custom Input**: Pengguna dapat menentukan kode sendiri yang mudah diingat (misal: `RIO123`, `MYQRN1`).
- **Anonim & Aman**: Tanpa akun/password/email. Hanya konfigurasi tampilan (nightmode, font size) dan penanda baca (ayat/juz terakhir dibaca, ayat favorit).
- **Auto Expiration**: Data memiliki masa kedaluwarsa otomatis (TTL 180 hari) yang diperpanjang setiap kali terjadi sinkronisasi baru.

---

## Persyaratan
- Node.js versi 18+ (tersedia `npx`).
- Akun Cloudflare (Free tier sudah sangat cukup).

---

## Cara Deployment

### 1. Login ke Akun Cloudflare (Cukup sekali di awal)
```bash
npx wrangler login
```
*Atau jika menggunakan CI/CD (GitHub Actions), set environment variable `CLOUDFLARE_API_TOKEN`.*

### 2. Jalankan Script Deploy
```bash
cd worker
./deploy.sh
```
Script ini akan secara otomatis:
1. Mengecek/membuat KV Namespace `QURAN_SYNC_KV` di akun Cloudflare Anda.
2. Memperbarui `wrangler.toml` dengan ID KV Namespace yang dibuat.
3. Melakukan build dan deploy worker ke edge Cloudflare.
4. Menampilkan URL publik worker Anda (misal: `https://quranweb-sync.<subdomain>.workers.dev`).

---

## Dokumentasi API Endpoint

### 1. Cek Status Worker
```http
GET /health
```
**Response (200 OK):**
```json
{
  "status": "ok",
  "service": "quranweb-sync",
  "timestamp": "2026-09-18T05:25:00.000Z"
}
```

---

### 2. Buat Kode Sync Baru (Generate Acak ATAU Input Custom)
```http
POST /sync
Content-Type: application/json
```

#### A. Jika pengguna ingin kode acak (Auto-generate):
```json
{
  "lastMarkedAyah": "2:255",
  "lastMarkedJuz": "1:1:1",
  "favorites": [1, 2, 255],
  "fontSize": 100,
  "nightMode": true
}
```
**Response (201 Created):**
```json
{
  "success": true,
  "code": "K7H2X9",
  "message": "Kode sinkronisasi berhasil didaftarkan"
}
```

#### B. Jika pengguna memasukkan kodenya sendiri (Custom Code):
```json
{
  "code": "RIO123",
  "data": {
    "lastMarkedAyah": "2:255",
    "lastMarkedJuz": "1:1:1",
    "favorites": [1, 2, 255],
    "fontSize": 100,
    "nightMode": true
  }
}
```
*Atau properti konfigurasi bisa langsung diletakkan sejajar tanpa pembungkus `data`:*
```json
{
  "code": "RIO123",
  "lastMarkedAyah": "2:255",
  "favorites": [1, 2, 255]
}
```
**Response (201 Created):**
```json
{
  "success": true,
  "code": "RIO123",
  "message": "Kode sinkronisasi berhasil didaftarkan"
}
```
*Jika kode yang diminta sudah terpakai oleh pengguna lain (409 Conflict):*
```json
{
  "error": "Kode \"RIO123\" sudah digunakan. Silakan gunakan kode lain atau hubungkan dengan kode yang sudah ada.",
  "codeTaken": true
}
```

---

### 3. Ambil Konfigurasi Berdasarkan Kode
```http
GET /sync/RIO123
```
**Response (200 OK):**
```json
{
  "success": true,
  "code": "RIO123",
  "data": {
    "lastMarkedAyah": "2:255",
    "lastMarkedJuz": "1:1:1",
    "favorites": [1, 2, 255],
    "fontSize": 100,
    "nightMode": true
  },
  "updatedAt": "2026-09-18T05:25:00.000Z"
}
```

---

### 4. Perbarui Konfigurasi untuk Kode Tertentu
```http
PUT /sync/RIO123
Content-Type: application/json

{
  "lastMarkedAyah": "3:18",
  "lastMarkedJuz": "3:3:1",
  "favorites": [1, 2, 255, 300],
  "fontSize": 110,
  "nightMode": true
}
```
**Response (200 OK):**
```json
{
  "success": true,
  "code": "RIO123",
  "message": "Data berhasil diperbarui"
}
```
