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

Berikut ini bila gagal

````markdown
{
  "name":
  "site":
  "error": true,
  "message": "Nomor resi tidak ditemukan."
}
````

<hr>

Shield: [![CC BY-NC-SA 4.0][cc-by-nc-sa-shield]][cc-by-nc-sa]

This work is licensed under a
[Creative Commons Attribution-NonCommercial-ShareAlike 4.0 International License][cc-by-nc-sa].

[![CC BY-NC-SA 4.0][cc-by-nc-sa-image]][cc-by-nc-sa]

[cc-by-nc-sa]: http://creativecommons.org/licenses/by-nc-sa/4.0/
[cc-by-nc-sa-image]: https://licensebuttons.net/l/by-nc-sa/4.0/88x31.png
[cc-by-nc-sa-shield]: https://img.shields.io/badge/License-CC%20BY--NC--SA%204.0-lightgrey.svg
