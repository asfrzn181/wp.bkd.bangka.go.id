<?php
/**
 * ASN Data Dashboard Shortcode Read-Only View Template.
 * Visualizations & Cards only (No Tables).
 *
 * @package ASN_Data_Dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$pns_jabatan   = ASNDD_Schema::get_pns_jabatan();
$pns_pangkat   = ASNDD_Schema::get_pns_pangkat();
$pendidikan    = ASNDD_Schema::get_pendidikan_levels();
$pppk_jabatan  = ASNDD_Schema::get_pppk_jabatan();
$pppk_golongan = ASNDD_Schema::get_pppk_golongan();
$pppk_pw_jab   = ASNDD_Schema::get_pppk_pw_jabatan();

// Pre-calculate metric card values in PHP for immediate HTML rendering
$pns_l = 0; $pns_p = 0;
foreach ( $pns_jabatan as $k => $lbl ) {
	$pns_l += $data['pnsCpns']['jabatan'][ $k ]['l'] ?? 0;
	$pns_p += $data['pnsCpns']['jabatan'][ $k ]['p'] ?? 0;
}
$pppk_l = 0; $pppk_p = 0;
foreach ( $pppk_jabatan as $k => $lbl ) {
	$pppk_l += $data['pppk']['jabatan'][ $k ]['l'] ?? 0;
	$pppk_p += $data['pppk']['jabatan'][ $k ]['p'] ?? 0;
}
$pw_l = 0; $pw_p = 0;
foreach ( $pppk_pw_jab as $k => $lbl ) {
	$pw_l += $data['pppkPw']['jabatan'][ $k ]['l'] ?? 0;
	$pw_p += $data['pppkPw']['jabatan'][ $k ]['p'] ?? 0;
}

$tot_pns     = $pns_l + $pns_p;
$tot_pppk    = $pppk_l + $pppk_p;
$tot_pw      = $pw_l + $pw_p;
$grand_total = $tot_pns + $tot_pppk + $tot_pw;
$grand_l     = $pns_l + $pppk_l + $pw_l;
$grand_p     = $pns_p + $pppk_p + $pw_p;
?>

<div class="asndd-frontend-wrap" id="<?php echo esc_attr( $instance_id ); ?>">
	
	<!-- Inline Config JSON to ensure 100% reliable JS initialization -->
	<script type="application/json" class="asndd-config">
	<?php
	echo wp_json_encode(
		array(
			'instanceId' => $instance_id,
			'periode'    => $current_periode,
			'restUrl'    => esc_url_raw( rest_url( 'asn-dashboard/v1/data' ) ),
			'data'       => $data,
			'schema'     => array(
				'pnsJabatan'    => $pns_jabatan,
				'pnsPangkat'    => $pns_pangkat,
				'pendidikan'    => $pendidikan,
				'pppkJabatan'   => $pppk_jabatan,
				'pppkGolongan'  => $pppk_golongan,
				'pppkPwJabatan' => $pppk_pw_jab,
			),
		)
	);
	?>
	</script>

	<!-- DASHBOARD HEADER -->
	<div class="asndd-fe-header">
		<div class="asndd-fe-title-wrap">
			<h2 class="asndd-fe-title"><?php esc_html_e( 'Visualisasi Rekapitulasi Data ASN', 'asn-data-dashboard' ); ?></h2>
			<div class="asndd-fe-filter-group">
				<label for="<?php echo esc_attr( $instance_id ); ?>-period-select"><strong><?php esc_html_e( 'Periode:', 'asn-data-dashboard' ); ?></strong></label>
				<select id="<?php echo esc_attr( $instance_id ); ?>-period-select" class="asndd-fe-period-select">
					<?php if ( empty( $all_periods ) ) : ?>
						<option value="<?php echo esc_attr( $current_periode ); ?>"><?php echo esc_html( $current_periode ); ?></option>
					<?php else : ?>
						<?php foreach ( $all_periods as $p ) : ?>
							<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $p, $current_periode ); ?>>
								<?php echo esc_html( $p ); ?>
							</option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
		</div>
	</div>

	<!-- CATEGORY FILTER BUTTONS & SUMMARY CARDS -->
	<div class="asndd-fe-filter-hint">
		<span class="dashicons dashicons-filter"></span> <?php esc_html_e( 'Klik kartu atau kategori di bawah untuk menampilkan visualisasi spesifik:', 'asn-data-dashboard' ); ?>
	</div>

	<div class="asndd-fe-cards">
		<div class="asndd-fe-card asndd-fe-card-total asndd-fe-card-active" data-cat="total">
			<div class="asndd-fe-card-header">
				<span class="asndd-fe-card-label"><?php esc_html_e( 'TOTAL ASN', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-fe-active-dot"></span>
			</div>
			<span class="asndd-fe-card-val" id="<?php echo esc_attr( $instance_id ); ?>-val-total"><?php echo esc_html( number_format_i18n( $grand_total ) ); ?></span>
			<span class="asndd-fe-card-sub" id="<?php echo esc_attr( $instance_id ); ?>-sub-total">L: <?php echo esc_html( number_format_i18n( $grand_l ) ); ?> | P: <?php echo esc_html( number_format_i18n( $grand_p ) ); ?></span>
		</div>

		<div class="asndd-fe-card asndd-fe-card-pns" data-cat="pns">
			<div class="asndd-fe-card-header">
				<span class="asndd-fe-card-label"><?php esc_html_e( 'PNS & CPNS', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-fe-active-dot"></span>
			</div>
			<span class="asndd-fe-card-val" id="<?php echo esc_attr( $instance_id ); ?>-val-pns"><?php echo esc_html( number_format_i18n( $tot_pns ) ); ?></span>
			<span class="asndd-fe-card-sub" id="<?php echo esc_attr( $instance_id ); ?>-sub-pns">L: <?php echo esc_html( number_format_i18n( $pns_l ) ); ?> | P: <?php echo esc_html( number_format_i18n( $pns_p ) ); ?></span>
		</div>

		<div class="asndd-fe-card asndd-fe-card-pppk" data-cat="pppk">
			<div class="asndd-fe-card-header">
				<span class="asndd-fe-card-label"><?php esc_html_e( 'PPPK', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-fe-active-dot"></span>
			</div>
			<span class="asndd-fe-card-val" id="<?php echo esc_attr( $instance_id ); ?>-val-pppk"><?php echo esc_html( number_format_i18n( $tot_pppk ) ); ?></span>
			<span class="asndd-fe-card-sub" id="<?php echo esc_attr( $instance_id ); ?>-sub-pppk">L: <?php echo esc_html( number_format_i18n( $pppk_l ) ); ?> | P: <?php echo esc_html( number_format_i18n( $pppk_p ) ); ?></span>
		</div>

		<div class="asndd-fe-card asndd-fe-card-pppkpw" data-cat="pppkpw">
			<div class="asndd-fe-card-header">
				<span class="asndd-fe-card-label"><?php esc_html_e( 'PPPK-PW', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-fe-active-dot"></span>
			</div>
			<span class="asndd-fe-card-val" id="<?php echo esc_attr( $instance_id ); ?>-val-pppkpw"><?php echo esc_html( number_format_i18n( $tot_pw ) ); ?></span>
			<span class="asndd-fe-card-sub" id="<?php echo esc_attr( $instance_id ); ?>-sub-pppkpw">L: <?php echo esc_html( number_format_i18n( $pw_l ) ); ?> | P: <?php echo esc_html( number_format_i18n( $pw_p ) ); ?></span>
		</div>
	</div>

	<!-- VISUALIZATIONS & CHARTS SECTION -->
	<div class="asndd-fe-charts-section">
		<div class="asndd-fe-section-header">
			<h3 class="asndd-fe-section-title">
				<span class="dashicons dashicons-chart-area"></span>
				<span class="asndd-fe-cat-title-text"><?php esc_html_e( 'Visualisasi Grafik: Total ASN', 'asn-data-dashboard' ); ?></span>
			</h3>
		</div>

		<div class="asndd-fe-grid-2">
			<div class="asndd-fe-box">
				<h4 class="asndd-fe-box-title"><?php esc_html_e( 'Jabatan Seluruh ASN per Gender', 'asn-data-dashboard' ); ?></h4>
				<div class="asndd-fe-chart-wrap">
					<canvas id="<?php echo esc_attr( $instance_id ); ?>-chart-pns-jabatan"></canvas>
				</div>
			</div>

			<div class="asndd-fe-box">
				<h4 class="asndd-fe-box-title"><?php esc_html_e( 'Pangkat & Golongan Seluruh ASN per Gender', 'asn-data-dashboard' ); ?></h4>
				<div class="asndd-fe-chart-wrap">
					<canvas id="<?php echo esc_attr( $instance_id ); ?>-chart-pns-pangkat"></canvas>
				</div>
			</div>
		</div>

		<div class="asndd-fe-grid-3 asndd-fe-mt-15">
			<div class="asndd-fe-box">
				<h4 class="asndd-fe-box-title"><?php esc_html_e( 'Komposisi Pendidikan Seluruh ASN', 'asn-data-dashboard' ); ?></h4>
				<div class="asndd-fe-chart-wrap asndd-fe-chart-square">
					<canvas id="<?php echo esc_attr( $instance_id ); ?>-chart-pns-pendidikan"></canvas>
				</div>
			</div>

			<div class="asndd-fe-box">
				<h4 class="asndd-fe-box-title"><?php esc_html_e( 'Distribusi Gender Total ASN', 'asn-data-dashboard' ); ?></h4>
				<div class="asndd-fe-chart-wrap asndd-fe-chart-square">
					<canvas id="<?php echo esc_attr( $instance_id ); ?>-chart-gender-total"></canvas>
				</div>
			</div>

			<div class="asndd-fe-box">
				<h4 class="asndd-fe-box-title"><?php esc_html_e( 'Komposisi Jenis Pegawai', 'asn-data-dashboard' ); ?></h4>
				<div class="asndd-fe-chart-wrap asndd-fe-chart-square">
					<canvas id="<?php echo esc_attr( $instance_id ); ?>-chart-jenis-pegawai"></canvas>
				</div>
			</div>
		</div>
	</div>

</div>
