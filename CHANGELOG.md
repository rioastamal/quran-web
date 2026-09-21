Penambahan atau perubahan fitur pada QuranWeb akan selalu didokumentasikan pada file ini.

## Versi 1.12.2

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Perbaikan bug navigasi bookmark: klik penanda terakhir dibaca (Surah atau Juz) pada halaman yang sama kini langsung scroll ke nomor ayat target dan tidak lagi tertimpa oleh posisi scroll sebelumnya (terutama pada browser Chrome / Safari di iPad)
- Penambahan koreksi penyesuaian scroll otomatis saat halaman dimuat dengan hash anchor setelah font LPMQ selesai di-render
- Memindahkan posisi nomor ayat dari pojok kiri atas ke baris toolbar (sisi paling kiri sejajar dengan tombol bookmark, favorit, tafsir, dan audio) dengan ukuran kotak yang sama dengan tombol-tombol tersebut

## Versi 1.12.1

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Migrasi seluruh konfigurasi dan penanda baca (ayat & juz terakhir dibaca, mode malam, terjemahan, ukuran font, qari murottal) dari Cookie ke `localStorage` dengan fitur migrasi otomatis dari data cookie lama
- Penanda baca ayat atau juz yang baru ditandai kini otomatis disinkronkan ke cloud secara realtime (auto-sync) jika perangkat sudah terhubung
- Format keterangan waktu sinkronisasi disesuaikan menggunakan format 24 jam yang umum di Indonesia (DD/MM/YYYY, HH:mm:ss)
- Perbaikan bug: tombol Back to Top pada floating bar yang tidak terlihat pada tema mode terang (diperbaiki menggunakan ikon SVG panah atas dan penyesuaian kontras CSS)

## Versi 1.12

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Penambahan fitur Sinkronisasi Cloud (Cloud Sync) untuk menyinkronkan penanda baca (ayat dan juz terakhir dibaca), daftar ayat favorit, serta preferensi tampilan antar-perangkat menggunakan kode sinkronisasi unik tanpa perlu login akun
- Dukungan pembuatan kode acak otomatis maupun kode kustom sesuai keinginan pengguna

## Versi 1.11.2

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Menyatukan tombol Menu dan Back to Top ke dalam satu komponen floating rounded bar yang dapat digeser (draggable) ke posisi mana saja oleh pengguna
- Memperbaiki layout halaman menu (overlay fixed) agar tidak ada ruang kosong di bagian bawah
- Menjaga posisi scroll layar tetap stabil saat halaman menu dibuka maupun ditutup
- Menambahkan dukungan tombol Escape pada keyboard untuk menutup menu

## Versi 1.11.1

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Menghilangkan tombol back to top di bawah setiap ayat
- Mengubah tombol back to top menjadi floating button di pojok kiri bawah (default) yang dapat digeser (draggable) ke posisi mana saja oleh pengguna
- Posisi tombol floating back to top tersimpan otomatis di browser

## Versi 1.11

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Penambahan fitur ayat favorit

## Versi 1.10.1

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Perbaikan bug: klik pada ayah di halaman Juz kini otomatis scroll ke ayah berikutnya seperti halaman Surah

## Versi 1.10

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Penambahan halaman Juz yang menampilkan 30 Juz berdasarkan Standar Mushaf Indonesia
- Setiap Juz memiliki halaman tersendiri yang dapat diakses melalui URL /juz/{nomor_juz}/
- Terdapat halaman Daftar Juz yang memudahkan navigasi ke seluruh 30 Juz
- Penanda terakhir dibaca kini mendukung dua sumber: halaman Surah dan halaman Juz secara terpisah
- Navigasi prev/next pada halaman Juz dengan wrap-around dari Juz 30 ke Juz 1 dan sebaliknya
- Menu samping kini menampilkan tautan Daftar Juz

## Versi 1.9

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Penambahan fitur ukuran font (Zoom In / Zoom Out) untuk kenyamanan membaca
- Terdapat tombol reset untuk mengembalikan ukuran font ke ukuran semula
- Indikator persentase ukuran font yang aktif ditampilkan secara langsung pada menu
- Perubahan ukuran font tersimpan otomatis dan tetap konsisten saat berpindah halaman

## Versi 1.8

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Menghilangkan konfigurasi Google Analytics
- Menambahkan konfigurasi untuk kode RAW HTML pada tag HEAD
- Helper baru functions.sh untuk menjalankan via Docker

## Versi 1.7.2

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

- Perbaikan performa pada mode malam dan terjemahan
- URL audio murottal sekarang diarahkan ke server everyayah.com
- QURAN_BASE_URL sekarang optional
- QURAN_BASE_MUROTTAL_URL sekarang default ke https://everyayah.com/data

## Versi 1.7.1

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Bug Fix: Ayat otomatis scroll ke berikutnya jika salah satu tombol ayat ditekan. Misal tombol Play Murottal.

## Versi 1.7

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Penggunakan CSS font-display swap untuk modern browser
* Pengguna dapat pergi ke ayat berikutnya dengan melakukan klik ayat atau menekan tombol Enter. Hanya berlaku pada mode surat bukan ayat tunggal.

## Versi 1.6

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Pengguna dapat memilih ayat yang akan diulang. Contoh format input "5,6,11-20,28".

## Versi 1.5

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Penambahan fitur audio murottal untuk setiap ayat (sumber everyayah.com)
* Autoscroll ke ayat yang diputar ketika diulang per surah
* Terdapat 3 pilihan qori yaitu Sheikh Sudais, Sheikh Al-Hussary dan Sheikh Alafasy

## Versi 1.4

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Pencarian nama surah pada halaman Daftar Surah

## Versi 1.3

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Penambahan tafsir pada setiap ayat
* Setiap ayat memiliki URL sendiri dan dapat dibagikan (shareable)

## Versi 1.2

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Pergi ke nomor ayat tertentu dalam satu surah
* Back to top

## Versi 1.1

Perubahan utama yang dilakukan pada QuranWeb diantaranya:

* Penambahan sitemap untuk SEO
* Fitur baru: Penanda terakhir dibaca
* Peningkatan performa: Mengganti window.onload dengan DOMContentLoaded

## Versi 1.0

Ini adalah release pertama dari QuranWeb. Fitur-fitur atau keunggulan yang dimiliki diantaranya:

* Cepat dan ringan
* Mobile web frienldy
* Terjemahan Bahasa Indonesia
* Mode malam untuk kenyamanan membaca
