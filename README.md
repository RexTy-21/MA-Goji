# SIMPLE - MA Ghoyatul Jihad Karawang

<div align="center">
  <img src="assets/img/logo.png" alt="Logo MA Ghoyatul Jihad" width="120" style="vertical-align: middle;">
  <h3><b>Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif</b></h3>
  <p>Aplikasi Tata Kelola & Sistem Informasi Akademik Madrasah Aliyah Ghoyatul Jihad Karawang berbasis PHP Native & MySQL.</p>

  <p>
    <img src="https://img.shields.io/badge/PHP-7.4%20|%208.0%20|%208.1%20|%208.2-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP Version">
    <img src="https://img.shields.io/badge/Database-MySQL%20|%20MariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="Database">
    <img src="https://img.shields.io/badge/Frontend-Bootstrap%203%20|%20Gentelella-563D7C?style=flat-square&logo=bootstrap&logoColor=white" alt="Bootstrap">
    <img src="https://img.shields.io/badge/Status-Production%20Ready-16a34a?style=flat-square" alt="Status">
  </p>
</div>

---

## 📌 Tentang Aplikasi

**SIMPLE (Sistem Informasi Madrasah Pintar, Lengkap, dan Efektif)** adalah platform manajemen madrasah terintegrasi yang dirancang khusus untuk mempermudah operasional harian di **MA Ghoyatul Jihad Karawang**. 

Sistem ini mencakup seluruh siklus manajemen madrasah: mulai dari data induk siswa dan guru, presensi digital berbasis scan QR Code dengan kamera real-time, penilaian dan e-rapor, sistem poin pelanggaran kedisiplinan kesiswaan, perizinan online, inventaris sarana dan prasarana, hingga manajemen arsip berkas digital.

---

## 🚀 Fitur Utama

### 1. 📊 Dashboard & Monitoring Madrasah
* **Statistik Cepat**: Ringkasan jumlah siswa aktif, guru & staf, rombongan belajar (kelas), dan rekapitulasi kehadiran hari ini (Hadir, Terlambat, Izin/Sakit/Alpa).
* **Jadwal KBM Hari Ini**: Menampilkan mata pelajaran yang sedang dan akan berlangsung pada hari berjalan.
* **Pengumuman Terkini**: Papan agenda dan maklumat internal madrasah dengan indikator status penting.

### 2. 🎓 Manajemen Akademik Terpadu
* **Data Siswa**: Pengelolaan data NIS, NISN, biodata pribadi, data wali, nomor kontak darurat, serta filter berdasarkan kelas dan jenis kelamin.
* **Data Guru & Tenaga Kependidikan**: Manajemen NIP, nama lengkap, status kepegawaian (PNS, PPPK, GTY, GTT/Honorer), jabatan, dan mata pelajaran yang diampu.
* **Rombongan Belajar (Kelas)**: Manajemen tingkatan kelas (X, XI, XII) dan penjurusan (IPA, IPS) disertai penetapan Wali Kelas.
* **Mata Pelajaran**: Pengelompokan mapel (Umum, Keagamaan, Peminatan, Muatan Lokal) dan penetapan Kriteria Ketuntasan Minimal (KKM).
* **Jadwal Pelajaran**: Penjadwalan mingguan terstruktur per hari, jam pelajaran (jam mulai - jam selesai), kelas, mapel, dan guru pengajar.
* **Tahun Ajaran & Semester**: Kontrol periode akademik aktif (Ganjil/Genap).
* **Kenaikan Kelas & Kelulusan**: Utilitas pemindahan kelas massal pada akhir tahun ajaran.

### 3. 📷 Presensi Digital & Scanner QR Code
* **Scanner Kamera Real-time**: Absensi cepat menggunakan kamera laptop/webcam atau smartphone via peramban web (didukung oleh `html5-qrcode`).
* **Format Kode Unik**:
  * Siswa: `SISWA:<nis>` (contoh: `SISWA:2526001`)
  * Guru: `GURU:<nip>` (contoh: `GURU:198010022005091004`)
* **Deteksi Otomatis Masuk & Pulang**:
  * Scan ke-1 pada hari yang sama dicatat sebagai **Jam Masuk**.
  * Scan ke-2 dicatat otomatis sebagai **Jam Pulang**.
* **Deteksi Keterlambatan Otomatis**: Batas waktu masuk standar pukul `07:00:00 WIB`. Masuk setelah jam tersebut otomatis tercatat berstatus *Terlambat*.
* **Integrasi Poin Disiplin Otomatis**: Keterlambatan lebih dari toleransi 15 menit secara otomatis memotong poin kedisiplinan siswa sebesar 5 poin.
* **Rekapitulasi Presensi**: Laporan absensi harian dan bulanan siswa serta guru, dilengkapi opsi input manual (Izin, Sakit, Alpa).
* **Cetak Kartu Pelajar (ID Card)**: Generate kartu identitas siswa otomatis yang dilengkapi foto, biodata, dan QR Code siap cetak (print layout).

### 4. 📝 Penilaian & E-Rapor Madrasah
* **Input Nilai Kolektif**: Pengisian nilai tugas, UTS, dan UAS per mata pelajaran, kelas, dan tahun ajaran aktif.
* **Kalkulasi Nilai Otomatis**: Perhitungan bobot Nilai Akhir otomatis:
  $$\text{Nilai Akhir} = (30\% \times \text{Tugas}) + (30\% \times \text{UTS}) + (40\% \times \text{UAS})$$
* **Predikat Mutu**: Konversi nilai otomatis ke predikat huruf (A $\ge$ 90, B $\ge$ 80, C $\ge$ 70, D < 70).
* **Catatan Sikap & Evaluasi**: Pengisian catatan predikat sikap spiritual/sosial dan motivasi oleh Wali Kelas.
* **Cetak Lembar Rapor**: Format cetak rapor siap serah ke orang tua dengan kop madrasah, capaian kompetensi, kehadiran, dan tanda tangan resmi.

### 5. 🛡️ Kesiswaan & Sistem Poin Kedisiplinan
* **Sistem Poin Awal**: Setiap siswa dibekali modal **100 poin kedisiplinan**.
* **Kategori Pelanggaran**:
  * **Ringan** (contoh: atribut seragam tidak lengkap, terlambat masuk).
  * **Sedang** (contoh: membolos jam pelajaran, keluar madrasah tanpa izin).
  * **Berat** (contoh: merokok, perkelahian, tindakan asusila).
* **Status Kedisiplinan Berdasarkan Sisa Poin**:
  * `76 - 100 Poin` : **Aman** (Badge Hijau)
  * `51 - 75 Poin`  : **Teguran Lisan** (Badge Biru)
  * `26 - 50 Poin`  : **Peringatan Tertulis** (Badge Kuning)
  * `1 - 25 Poin`   : **Panggilan Orang Tua** (Badge Merah)
  * `$\le$ 0 Poin`  : **Sanksi Berat / Drop Out** (Badge Merah Gelap)

### 6. 📢 Layanan Madrasah
* **Perizinan Terpadu**: Formulir pengajuan izin dan sakit bagi siswa maupun guru dengan alur persetujuan (*Pending*, *Approved*, *Rejected*).
* **Pengumuman Digital**: Publikasi berita dan edaran madrasah dengan penanda prioritas penting.

### 7. 📦 Inventaris Sarana & Prasarana
* Pencatatan aset dan perlengkapan madrasah (kode barang, nama barang, kategori, ruangan/penempatan, jumlah stok, dan kondisi fisik barang: Baik, Rusak Ringan, Rusak Berat).

### 8. 📁 Pengarsipan & Upload Berkas Digital
* Manajemen berkas sekolah berbasis kategori dan tanggal upload.
* Fitur pengunduhan massal dalam format arsip **ZIP** (`ZipArchive`).
* Keamanan direktori upload: dilindungi berkas `.htaccess` untuk memblokir eksekusi skrip berbahaya (`.php`, `.sh`, `.py`, dll).

### 9. 🔐 Keamanan, Pengguna & Master Menu (RBAC)
* **Manajemen Multi-Peran**: Pembatasan hak akses berbasis peran pengguna (Role-Based Access Control).
* **Konfigurasi Master Menu Dinamis**: Administrator dapat mengatur hak akses visibilitas menu sidebar secara fleksibel baik per-Role maupun override per-User langsung dari dashboard tanpa mengubah kode sumber.
* **Fitur Impersonate ("Masuk sebagai user lain")**: Administrator dapat menguji tampilan dan hak akses user lain secara instan, serta kembali (*Revert*) ke sesi admin hanya dengan satu klik.
* **Audit Login Log**: Pencatatan riwayat setiap aktivitas login (User ID, IP Address pengakses, Waktu, dan Status).
* **Enkripsi Password**: Pengamanan autentikasi menggunakan algoritma hashing SHA-256 dan Prepared Statements (MySQLi) untuk mencegah ancaman SQL Injection.

---

## 👥 Matriks Hak Akses Peran

| Modul / Menu | ADMIN | TATA USAHA | KEPALA MADRASAH | WAKIL KEPALA | GURU |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Dashboard & Profil** | ✅ Penuh | ✅ Lihat | ✅ Lihat | ✅ Lihat | ✅ Lihat |
| **Data Siswa** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | 👁️ Lihat |
| **Data Guru** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | ❌ |
| **Data Kelas & Rombel** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | 👁️ Lihat |
| **Mata Pelajaran** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | 👁️ Lihat |
| **Jadwal Pelajaran** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | 👁️ Lihat |
| **Tahun Ajaran** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | ❌ |
| **Kenaikan Kelas** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | ❌ |
| **Scan Absensi (QR)** | ✅ Scan | ✅ Scan | ✅ Scan | ✅ Scan | ✅ Scan |
| **Absensi Siswa** | ✅ Penuh | ✅ Penuh | 👁️ Rekap | 👁️ Rekap | ✅ Input Izin/Sakit |
| **Absensi Guru** | ✅ Penuh | ✅ Penuh | 👁️ Rekap | 👁️ Rekap | ❌ |
| **Input Nilai & E-Rapor**| ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | ✅ Input Nilai Mapel |
| **Pelanggaran Siswa** | ✅ Penuh | ✅ Penuh | 👁️ Rekap | 👁️ Rekap | ✅ Catat Pelanggaran |
| **Perizinan** | ✅ Approval | ✅ Approval | 👁️ Lihat | 👁️ Lihat | ✅ Ajukan Izin |
| **Pengumuman** | ✅ Penuh | ✅ Penuh | ✅ Penuh | ✅ Penuh | 👁️ Lihat |
| **Inventaris Barang** | ✅ Penuh | ✅ Penuh | 👁️ Lihat | 👁️ Lihat | ❌ |
| **Upload Dokumen** | ✅ Penuh | ✅ Penuh | 👁️ Unduh | 👁️ Unduh | 👁️ Unduh |
| **Kelola User & Menu** | ✅ Penuh | ❌ | ❌ | ❌ | ❌ |

> *Catatan: Hak akses di atas merupakan aturan bawaan (*default*). Administrator dapat menyesuaikan atau menambah izin menu kapan saja melalui menu **Pengaturan > Master Menu**.*

---

## 🔑 Akun Demo Pengujian

Semua akun demo di bawah ini menggunakan kata sandi bawaan yang sama: **`demo1234`**

| Username | Peran (*Role*) | Nama Pengguna | Keterangan / Fungsi Utama |
| :--- | :--- | :--- | :--- |
| **`admin`** | **ADMIN** | Administrator Demo | Memiliki akses penuh ke seluruh sistem dan pengaturan |
| **`tu`** | **TATA USAHA** | Muhammad Rizki, S.Kom | Pengelolaan data operasional, guru, siswa, dan administrasi |
| **`kepsek`** | **KEPALA MADRASAH**| Drs. H. Ahmad Fauzi, M.Pd | Monitoring seluruh data madrasah, absensi, dan laporan |
| **`wakasek`**| **WAKIL KEPALA** | Hj. Siti Nurhaliza, S.Pd | Pengawasan akademik, jadwal, kurikulum, dan kesiswaan |
| **`guru.fiqih`**| **GURU** | Ustadz Abdul Karim, Lc | Guru Mapel Fiqih / Wali Kelas X IPA |
| **`guru.mtk`** | **GURU** | Dewi Lestari, S.Pd | Guru Mapel Matematika / Wali Kelas X IPS |
| **`guru.bing`**| **GURU** | Nurul Aini, S.Pd | Guru Mapel Bahasa Inggris / Wali Kelas XI IPA |

---

## 💻 Kebutuhan Sistem

* **Web Server**: Apache 2.4+ (disarankan menggunakan paket **Laragon** atau **XAMPP**).
* **Bahasa Pemrograman**: PHP versi **7.4**, **8.0**, **8.1**, atau **8.2**.
* **Ekstensi PHP**: `mysqli`, `mbstring`, `zip`, `json`, `session`.
* **Database Server**: MySQL versi 5.7+ atau MariaDB 10.3+.
* **Peramban Web**: Google Chrome, Mozilla Firefox, Microsoft Edge, atau Safari (disarankan versi terbaru untuk dukungan kamera scanner QR).

---

## 🛠️ Panduan Instalasi & Menjalankan

### Langkah 1: Tempatkan Source Code
Pastikan direktori project berada di dalam root web server Anda:
* **Laragon**: `C:\laragon\www\ma-goji`
* **XAMPP**: `C:\xampp\htdocs\ma-goji`

### Langkah 2: Setup Database MySQL
Buka antarmuka database Anda (misalnya **phpMyAdmin** di `http://localhost/phpmyadmin` atau melalui HeidiSQL/DBeaver):

1. Buat database baru bernama **`goji_db`** (atau sesuaikan dengan konfigurasi Anda).
2. Jalankan/import skrip SQL berikut secara berurutan:
   * 📄 `database/schema.sql` *(Database utama)*
   * 📄 `database/seed.sql` *(Data master awal, siswa, guru, kelas, & akun demo)*


> **Tips:** Jika menggunakan database bernama `goji_db`, saat mengimpor `schema.sql` dan `seed.sql`, pastikan Anda mengimpor langsung ke database `goji_db` atau sesuaikan baris `USE simple_sekolah;` pada baris atas file tersebut.

### Langkah 3: Konfigurasi Koneksi (`config.php`)
Secara bawaan, aplikasi membaca koneksi pada file `config.php`:
```php
$koneksidb = mysqli_connect(
    getenv('SIMPLE_DB_HOST') ?: '127.0.0.1',
    getenv('SIMPLE_DB_USER') ?: 'root',
    getenv('SIMPLE_DB_PASS') ?: '',
    getenv('SIMPLE_DB_NAME') ?: 'goji_db'
);
```
Anda dapat menyesuaikan host, username, password, atau nama database langsung di file `config.php` atau melalui *Environment Variables*.

### Langkah 4: Buka Aplikasi
Buka peramban web dan akses URL:
```text
http://localhost/ma-goji/login.php
```
Masukkan salah satu akun demo di atas untuk mulai menggunakan aplikasi.

---

## 📂 Struktur Direktori Proyek

```text
ma-goji/
├── assets/
│   ├── css/
│   │   ├── legacy.css            # Penyesuaian layout kompatibilitas tema
│   │   ├── simple.css            # Gaya kustom utama aplikasi SIMPLE
│   │   └── theme.css             # Variabel warna hijau khas MA Ghoyatul Jihad
│   ├── img/
│   │   ├── gedung.webp           # Foto gedung madrasah untuk latar login
│   │   └── logo.png              # Logo resmi madrasah
│   └── js/
│       ├── app.js                # Interaktivitas UI, sidebar, datatables, sweetalert
│       └── card.js               # Handler pratinjau kartu pelajar
├── database/
│   ├── schema.sql                # Skema tabel dasar (user, guru, siswa, kelas, absensi)
│   ├── seed.sql                  # Data seeder awal (pengguna & sampel siswa/guru)
│   ├── update_tahap1.sql         # Skema Tahun Ajaran, Mapel, & Jadwal KBM
│   ├── update_tahap2.sql         # Skema Layanan Perizinan & Pengumuman
│   ├── update_tahap3.sql         # Skema Input Nilai & Catatan Rapor
│   ├── update_tahap4.sql         # Skema Poin Pelanggaran & Inventaris Barang
│   ├── update_master_menu.sql    # Skema Hak Akses Master Menu Dinamis
│   └── update_poin_otomatis.sql  # Aturan sanksi keterlambatan otomatis
├── library/
│   ├── access.php                # Logic otorisasi peran & pemeriksaan Master Menu
│   └── helpers.php               # Helper fungsi: sanitasi data, query DB, predikat nilai
├── pages/
│   ├── absen_guru.php            # Rekapitulasi absensi dewan guru
│   ├── absen_scan.php            # Antarmuka kamera pemindai QR Code presensi
│   ├── absen_siswa.php           # Rekapitulasi & input absensi siswa (I/S/A)
│   ├── cetak_rapor.php           # Halaman siap cetak lembar rapor siswa
│   ├── guru.php                  # Manajemen data guru & staf
│   ├── help.php                  # Panduan penggunaan & bantuan aplikasi
│   ├── home.php                  # Dashboard beranda & ringkasan statistik
│   ├── inventaris.php            # Manajemen aset dan sarana prasarana
│   ├── jadwal.php                # Pengaturan jadwal pelajaran per kelas
│   ├── kartu.php                 # Cetak kartu pelajar dengan QR Code
│   ├── kelas.php                 # Manajemen rombel/kelas & penetapan wali kelas
│   ├── kenaikan.php              # Utilitas kenaikan kelas dan kelulusan siswa
│   ├── laporan.php               # Rekapitulasi laporan madrasah
│   ├── mapel.php                 # Manajemen mata pelajaran & KKM
│   ├── master_menu.php           # Konfigurasi aksesibilitas menu dinamis (Admin)
│   ├── nilai.php                 # Input nilai harian/tugas, UTS, dan UAS
│   ├── pelanggaran.php           # Master pelanggaran & pencatatan poin kesiswaan
│   ├── pengumuman.php            # Pengumuman dan agenda madrasah
│   ├── perizinan.php             # Pengajuan & persetujuan izin/sakit
│   ├── profile.php               # Profil akun user & formulir ubah password
│   ├── rapor.php                 # Pratinjau nilai rapor dan catatan wali kelas
│   ├── siswa.php                 # Manajemen biodata siswa
│   ├── tahun_ajaran.php          # Manajemen semester & tahun ajaran aktif
│   ├── upload_file.php           # Pusat arsip dokumen dan upload berkas
│   └── users.php                 # Kelola user, reset password, & fitur Impersonate
├── uploads/
│   └── file/                     # Direktori penyimpanan berkas upload (terlindungi .htaccess)
├── config.php                    # Konfigurasi koneksi database & zona waktu
├── header.php                    # Template navigasi samping (sidebar) & bilah atas (topbar)
├── footer.php                    # Penutup template halaman & script pendukung
├── index.php                     # Router sentral aplikasi (?page=...)
├── login.php                     # Halaman autentikasi login pengguna
├── logout.php                    # Penghancur sesi & keluar sistem
└── README.md                     # Dokumentasi teknis aplikasi
```

---

## 🎨 Kustomisasi

### 1. Mengganti Logo Madrasah
Ganti berkas gambar pada path:
```text
assets/img/logo.png
```
*Gunakan format gambar PNG dengan latar belakang transparan agar serasi dengan sidebar.*

### 2. Mengganti Gambar Latar Halaman Login
Ganti berkas gambar pada path:
```text
assets/img/gedung.webp
```
*Disarankan menggunakan rasio gambar lanskap (16:9) dengan resolusi minimal 1280x720 piksel.*

### 3. Mengatur Jam Masuk & Batas Toleransi Absensi
Buka file `pages/absen_scan.php` dan sesuaikan parameter berikut:
```php
$BATAS = '07:00:00';       // Batas jam masuk tepat waktu
$TOLERANSI_MENIT = 15;     // Batas toleransi sebelum dikenai pemotongan poin
```

---

## ❓ FAQ & Penyelesaian Masalah (*Troubleshooting*)

> **Q: Kamera pemindai QR Code tidak mau menyala / muncul error "NotAllowedError"?**  
> **A:** Kebijakan keamanan peramban modern (*Web Security API*) hanya mengizinkan akses kamera (`navigator.mediaDevices`) pada protokol **`http://localhost`**, **`http://127.0.0.1`**, atau domain yang memiliki sertifikat **`HTTPS`**. Jika mengakses lewat IP jaringan lokal (misal `192.168.x.x`), jalankan lewat virtual host HTTPS atau gunakan alternatif input manual NIS/NIP pada kolom cadangan yang telah disediakan di halaman scan.

> **Q: Muncul pesan error "Koneksi database gagal"?**  
> **A:** Pastikan layanan MySQL di Laragon/XAMPP sudah aktif (status *Running*). Periksa nama database pada file `config.php` (apakah database yang Anda buat bernama `goji_db` atau `simple_sekolah`).

> **Q: Muncul pesan "Akses Ditolak (403)" saat membuka halaman tertentu?**  
> **A:** Akun Anda tidak memiliki hak akses bawaan untuk halaman tersebut. Masuklah sebagai akun **`admin`**, buka menu **Pengaturan > Master Menu**, lalu aktifkan centang menu halaman tersebut untuk peran (*role*) Anda.

> **Q: Gagal mengunduh berkas massal (ZIP) di menu Upload File?**  
> **A:** Pastikan ekstensi `php_zip` sudah diaktifkan di file konfigurasi `php.ini` server Anda (hapus tanda titik koma `;` pada baris `extension=zip`, lalu restart web server).

---

## 📄 Lisensi & Hak Cipta

Aplikasi ini dikembangkan untuk keperluan operasional dan pembelajaran di lingkungan **MA Ghoyatul Jihad Karawang**.  
Seluruh hak cipta dan merek madrasah dilindungi.
