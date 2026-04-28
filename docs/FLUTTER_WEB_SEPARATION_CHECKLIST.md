# Flutter vs Web Laravel Separation Checklist

Tujuan:
- Web Laravel hanya untuk role internal: manager (level 1), admin (level 2), kasir (level 3).
- Mobile Flutter hanya untuk customer dan courier lewat API (tanpa halaman web mobile).

## Status Implementasi Saat Ini

- Selesai: route Flutter dipindah ke `routes/api.php` untuk customer/courier + push-ready notification.
- Selesai: kontrak lama `mobile/admin-kasir/*` dinonaktifkan dari routing.
- Selesai: route web dibatasi internal-only (admin/manager/kasir) untuk dashboard operasional.
- Catatan: autentikasi API saat ini masih middleware `auth` berbasis guard web; untuk produksi Flutter disarankan lanjut ke token-based auth (mis. Sanctum/JWT) agar benar-benar stateless.

## 1) File/Route yang Perlu Dipindah ke API Layer Flutter

Status saat ini: endpoint mobile masih ada di web routes.

- Pindahkan route group berikut dari `routes/web.php` ke route API khusus (buat file `routes/api.php` bila belum ada):
  - `mobile/customer/*`
  - `mobile/courier/*`
  - `notifications/push-ready`
- Tujuan akhir:
  - Web routes: hanya HTML/internal operations.
  - API routes: kontrak JSON untuk Flutter customer/courier.

File sumber:
- `routes/web.php`
- `app/Http/Controllers/MobileRoleApiController.php`
- `app/Http/Controllers/NotificationController.php`

## 2) File/Surface Mobile Internal yang Perlu Dihapus dari Kontrak Flutter

Jika Flutter hanya customer/courier, endpoint mobile internal berikut harus dihapus dari kontrak mobile:

- `MobileRoleApiController::adminKasirShipments`
- `MobileRoleApiController::adminKasirPayments`
- Route prefix `mobile/admin-kasir/*`

File terkait:
- `app/Http/Controllers/MobileRoleApiController.php`
- `routes/web.php`

Catatan: bila internal tetap butuh data ringkas serupa, buat endpoint baru bernama internal (mis. `/internal/mobile-dashboard/*`) di web internal, bukan bagian kontrak Flutter.

## 3) File View Web yang Tidak Dipakai untuk Mobile

Jangan dipakai untuk Flutter, tetap web-only atau sederhanakan:

- `resources/views/customer-portal/*` (jika customer full migrasi ke Flutter)
- `resources/views/dashboard.blade.php` lama (monolitik) — kandidat deprecate setelah 3 dashboard role-based stabil.

## 4) File yang Perlu Ditambah untuk API Separation (Belum Ada)

- `routes/api.php` (baru)
- Middleware auth API mobile (token/sanctum/jwt sesuai pilihan)
- Resource transformer untuk payload mobile agar kontrak stabil versi

## 5) Perubahan Dokumentasi yang Wajib

- Update README arsitektur role channel:
  - Web: manager/admin/kasir
  - Mobile: customer/courier
- Tambahkan matrix endpoint ownership (web vs mobile API) agar tim tidak mencampur route baru.

## 6) Urutan Migrasi Aman (Disarankan)

1. Buat `routes/api.php` dan duplikasi route mobile customer/courier + push-ready ke API.
2. Flutter pindah konsumsi endpoint API baru.
3. Freeze route mobile lama di web (deprecated warning di docs).
4. Hapus route mobile lama di web setelah satu siklus rilis.
5. Hapus endpoint `mobile/admin-kasir/*` dari kontrak mobile.

## 7) Acceptance Criteria

- Tidak ada route `mobile/*` di web routes untuk kebutuhan Flutter.
- Tidak ada payload admin/manager/kasir di response yang ditujukan Flutter customer/courier.
- Dashboard web hanya bisa diakses manager/admin/kasir.
- Semua test mobile customer/courier lulus via API routes baru.
