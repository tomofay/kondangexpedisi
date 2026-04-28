# Error Scenarios Runbook

Dokumen ini mendefinisikan skenario error utama yang memang diantisipasi sistem, termasuk cara deteksi, respon otomatis, langkah manual, bukti audit, dan prioritas penanganan.

## Tujuan

- Menyamakan standar respon insiden antara tim admin, operasional, dan developer.
- Menghindari data setengah jadi saat terjadi gangguan.
- Memastikan setiap koreksi manual dapat ditelusuri saat dispute.

## Referensi Endpoint dan Komponen

- Queue unresolved dan retry: [app/Http/Controllers/ErrorLogController.php](app/Http/Controllers/ErrorLogController.php)
- Tracking failure handling: [app/Http/Controllers/ShipmentTrackingController.php](app/Http/Controllers/ShipmentTrackingController.php)
- Midtrans callback handling: [app/Http/Controllers/MidtransWebhookController.php](app/Http/Controllers/MidtransWebhookController.php)
- Retry job Midtrans: [app/Jobs/RetryMidtransCallbackJob.php](app/Jobs/RetryMidtransCallbackJob.php)
- Retry job Tracking: [app/Jobs/RetryTrackingSyncJob.php](app/Jobs/RetryTrackingSyncJob.php)
- Audit trail service: [app/Services/AuditLogService.php](app/Services/AuditLogService.php)
- Operational issue service: [app/Services/OperationalIssueService.php](app/Services/OperationalIssueService.php)

## Severity Matrix

- P1 Critical: transaksi finansial, callback payment, data shipment final.
- P2 High: tracking gagal, sinkronisasi status, mismatch operasional.
- P3 Medium: data master tidak lengkap, input salah user, duplikasi non-final.

## Daftar Skenario Error

1. Payment gateway timeout
- Trigger: request ke payment gateway tidak mendapat respon dalam batas waktu.
- Dampak: status payment tidak pasti, potensi user melakukan retry berulang.
- Deteksi: error log module integration.midtrans dan payment, payment processing_status error.
- Respon otomatis: tandai payment error, catat error log, masukkan ke unresolved queue.
- Respon manual: admin cek payload, verifikasi status transaksi, jalankan retry job dari queue atau resolve manual.
- Bukti audit: action payment.midtrans_callback.retry atau payment.manual_override dengan source jelas.
- Prioritas: P1.

2. Callback Midtrans telat atau gagal
- Trigger: callback tidak masuk tepat waktu, signature tidak valid, atau proses callback gagal.
- Dampak: payment dan shipment payment_status tidak sinkron.
- Deteksi: action payment.midtrans_callback_failed, error log integration.midtrans.
- Respon otomatis: simpan error, update integration status, auto-dispatch retry callback untuk failure proses internal.
- Respon manual: admin compare order_id, transaction_status, lalu retry endpoint per error log.
- Bukti audit: changed_fields pada audit log callback/retry/override.
- Prioritas: P1.

3. Tracking event dobel
- Trigger: request tracking sama dikirim dua kali oleh client/kurir atau retry jaringan.
- Dampak: timeline shipment berisi event duplikat dan membingungkan CS/customer.
- Deteksi: payload tracking identik dalam rentang waktu pendek pada shipment yang sama.
- Respon otomatis: catat sebagai warning/error queue bila gagal sinkronisasi.
- Respon manual: admin validasi event_at dan status_id, hapus/normalisasi event duplikat sesuai SOP.
- Bukti audit: shipment_tracking.update atau shipment_tracking.retry_update dengan changed_fields.
- Prioritas: P2.

4. Resi tidak sinkron
- Trigger: tracking_number tidak cocok dengan payment/order atau status berbeda antar modul.
- Dampak: customer melihat status tidak sesuai, SLA pengiriman sulit diverifikasi.
- Deteksi: mismatch antara shipment, tracking, dan payment pada investigasi queue.
- Respon otomatis: tandai shipment processing_status error jika sinkronisasi gagal.
- Respon manual: lakukan rekonsiliasi shipment by tracking_number dan update terkendali.
- Bukti audit: source user_action untuk koreksi manual, system_retry untuk sinkronisasi otomatis.
- Prioritas: P2.

5. Kurir tidak tersedia
- Trigger: assignment meminta courier_id yang tidak aktif, tidak valid, atau tidak bisa dipakai.
- Dampak: shipment tertahan, assignment gagal.
- Deteksi: error saat assign kurir/kendaraan, queue task operasional meningkat.
- Respon otomatis: rollback transaksi assignCourier bila create tracking assignment gagal.
- Respon manual: pilih kurir pengganti sesuai branch, catat alasan.
- Bukti audit: shipment.assign_courier dengan before_state dan after_state.
- Prioritas: P2.

6. Kendaraan tidak tersedia
- Trigger: vehicle_id tidak valid atau tidak bisa dipakai saat assignment.
- Dampak: assignment tidak tereksekusi penuh.
- Deteksi: request assign courier gagal atau tidak lolos validasi operasional.
- Respon otomatis: rollback transaksi assign bila proses lanjutan gagal.
- Respon manual: re-assign vehicle alternatif dan catat alasan operasional.
- Bukti audit: perubahan vehicle_id di changed_fields.
- Prioritas: P2.

7. Cabang tujuan tidak punya zone
- Trigger: destination branch tidak memiliki zone_id aktif.
- Dampak: kalkulasi ongkir gagal.
- Deteksi: validasi zone_id gagal pada create/update shipment.
- Respon otomatis: blok proses normal, minta perbaikan data master atau manual override.
- Respon manual: gunakan manual pricing override dengan alasan wajib.
- Bukti audit: shipment.manual_override dengan correction_reference.
- Prioritas: P3.

8. Rate card tidak ketemu
- Trigger: kombinasi origin zone, destination zone, service type, dan weight tidak punya rate card aktif.
- Dampak: total_amount tidak dapat dihitung otomatis.
- Deteksi: exception validasi dari calculateTotalAmount.
- Respon otomatis: stop create normal, catat error bila terjadi di alur proses.
- Respon manual: input subtotal, insurance, admin fee, total_amount secara manual dengan alasan.
- Bukti audit: changed_fields untuk nilai biaya sebelum dan sesudah.
- Prioritas: P2.

9. Data customer atau shipment duplikat
- Trigger: submit ulang form atau integrasi eksternal mengirim data berulang.
- Dampak: data ganda, resiko double charge atau tracking ambigu.
- Deteksi: duplicate key, pola data identik, nomor resi/order_id berulang.
- Respon otomatis: constraint unik dan validasi mencegah insert ganda jika key tersedia.
- Respon manual: merge/void data duplikat dengan approval internal.
- Bukti audit: action update/delete/override dan reference dispute.
- Prioritas: P2.

10. User salah update data
- Trigger: human error saat edit shipment, payment, atau tracking.
- Dampak: data operasional tidak akurat, potensi komplain customer.
- Deteksi: perbedaan nilai besar pada changed_fields, laporan dispute.
- Respon otomatis: semua update penting tercatat before_state, after_state, changed_fields.
- Respon manual: lakukan koreksi manual resmi dengan alasan wajib dan reference tiket/dispute.
- Bukti audit: is_manual_correction true, source user_action, correction_reference terisi.
- Prioritas: P2.

## SOP Respon Cepat

1. Validasi severity dan dampak bisnis.
2. Cek unresolved queue.
3. Tentukan retry otomatis atau manual correction.
4. Jika manual correction, isi reason dan correction reference.
5. Verifikasi changed_fields dan status akhir.
6. Resolve error log dan catat hasil.

## Checklist Kesiapan QA

- Setiap skenario punya test negatif dan test recovery.
- Retry job terverifikasi ter-dispatch pada skenario yang mendukung.
- Manual correction terverifikasi menghasilkan audit dengan metadata dispute.
- Endpoint queue untuk admin mengembalikan kontrak JSON stabil untuk Flutter.