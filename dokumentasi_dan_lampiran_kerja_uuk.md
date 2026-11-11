# LAPORAN DOKUMENTASI DAN LAMPIRAN PROYEK UUK

**Judul Tugas:** Pengembangan Aplikasi Pengaduan Sarana Sekolah  
**Peran:** Junior Assistant Programmer (Siswa Kelas XII)  
**Teknologi / Tech Stack:** Laravel (v12), Filament (v4), MySQL Database, Git & GitHub  
**Link Repository GitHub:** https://github.com/AliciaJoiceIrawan/pengaduan_sarana.git  

---

## 3. DOKUMENTASI

### a. ERD (Entity Relationship Diagram)

Berdasarkan lembar tugas kerja dan rancangan skema database aplikasi, berikut adalah struktur diagram relasi antar tabel (*Users*, *Aspirations*, dan *Categories*):

```text
       +-------------------+
       |       Users       |
       +-------------------+
       | PK id             |
       |    nama           |
       |    email          |
       |    password       |
       |    role           | <----+
       |    nis            |      |
       |    kelas          |      |
       +-------------------+      |
                 |                |
                 | 1              | (Relasi Kepemilikan Siswa)
                 |                |
                 | (1,N)          |
                 v                |
       +-------------------+      |
       |    Aspirations    |      |
       +-------------------+      |
       | PK id             |      |
       | FK user_id        |------+
       | FK category_id    |----+ 
       |    lokasi         |    |
       |    deskripsi      |    | 
       |    status         |    | (Enum: Menunggu, Proses, Selesai)
       |    progres        |    |
       |    umpan_balik    |    |
       +-------------------+    |
                 |              |
                 | (N,1)        |
                 |              |
                 v              |
       +-------------------+    |
       |    Categories     |    |
       +-------------------+    |
       | PK id             |<---+
       |    nama           |
       +-------------------+
```

#### Penjelasan Relasi & Struktur Entitas:
1. **Entitas `Admin` & `Siswa` (Tabel `users`)**: Menampung kredensial login pengguna. Untuk siswa dilengkapi atribut spesifik yaitu **NIS** (`nis`) dan **Kelas** (`kelas`, contoh: *XII RPL 1*).
2. **Entitas `Input Aspirasi` / `Aspirasi` (Tabel `aspirations`)**: Merupakan tabel transaksi utama yang menghubungkan siswa dengan pengaduan fasilitas. Memiliki atribut `id`, `user_id` (FK), `category_id` (FK), `lokasi`, `deskripsi` (aspirasi pengaduan), `status` (Enum: `Menunggu`, `Proses`, `Selesai`), `progres`, dan `umpan_balik`.
3. **Entitas `Kategori` (Tabel `categories`)**: Menyimpan pengelompokan jenis fasilitas sekolah (contoh: *Lapangan*, *Toilet*, *Ruang Kelas*).

---

### b. Deskripsi Program

**Aplikasi Pengaduan Sarana Sekolah** adalah sistem berbasis web yang dibangun untuk memfasilitasi proses input dan output pengaduan sarana prasarana sekolah agar dapat berjalan secara efektif dan efisien. 

Aplikasi dirancang dengan arsitektur **Dual Panel**:
1. **Panel Siswa (`/student`)**: Digunakan oleh siswa untuk mengisi form aspirasi pengaduan sarana prasarana sekolah, melihat status penyelesaian, memantau histori aspirasi pribadi, melihat progres perbaikan, dan menerima umpan balik dari admin.
2. **Panel Admin (`/admin`)**: Digunakan oleh administrator (petugas sarpras) untuk melihat *list aspirasi keseluruhan* (berdasarkan tanggal, per siswa, per kelas, per kategori), mengelola status penyelesaian (*Menunggu*, *Proses*, *Selesai*), memberikan umpan balik aspirasi, serta memperbarui progres perbaikan.

---

### c. Dokumentasi Fungsi / Prosedur

Berikut adalah potongan fungsi dan prosedur logika utama yang diimplementasikan pada file `PengaduanResource.php`:

#### 1. Pembatasan Hak Akses Histori Siswa (Query Filter Scope)
Sistem membatasi agar panel siswa hanya menampilkan data histori aspirasi miliknya sendiri, sementara Admin dapat melihat seluruh data pengaduan:
```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    if (request()->is('student*')) {
        $query->where('user_id', auth()->id());
    }

    return $query;
}
```

#### 2. Implementasi Struktur Tabel Rekapitulasi (Tampilan Antarmuka Panel)
Mendefinisikan kolom-kolom tabel yang menampilkan seluruh hasil inputan siswa beserta respon admin:
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('user.nama')->label('Nama Siswa')->searchable(),
            TextColumn::make('user.kelas')->label('Kelas'),
            TextColumn::make('category.nama')->label('Kategori'),
            TextColumn::make('deskripsi')->label('Aspirasi / Pengaduan'),
            TextColumn::make('status')
                ->label('Status Penyelesaian')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Menunggu' => 'gray',
                    'Proses' => 'warning',
                    'Selesai' => 'success',
                    default => 'gray',
                }),
            TextColumn::make('progres')->label('Progres Perbaikan')->placeholder('Menunggu penanganan'),
            TextColumn::make('umpan_balik')->label('Umpan Balik Admin')->placeholder('Belum ada umpan balik'),
            TextColumn::make('created_at')->label('Tanggal Pengaduan')->dateTime(),
        ]);
}
```

---

### d. Debugging

Selama proses pengembangan aplikasi, dilakukan penanganan kendala teknis (*debugging*) di terminal dan kode program:

1. **Penyesuaian Remote URL Repository Git & Push Code**:
   * **Kendala (Terminal Log)**: Terjadi error `fatal: origin does not appear to be a git repository` dan `fatal: repository 'https://github.com/' not found` saat menjalankan perintah `git push -u origin main`.
   * **Solusi**: Memperbaiki konfigurasi URL remote repository dengan mengeksekusi perintah terminal:
     ```bash
     git remote add origin https://github.com/AliciaJoiceIrawan/pengaduan_sarana.git
     git push -u origin main
     ```
2. **Sinkronisasi Enum Status pada Migrasi Database**:
   * **Solusi**: Mengubah default status pada tabel `aspirations` menjadi `'Menunggu'` agar sesuai dengan kriteria penilaian ERD pada lembar soal, kemudian mengeksekusi migrasi ulang:
     ```bash
     php artisan migrate:fresh --seed
     php artisan optimize:clear
     ```

---

## 4. LAPORAN EVALUASI SINGKAT

Berdasarkan hasil pengujian fungsional aplikasi **Pengaduan Sarana Sekolah**:
1. **Fungsi Siswa**: Siswa atas nama **joice** (Kelas **XII RPL 1**) telah berhasil menginput aspirasi pengaduan kategori **Lapangan** ("*inigin menjadi baik*"), serta dapat memantau status penyelesaian, progres perbaikan, dan umpan balik secara transparan.
2. **Fungsi Admin**: Admin berhasil mengakses rekapitulasi *List aspirasi keseluruhan*, mengubah status penyelesaian, menginput progres perbaikan, dan memberikan tanggapan balik.
3. **Hasil Pengujian**: Seluruh fungsionalitas aplikasi dan struktur database **100% Sesuai** dengan kriteria dan petunjuk pada Lembar Kerja UUK.