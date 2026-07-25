# Panduan Penggunaan Modul Pengadaan Khusus
### Ditujukan Untuk: Admin Maintenance Baru (MRM System)

Selamat datang di Sistem Manajemen Perawatan Mesin (MRM). Dokumen ini bertujuan untuk memandu Anda dalam menggunakan **Modul Pengadaan Khusus**—sebuah sistem penanganan pengadaan (*Case Management*) suku cadang atau layanan non-rutin.

---

## 1. Tujuan Modul
Modul Pengadaan Khusus dirancang untuk mengelola dan melacak permintaan suku cadang/jasa non-rutin dari awal pengajuan, persetujuan bertingkat, pembelian, hingga penerimaan fisik barang di gudang. 

Sistem ini berfungsi sebagai **Case Management**, di mana setiap pengajuan memiliki **Case Number unik (PC-YYYYMM-XXXX)** sebagai identitas yang tidak akan pernah berubah (*immutable*).

---

## 2. Kapan Menggunakan Modul Ini?

### ✓ Gunakan Modul Ini Jika:
*   Suku cadang yang dibutuhkan **tidak tersedia di gudang** dan berstatus non-rutin.
*   Memerlukan jasa eksternal seperti pembubutan (*machining*), fabrikasi (*fabrication*), atau jasa perbaikan khusus (*service*).
*   Mesin mengalami breakdown total (*Machine Down*) akibat komponen rusak parah dan tidak ada stok pengganti.

### ✗ JANGAN Gunakan Modul Ini Jika:
*   Suku cadang bersifat rutin dan sudah terjadwal pengisiannya di inventaris gudang utama (gunakan modul gudang/sparepart reguler).
*   Anda ingin memproses negosiasi harga, perbandingan vendor, pajak, atau pembayaran finansial. **Proses keuangan ini sepenuhnya dikelola di ERP Perusahaan**, bukan di MRM.

---

## 3. Alur Pengadaan Khusus
Alur kerja modul ini bersifat sekuensial dan melibatkan beberapa penanggung jawab (*Current Owner*):

```
[Draft] ➔ [Submit] ➔ [Pending Kabag] ➔ [Pending Direktur] ➔ [Processing (PO)] ➔ [Waiting Delivery] ➔ [Ready Pickup] ➔ [Closed]
```

1.  **Draft**: Permintaan dibuat oleh Admin Maintenance. Status mesin dan detail spesifikasi dicatat.
2.  **Submit**: Kasus diajukan.
3.  **Approval Kabag**: Kabag Maintenance memeriksa spesifikasi teknis komponen.
4.  **Approval Direktur**: Direktur menyetujui alokasi kebutuhan mendesak ini.
5.  **Purchasing**: Bagian Purchasing menerbitkan Purchase Order (PO) di sistem ERP dan menginput nomor PO & nama vendor ke MRM.
6.  **Waiting Delivery**: Menunggu barang dikirim oleh vendor.
7.  **Ready Pickup**: Barang telah sampai di gudang. Admin Sparepart mengonfirmasi kedatangan fisik barang dan menentukan lokasi rak penyimpanan.
8.  **Closed**: Admin Maintenance mengambil barang dari gudang sparepart dan menandatangani konfirmasi serah terima fisik di sistem.

---

## 4. Arti Setiap Status & Penanggung Jawab (Current Owner)

| Status | Arti Status | Penanggung Jawab (Current Owner) | Tindakan yang Diperlukan |
| :--- | :--- | :--- | :--- |
| **DRAFT** | Pengajuan baru sedang dirancang, belum dikirim. | **Admin Maintenance** | Melengkapi data, menyimpan, atau menekan *Submit*. |
| **PENDING KABAG** | Menunggu verifikasi teknis dari Kepala Bagian. | **Kabag Maintenance** | Kabag menyetujui, menolak, atau mengembalikan (butuh info). |
| **PENDING DIR** | Menunggu persetujuan tingkat direksi/manajemen. | **Direktur** | Direktur menyetujui atau mengembalikan permintaan. |
| **NEED INFO** | Permintaan dikembalikan karena spesifikasi kurang jelas/salah. | **Admin Maintenance** | Memperbaiki isi draf dan mengajukan kembali (*Resubmit*). |
| **PROCESSING** | Permintaan disetujui, sedang diproses beli di ERP. | **Purchasing** | Membeli barang dan memasukkan nomor PO resmi ke sistem. |
| **WAITING DELIVERY**| PO telah terbit, barang sedang dalam perjalanan kurir. | **Purchasing** | Menunggu kurir mengirimkan barang fisik ke pabrik. |
| **READY TO PICKUP**| Barang telah sampai dan terdata di rak gudang sparepart. | **Admin Maintenance** | Mengambil fisik barang ke gudang dan menekan *Confirm Pickup*. |
| **CLOSED** | Barang telah diambil oleh teknisi. Alur kerja selesai. | **None** | Selesai. Kasus ditutup secara permanen. |
| **CANCELLED** | Kasus dibatalkan resmi di tengah jalan. | **None** | Kasus diarsipkan sebagai pembatalan. |

---

## 5. Cara Pengoperasian

### A. Membuat Pengadaan Baru
1.  Buka menu **Pengadaan Khusus** dari sidebar kiri.
2.  Klik tombol **+ Buat Pengadaan Baru** di kanan atas halaman.
3.  Isi formulir dengan lengkap:
    *   *Nama Barang*: Spesifikasi jelas (merk, tipe, ukuran).
    *   *Mesin Terkait*: Pilih mesin yang membutuhkan suku cadang tersebut.
    *   *Kategori*: Pilih kategori yang sesuai (Mechanical, Electrical, Hydraulic, dll).
    *   *Urgensi*: Tentukan urgensi (Normal, Urgent, atau Emergency jika produksi terhenti).
    *   *Target Tanggal Dibutuhkan*: Batas akhir barang harus tiba.
    *   *Machine Down*: Centang jika mesin saat ini mogok/breakdown akibat kerusakan ini.
    *   *Deskripsi Kerusakan*: Tuliskan gejala kerusakan fisik secara detail.
    *   *Alasan Pengadaan*: Jelaskan argumen mengapa komponen wajib segera dibeli.
4.  Pilih tombol:
    *   **Simpan Draft**: Jika data masih ingin direvisi nanti.
    *   **Submit Pengadaan**: Jika data sudah valid dan ingin langsung diteruskan ke Kabag.

### B. Memantau Progress & Menggunakan Filter
Di halaman Index utama, Anda disediakan berbagai alat bantu visual:
1.  **Summary Cards (Kartu Ringkasan)**: Klik salah satu kartu (Draft, Pending Approval, dll) untuk melihat daftar kasus di status tersebut secara cepat.
2.  **Filter Pencarian**: 
    *   Ketik kata kunci di kolom **Search** (bisa mencari No Case, Nama Barang, Mesin, Owner, atau Alasan).
    *   Gunakan dropdown Status, Urgensi, Kategori, atau Owner untuk mempersempit pencarian.
    *   Centang **Tugasku Saja (My Cases)** untuk melihat tugas-tugas yang saat ini tertahan di tangan Anda sendiri.
    *   Klik **Reset Filter** jika ingin membersihkan semua parameter penyaringan.

---

## 6. FAQ (Pertanyaan Sering Diajukan)

**P: Mengapa saya tidak bisa menghapus (DELETE) kasus yang sudah disubmit?**
*J: Pengadaan adalah dokumen bisnis resmi yang tercatat di audit perusahaan. Kasus yang sudah berstatus non-Draft tidak boleh dihapus. Jika ingin dibatalkan, gunakan tombol "Batalkan Request" (Status akan berubah menjadi Cancelled).*

**P: Saya mengklik "Tugasku Saja (My Cases)" tapi kosong, apa artinya?**
*J: Artinya tidak ada dokumen pengadaan khusus yang saat ini membutuhkan tindakan persetujuan atau aksi dari peran/role Anda. Dokumen tersebut sedang diproses oleh pihak lain.*

**P: Bagaimana saya tahu barang saya sudah sampai di pabrik?**
*J: Status pelacakan kasus akan berubah menjadi **READY TO PICKUP**, dan kolom "Current Owner" akan menunjuk kembali ke Anda. Di halaman detail, Anda dapat melihat informasi "Lokasi RAK Penyimpanan" yang diinput oleh Admin Sparepart.*
