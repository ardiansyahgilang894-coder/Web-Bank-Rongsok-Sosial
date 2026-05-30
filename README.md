# ♻️ Loopit - Bank Rongsok Sosial

Platform digital pengelolaan rongsok dan transparansi dana sosial berbasis web yang membantu organisasi, komunitas, maupun mitra sosial dalam mengelola hasil penjualan rongsok secara transparan, terdokumentasi, dan mudah diakses oleh masyarakat.

## 📖 Tentang Proyek

Loopit hadir sebagai solusi digital untuk membantu proses pencatatan penjualan rongsok, pengelolaan kas sosial, dokumentasi kegiatan, serta publikasi laporan transparansi dana secara real-time.

Seluruh hasil penjualan rongsok dikumpulkan sebagai dana sosial yang digunakan untuk berbagai kegiatan kemanusiaan dan sosial. Melalui platform ini, masyarakat dapat memantau pemasukan, pengeluaran, saldo kas, serta dokumentasi kegiatan yang telah dilaksanakan.

## 🎯 Tujuan

* Meningkatkan transparansi pengelolaan dana sosial.
* Mempermudah pencatatan penjualan rongsok.
* Mendigitalisasi proses administrasi yang sebelumnya dilakukan secara manual.
* Menyediakan laporan dan dokumentasi kegiatan yang dapat diakses publik.
* Mendukung akuntabilitas organisasi sosial.

## ✨ Fitur Utama

### 🔐 Autentikasi

* Login
* Registrasi
* Verifikasi OTP Email
* Lupa Password
* Reset Password menggunakan OTP
* Manajemen Hak Akses

### 👥 Manajemen User

* Admin
* Petugas
* User Publik
* Aktivasi dan Nonaktifkan Akun

### ♻️ Penjualan Rongsok

* Tambah Data Penjualan
* Edit Data Penjualan
* Hapus Data Penjualan
* Upload Bukti Penjualan
* Otomatis Menjadi Pemasukan Kas

### 💰 Pemasukan Kas

* Donasi
* Pemasukan Lainnya
* Integrasi Hasil Penjualan Rongsok
* Upload Bukti Pemasukan

### 💸 Pengeluaran Kas

* Pencatatan Pengeluaran Sosial
* Upload Bukti Pengeluaran
* Riwayat Pengeluaran

### 🖼️ Galeri Kegiatan

* Dokumentasi Pengambilan Rongsok
* Dokumentasi Kegiatan Sosial
* Dokumentasi Penyaluran Dana
* Dokumentasi Bukti Pemasukan dan Pengeluaran

### 📊 Dashboard Analitik

* Saldo Kas Sosial
* Total Pemasukan
* Total Pengeluaran
* Total Berat Rongsok
* Grafik Keuangan
* Statistik Transparansi

### 📑 Laporan

* Rekap Bulanan
* Laporan Penjualan Rongsok
* Laporan Pemasukan Kas
* Laporan Pengeluaran Kas
* Export Excel

### 🌐 Transparansi Publik

* Informasi Saldo Kas
* Riwayat Penjualan Rongsok
* Riwayat Pemasukan
* Riwayat Pengeluaran
* Dokumentasi Kegiatan
* Laporan Bulanan

## 🛠️ Teknologi yang Digunakan

### Backend

* Laravel 11
* Laravel Sanctum
* MySQL
* Mailtrap / SMTP
* Eloquent ORM

### Frontend

* Vue 3
* TypeScript
* Vite
* Tailwind CSS v4
* Pinia
* Vue Router
* ApexCharts
* SweetAlert2
* AOS Animation
* Heroicons

## 📂 Struktur Proyek

```text
rongsok-social/
│
├── backend/
│   ├── app/
│   ├── database/
│   ├── routes/
│   └── resources/
│
├── frontend/
│   ├── src/
│   ├── public/
│   └── components/
│
└── README.md
```

## 🚀 Instalasi

### Clone Repository

```bash
git clone https://github.com/username/rongsok-social.git
cd rongsok-social
```

### Backend

```bash
cd backend

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan storage:link

php artisan serve
```

### Frontend

```bash
cd frontend

npm install

npm run dev
```

## 🔑 Akun Demo

### Admin

```text
Email    : admin@loopit.com
Password : ********
```

### Petugas

```text
Email    : petugas@loopit.com
Password : ********
```

## 📸 Tampilan Sistem

Tambahkan screenshot dashboard, laporan, dan halaman transparansi di sini.

## 📚 Metode Pengembangan

Pengembangan sistem dilakukan menggunakan pendekatan:

* Design Thinking
* User-Centered Design
* Black Box Testing

## 👨‍💻 Pengembang

Muhammad Gilang Ardiansyah

Program Studi Informatika
Universitas Singaperbangsa Karawang (UNSIKA)

## 📄 Lisensi

Proyek ini dibuat untuk keperluan penelitian dan pengembangan sistem informasi transparansi dana sosial berbasis web.
