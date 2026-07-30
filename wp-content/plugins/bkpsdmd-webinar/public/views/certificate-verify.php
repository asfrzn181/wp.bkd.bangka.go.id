<?php
/**
 * Public View: Halaman Verifikasi Petikan Sertifikat (/verifikasi-petikan/{hash})
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$hash = get_query_var( 'wbr_verify' ) ?: ( $_GET['wbr_verify'] ?? '' );
$cert = WBR_Certificate::verify_by_hash( $hash );
$nama = $cert ? $cert->holder_name : '';
$email = $cert ? $cert->holder_email : '';
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi Petikan Sertifikat — BKPSDMD</title>
    <?php wp_head(); ?>
</head>
<body class="wbr-verify-page">
<div class="wbr-verify-wrap">
    <div class="wbr-verify-card">

        <?php if ( ! $cert ) : ?>
        <!-- Petikan tidak ditemukan -->
        <div class="wbr-verify-status invalid">
            <div class="wbr-status-icon">❌</div>
            <h2>Petikan Tidak Ditemukan</h2>
            <p>Kode verifikasi ini tidak terdaftar dalam sistem sertifikasi BKPSDMD Kabupaten Bangka.</p>
        </div>

        <?php elseif ( $cert->status === 'revoked' ) : ?>
        <!-- Petikan dicabut -->
        <div class="wbr-verify-status revoked">
            <div class="wbr-status-icon">🚫</div>
            <h2>Petikan Sertifikat TELAH DICABUT</h2>
            <p>Petikan sertifikat ini pernah diterbitkan namun telah resmi dicabut dan tidak berlaku lagi.</p>
        </div>
        <div class="wbr-verify-details">
            <div class="wbr-vrow"><span>Nomor Petikan:</span> <strong><?php echo esc_html( $cert->petikan_number ); ?></strong></div>
            <div class="wbr-vrow"><span>Nama Pemilik:</span> <strong><?php echo esc_html( $nama ); ?></strong></div>
            <div class="wbr-vrow"><span>Tanggal Pencabutan:</span> <?php echo esc_html( wp_date( 'd F Y, H:i', strtotime( $cert->revoked_at ) ) ); ?> WIB</div>
            <?php if ( $cert->revoke_reason ) : ?>
            <div class="wbr-vrow"><span>Alasan:</span> <em><?php echo esc_html( $cert->revoke_reason ); ?></em></div>
            <?php endif; ?>
        </div>

        <?php else : ?>
        <!-- Petikan sah & aktif -->
        <div class="wbr-verify-status valid">
            <div class="wbr-status-icon">✅</div>
            <h2>Petikan Sertifikat SAH &amp; TERVERIFIKASI</h2>
            <p>Dokumen ini secara resmi tercatat dalam sistem database BKPSDMD Kabupaten Bangka.</p>
        </div>

        <div class="wbr-verify-details">
            <div class="wbr-vrow"><span>Nomor Petikan:</span> <strong><?php echo esc_html( $cert->petikan_number ); ?></strong></div>
            <div class="wbr-vrow"><span>Nama Peserta:</span> <strong><?php echo esc_html( $nama ); ?></strong></div>
            <div class="wbr-vrow"><span>Email:</span> <?php echo esc_html( $email ); ?></div>
            <div class="wbr-vrow"><span>Webinar:</span> <?php echo esc_html( $cert->webinar_title ); ?></div>
            <div class="wbr-vrow"><span>Pelaksanaan:</span> <?php echo esc_html( wp_date( 'd F Y', strtotime( $cert->start_datetime ) ) ); ?></div>
            <div class="wbr-vrow"><span>Referensi SK Minut:</span> <strong><?php echo esc_html( $cert->sk_number ?: 'Belum disahkan' ); ?></strong></div>
            <?php if ( $cert->sk_date ) : ?>
            <div class="wbr-vrow"><span>Tanggal SK:</span> <?php echo esc_html( wp_date( 'd F Y', strtotime( $cert->sk_date ) ) ); ?></div>
            <?php endif; ?>
            <div class="wbr-vrow"><span>Penandatangan SK:</span> <?php echo esc_html( $cert->signing_official ?: 'Belum disahkan' ); ?></div>
            <div class="wbr-vrow"><span>Tanggal Terbit:</span> <?php echo esc_html( wp_date( 'd F Y, H:i', strtotime( $cert->generated_at ) ) ); ?> WIB</div>
        </div>

        <?php endif; ?>

        <div class="wbr-verify-footer">
            BKPSDMD Kabupaten Bangka &bull; Sistem Publikasi Sertifikat Official
        </div>
    </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
