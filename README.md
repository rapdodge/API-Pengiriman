# API-Pengiriman
Repositori ini berisi skrip PHP Native untuk mempermudah mengecek resi pengiriman

Hasil diusahakan agar serupa, berikut ini adalah gambaran hasil keluaran yang <b>berhasil</b>

````markdown
{
  "name":
  "site":
  "error": false,
  "message":
  "info": {
    "no_awb":
    "service":
    "status":
    "tanggal_kirim":
    "tanggal_terima":
    "harga":
    "berat":
    "catatan":
  },
  "pengirim": {
    "nama":
    "phone":
    "alamat":
  },
  "penerima": {
    "nama":
    "nama_penerima":
    "phone":
    "alamat":
  },
  "history": [
    {
      "tanggal":
      "posisi":
      "message":
    }
  ]
}
````

Berikut ini bila <b>gagal</b>

````markdown
{
  "name":
  "site":
  "error": true,
  "message": "Nomor resi tidak ditemukan."
}
````

<h3>Penggunaan:</h3>
http(s)://domain_atau.ip/lokasi/skrip/pengiriman.php?kurir=(kurir)&resi=(resi)

<h3>Jasa pengiriman yang saat ini sudah bisa digunakan:</h3>

````markdown
1. AnterAja | anteraja
2. NinjaXpress | ninja
3. Pos Indonesia | pos
4. SiCepat | sicepat
5. TIKI | tiki

Selanjutnya: [?]
Bila ada saran mengenai jasa pengiriman, bisa buka isu baru, dan sertakan tautan API yang bisa diambil dan metode yang digunakan :)
````

<hr>
<h3>Donasi</h3>
<details>
  <summary>Donasi dengan QRIS</summary>
  <img src="https://github.com/rapdodge/API-Pengiriman/raw/main/.github/QRIS.jpg" style="width:50%;height:50%;">
</details>
<hr>

Shield: [![CC BY-NC-SA 4.0][cc-by-nc-sa-shield]][cc-by-nc-sa]

This work is licensed under a
[Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License][cc-by-nc-sa].

[![CC BY-NC-SA 4.0][cc-by-nc-sa-image]][cc-by-nc-sa]

[cc-by-nc-sa]: http://creativecommons.org/licenses/by-nc-sa/4.0/
[cc-by-nc-sa-image]: https://licensebuttons.net/l/by-nc-sa/4.0/88x31.png
[cc-by-nc-sa-shield]: https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-lightgrey.svg
