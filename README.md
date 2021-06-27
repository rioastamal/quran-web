## Tentang

Alhamdulillah puji syukur ke hadirat Allah SWT. Sholawat serta salam semoga selalu tercurahkan kepada Nabi Muhammad SAW.

QuranWeb adalah project untuk menyajikan kitab suci Al-Quran dalam bentuk website yang ringan dan cepat. Prinsip pengembangan yang digunakan adalah penggunaan Javascript seminimal mungkin dan _zero dependencies_. One page served by one HTML file.

Sehingga perangkat dengan spesifikasi rendah pun dapat mengakses tanpa kesulitan. Fitur utama dari QuranWeb adalah:

* Cepat dan ringan
* Mobile web frienldy
* Penanda terakhir dibaca
* Terjemahan Bahasa Indonesia
* Mode malam untuk kenyamanan membaca
* Pergi ke nomor ayat tertentu dalam satu surah
* Setiap ayat memiliki URL sendiri dan dapat dibagikan
* Audio murottal untuk setiap ayat (sumber everyayah.com)

### To do

Beberapa fitur yang akan ditambahkan pada versi yang akan datang adalah:

* Bookmark ayat

## Latar Belakang

Latar belakang kenapa saya membuat project ini adalah karena pada handphone Blackberry Passport (SE) yang saya miliki tidak ada aplikasi atau mobile website Quran yang dapat berjalan dengan baik di perangkat saya tersebut.

Kebanyakan mobile site tersebut menggunakan terlalu banyak javascript sehingga lambat atau kadang tampilannya tidak sesuai dengan yang saya inginkan. Sehingga saya berinisiatif untuk membuat situs mobile Quran static yang ringan dan juga mudah digunakan. Tentunya sesuai selera saya karena untuk saya gunakan pribadi.

Dalam proses pembuatan itulah saya juga membuat beberapa project Al-Quran lain diantaranya.

- [quran-text](https://github.com/rioastamal/quran-text)
- [quran-json](https://github.com/rioastamal/quran-json)
- [quran-single-file](https://github.com/rioastamal/quran-single-file)

## Sumber

Sumber utama ayat-ayat Al-Quran dan terjemahannya dalam project ini didapat dari situs dan aplikasi resmi dari Kementrian Agama Republik Indonesia yang dapat diakses di https://quran.kemenag.go.id.

## Laporkan Kesalahan

Al-Quran adalah kitab suci sempurna yang diturunkan Allah SWT. segala bentuk kesalahan yang ada pada project ini adalah pasti karena kebodohan dan kekhilafan saya sendiri. Untuk itu mohon dengan hormat jika anda menemukan ada suatu kesalahan untuk menghubungi saya melalui email di **rio@rioastamal.net**.

## Build

Untuk melakukan build anda memerlukan PHP interpreter pada sistem anda. Hasil build akan disimpan dalam direktori `build/public`. Konten dari direktori tersebut yang dapat anda upload ke server.

Script ini memerlukan file quran dan terjemahan yang ada pada project [quran-json](https://github.com/rioastamal/quran-json).

### Cara menjalankan build

Semua konfigurasi build dapat diubah lewat environment variable. Proses build secara umum sangat cepat hanya memerlukan waktu kurang dari satu detik.

```
$ export QURAN_JSON_DIR=/path/to/directory/of/quran-json
$ php src/generator/generator.php
Generating website...done.
```

Berikut adalah daftar konfigurasi yang dapat diubah.


| Konfigurasi | Status | Keterangan |
|-------------|--------|------------|
| QURAN\_JSON\_DIR | **required** | Path ke quran-json project |
| QURAN\_BASE\_URL | optional | Base URL dari website contoh: https://quranweb.id. Tanpa slash diakhir. Default is tidak ada.
| QURAN\_BASE_MUROTTAL\_URL | optional | Base URL dari audio murottal. Tanpa slash diakhir. Default https://everyayah.com/data |
| QURAN\_BEGIN\_SURAH | optional | Awal surah. Default = 1 |
| QURAN\_END\_SURAH | optional | Akhir surah. Default = 114 |
| QURAN\_TEMPLATE_DIR | optional | Path ke template directory. Default = src/generator/template |
| QURAN\_APP\_NAME | optional | Nama dari website. Default = QuranWeb.
| QURAN\_ANALYTICS\_ID | optional | Google Analytics tracking id. Default tidak ada.
| QURAN\_OG\_IMAGE\_URL | optional | OpenGraph image url. Default tidak ada.

Isi dari direktori `build/` dapat anda hapus jika memang sudah tidak diperlukan.

```
$ rm -rf build/*
```

## Penulis

Project ini dibuat oleh Rio Astamal \<rio@rioastamal.net\>.

## Kontribusi

Cara termudah untuk kontribusi adalah dengan menggunakan QuranWeb, laporkan jika ada bugs atau kesalahan. Jika anda adalah web developer dan ingin melakukan kontribusi pada project ini silahkan lakukan Pull Request [PR] melalui Github.

## Lisensi

Project ini dilisensikan dibawah naungan MIT License. Lihat file LICENSE.md.
