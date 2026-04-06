# 🖼️ Display & Send NFT via OpenSea

Aplikasi web berbasis **Laravel + Livewire + React** untuk menampilkan koleksi NFT ERC1155 dari OpenSea dan mengirim NFT langsung dari browser menggunakan wallet Web3.

> 🔗 Koleksi NFT: [QuietMinka di OpenSea](https://opensea.io/quietminka)

---

## 📋 Daftar Isi

- [Tentang Proyek](#-tentang-proyek)
- [Preview](#-preview)
- [Fitur](#-fitur)
- [Tech Stack](#-tech-stack)
- [Cara Kerja](#-cara-kerja)
- [Prasyarat](#-prasyarat)
- [Instalasi](#-instalasi)
- [Konfigurasi Environment](#-konfigurasi-environment)
- [Menjalankan Aplikasi](#-menjalankan-aplikasi)
- [Perintah Artisan](#-perintah-artisan)
- [Scheduler (Worker)](#-scheduler-worker)
- [Struktur Database](#-struktur-database)
- [Catatan Teknis](#-catatan-teknis)
- [Lisensi](#-lisensi)

---

## 🧩 Tentang Proyek

Proyek ini adalah contoh implementasi bagaimana menampilkan NFT ERC1155 yang sudah di-mint di OpenSea ke dalam sebuah website, sekaligus menyediakan fitur untuk **mengirim NFT** langsung dari web menggunakan koneksi wallet (MetaMask / WalletConnect).

Koleksi NFT yang digunakan sebagai contoh adalah **[QuietMinka](https://opensea.io/quietminka)** — koleksi NFT bertema suasana tenang Jepang yang di-mint di jaringan Polygon.

Selain itu, proyek ini juga memiliki **background worker** yang secara otomatis memantau kepemilikan NFT di blockchain Polygon dan menyimpan data owner ke database, sehingga website selalu menampilkan data owner yang up-to-date.

---

## 🖥️ Preview

Tampilan halaman utama yang menampilkan daftar NFT lengkap dengan informasi traits, balance wallet yang terkoneksi, dan form untuk mengirim NFT:

![Preview NFT Display & Send](https://github.com/yogabagaskurniawan/display_and_send_nft_via_opensea/blob/main/public/list-nft.png)

Setiap kartu NFT menampilkan:
- Gambar NFT
- Nama dan deskripsi
- Token ID dan standar token
- Traits (Mood, Time, Season, Weather, dsb)
- Tombol "Lihat di OpenSea"
- Balance NFT yang dimiliki wallet yang sedang terkoneksi
- Form send NFT (input alamat tujuan dan jumlah)

---

## ✨ Fitur

- **Tampilkan NFT** — Menampilkan semua NFT dalam sebuah smart contract ERC1155 dalam bentuk kartu grid lengkap dengan gambar, nama, deskripsi, dan traits
- **Send NFT** — Mengirim NFT ke alamat wallet tujuan langsung dari browser menggunakan koneksi wallet Web3
- **Connect Wallet** — Integrasi wallet menggunakan Reown AppKit (mendukung MetaMask dan WalletConnect)
- **NFT Owner Tracker** — Menampilkan daftar pemegang NFT beserta jumlah token yang dimiliki
- **Transfer History** — Riwayat pengiriman NFT tersimpan otomatis ke database setiap kali transaksi berhasil
- **Background Worker** — Sinkronisasi otomatis data owner NFT dari blockchain ke database menggunakan Laravel Scheduler
- **Smart Blockchain Scanning** — Membaca event `TransferSingle` ERC1155 dari blockchain Polygon menggunakan RPC untuk mendeteksi perpindahan kepemilikan
- **Resume on Failure** — Worker menyimpan progress scan ke cache sehingga bisa dilanjutkan jika terjadi timeout

---

## 🛠️ Tech Stack

### Backend
| Teknologi | Versi | Kegunaan |
|---|---|---|
| PHP | ^8.2 | Bahasa pemrograman utama |
| Laravel | ^12.x | Framework backend |
| Laravel Livewire | ^4.x | Reactive UI components |
| SQLite / MySQL | — | Database |

### Frontend
| Teknologi | Versi | Kegunaan |
|---|---|---|
| React | ^19.x | UI komponen wallet |
| TypeScript | ^3.x | Type safety |
| Tailwind CSS | ^4.x | Styling |
| Vite | ^7.x | Build tool |

### Web3 / Blockchain
| Teknologi | Versi | Kegunaan |
|---|---|---|
| Reown AppKit | ^1.8.x | Wallet connection modal |
| Reown AppKit Adapter Ethers | ^1.8.x | Adapter untuk ethers.js |
| Ethers.js | ^6.x | Interaksi dengan smart contract |
| Polygon (MATIC) | Mainnet | Blockchain yang digunakan |

### External Services
| Service | Kegunaan |
|---|---|
| OpenSea API v2 | Mengambil metadata dan daftar NFT |
| Infura / Ankr RPC | Membaca data blockchain (eth_getLogs, eth_call) |

---

## ⚙️ Cara Kerja

### Menampilkan NFT
```
OpenSea API v2
    ↓ GET /chain/matic/contract/{address}/nfts
Laravel Livewire Component
    ↓ mengambil data dari OpenSea API
Blade View
    ↓ menampilkan grid NFT
```

### Mengirim NFT
```
User klik "Send NFT"
    ↓
React Component (Reown AppKit)
    ↓ connect wallet (MetaMask / WalletConnect)
ethers.js → safeTransferFrom(from, to, tokenId, amount, data)
    ↓
Polygon Blockchain
    ↓ transaksi dikonfirmasi
Simpan ke tabel nft_transfers (tx_hash, wallet_from, wallet_to, dsb)
```

### Sinkronisasi Owner (Worker)
```
Laravel Scheduler (tiap 5 menit)
    ↓
SyncNftOwners Command
    ↓ eth_getLogs → scan TransferSingle event
    ↓ eth_call → balanceOf(address, tokenId)
Database (tabel nft_owners)
    ↓
Livewire → tampilkan daftar owner
```

---

## 📦 Prasyarat

Pastikan sistem kamu sudah terinstall:

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x
- **NPM** >= 9.x
- **SQLite** atau **MySQL**

Akun yang dibutuhkan:
- [OpenSea API Key](https://docs.opensea.io/reference/api-overview) — untuk mengambil data NFT
- [Reown Project ID](https://cloud.reown.com) — untuk wallet connection (gratis)
- RPC URL Polygon (pilih salah satu):
  - [Infura](https://infura.io) — `https://polygon-mainnet.infura.io/v3/YOUR_KEY`
  - [Alchemy](https://alchemy.com) — `https://polygon-mainnet.g.alchemy.com/v2/YOUR_KEY`
  - [Ankr](https://ankr.com) — `https://rpc.ankr.com/polygon` (tanpa API key)

---

## 🚀 Instalasi

### 1. Clone repositori

```bash
git clone https://github.com/yogabagaskurniawan/display_and_send_nft_via_opensea.git
cd display_and_send_nft_via_opensea
```

### 2. Install dependensi PHP

```bash
composer install
```

### 3. Install dependensi Node.js

```bash
npm install
```

### 4. Salin file environment

```bash
cp .env.example .env
```

### 5. Generate application key

```bash
php artisan key:generate
```

### 6. Jalankan migrasi database

```bash
php artisan migrate
```

---

## 🔧 Konfigurasi Environment

Buka file `.env` dan isi variabel berikut:

```env
# ===== Aplikasi =====
APP_NAME="NFT Display"
APP_URL=http://localhost:8000

# ===== Database =====
DB_CONNECTION=sqlite
# Atau jika pakai MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=nft_app
# DB_USERNAME=root
# DB_PASSWORD=

# ===== Cache & Queue =====
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
CACHE_STORE=redis

# ===== Blockchain & NFT =====
TATUM_RPC_URL="https://polygon-mainnet.infura.io/v3/YOUR_KEY"        # RPC URL Polygon
OPENSEA_API_KEY=your_opensea_api_key_here                            # API Key OpenSea
COLLECTION_OPENSEA_SMARTCONTRACT=0x1cba69...                         # Alamat smart contract NFT kamu
CONTRACT_DEPLOY_BLOCK=85034418                                       # Block saat contract di-deploy (cek di Polygonscan)

# ===== Frontend Web3 =====
VITE_REOWN_PROJECT_ID=your_reown_project_id_here     # Project ID dari cloud.reown.com
```

### Cara mencari `CONTRACT_DEPLOY_BLOCK`

1. Buka `https://polygonscan.com/address/{ALAMAT_CONTRACT_KAMU}`
2. Cari bagian **"Contract Creator"** → klik txn hash
3. Catat angka di field **"Block"**

---

## ▶️ Menjalankan Aplikasi

### Development

Buka **3 terminal** secara bersamaan:

```bash
# Terminal 1 — Laravel development server
php artisan serve

# Terminal 2 — Vite (build frontend assets)
npm run build

# Terminal 3 — Queue worker (untuk jobs)
php artisan queue:work
```

Buka browser di `http://localhost:8000`

### Production Build

```bash
npm run build
php artisan optimize
```

---

## 🧑‍💻 Perintah Artisan

### Sinkronisasi NFT Owner

```bash
# Sync normal (hanya block baru sejak terakhir sync)
php artisan app:sync-nft-owners

# Full rescan dari block deploy (pertama kali / reset total)
php artisan app:sync-nft-owners --fresh
```

> ⚠️ Gunakan `--fresh` hanya pertama kali atau ketika ingin reset data dari awal. Setelah itu biarkan scheduler yang menjalankan secara otomatis.

---

## ⏰ Scheduler (Worker)

Worker berjalan otomatis setiap 5 menit untuk memperbarui data owner NFT.

### Development

```bash
php artisan schedule:work
```

### Production (Crontab)

Tambahkan ke crontab server kamu:

```bash
crontab -e
```

```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Konfigurasi Scheduler

Di file `routes/console.php`:

```php
Schedule::command('app:sync-nft-owners')
    ->everyFiveMinutes()
    ->withoutOverlapping()  // Tidak jalan dobel jika sebelumnya belum selesai
    ->runInBackground();
```

---

## 🗄️ Struktur Database

### Tabel `nfts`
Menyimpan metadata NFT yang diambil dari OpenSea.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | Primary key |
| contract_address | string | Alamat smart contract |
| token_id | string | ID token NFT |
| name | string | Nama NFT |
| description | text | Deskripsi NFT |
| image | string | URL gambar NFT |
| token_standard | string | Standar token (erc1155) |
| supply | integer | Total supply token |
| created_at / updated_at | timestamp | — |

### Tabel `nft_owners`
Menyimpan data pemegang NFT yang disinkronisasi dari blockchain oleh worker. Data diperbarui otomatis setiap 5 menit.

| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint | Primary key |
| nft_id | bigint | Foreign key ke tabel `nfts` |
| wallet_address | string | Alamat wallet pemegang |
| balance | integer | Jumlah token yang dimiliki |
| created_at / updated_at | timestamp | — |

### Tabel `nft_transfers`
Menyimpan riwayat pengiriman NFT. Record dibuat otomatis setiap kali user berhasil mengirim NFT melalui form Send NFT di website.

| Kolom | Tipe | Default | Keterangan |
|---|---|---|---|
| id | bigint | — | Primary key |
| user_id | bigint (nullable) | null | Foreign key ke tabel `users`, nullable jika tidak login |
| wallet_from | string (nullable) | null | Alamat wallet pengirim |
| wallet_to | string (nullable) | null | Alamat wallet penerima |
| contract_address | string (nullable) | null | Alamat smart contract NFT |
| token_id | string (nullable) | null | ID token yang dikirim |
| amount | string | `'1'` | Jumlah token yang dikirim |
| tx_hash | string (nullable) | null | Transaction hash di blockchain (bisa dicek di Polygonscan) |
| chain | string (nullable) | null | Nama chain (contoh: `polygon`) |
| status | string | `'success'` | Status transaksi (`success` / `failed`) |
| created_at / updated_at | timestamp | — | — |

---

## 📝 Catatan Teknis

### Mengapa beli di OpenSea tidak muncul di tab "Transactions" contract?

Ketika NFT dibeli melalui OpenSea, transaksi masuk ke **Seaport Contract** milik OpenSea, bukan langsung ke contract NFT kamu. Namun event `TransferSingle` tetap ter-emit di contract NFT, sehingga tetap bisa dibaca melalui `eth_getLogs`. Inilah yang digunakan oleh worker untuk mendeteksi perpindahan kepemilikan.

### Kenapa pakai `substr($hex, 2)` bukan `ltrim($hex, '0x')`?

Fungsi `ltrim($string, '0x')` akan menghapus **semua karakter `0` dan `x`** dari kiri string, bukan hanya prefix `0x`. Ini menyebabkan hasil parsing hex menjadi salah (overflow ke scientific notation). Gunakan `substr($hex, 2)` untuk menghapus tepat 2 karakter pertama.

### Batasan RPC

Beberapa RPC provider (seperti Infura) membatasi range block untuk `eth_getLogs`. Worker sudah menangani ini dengan:
- Chunk size 250 block per request
- Otomatis mengecilkan chunk jika terkena rate limit
- Menyimpan progress ke cache agar bisa dilanjutkan jika timeout

### Rekomendasi RPC untuk Polygon

| Provider | URL | Keterangan |
|---|---|---|
| Infura | `https://polygon-mainnet.infura.io/v3/KEY` | Butuh API key |
| Ankr | `https://rpc.ankr.com/polygon` | Gratis, tanpa API key |
| Alchemy | `https://polygon-mainnet.g.alchemy.com/v2/KEY` | Butuh API key, paling reliable |

---

## 📄 Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).

---

## 👤 Author

**Yoga Bagas Kurniawan**

- GitHub: [@yogabagaskurniawan](https://github.com/yogabagaskurniawan)
- OpenSea: [QuietMinka](https://opensea.io/quietminka)

---

> Dibuat dengan ❤️ menggunakan Laravel, Livewire, React, dan Reown AppKit di atas blockchain Polygon.
