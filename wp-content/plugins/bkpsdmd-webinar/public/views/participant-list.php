<?php
/**
 * Public View: Halaman Daftar Peserta & Unduh Sertifikat (/daftar-peserta/{webinar_id})
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Menangkap query parameter sukses (dari redirect absensi)
$is_success = isset( $_GET['success'] ) && $_GET['success'] == '1';

// Inisialisasi variabel untuk tabel
$no = 1;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Peserta — <?php echo esc_html( $webinar->post_title ); ?></title>
    <?php wp_head(); ?>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background: #f3f4f6; color: #1f2937; margin: 0; padding: 2rem 1rem; }
        .wbr-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); }
        .wbr-header { text-align: center; margin-bottom: 2rem; }
        .wbr-header h1 { margin: 0 0 0.5rem 0; font-size: 1.5rem; color: #111827; }
        .wbr-header p { margin: 0; color: #6b7280; }
        
        .wbr-alert-success { background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 6px; margin-bottom: 2rem; text-align: center; border: 1px solid #34d399; font-weight: 500; }
        
        .wbr-table-responsive { overflow-x: auto; }
        .wbr-table { width: 100%; border-collapse: collapse; text-align: left; }
        .wbr-table th, .wbr-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #e5e7eb; }
        .wbr-table th { background: #f9fafb; font-weight: 600; color: #374151; white-space: nowrap; }
        .wbr-table tr:hover { background: #f9fafb; }
        
        .wbr-btn-download { display: inline-block; padding: 0.375rem 0.75rem; background: #2563eb; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.875rem; font-weight: 500; transition: background 0.2s; white-space: nowrap; }
        .wbr-btn-download:hover { background: #1d4ed8; color: #fff; }
        
        .wbr-empty { text-align: center; padding: 2rem; color: #6b7280; }
        .wbr-footer { text-align: center; margin-top: 2rem; font-size: 0.875rem; color: #9ca3af; }
    </style>
</head>
<body>

<div class="wbr-container">
    <?php if ( $is_success ) : ?>
        <div class="wbr-alert-success">
            ✅ Kehadiran Anda berhasil dicatat! Silakan cari nama Anda pada tabel di bawah ini untuk mengunduh sertifikat.
        </div>
    <?php endif; ?>

    <div class="wbr-header">
        <h1>Daftar Peserta & Unduh Sertifikat</h1>
        <p><?php echo esc_html( $webinar->post_title ); ?></p>
    </div>

    <div class="wbr-table-responsive">
        <?php if ( empty( $certificates ) ) : ?>
            <div class="wbr-empty">Belum ada peserta yang tercatat hadir.</div>
        <?php else : ?>
            <table class="wbr-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Petikan</th>
                        <th>Nama Peserta</th>
                        <th>Sertifikat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $certificates as $cert ) : 
                        $download_url = admin_url( 'admin-ajax.php?action=wbr_download_cert_public&hash=' . $cert->qr_verification_hash );
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td>
                            <?php echo esc_html( $cert->petikan_number ); ?><br>
                            <small style="color:#6b7280;"><?php echo $cert->sk_number ? esc_html($cert->sk_number) : 'SK Belum Disahkan'; ?></small>
                        </td>
                        <td>
                            <strong><?php echo esc_html( $cert->holder_name ); ?></strong><br>
                            <small style="color:#6b7280;"><?php echo esc_html( $cert->holder_email ); ?></small>
                        </td>
                        <td>
                            <?php if ( $cert->status === 'revoked' ) : ?>
                                <span style="color:#ef4444; font-weight:500;">Dicabut</span>
                            <?php elseif ( $cert->file_path_pdf ) : ?>
                                <a href="<?php echo esc_url( $download_url ); ?>" class="wbr-btn-download" target="_blank">⬇ Unduh</a>
                            <?php else : ?>
                                <span style="color:#9ca3af;">Memproses...</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    
    <div class="wbr-footer">
        BKPSDMD Kabupaten Bangka &bull; Sistem Publikasi Sertifikat Official
    </div>
</div>

<?php wp_footer(); ?>
</body>
</html>
