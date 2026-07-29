<?php
/**
 * Public View: Form Absensi (diakses via /absensi/{token} atau ?wbr_token={token})
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

if ( ! isset( $registrant ) || ! $registrant ) {
    wp_die( 'Token absensi tidak valid atau tidak ditemukan.', 'Error Absensi', [ 'response' => 404 ] );
}

$webinar_id = $registrant->webinar_id;
$webinar    = get_post( $webinar_id );

// Cek apakah sudah absen
$already_attended = (bool) $wpdb->get_var( $wpdb->prepare(
    "SELECT id FROM {$wpdb->prefix}webinar_attendance WHERE webinar_id = %d AND registrant_id = %d LIMIT 1",
    $webinar_id, $registrant->id
) );

// Ambil field form absensi
$fields = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}webinar_form_field
     WHERE webinar_id = %d AND form_type = 'attendance'
     ORDER BY sort_order ASC",
    $webinar_id
) );

$reg_data = json_decode( $registrant->submission_data, true ) ?: [];
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Absensi — <?php echo esc_html( $webinar->post_title ); ?></title>
    <?php wp_head(); ?>
</head>
<body class="wbr-attendance-page">
<div class="wbr-att-wrap">
    <div class="wbr-att-card">
        <div class="wbr-att-header">
            <div class="wbr-att-icon">📋</div>
            <h1>Form Absensi Peserta</h1>
            <p><?php echo esc_html( $webinar->post_title ); ?></p>
        </div>

        <?php if ( $already_attended ) : ?>
        <div class="wbr-pub-notice success">
            <h3>✅ Absensi Sudah Dicatat</h3>
            <p>Terima kasih! Kehadiran Anda dalam webinar ini telah terverifikasi.</p>
        </div>

        <?php else : ?>
        <form id="wbr-attendance-form" data-token="<?php echo esc_attr( $registrant->unique_token ); ?>">
            <div id="wbr-att-msg"></div>

            <?php foreach ( $fields as $f ) :
                $is_ident = (bool) $f->is_identity_field;
                $val      = $is_ident ? ( $reg_data[ $f->field_key ] ?? '' ) : '';
                $opts     = $f->options ? (array) json_decode( $f->options, true ) : [];
                $req_mark = $f->is_required ? ' <span class="wbr-req">*</span>' : '';
            ?>
            <div class="wbr-pub-field <?php echo $is_ident ? 'is-identity' : ''; ?>">
                <label class="wbr-pub-label">
                    <?php echo esc_html( $f->label ); ?><?php echo $req_mark; ?>
                    <?php if ( $is_ident ) : ?><span class="wbr-lock-badge">🔒 Terkunci (Data Registrasi)</span><?php endif; ?>
                </label>

                <?php if ( $is_ident ) : ?>
                <!-- Identity field: READ-ONLY -->
                <input type="text" class="wbr-pub-input readonly"
                       value="<?php echo esc_attr( is_array($val) ? implode(', ',$val) : $val ); ?>"
                       readonly disabled>

                <?php else : ?>
                <!-- Editable field -->
                <?php switch ( $f->field_type ) :
                    case 'textarea': ?>
                        <textarea name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                                  class="wbr-pub-input" <?php echo $f->is_required ? 'required' : ''; ?>></textarea>
                        <?php break;

                    case 'select': ?>
                        <select name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                                class="wbr-pub-input" <?php echo $f->is_required ? 'required' : ''; ?>>
                            <option value="">-- Pilih --</option>
                            <?php foreach ( $opts as $opt ) : ?>
                            <option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php break;

                    default: ?>
                        <input type="<?php echo esc_attr( $f->field_type === 'number' ? 'number' : 'text' ); ?>"
                               name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                               class="wbr-pub-input" <?php echo $f->is_required ? 'required' : ''; ?>>
                        <?php break;
                endswitch; ?>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <div class="wbr-pub-form-actions">
                <button type="submit" class="wbr-pub-submit-btn" id="wbr-att-submit">
                    ✅ Simpan Kehadiran Saya
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php wp_footer(); ?>
</body>
</html>
