<?php
/**
 * Email — kirim email token pendaftaran & notifikasi
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_Email {

    /**
     * Kirim email konfirmasi + token ke peserta yang baru daftar
     */
    public static function send_registration_confirmation( $registrant_id ) {
        global $wpdb;

        $reg = $wpdb->get_row( $wpdb->prepare(
            "SELECT r.*, p.post_title AS webinar_title, m.start_datetime, m.end_datetime
             FROM {$wpdb->prefix}webinar_registrant r
             JOIN {$wpdb->posts} p ON p.ID = r.webinar_id
             JOIN {$wpdb->prefix}webinar_meta m ON m.post_id = r.webinar_id
             WHERE r.id = %d",
            $registrant_id
        ) );

        if ( ! $reg ) return false;

        $submission = json_decode( $reg->submission_data, true );

        // Cari field nama (heuristic: key mengandung "nama")
        $nama = '';
        foreach ( (array) $submission as $key => $val ) {
            if ( is_string( $val ) && stripos( $key, 'nama' ) !== false ) {
                $nama = $val;
                break;
            }
        }
        if ( ! $nama ) $nama = $reg->email;

        $token_url = home_url( '/absensi/' . $reg->unique_token );
        $qr_b64    = WBR_QRCode::generate_base64( $token_url, 6 );

        $start  = wp_date( 'd F Y, H:i', strtotime( $reg->start_datetime ) );
        $end    = wp_date( 'H:i', strtotime( $reg->end_datetime ) );

        $subject = '[Webinar] Konfirmasi Pendaftaran — ' . $reg->webinar_title;

        ob_start();
        ?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><style>
body{font-family:Arial,sans-serif;color:#333;margin:0;padding:0;background:#f5f5f5}
.container{max-width:600px;margin:30px auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.08)}
.header{background:linear-gradient(135deg,#1a1a2e,#16213e);padding:32px;text-align:center;color:#fff}
.header h1{margin:0 0 6px;font-size:20px}
.header p{margin:0;font-size:13px;opacity:.8}
.body{padding:28px 32px}
.token-box{background:#f0f4ff;border-left:4px solid #6366f1;border-radius:6px;padding:16px;margin:20px 0}
.token-box a{color:#4f46e5;font-size:14px;word-break:break-all}
.qr-wrap{text-align:center;margin:20px 0}
.qr-wrap img{width:150px;height:150px;border:3px solid #eee;border-radius:6px}
.info{background:#f9f9f9;border-radius:6px;padding:16px;margin:20px 0;font-size:14px}
.info p{margin:4px 0}
.footer{background:#f5f5f5;padding:16px 32px;font-size:11px;color:#888;text-align:center}
</style></head><body>
<div class="container">
    <div class="header">
        <h1>🎓 Konfirmasi Pendaftaran Webinar</h1>
        <p>BKPSDMD Kabupaten Bangka</p>
    </div>
    <div class="body">
        <p>Yth. <strong><?php echo esc_html( $nama ); ?></strong>,</p>
        <p>Pendaftaran Anda untuk webinar berikut telah berhasil:</p>
        <div class="info">
            <p><strong>📌 Webinar:</strong> <?php echo esc_html( $reg->webinar_title ); ?></p>
            <p><strong>📅 Tanggal:</strong> <?php echo esc_html( $start ); ?> – <?php echo esc_html( $end ); ?> WIB</p>
        </div>
        <p><strong>🔑 Link Absensi Personal Anda:</strong></p>
        <div class="token-box">
            <a href="<?php echo esc_url( $token_url ); ?>"><?php echo esc_html( $token_url ); ?></a>
        </div>
        <p>Atau scan QR Code berikut saat absensi berlangsung:</p>
        <div class="qr-wrap">
            <?php if ( $qr_b64 ) : ?>
            <img src="<?php echo $qr_b64; ?>" alt="QR Absensi">
            <?php endif; ?>
        </div>
        <p style="font-size:13px;color:#666;">
            ⚠️ <strong>Simpan email ini.</strong> Link dan QR Code di atas bersifat personal dan digunakan untuk verifikasi absensi Anda.
        </p>
    </div>
    <div class="footer">
        BKPSDMD Kabupaten Bangka &bull; <?php echo esc_html( get_bloginfo( 'url' ) ); ?>
    </div>
</div>
</body></html>
        <?php
        $body = ob_get_clean();

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        return wp_mail( $reg->email, $subject, $body, $headers );
    }

    /**
     * Kirim email notifikasi bahwa petikan siap diunduh
     */
    public static function send_certificate_ready( $cert_id ) {
        global $wpdb;

        $cert = $wpdb->get_row( $wpdb->prepare(
            "SELECT c.*, r.email, r.submission_data, r.webinar_id,
                    sk.sk_number, p.post_title AS webinar_title
             FROM {$wpdb->prefix}webinar_certificate c
             JOIN {$wpdb->prefix}webinar_registrant r ON r.id = c.registrant_id
             JOIN {$wpdb->prefix}webinar_sk sk ON sk.id = c.sk_id
             JOIN {$wpdb->posts} p ON p.ID = r.webinar_id
             WHERE c.id = %d",
            $cert_id
        ) );

        if ( ! $cert || ! $cert->email ) return false;

        $submission = json_decode( $cert->submission_data, true );
        $nama       = '';
        foreach ( (array) $submission as $key => $val ) {
            if ( is_string( $val ) && stripos( $key, 'nama' ) !== false ) {
                $nama = $val; break;
            }
        }
        if ( ! $nama ) $nama = $cert->email;

        $verify_url = home_url( '/verifikasi-petikan/' . $cert->qr_verification_hash );
        $subject    = '[Webinar] Petikan Sertifikat Tersedia — ' . $cert->webinar_title;

        $body = '<p>Yth. <strong>' . esc_html( $nama ) . '</strong>,</p>
        <p>Petikan sertifikat keikutsertaan Anda dalam <strong>' . esc_html( $cert->webinar_title ) . '</strong> telah tersedia.</p>
        <p><strong>Nomor Petikan:</strong> ' . esc_html( $cert->petikan_number ) . '</p>
        <p><strong>Referensi SK:</strong> ' . esc_html( $cert->sk_number ) . '</p>
        <p>Verifikasi keaslian petikan: <a href="' . esc_url( $verify_url ) . '">' . esc_url( $verify_url ) . '</a></p>
        <p>Untuk mengunduh PDF, silakan hubungi panitia webinar.</p>';

        $headers = [ 'Content-Type: text/html; charset=UTF-8' ];
        return wp_mail( $cert->email, $subject, $body, $headers );
    }
}
