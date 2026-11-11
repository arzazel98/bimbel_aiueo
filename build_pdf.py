import os
import shutil
import subprocess

html_content = """<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Lembar Kerja Static Testing - Bimbel AIUEO</title>
<style>
  @page {
    size: A4 portrait;
    margin: 10mm 13mm 10mm 13mm;
  }
  * {
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  body {
    font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
    color: #1e293b;
    font-size: 8.5pt;
    line-height: 1.35;
    margin: 0;
    padding: 0;
    background: #fff;
  }
  .page {
    width: 100%;
    height: 277mm;
    position: relative;
    page-break-after: always;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
  }
  .page:last-child {
    page-break-after: avoid;
  }
  .page-header {
    border-bottom: 0.8pt solid #cbd5e1;
    padding-bottom: 3px;
    margin-bottom: 7px;
    text-align: right;
    font-size: 8pt;
    font-style: italic;
    color: #64748b;
  }
  .page-footer {
    border-top: 0.8pt solid #cbd5e1;
    padding-top: 4px;
    margin-top: 5px;
    text-align: center;
    font-size: 8pt;
    color: #64748b;
  }
  .content {
    flex: 1;
  }
  .doc-title {
    text-align: center;
    margin-bottom: 7px;
  }
  .doc-title .sub {
    font-size: 9pt;
    font-weight: 700;
    color: #1e5a8a;
    letter-spacing: 0.8px;
    text-transform: uppercase;
  }
  .doc-title .main {
    font-size: 13.5pt;
    font-weight: 800;
    color: #1a2d54;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    margin-top: 1px;
  }
  .section-banner {
    background-color: #1a2d54;
    color: #ffffff;
    font-size: 8.8pt;
    font-weight: 700;
    padding: 3px 8px;
    letter-spacing: 0.3px;
    margin-top: 5px;
    margin-bottom: 2px;
    border-radius: 2px;
  }
  .section-hint {
    font-size: 7.5pt;
    font-style: italic;
    color: #475569;
    margin-bottom: 3px;
    margin-left: 2px;
  }
  .sub-heading {
    font-size: 8.5pt;
    font-weight: 700;
    color: #1a2d54;
    margin-top: 4px;
    margin-bottom: 2px;
  }
  table.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    font-size: 7.8pt;
  }
  table.data-table th, table.data-table td {
    border: 0.6pt solid #cbd5e1;
    padding: 3px 5px;
    vertical-align: top;
  }
  table.data-table th {
    background-color: #176982;
    color: #ffffff;
    font-weight: 700;
    text-align: left;
    font-size: 7.8pt;
  }
  table.data-table th.center, table.data-table td.center {
    text-align: center;
  }
  table.kv-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 4px;
    font-size: 7.8pt;
  }
  table.kv-table th, table.kv-table td {
    border: 0.6pt solid #cbd5e1;
    padding: 2.8px 5px;
    vertical-align: top;
  }
  table.kv-table td.key {
    width: 27%;
    background-color: #ebf1f7;
    font-weight: 600;
    color: #1e3a68;
  }
  table.kv-table td.val {
    background-color: #ffffff;
    color: #1e293b;
  }
  .checklist-item {
    margin-bottom: 2.5px;
    font-size: 7.8pt;
  }
  .check-box {
    display: inline-block;
    width: 11px;
    height: 11px;
    background: #176982;
    color: white;
    font-size: 7.5pt;
    line-height: 11px;
    text-align: center;
    border-radius: 2px;
    margin-right: 4px;
    vertical-align: middle;
    font-weight: bold;
  }
  .check-label {
    font-weight: 700;
    color: #0f172a;
  }
  .check-answer {
    margin-left: 17px;
    font-size: 7.3pt;
    color: #334155;
    margin-top: 1px;
  }
  .badge {
    display: inline-block;
    padding: 1px 4px;
    border-radius: 2px;
    font-size: 6.8pt;
    font-weight: bold;
    text-transform: uppercase;
  }
  .badge-critical { background: #fee2e2; color: #b91c1c; border: 0.5pt solid #f87171; }
  .badge-major { background: #fef3c7; color: #b45309; border: 0.5pt solid #fcd34d; }
  .badge-minor { background: #e0f2fe; color: #0369a1; border: 0.5pt solid #7dd3fc; }
  .badge-info { background: #f1f5f9; color: #475569; border: 0.5pt solid #cbd5e1; }
  .badge-p1 { background: #fee2e2; color: #991b1b; font-weight: 800; }
  .badge-p2 { background: #ffedd5; color: #9a3412; font-weight: 700; }
  .badge-p3 { background: #f1f5f9; color: #334155; }
  .badge-defect { background: #fef2f2; color: #dc2626; border: 0.5pt solid #fca5a5; font-weight: 700; }
  .badge-tanya { background: #eff6ff; color: #1d4ed8; border: 0.5pt solid #93c5fd; font-weight: 700; }
  .note-box {
    font-size: 7pt;
    font-style: italic;
    color: #475569;
    background: #f8fafc;
    border-left: 2.5pt solid #94a3b8;
    padding: 2.5px 5px;
    margin-top: 2px;
    margin-bottom: 3px;
    line-height: 1.25;
  }
  .reflection-q {
    font-weight: 700;
    color: #0f172a;
    font-size: 7.8pt;
    margin-top: 4px;
    margin-bottom: 2px;
  }
  .reflection-a {
    font-size: 7.3pt;
    color: #334155;
    background: #f8fafc;
    border-left: 2.5pt solid #176982;
    padding: 3.5px 6px;
    margin-bottom: 5px;
    line-height: 1.32;
    text-align: justify;
  }
  ol, ul {
    margin: 1px 0;
    padding-left: 14px;
  }
  li {
    margin-bottom: 1px;
  }
  code {
    font-family: Consolas, monospace;
    font-size: 7.4pt;
    background: #f1f5f9;
    padding: 0.5px 2px;
    border-radius: 2px;
  }
</style>
</head>
<body>

<!-- HALAMAN 1 -->
<div class="page">
  <div class="page-header">Lembar Kerja Static Testing— Bab 3</div>
  <div class="content">
    <div class="doc-title">
      <div class="sub">UJI KUALITAS PERANGKAT LUNAK</div>
      <div class="main">LEMBAR KERJA STATIC TESTING</div>
    </div>

    <!-- IDENTITAS MAHASISWA -->
    <div class="section-banner" style="margin-top: 0;">IDENTITAS MAHASISWA</div>
    <table class="kv-table">
      <tr>
        <td class="key">Nama Mahasiswa</td>
        <td class="val"><strong>(Silakan isi Nama Lengkap Anda)</strong></td>
      </tr>
      <tr>
        <td class="key">NIM</td>
        <td class="val"><strong>(Silakan isi NIM Anda)</strong></td>
      </tr>
      <tr>
        <td class="key">Kelas</td>
        <td class="val"><strong>(Silakan isi Kelas / Paralel Anda)</strong></td>
      </tr>
      <tr>
        <td class="key">Nama Proyek RBPL</td>
        <td class="val">Sistem Informasi & Portal Belajar Bimbel AIUEO</td>
      </tr>
      <tr>
        <td class="key">Tanggal & Jam Mulai Pengerjaan</td>
        <td class="val">12 November 2026, 08:00 WIB</td>
      </tr>
    </table>

    <!-- BAGIAN 1 -->
    <div class="section-banner">BAGIAN 1 — Ringkasan & Rencana Review</div>
    <div class="section-hint">Pilih scope sesempit mungkin: 1 fitur, 5–8 requirement, dan 1 fungsi/modul kecil.</div>
    <table class="kv-table">
      <tr>
        <td class="key">Fitur/Bagian Proyek yang Dipilih</td>
        <td class="val"><strong>Modul Autentikasi Pengguna & Registrasi Akun Siswa Baru</strong></td>
      </tr>
      <tr>
        <td class="key">Requirement/User Story yang Direview (5–8 butir, sebutkan No./nama)</td>
        <td class="val">
          <ol>
            <li><strong>REQ-REG-01</strong>: Pengguna baru (calon siswa) mendaftarkan akun dengan username, email/telepon, & password.</li>
            <li><strong>REQ-REG-02</strong>: Sistem memvalidasi panjang password minimal 6 karakter sebelum diproses.</li>
            <li><strong>REQ-REG-03</strong>: Sistem menolak pendaftaran jika username atau email sudah terdaftar di database.</li>
            <li><strong>REQ-REG-04</strong>: Password akun dienkripsi menggunakan hashing yang aman (bcrypt / <code>PASSWORD_DEFAULT</code>).</li>
            <li><strong>REQ-REG-05</strong>: Record baru disimpan dengan role default 'siswa' dan status 'aktif'.</li>
            <li><strong>REQ-REG-06</strong>: Mampu menangani kegagalan koneksi database secara aman tanpa crash fatal (graceful error handling).</li>
            <li><strong>REQ-REG-07</strong>: Antarmuka pendaftaran berbasis web modern responsif dengan notifikasi alert interaktif.</li>
          </ol>
        </td>
      </tr>
      <tr>
        <td class="key">Fungsi/Modul Kode yang Direview (nama file & fungsi)</td>
        <td class="val">
          1. <code>views/auth/register.php</code> (Handler proses POST submit registrasi akun siswa)<br>
          2. <code>config/database.php</code> (Metode <code>Database::getConnection()</code> & fungsi helper <code>getDB()</code>)
        </td>
      </tr>
      <tr>
        <td class="key">Tujuan Review</td>
        <td class="val">Mengidentifikasi inkonsistensi konfigurasi database, potensi fatal runtime exception (dereferensi objek null), kelemahan sanitasi input/exception handling, cacat tata letak antarmuka mobile simulator, serta kesiapan standar PHP 8.2 sebelum kode dieksekusi secara dinamis.</td>
      </tr>
      <tr>
        <td class="key">Exit Criteria Singkat</td>
        <td class="val">
          1. Semua anomali Critical dan Major teridentifikasi dan tercatat lengkap.<br>
          2. Minimal 3 anomali prioritas tertinggi berhasil diperbaiki pada source code.<br>
          3. Seluruh berkas yang direvisi lolos uji sintaks <code>php -l</code> dengan 0 error dan 0 warning log.
        </td>
      </tr>
      <tr>
        <td class="key">Tipe Review</td>
        <td class="val"><strong>Informal Review</strong> (didukung checklist evaluasi mandiri terstruktur).</td>
      </tr>
    </table>
    <div class="note-box">
      <strong>Catatan:</strong> Tipe review yang dipakai pada versi ringkas ini adalah Informal Review. Peran manager/author/reviewer/moderator/scribe/review leader tetap Anda jalankan sendiri secara berurutan — lihat Langkah 2 (jeda singkat 15 menit) pada Lembar Tugas sebagai pengganti cold review bermalam.
    </div>

    <!-- BAGIAN 2.A -->
    <div class="section-banner">BAGIAN 2 — Review Mandiri (Individual Review)</div>
    <div class="section-hint">Petunjuk: Langkah 3–4, total ±70 menit</div>
    <div class="sub-heading">A. Checklist Dokumen Requirement</div>
    
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah setiap requirement jelas / tidak ambigu?</span>
      <div class="check-answer">&rarr; <strong>TIDAK</strong>. Pada REQ-REG-01, input gabungan <code>email_or_phone</code> tidak memiliki aturan validasi format yang jelas antara struktur email dan pola nomor telepon seluler.</div>
    </div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah ada kontradiksi antar requirement yang direview?</span>
      <div class="check-answer">&rarr; <strong>YA</strong>. Requirement aplikasi menyatakan sistem portal web desktop (REQ-REG-07), namun realisasi desain awal dibatasi dalam format kontainer ponsel sempit (max-width: 360-400px).</div>
    </div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah requirement lengkap untuk fitur yang dipilih?</span>
      <div class="check-answer">&rarr; <strong>TIDAK</strong>. Tidak ada requirement yang mendefinisikan contingency behavior saat database server offline atau database belum diinisialisasi.</div>
    </div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah setiap requirement punya acceptance criteria yang testable?</span>
      <div class="check-answer">&rarr; <strong>YA</strong>. Sebagian besar memiliki kriteria uji terukur (panjang password &ge; 6, pengecekan duplikasi record, status redirect).</div>
    </div>

    <div class="sub-heading">Daftar Anomali — Dokumen</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%;" class="center">No</th>
          <th style="width: 28%;">Requirement / Bagian</th>
          <th>Deskripsi Anomali</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center"><strong>1</strong></td>
          <td><strong>REQ-REG-01</strong><br>(Form Input Akun)</td>
          <td><strong>Ambiguitas Format Input:</strong> Penggabungan field email & telepon tanpa regex validator ketat menyebabkan nomor telepon disimpan dengan email tiruan <code>$username . '@gmail.com'</code>.</td>
        </tr>
        <tr>
          <td class="center"><strong>2</strong></td>
          <td><strong>REQ-REG-06</strong><br>(Exception Handling)</td>
          <td><strong>Ketidaklengkapan Spesifikasi:</strong> Tidak adanya requirement penanganan kegagalan infrastruktur basis data membuat pengembang tidak menyediakan fallback koneksi saat database offline.</td>
        </tr>
        <tr>
          <td class="center"><strong>3</strong></td>
          <td><strong>REQ-REG-07</strong><br>(Spesifikasi Tampilan)</td>
          <td><strong>Kontradiksi Desain Antarmuka:</strong> Dokumen menyatakan sistem berbasis Web Desktop, namun template UI dirancang menyerupai emulator aplikasi smartphone (terkunci di tengah layar).</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="page-footer">Halaman 1 dari 3</div>
</div>

<!-- HALAMAN 2 -->
<div class="page">
  <div class="page-header">Lembar Kerja Static Testing— Bab 3</div>
  <div class="content">
    <!-- BAGIAN 2.B -->
    <div class="sub-heading" style="margin-top: 0;">B. Checklist Source Code</div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah ada variabel tidak dideklarasikan/tidak digunakan?</span>
      <div class="check-answer">&rarr; <strong>YA</strong>. Variabel <code>$alamat</code> diinisialisasi sebagai string kosong (<code>$alamat = ''</code>), namun tidak ada field input alamat pada tampilan form registrasi.</div>
    </div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah ada kode yang tidak pernah dijangkau (unreachable)?</span>
      <div class="check-answer">&rarr; <strong>TIDAK</strong>. Alur kondisional submit form POST dapat dijangkau seluruhnya sesuai alur eksekusi aplikasi.</div>
    </div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah penamaan konsisten dengan coding standard?</span>
      <div class="check-answer">&rarr; <strong>SEBAGIAN</strong>. Terjadi percampuran konvensi penamaan antara bahasa Inggris dan Indonesia (misal <code>$email_or_phone</code>, <code>$nama_lengkap</code>, dan <code>$hashed_password</code>).</div>
    </div>
    <div class="checklist-item">
      <span class="check-box">&#10003;</span> <span class="check-label">Apakah ada kompleksitas/nested condition berlebihan?</span>
      <div class="check-answer">&rarr; <strong>YA</strong>. Terdapat struktur <code>if-elseif-else</code> bertingkat 3 level di dalam blok penanganan <code>try-catch</code> registrasi.</div>
    </div>

    <div class="sub-heading">Daftar Anomali — Kode</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%;" class="center">No</th>
          <th style="width: 28%;">File / Baris</th>
          <th>Deskripsi Anomali</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center"><strong>1</strong></td>
          <td><code>config/database.php</code><br>(Baris 7)</td>
          <td><strong>Hardcoded Inconsistency:</strong> Nilai <code>$db_name</code> tertulis <code>"bimbel_billa"</code>, padahal skema berkas database proyek adalah <code>bimbel_aiueo.sql</code>, memicu galat <em>Unknown database</em>.</td>
        </tr>
        <tr>
          <td class="center"><strong>2</strong></td>
          <td><code>views/auth/register.php</code><br>(Baris 47–50)</td>
          <td><strong>Fatal Null Pointer Dereference:</strong> Pemanggilan langsung <code>$stmt = $db->prepare(...)</code> tanpa memeriksa apakah <code>$db</code> bernilai <code>null</code> saat database gagal koneksi, menyebabkan fatal crash.</td>
        </tr>
        <tr>
          <td class="center"><strong>3</strong></td>
          <td><code>config/database.php</code><br>(Baris 20)</td>
          <td><strong>Information Disclosure:</strong> Pemanggilan <code>echo "Connection error: " . $exception->getMessage();</code> membocorkan detail sensitif arsitektur database langsung ke browser pengguna.</td>
        </tr>
        <tr>
          <td class="center"><strong>4</strong></td>
          <td><code>views/auth/register.php</code><br>(Baris 119)</td>
          <td><strong>Narrow Exception Handling:</strong> Blok <code>catch</code> hanya menangkap <code>PDOException</code>, mengabaikan hierarki <code>Error</code> (PHP 7/8), sehingga fatal runtime error memutus aliran halaman menjadi layar putih.</td>
        </tr>
      </tbody>
    </table>

    <!-- BAGIAN 3 -->
    <div class="section-banner">BAGIAN 3 — Static Analysis Tool</div>
    <div class="section-hint">Petunjuk: Langkah 5, ±35 menit. Jalankan HANYA pada fungsi/modul yang dipilih di Bagian 1.</div>
    <table class="kv-table">
      <tr>
        <td class="key" style="width: 25%;">Nama Tool & Versi/Rule</td>
        <td class="val">PHP CLI Built-in Syntax Checker (<code>php -l</code>), PHP 8.2 Strict Type & Nullability Inspection</td>
      </tr>
      <tr>
        <td class="key">Tanggal Dijalankan</td>
        <td class="val">12 November 2026</td>
      </tr>
    </table>

    <div class="sub-heading">Ringkasan Jumlah Issue:</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Kategori Issue</th>
          <th style="width: 14%;" class="center">Jumlah</th>
          <th style="width: 26%;" class="center">Severity Tertinggi</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Configuration & Target Database Mismatch</td>
          <td class="center">1</td>
          <td class="center"><span class="badge badge-critical">Critical</span></td>
        </tr>
        <tr>
          <td>Fatal Runtime (Null Pointer Dereference on <code>prepare()</code>)</td>
          <td class="center">2</td>
          <td class="center"><span class="badge badge-critical">Critical</span></td>
        </tr>
        <tr>
          <td>Design & Layout Architecture Flaw (Fixed 360px Constraint)</td>
          <td class="center">1</td>
          <td class="center"><span class="badge badge-major">Major</span></td>
        </tr>
        <tr>
          <td>Compatibility & Deprecation Warnings (PHP 8.2 Null Parameters)</td>
          <td class="center">2</td>
          <td class="center"><span class="badge badge-minor">Minor</span></td>
        </tr>
        <tr>
          <td>Coding Standard & Unused Variables (<code>$alamat</code>)</td>
          <td class="center">2</td>
          <td class="center"><span class="badge badge-info">Info / Low</span></td>
        </tr>
      </tbody>
    </table>

    <div class="sub-heading">3 Issue Teratas:</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%;" class="center">No</th>
          <th style="width: 32%;">File / Baris</th>
          <th>Deskripsi Issue</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center"><strong>1</strong></td>
          <td><code>config/database.php : 7</code></td>
          <td><span class="badge badge-critical">Critical</span> <strong>Database Mismatch:</strong> Objek database mengarah ke skema yang tidak eksis (<code>bimbel_billa</code>), memicu kegagalan total seluruh operasi registrasi (<em>SQLSTATE[HY000] [1049] Unknown database</em>).</td>
        </tr>
        <tr>
          <td class="center"><strong>2</strong></td>
          <td><code>views/auth/register.php : 50</code></td>
          <td><span class="badge badge-critical">Critical</span> <strong>Uncaught Fatal Error:</strong> Memanggil fungsi anggota <code>prepare()</code> pada nilai <code>null</code> saat koneksi gagal (<em>Uncaught Error: Call to a member function prepare() on null</em>).</td>
        </tr>
        <tr>
          <td class="center"><strong>3</strong></td>
          <td><code>views/auth/register.php : 164–380</code></td>
          <td><span class="badge badge-major">Major</span> <strong>Design Constraint Flaw:</strong> Struktur CSS mengunci tampilan pada lebar statis 360px (<code>.phone</code>), merusak prinsip ergonomi dan usability portal web modern.</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="page-footer">Halaman 2 dari 3</div>
</div>

<!-- HALAMAN 3 -->
<div class="page">
  <div class="page-header">Lembar Kerja Static Testing— Bab 3</div>
  <div class="content">
    <!-- BAGIAN 4 -->
    <div class="section-banner" style="margin-top: 0;">BAGIAN 4 — Klasifikasi Anomali & Perbaikan</div>
    <div class="section-hint">Petunjuk: Langkah 6–7, ±75 menit. Gabungkan temuan Bagian 2 & 3, tentukan status, lalu perbaiki 3 prioritas tertinggi.</div>
    <table class="data-table">
      <thead>
        <tr>
          <th style="width: 5%;" class="center">No</th>
          <th style="width: 25%;">Anomali</th>
          <th style="width: 13%;" class="center">Status</th>
          <th style="width: 14%;" class="center">Prioritas</th>
          <th>Perbaikan Dilakukan (khusus 3 prioritas tertinggi)</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td class="center"><strong>1</strong></td>
          <td>Database Mismatch (<code>bimbel_billa</code>) pada konfigurasi database</td>
          <td class="center"><span class="badge badge-defect">Defect</span></td>
          <td class="center"><span class="badge badge-p1">P1 (Tinggi)</span></td>
          <td><strong>Diperbaiki:</strong> Mengubah nama basis data default menjadi <code>bimbel_aiueo</code>, menambahkan fallback otomatis ke <code>bimbel_billa</code>, serta menyematkan fitur auto-create database dan auto-import <code>bimbel_aiueo.sql</code> saat startup.</td>
        </tr>
        <tr>
          <td class="center"><strong>2</strong></td>
          <td>Fatal Error <code>Call to a member function prepare() on null</code> di <code>register.php</code></td>
          <td class="center"><span class="badge badge-defect">Defect</span></td>
          <td class="center"><span class="badge badge-p1">P1 (Tinggi)</span></td>
          <td><strong>Diperbaiki:</strong> Menambahkan validasi guard <code>if (!$db) throw new Exception(...)</code>, memperluas penanganan exception menjadi <code>catch (Throwable $e)</code>, dan merender pesan galat ke alert Bootstrap yang informatif.</td>
        </tr>
        <tr>
          <td class="center"><strong>3</strong></td>
          <td>Tampilan antarmuka terdistorsi dalam bingkai HP 360px (<code>.phone</code>)</td>
          <td class="center"><span class="badge badge-defect">Defect</span></td>
          <td class="center"><span class="badge badge-p2">P2 (Sedang)</span></td>
          <td><strong>Diperbaiki:</strong> Menghapus wrapper <code>.phone</code> dan merombak total form registrasi menjadi <em>Modern Split-Screen Web Layout</em> dengan banner edukasi, font Plus Jakarta Sans, dan fitur interaktif toggle password.</td>
        </tr>
        <tr>
          <td class="center"><strong>4</strong></td>
          <td>Variabel <code>$alamat</code> tidak memiliki input pada form registrasi</td>
          <td class="center"><span class="badge badge-defect">Defect</span></td>
          <td class="center"><span class="badge badge-p3">P3 (Rendah)</span></td>
          <td>Dialokasikan untuk disempurnakan pada fitur pembaruan profil siswa di dashboard (bukan blocker registrasi awal).</td>
        </tr>
        <tr>
          <td class="center"><strong>5</strong></td>
          <td>Penggabungan input email & telepon dalam satu field (<code>email_or_phone</code>)</td>
          <td class="center"><span class="badge badge-tanya">Tanya</span></td>
          <td class="center"><span class="badge badge-p3">P3 (Rendah)</span></td>
          <td>Dikonfirmasikan kepada analis sistem/klien apakah field perlu dipisah menjadi dua input terpisah pada iterasi selanjutnya.</td>
        </tr>
      </tbody>
    </table>

    <!-- BAGIAN 5 -->
    <div class="section-banner">BAGIAN 5 — Refleksi Singkat</div>
    <div class="section-hint">Petunjuk: Langkah 8, ±15 menit.</div>

    <div class="reflection-q">1. Jenis anomali apa yang lebih mudah ditemukan lewat review manual vs static analysis tool, dan mengapa?</div>
    <div class="reflection-a">
      <strong>Jawaban:</strong><br>
      <strong>Review manual</strong> jauh lebih unggul dan efektif dalam menemukan <strong>anomali semantik, logika domain bisnis, dan ketidaksesuaian desain terhadap requirement</strong>.<br>
      <em>Contoh nyata:</em> Kesalahan penulisan nama database (<code>bimbel_billa</code>) di berkas konfigurasi tidak akan terdeteksi sebagai error oleh tool statis (seperti linter/checker) karena sintaks kodenya 100% valid secara gramatikal PHP. Namun, melalui review manual manusia, inkonsistensi terhadap konteks proyek Bimbel AIUEO langsung teridentifikasi. Hal serupa berlaku pada anomali tampilan (layout web desktop yang keliru dibungkus dalam bingkai smartphone 360px); tool analisa kode tidak mampu menilai aspek ergonomi, proporsi visual, dan kenyamanan pengguna (UX).<br>
      Sebaliknya, <strong>static analysis tool</strong> jauh lebih cepat, konsisten, dan akurat dalam mendeteksi <strong>kesalahan sintaksis kode, variabel tidak terpakai, potensi dereferensi null pointer, inkonsistensi tipe data, serta peringatan deprecation fungsi pada versi PHP modern (seperti PHP 8.2)</strong>.
    </div>

    <div class="reflection-q">2. Faktor apa (persiapan, checklist, jeda singkat, dll.) yang paling membantu Anda tetap objektif saat mereview karya sendiri dalam waktu terbatas ini?</div>
    <div class="reflection-a">
      <strong>Jawaban:</strong><br>
      Faktor-faktor yang paling membantu objektivitas saya selama proses review mandiri adalah:
      <ol>
        <li><strong>Penggunaan Checklist Terstruktur:</strong> Checklist memberikan panduan verifikasi yang eksplisit dan terstandarisasi. Saya diarahkan untuk memeriksa aspek-aspek kritis secara sistematis (seperti penanganan kondisi null, keamanan password hashing, dan validasi input) tanpa terpengaruh rasa percaya diri berlebih bahwa kode yang saya tulis sudah sempurna.</li>
        <li><strong>Jeda Singkat (Cooling-off Period):</strong> Memberikan jeda waktu sejenak (15 menit) sebelum mulai mereview sangat efektif melepaskan bias pencipta (<em>author bias</em>). Hal ini memungkinkan saya memposisikan diri kembali dengan sudut pandang penguji independen yang kritis (<em>adversarial mindset</em>).</li>
        <li><strong>Verifikasi Sintaks Otomatis (<code>php -l</code>):</strong> Menjalankan linter memberikan bukti empiris tanpa prasangka untuk memastikan integritas sintaks seluruh berkas sebelum masuk ke telaah logika yang lebih dalam.</li>
      </ol>
    </div>
  </div>
  <div class="page-footer">Halaman 3 dari 3</div>
</div>

</body>
</html>
"""

html_file = os.path.abspath("Lembar_Kerja_Static_Testing.html")
pdf_file = os.path.abspath("Lembar_Kerja_Static_Testing_Bimbel_AIUEO.pdf")
artifact_dir = r"C:\Users\bpkhe\.gemini\antigravity\brain\02a8e7e1-e78a-4f7f-8c78-abfd23cfdf58"
artifact_pdf = os.path.join(artifact_dir, "Lembar_Kerja_Static_Testing_Bimbel_AIUEO.pdf")

with open(html_file, "w", encoding="utf-8") as f:
    f.write(html_content)

chrome_path = r"C:\Program Files\Google\Chrome\Application\chrome.exe"
cmd = [
    chrome_path,
    "--headless=new",
    "--disable-gpu",
    "--no-sandbox",
    "--no-pdf-header-footer",
    f"--print-to-pdf={pdf_file}",
    html_file
]

print("Executing Chrome headless PDF generation...")
res = subprocess.run(cmd, capture_output=True, text=True)
print("Chrome return code:", res.returncode)

if os.path.exists(pdf_file):
    size = os.path.getsize(pdf_file)
    print(f"Generated PDF successfully: {pdf_file} ({size} bytes)")
    shutil.copyfile(pdf_file, artifact_pdf)
    print(f"Copied to artifact directory: {artifact_pdf}")
else:
    print("Failed to generate PDF!")
