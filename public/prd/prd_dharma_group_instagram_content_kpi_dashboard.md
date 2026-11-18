# Product Requirements Document (PRD)
## Dharma Group — Instagram Content Log & KPI Analytics Dashboard

| Informasi Dokumen | Detail |
| :--- | :--- |
| **Nama Produk** | Dharma Group IG Content & KPI Dashboard |
| **Versi** | 1.0 (MVP) |
| **Tanggal** | 17 November 2026 |
| **Status** | Draft / Ready for Development |
| **Target Pengguna** | Tim Social Media / Employer Branding, HR / Talent Acquisition, & Manajemen PT Dharma Polimetal Tbk |

---

## 1. Latar Belakang & Tujuan Produk

### 1.1 Latar Belakang
Saat ini, pencatatan riwayat publikasi konten dan evaluasi performa H+30 Instagram Dharma Group dikelola secara manual menggunakan spreadsheet (`Master_Rekap_Konten_Dharma_Group`). Seiring bertambahnya volume konten (44+ konten sepanjang 2026) dan kebutuhan pelaporan KPI bulanan, pengelolaan berbasis Excel memiliki keterbatasan dalam hal:
* Pemantauan tenggat evaluasi **H+30** secara real-time (sering terlewat atau berstatus *Menunggu H+30*).
* Visualisasi tren *Engagement Rate* (ER) dan komposisi interaksi bulanan yang dinamis.
* Standardisasi input data (mencegah inkonsistensi label seperti `"Feed"` vs `"Feeds"`, atau kolom `PIC` dan `Evidence` yang kosong).

### 1.2 Tujuan Produk (Objectives)
1. **Sentralisasi Basis Data Konten:** Mengubah *Master Log Konten* menjadi sistem manajemen konten web yang terstruktur dengan validasi data otomatis.
2. **Otomatisasi KPI & Visualisasi:** Menyajikan 4 KPI utama performa Instagram secara interaktif dan otomatis tanpa perlu rumus manual.
3. **Peningkatan Kedisiplinan Administrasi:** Memberikan pengingat otomatis untuk jadwal input metrik **H+30** dan kelengkapan tautan *evidence* posting.

---

## 2. Target Pengguna (User Personas)

1. **Social Media Specialist / PIC Konten (Editor/Admin):**
   * *Kebutuhan:* Mencatat jadwal/riwayat konten baru, melampirkan link Instagram (*evidence*), dan menginput metrik H+30 (*Views, Viewers, Like, Comment, Share/Repost, Save, Follows*).
2. **HR / Employer Branding Lead & Manajemen (Viewer/Reviewer):**
   * *Kebutuhan:* Memantau performa bulanan secara cepat, melihat efektivitas tiap pilar konten (seperti *Info Lowongan* vs *Employer Branding*), serta mengunduh laporan rekap untuk presentasi manajemen.

---

## 3. Ruang Lingkup Fitur (Functional Requirements)

### 3.1 Modul 1: KPI Analytics Dashboard (Halaman Utama)
Halaman utama menampilkan ringkasan eksekutif dengan filter global berdasarkan **Tahun** (default: 2026), **Pilar Konten**, dan **Jenis Konten** (*Feed, Reels, Carousel*).

#### A. Kartu Ringkasan (KPI Summary Cards)
* **Total Konten Tayang:** Jumlah total konten + indikator status (*Sudah Diisi* vs *Menunggu H+30*).
* **Total Views (H+30):** Akumulasi tayangan dan rata-rata views per konten.
* **Total Engagement:** Akumulasi (`Like + Comment + Share/Repost + Save`).
* **Average Engagement Rate (ER):** Rata-rata ER per konten (Benchmark: 1,72%) dan *Aggregate ER* ($\frac{\text{Total Interaksi}}{\text{Total Views}}$).
* **Total New Follows:** Akumulasi pengikut baru yang didapat dari konten.

#### B. Visualisasi 4 KPI Utama (Core Charts)
1. **KPI 1 — Chart Engagement Seluruh Bulan (Jan–Des):**
   * *Tipe Grafik:* Combo Chart (Bar Chart untuk *Total Engagement* + Line Chart untuk *Total Views H+30*).
   * *Interaktivitas:* Tooltip menampilkan rincian jumlah konten yang sudah dievaluasi pada bulan tersebut.
2. **KPI 2 — Chart Engagement Rate (ER) Seluruh Bulan (Jan–Des):**
   * *Tipe Grafik:* Multi-Line Chart yang membandingkan **Monthly Aggregate ER (%)** dan **Rata-rata ER per Konten (%)**, dilengkapi garis referensi putus-putus (*Threshold Line*) untuk **Benchmark ER Tahunan**.
3. **KPI 3 — Chart Analisa Komposisi Interaksi per Bulan:**
   * *Tipe Grafik:* Stacked Bar Chart (dan opsi toggle ke 100% Stacked Bar) yang memecah metrik **Like**, **Share/Repost**, **Save**, dan **Comment** berdasarkan bulan publikasi konten.
   * *Catatan Data:* Menyediakan opsi tampilan *Breakdown per Konten* ketika salah satu bulan diklik (*drill-down*), sehingga pengguna bisa melihat kontribusi interaksi tiap judul konten di bulan tersebut.
4. **KPI 4 — Tabel & Kartu Top Performance per Bulan:**
   * Menampilkan konten dengan performa tertinggi pada setiap bulan (Januari–Desember) berdasarkan 5 kategori metrik:
     * **Top Views (H+30)**
     * **Top Like**
     * **Top Share / Repost**
     * **Top Save**
     * **Top Engagement Rate (%)**
   * Dilengkapi filter pemilih bulan (*Month Selector*) dan tampilan tabel ringkasan seluruh bulan.

---

### 3.2 Modul 2: Master Log Konten (CRUD & Tracking H+30)
Digitalisasi penuh dari sheet `Log Konten` dengan peningkatan validasi dan otomatisasi.

* **Tabel Data Interaktif:** Pencarian berdasarkan judul/topik, filter berdasarkan *Bulan*, *Pilar*, *Jenis Konten*, *PIC*, dan *Status Data* (`Sudah diisi` / `Menunggu H+30`).
* **Form Tambah & Edit Konten:**
  * *Input Identitas:* Tanggal Posting, Jenis Konten (*Dropdown terstandarisasi:* `Feed`, `Reels`, `Carousel`), Judul/Topik, Tujuan/Pilar (*Dropdown:* `Internal/Culture`, `Employer Branding`, `Employee Branding`, `Event/Job Fair`, `Info Lowongan`), PIC.
  * *Administrasi Evidence:* Input URL Instagram (`Link Evidence Posting`). Jika URL diisi, `Status Evidence` otomatis berubah menjadi `Ada` (jika kosong: `Belum`).
  * *Otomatisasi Jadwal H+30:* Sistem otomatis menghitung `Tgl Cek H+30 = Tanggal Posting + 30 hari`.
* **Kalkulasi Metrik Otomatis:**
  * Ketika tim menginput `Views H+30`, `Viewers H+30`, `Like`, `Comment`, `Share/Repost`, `Save`, dan `Follows`:
  * `Total Interaksi` dihitung otomatis: $\text{Like} + \text{Comment} + \text{Share/Repost} + \text{Save}$.
  * `Engagement Rate (%)` dihitung otomatis: $\left(\frac{\text{Total Interaksi}}{\text{Views H+30}}\right) \times 100\%$.
  * `Status Data` otomatis berubah dari `Menunggu H+30` menjadi `Sudah diisi`.
* **Notifikasi / Highlight Jatuh Tempo H+30:** Badge peringatan berwarna kuning/merah untuk konten yang sudah melewati `Tgl Cek H+30` namun metriknya belum diisi.

---

### 3.3 Modul 3: Analisa Pilar & Format Konten (Secondary Insights)
* **Perbandingan Pilar Konten:** Grafik perbandingan rata-rata *Views*, *Interaksi*, *Follows*, dan *ER* antar pilar (menyoroti performa tinggi *Info Lowongan* dan *Employer Branding* vs frekuensi tinggi *Internal/Culture*).
* **Perbandingan Format Konten:** Komparasi performa antara **Reels**, **Feed**, dan **Carousel**.

---

### 3.4 Modul 4: Import & Export Data
* **Import Excel/CSV:** Fitur migrasi awal untuk mengunggah file `Master_Rekap_Konten_Dharma_Group.xlsx` langsung ke database tanpa input ulang satu per satu.
* **Export Laporan:** Unduh rekap bulanan maupun keseluruhan data dalam format `.xlsx` dan laporan visual `.pdf` untuk kebutuhan presentasi.

---

## 4. Struktur Data (Database Schema)

Tabel utama `content_logs` dirancang menyesuaikan struktur sheet `Log Konten`:

| Nama Kolom | Tipe Data | Keterangan / Aturan Validasi |
| :--- | :--- | :--- |
| `id` | UUID / Integer (PK) | Auto-increment ID |
| `post_date` | DATE (Required) | Tanggal posting konten |
| `content_type` | ENUM (Required) | `Feed`, `Reels`, `Carousel` (mencegah typo seperti *Feeds*) |
| `title` | VARCHAR(255) (Required) | Judul atau topik konten |
| `pillar` | ENUM (Required) | `Internal/Culture`, `Employer Branding`, `Employee Branding`, `Event/Job Fair`, `Info Lowongan` |
| `pic_name` | VARCHAR(100) (Required) | Nama penanggung jawab konten |
| `evidence_url` | TEXT (Nullable) | Tautan langsung ke postingan Instagram |
| `evidence_status` | BOOLEAN / Computed | `true` (`Ada`) jika `evidence_url` terisi, `false` (`Belum`) jika kosong |
| `h30_due_date` | DATE (Computed) | `post_date + 30 days` |
| `data_status` | ENUM (Computed) | `Sudah diisi` atau `Menunggu H+30` |
| `views_h30` | INTEGER (Default: 0) | Jumlah tayangan pada H+30 |
| `viewers_h30` | INTEGER (Default: 0) | Jumlah akun unik yang menonton pada H+30 |
| `likes` | INTEGER (Default: 0) | Jumlah Like |
| `comments` | INTEGER (Default: 0) | Jumlah Comment |
| `shares_reposts` | INTEGER (Default: 0) | Jumlah Share & Repost |
| `saves` | INTEGER (Default: 0) | Jumlah Save |
| `follows` | INTEGER (Default: 0) | Jumlah pengikut baru dari konten tersebut |
| `total_interactions` | INTEGER (Computed) | `likes + comments + shares_reposts + saves` |
| `engagement_rate` | DECIMAL(5,2) (Computed)| `(total_interactions / views_h30) * 100` |
| `notes` | TEXT (Nullable) | Catatan tambahan evaluasi |

---

## 5. Kebutuhan Non-Fungsional & Rekomendasi Tech Stack

### 5.1 Kebutuhan Non-Fungsional
* **Responsivitas:** Tampilan optimal pada layar Desktop (untuk input data & analisis mendalam) serta Tablet/Mobile (untuk pengecekan cepat oleh manajemen).
* **Keamanan & Akses (RBAC):** Sistem login sederhana dengan 2 peran: `Admin` (Akses penuh CRUD) dan `Viewer` (Hanya baca & export laporan).
* **Akurasi Perhitungan:** Konten dengan status `Menunggu H+30` (nilai metrik masih 0/kosong) **tidak boleh** ikut dihitung dalam pembagi rata-rata *Engagement Rate* bulanan agar tidak menarik turun nilai rata-rata secara artifisial.

### 5.2 Rekomendasi Teknologi (Tech Stack)
* **Frontend:** Next.js (React) atau Vue.js + Tailwind CSS (untuk antarmuka modern yang bersih dan cepat).
* **Library Visualisasi Grafik:** Apache ECharts, Chart.js, atau Recharts (mendukung *Combo Chart*, *Stacked Bar*, *Reference Line*, dan *Drill-down*).
* **Backend & Database:** Node.js / Laravel atau Supabase / PostgreSQL (mendukung autentikasi, REST API, dan *computed columns*).
* **Excel Parser:** SheetJS (`xlsx`) untuk fitur *Import* dan *Export* file spreadsheet existing.

---

## 6. Tahapan Pengembangan (Roadmap)

1. **Fase 1 — Setup Database & Migrasi Data (Minggu 1):** Pembuatan skema database dan fitur *import* data historis 44 konten dari Excel ke sistem.
2. **Fase 2 — Modul Master Log Konten (Minggu 2):** Pembangunan halaman tabel log, form input/edit, validasi *evidence*, dan kalkulasi otomatis H+30.
3. **Fase 3 — Modul KPI Dashboard (Minggu 3):** Implementasi 4 visualisasi KPI utama (*Engagement*, *ER*, *Komposisi Interaksi*, dan *Top Performance per Bulan*) beserta filter interaktif.
4. **Fase 4 — Testing, Export Laporan & Go-Live (Minggu 4):** Pengujian akurasi rumus terhadap Excel existing, penambahan fitur export PDF/Excel, dan peluncuran untuk tim Dharma Group.
```

Dokumen PRD di atas sudah mencakup spesifikasi teknis mulai dari arsitektur data *Log Konten*, aturan validasi agar tidak ada lagi data yang terlewat (seperti status *evidence* dan pengingat H+30), hingga detail keempat grafik KPI yang Anda minta. Apakah Anda ingin saya langsung membuatkan **prototipe antarmuka website (UI/UX interaktif)** dari PRD ini, atau ada bagian tertentu dalam PRD yang ingin disesuaikan terlebih dahulu?