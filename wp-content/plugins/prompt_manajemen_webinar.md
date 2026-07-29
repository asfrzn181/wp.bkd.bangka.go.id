# Prompt pengembangan: Plugin WordPress — Sistem Manajemen Webinar Instansi

Gunakan dokumen ini sebagai prompt/spesifikasi untuk membangun plugin WordPress custom. Ikuti struktur data, alur bisnis, dan keputusan teknis di bawah ini secara ketat — jangan menyederhanakan relasi antar entitas tanpa konfirmasi.

## 1. Konteks & tujuan

Bangun plugin WordPress untuk manajemen webinar milik instansi pemerintah, dengan kebutuhan khusus:
- Menampilkan daftar webinar (akan datang & selesai) di halaman WordPress via shortcode
- Pendaftaran peserta dengan form custom seperti Google Form (field bisa dikustomisasi admin)
- Absensi peserta dengan form custom terpisah dari form pendaftaran
- Penerbitan sertifikat berjenjang mengikuti konvensi administrasi ASN: **SK Minut** (dokumen master, ditandatangani basah atau via TTE Srikandi di luar sistem) dan **Petikan** (salinan resmi per peserta, dengan QR verifikasi buatan sistem)

## 2. Pendekatan teknis

- **Custom Post Type `webinar`**: judul, deskripsi (editor Gutenberg), featured image, permalink native WP, shortcode `[webinar_list]` dan `[webinar_detail id="123"]`
- **Custom tables** (pakai `dbDelta`) untuk data teknis & relasional — jangan pakai `postmeta`/EAV untuk data yang sering di-query dengan filter tanggal atau join
- Field jawaban form disimpan sebagai **JSON** (`submission_data`), bukan EAV, agar skema fleksibel tanpa migrasi setiap admin ubah field
- Status "akan datang / selesai" dihitung on-the-fly dari `start_datetime`/`end_datetime`, bukan kolom statis

## 3. Skema database

### `wp_webinar_meta` (1:1 dengan CPT `webinar`)
| Kolom | Tipe | Keterangan |
|---|---|---|
| post_id | bigint FK | referensi ke wp_posts |
| start_datetime | datetime | |
| end_datetime | datetime | |
| zoom_link | varchar | |
| youtube_link | varchar | |
| cert_number_pattern | varchar | pola nomor petikan |

### `wp_webinar_form_field`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| webinar_id | bigint FK | |
| form_type | enum | `registration` / `attendance` |
| field_key | varchar | |
| label | varchar | |
| field_type | varchar | text, textarea, email, phone, radio, checkbox, select, date, file_upload |
| options | json | untuk radio/checkbox/select |
| is_required | boolean | |
| is_identity_field | boolean | khusus form attendance — field yang otomatis terisi dari data registrant (nama/email), terkunci dari edit |
| sort_order | int | |

### `wp_webinar_registrant`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| webinar_id | bigint FK | |
| unique_token | varchar | dikirim via email, dipakai untuk buka form absensi tanpa login WP |
| email | varchar | |
| submission_data | json | jawaban form pendaftaran |
| registered_at | datetime | |

### `wp_webinar_attendance`
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| webinar_id | bigint FK | |
| registrant_id | bigint FK | wajib cocok dengan registrant yang sudah daftar |
| submission_data | json | jawaban form absensi (di luar identity field) |
| attended_at | datetime | |

### `wp_webinar_sk` (SK Minut — 1 SK per 1 webinar)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| webinar_id | bigint FK unique | |
| sk_number | varchar | |
| sk_date | date | |
| signing_official | varchar | |
| sk_template_file | varchar | path template docx untuk draft |
| sk_draft_file | varchar | hasil generate draft (belum sah) |
| signing_method | enum | `wet_signature` / `tte_srikandi` |
| sk_signed_file | varchar | file final hasil unggahan admin (scan basah atau unduhan Srikandi) |
| status | enum | `draft` → `menunggu_ttd` → `final` |

### `wp_webinar_certificate` (Petikan — banyak per SK)
| Kolom | Tipe | Keterangan |
|---|---|---|
| id | bigint PK | |
| sk_id | bigint FK | wajib merujuk SK berstatus final |
| registrant_id | bigint FK | |
| petikan_number | varchar | |
| file_path_pdf | varchar | |
| qr_verification_hash | varchar | unik, dipakai di halaman verifikasi publik |
| status | enum | `active` / `revoked` |
| revoked_at | datetime nullable | |
| revoked_by | bigint nullable | user ID admin |
| revoke_reason | text nullable | |
| generated_at | datetime | |

## 4. Alur bisnis wajib

1. **Pendaftaran**: peserta isi form custom (`form_type=registration`) → sistem buat `wp_webinar_registrant` dengan `unique_token` unik → kirim email berisi link personal (`?token=xxx`) dan QR code yang meng-encode link yang sama
2. **Absensi**: peserta buka link/scan QR → sistem validasi token ke registrant → tampilkan form absensi custom (`form_type=attendance`) dengan field `is_identity_field` sudah terisi read-only → submit tercatat di `wp_webinar_attendance`. Dukung juga skenario panitia yang men-scan QR di lokasi (bukan peserta sendiri yang scan)
3. **Penerbitan SK Minut** (dilakukan admin setelah webinar selesai & absensi lengkap):
   - Generate draft dari `sk_template_file` (docx placeholder) → status `draft`
   - Draft diajukan untuk ditandatangani **di luar sistem** (cetak untuk tanda tangan basah, atau diproses melalui portal Srikandi untuk TTE) → status `menunggu_ttd`
   - Admin unggah kembali file yang sudah sah (`sk_signed_file`) → status `final`
4. **Generate petikan (batch, otomatis begitu SK final)**: sistem buat satu `wp_webinar_certificate` per peserta yang tercatat di `wp_webinar_attendance` untuk webinar tersebut, masing-masing dengan nomor petikan dan `qr_verification_hash` sendiri, mereferensikan `sk_number` induknya di badan dokumen
5. **Revoke**: admin bisa mencabut satu petikan (ubah status jadi `revoked`) tanpa mempengaruhi SK maupun petikan peserta lain. File PDF tidak dihapus — halaman verifikasi publik menampilkan status "telah dicabut", bukan 404

## 5. Generate dokumen (docx → PDF)

- Template SK dan template petikan berbentuk `.docx` dengan placeholder (format `${variable}`)
- Gunakan `PhpOffice/PhpWord` (TemplateProcessor) untuk replace placeholder
- Convert docx hasil isian ke PDF via LibreOffice headless: `soffice --headless --convert-to pdf`
- QR code untuk petikan dibuat dengan library seperti `chillerlan/php-qrcode`, mengarah ke halaman verifikasi publik `https://site.com/verifikasi-petikan/{qr_verification_hash}`
- SK Minut **tidak** menggunakan QR buatan sistem — keabsahannya berasal dari tanda tangan basah atau TTE Srikandi itu sendiri

## 6. Fitur admin yang dibutuhkan

- CRUD webinar (judul, deskripsi, tanggal, link zoom/youtube, upload template docx SK & petikan)
- Form builder generik (dipakai untuk kedua `form_type`), drag reorder field, pilih tipe field, tandai required/identity field
- Dashboard peserta: daftar registrant, status hadir/belum, filter per webinar
- Manajemen SK: generate draft, ubah metode tanda tangan, upload file signed, lihat status
- Manajemen petikan: lihat daftar petikan per SK, revoke dengan alasan, download ulang PDF
- Role/permission: minimal 2 role — admin (bisa generate SK & petikan) dan panitia/operator (kelola webinar & absensi, tanpa akses SK)

## 7. Fitur publik yang dibutuhkan

- `[webinar_list]` — daftar webinar akan datang & selesai
- `[webinar_detail id="x"]` — detail webinar, link zoom/youtube (tampil sesuai jadwal), tombol daftar
- Halaman form pendaftaran publik
- Halaman form absensi (akses via token link atau QR)
- Halaman verifikasi petikan publik (`/verifikasi-petikan/{hash}`) — tampilkan status active/revoked, nama peserta, nomor petikan, referensi nomor SK

## 8. Non-fungsional

- Semua input publik wajib melalui nonce WordPress & sanitasi standar (`sanitize_text_field`, dsb)
- Query list webinar & registrant harus pakai index yang tepat pada `webinar_id` dan `start_datetime`/`end_datetime`
- Proses generate PDF batch (banyak petikan sekaligus) sebaiknya dijalankan sebagai background job (WP Cron / Action Scheduler), bukan sinkron di request HTTP, untuk menghindari timeout