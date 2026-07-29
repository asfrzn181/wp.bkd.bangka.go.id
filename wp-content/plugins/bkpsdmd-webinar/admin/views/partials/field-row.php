<?php
/**
 * Partial: Row template untuk Form Builder (Tab 4 & 5)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$key   = esc_attr( $f->field_key ?? 'field_' . rand(100,999) );
$label = esc_attr( $f->label ?? '' );
$type  = esc_attr( $f->field_type ?? 'text' );
$req   = ! empty( $f->is_required );
$ident = ! empty( $f->is_identity_field );

$opts = [];
if ( ! empty( $f->options ) ) {
    $opts = is_array( $f->options ) ? $f->options : json_decode( $f->options, true );
}
$opts_text = is_array( $opts ) ? implode( "\n", $opts ) : '';
$show_opts = in_array( $type, [ 'radio', 'checkbox', 'select' ] );
?>
<div class="wbr-field-row" data-key="<?php echo $key; ?>">
    <div class="wbr-field-drag" title="Geser urutan">☰</div>
    <div class="wbr-field-body">
        <div class="wbr-field-main-row">
            <input type="text" class="wbr-field-label" placeholder="Label field (misal: Nama Lengkap)" value="<?php echo $label; ?>">
            <input type="text" class="wbr-field-key" placeholder="key (misal: nama_lengkap)" value="<?php echo $key; ?>">
            
            <select class="wbr-field-type">
                <option value="text" <?php selected( $type, 'text' ); ?>>text</option>
                <option value="textarea" <?php selected( $type, 'textarea' ); ?>>textarea</option>
                <option value="email" <?php selected( $type, 'email' ); ?>>email</option>
                <option value="phone" <?php selected( $type, 'phone' ); ?>>phone</option>
                <option value="radio" <?php selected( $type, 'radio' ); ?>>radio</option>
                <option value="checkbox" <?php selected( $type, 'checkbox' ); ?>>checkbox</option>
                <option value="select" <?php selected( $type, 'select' ); ?>>select</option>
                <option value="date" <?php selected( $type, 'date' ); ?>>date</option>
                <option value="number" <?php selected( $type, 'number' ); ?>>number</option>
            </select>

            <label class="wbr-field-required-label">
                <input type="checkbox" class="wbr-field-required" <?php checked( $req ); ?>> Wajib
            </label>

            <label class="wbr-field-identity-label" title="Centang jika field ini otomatis terisi dari data pendaftaran & locked saat absensi">
                <input type="checkbox" class="wbr-field-identity" <?php checked( $ident ); ?>> 🔒 Identity
            </label>

            <button type="button" class="button wbr-remove-field" title="Hapus field">✕</button>
        </div>

        <textarea class="wbr-field-options"
                  placeholder="Opsi pilihan (satu per baris) — khusus untuk radio/checkbox/select"
                  style="<?php echo $show_opts ? '' : 'display:none;'; ?>"><?php echo esc_textarea( $opts_text ); ?></textarea>
    </div>
</div>
