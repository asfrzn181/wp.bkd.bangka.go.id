<?php
/**
 * ASN Data Dashboard Admin Page Template.
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
?>

<div class="wrap asndd-admin-wrap">
	<h1 class="wp-heading-inline">
		<span class="dashicons dashicons-chart-bar"></span>
		<?php esc_html_e( 'Rekapitulasi Data ASN', 'asn-data-dashboard' ); ?>
	</h1>
	<hr class="wp-header-end">

	<div id="asndd-notice-container"></div>

	<!-- TOP CONTROL BAR -->
	<div class="asndd-control-bar">
		<div class="asndd-control-group">
			<label for="asndd-periode-select"><strong><?php esc_html_e( 'Periode Data:', 'asn-data-dashboard' ); ?></strong></label>
			<select id="asndd-periode-select" class="asndd-select">
				<?php if ( empty( $periods ) ) : ?>
					<option value="<?php echo esc_attr( $current_period ); ?>"><?php echo esc_html( $current_period ); ?> (Baru)</option>
				<?php else : ?>
					<?php foreach ( $periods as $p ) : ?>
						<option value="<?php echo esc_attr( $p ); ?>" <?php selected( $p, $current_period ); ?>>
							<?php echo esc_html( $p ); ?>
						</option>
					<?php endforeach; ?>
				<?php endif; ?>
			</select>

			<button type="button" class="button button-secondary" id="asndd-btn-new-period">
				<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Buat Periode Baru', 'asn-data-dashboard' ); ?>
			</button>

			<button type="button" class="button button-secondary" id="asndd-btn-copy-period">
				<span class="dashicons dashicons-admin-page"></span> <?php esc_html_e( 'Salin dari Periode Lain', 'asn-data-dashboard' ); ?>
			</button>
		</div>

		<div class="asndd-control-actions">
			<button type="button" class="button button-primary button-large" id="asndd-btn-save">
				<span class="dashicons dashicons-saved"></span> <?php esc_html_e( 'Simpan Data', 'asn-data-dashboard' ); ?>
			</button>
		</div>
	</div>

	<!-- SUMMARY METRIC CARDS -->
	<div class="asndd-summary-grid">
		<div class="asndd-card asndd-card-blue">
			<div class="asndd-card-icon"><span class="dashicons dashicons-groups"></span></div>
			<div class="asndd-card-body">
				<span class="asndd-card-title"><?php esc_html_e( 'PNS & CPNS', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-card-value" id="stat-total-pns">0</span>
				<span class="asndd-card-sub" id="stat-gender-pns">L: 0 | P: 0</span>
			</div>
		</div>

		<div class="asndd-card asndd-card-green">
			<div class="asndd-card-icon"><span class="dashicons dashicons-id-alt"></span></div>
			<div class="asndd-card-body">
				<span class="asndd-card-title"><?php esc_html_e( 'PPPK', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-card-value" id="stat-total-pppk">0</span>
				<span class="asndd-card-sub" id="stat-gender-pppk">L: 0 | P: 0</span>
			</div>
		</div>

		<div class="asndd-card asndd-card-orange">
			<div class="asndd-card-icon"><span class="dashicons dashicons-businessman"></span></div>
			<div class="asndd-card-body">
				<span class="asndd-card-title"><?php esc_html_e( 'PPPK-PW', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-card-value" id="stat-total-pppkpw">0</span>
				<span class="asndd-card-sub" id="stat-gender-pppkpw">L: 0 | P: 0</span>
			</div>
		</div>

		<div class="asndd-card asndd-card-purple">
			<div class="asndd-card-icon"><span class="dashicons dashicons-chart-pie"></span></div>
			<div class="asndd-card-body">
				<span class="asndd-card-title"><?php esc_html_e( 'GRAND TOTAL ASN', 'asn-data-dashboard' ); ?></span>
				<span class="asndd-card-value" id="stat-grand-total">0</span>
				<span class="asndd-card-sub" id="stat-gender-total">L: 0 | P: 0</span>
			</div>
		</div>
	</div>

	<!-- TAB NAVIGATION -->
	<h2 class="nav-tab-wrapper asndd-nav-tabs">
		<a href="#tab-pns" class="nav-tab nav-tab-active" data-tab="pns"><?php esc_html_e( 'PNS & CPNS', 'asn-data-dashboard' ); ?></a>
		<a href="#tab-pppk" class="nav-tab" data-tab="pppk"><?php esc_html_e( 'PPPK', 'asn-data-dashboard' ); ?></a>
		<a href="#tab-pppkpw" class="nav-tab" data-tab="pppkpw"><?php esc_html_e( 'PPPK-PW', 'asn-data-dashboard' ); ?></a>
		<a href="#tab-charts" class="nav-tab" data-tab="charts"><span class="dashicons dashicons-chart-area"></span> <?php esc_html_e( 'Grafik & Visualisasi', 'asn-data-dashboard' ); ?></a>
	</h2>

	<!-- TAB CONTENT: PNS / CPNS -->
	<div id="tab-pns" class="asndd-tab-content asndd-tab-active">
		<div class="asndd-grid-2">
			<!-- Tabel Jabatan PNS -->
			<div class="asndd-box">
				<h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e( 'Berdasarkan Jabatan (PNS & CPNS)', 'asn-data-dashboard' ); ?></h3>
				<table class="wp-list-table widefat fixed striped asndd-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Kelompok Jabatan', 'asn-data-dashboard' ); ?></th>
							<th class="col-num"><?php esc_html_e( 'Laki-Laki (L)', 'asn-data-dashboard' ); ?></th>
							<th class="col-num"><?php esc_html_e( 'Perempuan (P)', 'asn-data-dashboard' ); ?></th>
							<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $pns_jabatan as $key => $label ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $label ); ?></strong></td>
								<td class="col-num">
									<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.jabatan.<?php echo esc_attr( $key ); ?>.l" value="0">
								</td>
								<td class="col-num">
									<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.jabatan.<?php echo esc_attr( $key ); ?>.p" value="0">
								</td>
								<td class="col-num col-total">
									<span class="asndd-row-total" id="tot-pns-jab-<?php echo esc_attr( $key ); ?>">0</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr class="asndd-tfoot-total">
							<th><?php esc_html_e( 'TOTAL JABATAN PNS', 'asn-data-dashboard' ); ?></th>
							<th class="col-num" id="subtot-pns-jab-l">0</th>
							<th class="col-num" id="subtot-pns-jab-p">0</th>
							<th class="col-num col-total" id="subtot-pns-jab-all">0</th>
						</tr>
					</tfoot>
				</table>
			</div>

			<!-- Tabel Pangkat PNS -->
			<div class="asndd-box">
				<h3><span class="dashicons dashicons-awards"></span> <?php esc_html_e( 'Berdasarkan Pangkat / Golongan (PNS)', 'asn-data-dashboard' ); ?></h3>
				<div class="asndd-table-scroll">
					<table class="wp-list-table widefat fixed striped asndd-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Golongan', 'asn-data-dashboard' ); ?></th>
								<th class="col-num"><?php esc_html_e( 'Laki-Laki (L)', 'asn-data-dashboard' ); ?></th>
								<th class="col-num"><?php esc_html_e( 'Perempuan (P)', 'asn-data-dashboard' ); ?></th>
								<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $pns_pangkat as $key => $label ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $label ); ?></strong></td>
									<td class="col-num">
										<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.pangkat.<?php echo esc_attr( $key ); ?>.l" value="0">
									</td>
									<td class="col-num">
										<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.pangkat.<?php echo esc_attr( $key ); ?>.p" value="0">
									</td>
									<td class="col-num col-total">
										<span class="asndd-row-total" id="tot-pns-pkt-<?php echo esc_attr( $key ); ?>">0</span>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot>
							<tr class="asndd-tfoot-total">
								<th><?php esc_html_e( 'TOTAL GOLONGAN PNS', 'asn-data-dashboard' ); ?></th>
								<th class="col-num" id="subtot-pns-pkt-l">0</th>
								<th class="col-num" id="subtot-pns-pkt-p">0</th>
								<th class="col-num col-total" id="subtot-pns-pkt-all">0</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>

		<!-- Tabel Pendidikan PNS -->
		<div class="asndd-box asndd-mt-20">
			<h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Berdasarkan Tingkat Pendidikan (PNS & CPNS)', 'asn-data-dashboard' ); ?></h3>
			<table class="wp-list-table widefat fixed striped asndd-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tingkat Pendidikan', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Struktural', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Guru', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Nakes', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Teknis', 'asn-data-dashboard' ); ?></th>
						<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pendidikan as $key => $label ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $label ); ?></strong></td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.didik.<?php echo esc_attr( $key ); ?>.struktural" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.didik.<?php echo esc_attr( $key ); ?>.guru" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.didik.<?php echo esc_attr( $key ); ?>.nakes" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pnsCpns.didik.<?php echo esc_attr( $key ); ?>.teknis" value="0">
							</td>
							<td class="col-num col-total">
								<span class="asndd-row-total" id="tot-pns-didik-<?php echo esc_attr( $key ); ?>">0</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="asndd-tfoot-total">
						<th><?php esc_html_e( 'TOTAL PENDIDIKAN PNS', 'asn-data-dashboard' ); ?></th>
						<th class="col-num" id="subtot-pns-didik-struk">0</th>
						<th class="col-num" id="subtot-pns-didik-guru">0</th>
						<th class="col-num" id="subtot-pns-didik-nakes">0</th>
						<th class="col-num" id="subtot-pns-didik-teknis">0</th>
						<th class="col-num col-total" id="subtot-pns-didik-all">0</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<!-- TAB CONTENT: PPPK -->
	<div id="tab-pppk" class="asndd-tab-content">
		<div class="asndd-grid-2">
			<!-- Tabel Jabatan PPPK -->
			<div class="asndd-box">
				<h3><span class="dashicons dashicons-portfolio"></span> <?php esc_html_e( 'Berdasarkan Jabatan (PPPK)', 'asn-data-dashboard' ); ?></h3>
				<table class="wp-list-table widefat fixed striped asndd-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Kelompok Jabatan', 'asn-data-dashboard' ); ?></th>
							<th class="col-num"><?php esc_html_e( 'Laki-Laki (L)', 'asn-data-dashboard' ); ?></th>
							<th class="col-num"><?php esc_html_e( 'Perempuan (P)', 'asn-data-dashboard' ); ?></th>
							<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $pppk_jabatan as $key => $label ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $label ); ?></strong></td>
								<td class="col-num">
									<input type="number" min="0" class="asndd-input-num" data-path="pppk.jabatan.<?php echo esc_attr( $key ); ?>.l" value="0">
								</td>
								<td class="col-num">
									<input type="number" min="0" class="asndd-input-num" data-path="pppk.jabatan.<?php echo esc_attr( $key ); ?>.p" value="0">
								</td>
								<td class="col-num col-total">
									<span class="asndd-row-total" id="tot-pppk-jab-<?php echo esc_attr( $key ); ?>">0</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr class="asndd-tfoot-total">
							<th><?php esc_html_e( 'TOTAL JABATAN PPPK', 'asn-data-dashboard' ); ?></th>
							<th class="col-num" id="subtot-pppk-jab-l">0</th>
							<th class="col-num" id="subtot-pppk-jab-p">0</th>
							<th class="col-num col-total" id="subtot-pppk-jab-all">0</th>
						</tr>
					</tfoot>
				</table>
			</div>

			<!-- Tabel Golongan PPPK -->
			<div class="asndd-box">
				<h3><span class="dashicons dashicons-awards"></span> <?php esc_html_e( 'Berdasarkan Golongan (PPPK)', 'asn-data-dashboard' ); ?></h3>
				<table class="wp-list-table widefat fixed striped asndd-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Golongan', 'asn-data-dashboard' ); ?></th>
							<th class="col-num"><?php esc_html_e( 'Laki-Laki (L)', 'asn-data-dashboard' ); ?></th>
							<th class="col-num"><?php esc_html_e( 'Perempuan (P)', 'asn-data-dashboard' ); ?></th>
							<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $pppk_golongan as $key => $label ) : ?>
							<tr>
								<td><strong><?php echo esc_html( $label ); ?></strong></td>
								<td class="col-num">
									<input type="number" min="0" class="asndd-input-num" data-path="pppk.golongan.<?php echo esc_attr( $key ); ?>.l" value="0">
								</td>
								<td class="col-num">
									<input type="number" min="0" class="asndd-input-num" data-path="pppk.golongan.<?php echo esc_attr( $key ); ?>.p" value="0">
								</td>
								<td class="col-num col-total">
									<span class="asndd-row-total" id="tot-pppk-gol-<?php echo esc_attr( $key ); ?>">0</span>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
					<tfoot>
						<tr class="asndd-tfoot-total">
							<th><?php esc_html_e( 'TOTAL GOLONGAN PPPK', 'asn-data-dashboard' ); ?></th>
							<th class="col-num" id="subtot-pppk-gol-l">0</th>
							<th class="col-num" id="subtot-pppk-gol-p">0</th>
							<th class="col-num col-total" id="subtot-pppk-gol-all">0</th>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>

		<!-- Tabel Pendidikan PPPK -->
		<div class="asndd-box asndd-mt-20">
			<h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Berdasarkan Tingkat Pendidikan & Jenis Jabatan (PPPK)', 'asn-data-dashboard' ); ?></h3>
			<table class="wp-list-table widefat fixed striped asndd-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tingkat Pendidikan', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Guru', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Nakes', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Teknis', 'asn-data-dashboard' ); ?></th>
						<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pendidikan as $key => $label ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $label ); ?></strong></td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppk.didik.<?php echo esc_attr( $key ); ?>.guru" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppk.didik.<?php echo esc_attr( $key ); ?>.nakes" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppk.didik.<?php echo esc_attr( $key ); ?>.teknis" value="0">
							</td>
							<td class="col-num col-total">
								<span class="asndd-row-total" id="tot-pppk-didik-<?php echo esc_attr( $key ); ?>">0</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="asndd-tfoot-total">
						<th><?php esc_html_e( 'TOTAL PENDIDIKAN PPPK', 'asn-data-dashboard' ); ?></th>
						<th class="col-num" id="subtot-pppk-didik-guru">0</th>
						<th class="col-num" id="subtot-pppk-didik-nakes">0</th>
						<th class="col-num" id="subtot-pppk-didik-teknis">0</th>
						<th class="col-num col-total" id="subtot-pppk-didik-all">0</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>


	<!-- TAB CONTENT: PPPK PW -->
	<div id="tab-pppkpw" class="asndd-tab-content">
		<div class="asndd-box" style="max-width:700px;">
			<h3><span class="dashicons dashicons-businessman"></span> <?php esc_html_e( 'Berdasarkan Jabatan (PPPK-PW)', 'asn-data-dashboard' ); ?></h3>
			<table class="wp-list-table widefat fixed striped asndd-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Kelompok Jabatan', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Laki-Laki (L)', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Perempuan (P)', 'asn-data-dashboard' ); ?></th>
						<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pppk_pw_jab as $key => $label ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $label ); ?></strong></td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppkPw.jabatan.<?php echo esc_attr( $key ); ?>.l" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppkPw.jabatan.<?php echo esc_attr( $key ); ?>.p" value="0">
							</td>
							<td class="col-num col-total">
								<span class="asndd-row-total" id="tot-pppkpw-jab-<?php echo esc_attr( $key ); ?>">0</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="asndd-tfoot-total">
						<th><?php esc_html_e( 'TOTAL JABATAN PPPK-PW', 'asn-data-dashboard' ); ?></th>
						<th class="col-num" id="subtot-pppkpw-jab-l">0</th>
						<th class="col-num" id="subtot-pppkpw-jab-p">0</th>
						<th class="col-num col-total" id="subtot-pppkpw-jab-all">0</th>
					</tr>
				</tfoot>
			</table>
		</div>

		<!-- Tabel Pendidikan PPPK-PW -->
		<div class="asndd-box asndd-mt-20">
			<h3><span class="dashicons dashicons-welcome-learn-more"></span> <?php esc_html_e( 'Berdasarkan Tingkat Pendidikan & Jenis Jabatan (PPPK-PW)', 'asn-data-dashboard' ); ?></h3>
			<table class="wp-list-table widefat fixed striped asndd-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Tingkat Pendidikan', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Guru', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Nakes', 'asn-data-dashboard' ); ?></th>
						<th class="col-num"><?php esc_html_e( 'Teknis', 'asn-data-dashboard' ); ?></th>
						<th class="col-num col-total"><?php esc_html_e( 'Jumlah', 'asn-data-dashboard' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $pendidikan as $key => $label ) : ?>
						<tr>
							<td><strong><?php echo esc_html( $label ); ?></strong></td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppkPw.didik.<?php echo esc_attr( $key ); ?>.guru" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppkPw.didik.<?php echo esc_attr( $key ); ?>.nakes" value="0">
							</td>
							<td class="col-num">
								<input type="number" min="0" class="asndd-input-num" data-path="pppkPw.didik.<?php echo esc_attr( $key ); ?>.teknis" value="0">
							</td>
							<td class="col-num col-total">
								<span class="asndd-row-total" id="tot-pppkpw-didik-<?php echo esc_attr( $key ); ?>">0</span>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr class="asndd-tfoot-total">
						<th><?php esc_html_e( 'TOTAL PENDIDIKAN PPPK-PW', 'asn-data-dashboard' ); ?></th>
						<th class="col-num" id="subtot-pppkpw-didik-guru">0</th>
						<th class="col-num" id="subtot-pppkpw-didik-nakes">0</th>
						<th class="col-num" id="subtot-pppkpw-didik-teknis">0</th>
						<th class="col-num col-total" id="subtot-pppkpw-didik-all">0</th>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>


	<!-- TAB CONTENT: CHARTS -->
	<div id="tab-charts" class="asndd-tab-content">
		<div class="asndd-grid-2">
			<!-- Chart 1: Jabatan Seluruh ASN per Gender -->
			<div class="asndd-box">
				<h3><?php esc_html_e( 'Jabatan Seluruh ASN per Gender', 'asn-data-dashboard' ); ?></h3>
				<div class="asndd-chart-container">
					<canvas id="chart-pns-jabatan"></canvas>
				</div>
			</div>

			<!-- Chart 2: Pangkat & Golongan Seluruh ASN per Gender -->
			<div class="asndd-box">
				<h3><?php esc_html_e( 'Pangkat & Golongan Seluruh ASN per Gender', 'asn-data-dashboard' ); ?></h3>
				<div class="asndd-chart-container">
					<canvas id="chart-pns-pangkat"></canvas>
				</div>
			</div>
		</div>

		<div class="asndd-grid-3 asndd-mt-20">
			<!-- Chart 3: Donut Pendidikan Seluruh ASN -->
			<div class="asndd-box">
				<h3><?php esc_html_e( 'Komposisi Pendidikan Seluruh ASN', 'asn-data-dashboard' ); ?></h3>
				<div class="asndd-chart-container asndd-chart-square">
					<canvas id="chart-pns-pendidikan"></canvas>
				</div>
			</div>


			<!-- Chart 4: Donut Gender Keseluruhan -->
			<div class="asndd-box">
				<h3><?php esc_html_e( 'Distribusi Gender Total ASN', 'asn-data-dashboard' ); ?></h3>
				<div class="asndd-chart-container asndd-chart-square">
					<canvas id="chart-gender-total"></canvas>
				</div>
			</div>

			<!-- Chart 5: Comparison Types -->
			<div class="asndd-box">
				<h3><?php esc_html_e( 'Komposisi Jenis Pegawai', 'asn-data-dashboard' ); ?></h3>
				<div class="asndd-chart-container asndd-chart-square">
					<canvas id="chart-jenis-pegawai"></canvas>
				</div>
			</div>
		</div>
	</div>

</div><!-- /.wrap -->

<!-- MODAL: BUAT PERIODE BARU -->
<div class="asndd-modal" id="asndd-modal-new">
	<div class="asndd-modal-content">
		<div class="asndd-modal-header">
			<h3><?php esc_html_e( 'Buat Periode Data Baru', 'asn-data-dashboard' ); ?></h3>
			<button type="button" class="asndd-modal-close">&times;</button>
		</div>
		<div class="asndd-modal-body">
			<label for="asndd-new-periode-input"><strong><?php esc_html_e( 'Pilih Bulan & Tahun:', 'asn-data-dashboard' ); ?></strong></label>
			<input type="month" id="asndd-new-periode-input" class="widefat" value="<?php echo esc_attr( date( 'Y-m' ) ); ?>">
			<p class="description"><?php esc_html_e( 'Format: YYYY-MM (Contoh: 2026-06). Jika periode sudah ada, data lama akan dimuat.', 'asn-data-dashboard' ); ?></p>
		</div>
		<div class="asndd-modal-footer">
			<button type="button" class="button button-secondary asndd-modal-close-btn"><?php esc_html_e( 'Batal', 'asn-data-dashboard' ); ?></button>
			<button type="button" class="button button-primary" id="asndd-btn-confirm-new"><?php esc_html_e( 'Buka Periode', 'asn-data-dashboard' ); ?></button>
		</div>
	</div>
</div>

<!-- MODAL: SALIN PERIODE -->
<div class="asndd-modal" id="asndd-modal-copy">
	<div class="asndd-modal-content">
		<div class="asndd-modal-header">
			<h3><?php esc_html_e( 'Salin Data dari Periode Lain', 'asn-data-dashboard' ); ?></h3>
			<button type="button" class="asndd-modal-close">&times;</button>
		</div>
		<div class="asndd-modal-body">
			<p><?php esc_html_e( 'Salin data dari periode terdaftar sebagai titik awal untuk periode aktif saat ini.', 'asn-data-dashboard' ); ?></p>
			
			<div class="asndd-form-field">
				<label for="asndd-copy-source-select"><strong><?php esc_html_e( 'Salin Dari Periode (Asal):', 'asn-data-dashboard' ); ?></strong></label>
				<select id="asndd-copy-source-select" class="widefat">
					<?php foreach ( $periods as $p ) : ?>
						<option value="<?php echo esc_attr( $p ); ?>"><?php echo esc_html( $p ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="asndd-form-field asndd-mt-10">
				<label for="asndd-copy-target-display"><strong><?php esc_html_e( 'Ke Periode Aktif (Tujuan):', 'asn-data-dashboard' ); ?></strong></label>
				<input type="text" id="asndd-copy-target-display" class="widefat" value="<?php echo esc_attr( $current_period ); ?>" readonly>
			</div>
		</div>
		<div class="asndd-modal-footer">
			<button type="button" class="button button-secondary asndd-modal-close-btn"><?php esc_html_e( 'Batal', 'asn-data-dashboard' ); ?></button>
			<button type="button" class="button button-primary" id="asndd-btn-confirm-copy"><?php esc_html_e( 'Proses Salin', 'asn-data-dashboard' ); ?></button>
		</div>
	</div>
</div>
