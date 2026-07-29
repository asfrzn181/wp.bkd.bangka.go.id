<?php
/**
 * Public View: Form Pendaftaran (embedded di webinar-detail.php)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$fields = $wpdb->get_results( $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}webinar_form_field
     WHERE webinar_id = %d AND form_type = 'registration'
     ORDER BY sort_order ASC",
    $post->ID
) );

if ( empty( $fields ) ) {
    echo '<p class="wbr-pub-notice">Form pendaftaran belum dikonfigurasi oleh panitia.</p>';
    return;
}
?>
<form id="wbr-registration-form" class="wbr-pub-form" data-webinar-id="<?php echo esc_attr( $post->ID ); ?>">
    <div id="wbr-reg-msg"></div>

    <?php foreach ( $fields as $f ) :
        $opts = $f->options ? (array) json_decode( $f->options, true ) : [];
        $req  = $f->is_required ? 'required' : '';
        $req_mark = $f->is_required ? ' <span class="wbr-req">*</span>' : '';
    ?>
    <div class="wbr-pub-field">
        <label class="wbr-pub-label">
            <?php echo esc_html( $f->label ); ?><?php echo $req_mark; ?>
        </label>

        <?php switch ( $f->field_type ) :
            case 'textarea': ?>
                <textarea name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                          class="wbr-pub-input" <?php echo $req; ?>></textarea>
                <?php break;

            case 'select': ?>
                <select name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                        class="wbr-pub-input" <?php echo $req; ?>>
                    <option value="">-- Pilih --</option>
                    <?php foreach ( $opts as $opt ) : ?>
                    <option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php break;

            case 'radio': ?>
                <div class="wbr-pub-options">
                    <?php foreach ( $opts as $i => $opt ) : ?>
                    <label class="wbr-pub-radio-label">
                        <input type="radio" name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                               value="<?php echo esc_attr( $opt ); ?>" <?php echo $i===0 && $f->is_required ? 'required' : ''; ?>>
                        <span><?php echo esc_html( $opt ); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php break;

            case 'checkbox': ?>
                <div class="wbr-pub-options">
                    <?php foreach ( $opts as $opt ) : ?>
                    <label class="wbr-pub-radio-label">
                        <input type="checkbox" name="form_data[<?php echo esc_attr( $f->field_key ); ?>][]"
                               value="<?php echo esc_attr( $opt ); ?>">
                        <span><?php echo esc_html( $opt ); ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php break;

            default: ?>
                <input type="<?php echo esc_attr( $f->field_type === 'email' ? 'email' : ( $f->field_type === 'number' ? 'number' : 'text' ) ); ?>"
                       name="form_data[<?php echo esc_attr( $f->field_key ); ?>]"
                       class="wbr-pub-input" <?php echo $req; ?>>
                <?php break;
        endswitch; ?>
    </div>
    <?php endforeach; ?>

    <div class="wbr-pub-form-actions">
        <button type="submit" class="wbr-pub-submit-btn" id="wbr-reg-submit">
            🎓 Kirim Pendaftaran
        </button>
    </div>
</form>
