<?php
/**
 * Admin Page - BKPSDMD Struktur Organisasi
 * Halaman manajemen di WordPress Admin
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class BKPSDMD_Org_Admin {

    public static function init() {
        add_action( 'admin_menu',             array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts',  array( __CLASS__, 'enqueue_assets' ) );
        add_action( 'admin_post_bkpsdmd_org_save',   array( __CLASS__, 'handle_save' ) );
        add_action( 'admin_post_bkpsdmd_org_delete', array( __CLASS__, 'handle_delete' ) );
        add_action( 'admin_post_bkpsdmd_org_import', array( __CLASS__, 'handle_import' ) );
    }

    public static function register_menu() {
        add_menu_page(
            'Struktur Organisasi',
            'Struktur Organisasi',
            'manage_options',
            'bkpsdmd-org-chart',
            array( __CLASS__, 'page_main' ),
            'dashicons-networking',
            30
        );
        add_submenu_page(
            'bkpsdmd-org-chart',
            'Tambah / Edit Jabatan',
            'Tambah Jabatan',
            'manage_options',
            'bkpsdmd-org-add',
            array( __CLASS__, 'page_form' )
        );
    }

    public static function enqueue_assets( $hook ) {
        if ( strpos( $hook, 'bkpsdmd-org' ) === false ) return;

        wp_enqueue_media(); // WordPress media uploader
        wp_enqueue_style(
            'bkpsdmd-org-admin',
            BKPSDMD_ORG_URL . 'assets/css/admin.css',
            array(),
            BKPSDMD_ORG_VERSION
        );
        wp_enqueue_script(
            'bkpsdmd-org-admin',
            BKPSDMD_ORG_URL . 'assets/js/admin.js',
            array( 'jquery' ),
            BKPSDMD_ORG_VERSION,
            true
        );
    }

    /**
     * Halaman utama: daftar semua jabatan
     */
    public static function page_main() {
        $rows    = BKPSDMD_Org_DB::get_all();
        $tree    = BKPSDMD_Org_DB::build_tree( $rows );
        $notice  = '';

        if ( isset( $_GET['saved'] ) )   $notice = '<div class="notice notice-success is-dismissible"><p>✅ Data berhasil disimpan.</p></div>';
        if ( isset( $_GET['deleted'] ) ) $notice = '<div class="notice notice-warning is-dismissible"><p>🗑️ Data berhasil dihapus.</p></div>';
        if ( isset( $_GET['error'] ) )   $notice = '<div class="notice notice-error is-dismissible"><p>❌ Terjadi kesalahan. Silakan coba lagi.</p></div>';
        if ( isset( $_GET['imported'] ) ) {
            $n = intval( $_GET['imported'] );
            $notice = '<div class="notice notice-success is-dismissible"><p>✅ Import berhasil! <strong>' . $n . ' jabatan</strong> berhasil dimasukkan ke database.</p></div>';
        }
        if ( isset( $_GET['already'] ) ) {
            $notice = '<div class="notice notice-warning is-dismissible"><p>⚠️ Data sudah ada. Gunakan tombol <strong>"Import Ulang (Reset)"</strong> jika ingin menimpa data lama.</p></div>';
        }
        ?>
        <div class="wrap bkpsdmd-admin-wrap">
            <div class="bkpsdmd-admin-header">
                <div class="bkpsdmd-admin-header-left">
                    <span class="dashicons dashicons-networking bkpsdmd-header-icon"></span>
                    <div>
                        <h1>Struktur Organisasi</h1>
                        <p>Kelola jabatan dan pegawai dalam hierarki organisasi dinas.</p>
                    </div>
                </div>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-org-add' ) ); ?>" class="bkpsdmd-btn bkpsdmd-btn-primary">
                    <span class="dashicons dashicons-plus-alt2"></span> Tambah Jabatan
                </a>
            </div>

            <?php echo wp_kses_post( $notice ); ?>

            <!-- Shortcode info -->
            <div class="bkpsdmd-shortcode-info">
                <span class="dashicons dashicons-info-outline"></span>
                Gunakan shortcode ini di halaman mana saja:
                <code>[struktur_organisasi judul="Struktur Organisasi"]</code>
                <button class="bkpsdmd-copy-btn" onclick="navigator.clipboard.writeText('[struktur_organisasi judul=\'Struktur Organisasi\']');this.textContent='✓ Disalin!'">Salin</button>
            </div>

            <!-- Panel Import BKPSDMD -->
            <div class="bkpsdmd-import-panel">
                <div class="bkpsdmd-import-panel-left">
                    <span class="dashicons dashicons-database-import bkpsdmd-import-icon"></span>
                    <div>
                        <strong>Import Struktur Jabatan BKPSDMD Bangka</strong>
                        <p>Otomatis mengisi <strong>61 jabatan</strong> lengkap sesuai struktur organisasi resmi BKPSDMD Bangka. Nama pejabat dapat diisi setelah import.</p>
                    </div>
                </div>
                <div class="bkpsdmd-import-panel-actions">
                    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                        <?php wp_nonce_field( 'bkpsdmd_org_import', 'bkpsdmd_import_nonce' ); ?>
                        <input type="hidden" name="action" value="bkpsdmd_org_import">
                        <input type="hidden" name="reset" value="0">
                        <button type="submit" class="bkpsdmd-btn bkpsdmd-btn-import"
                            onclick="return confirm('Import struktur jabatan BKPSDMD? Data yang sudah ada TIDAK akan ditimpa.')">
                            <span class="dashicons dashicons-database-import"></span> Import Struktur
                        </button>
                    </form>
                    <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:inline">
                        <?php wp_nonce_field( 'bkpsdmd_org_import', 'bkpsdmd_import_nonce' ); ?>
                        <input type="hidden" name="action" value="bkpsdmd_org_import">
                        <input type="hidden" name="reset" value="1">
                        <button type="submit" class="bkpsdmd-btn bkpsdmd-btn-danger-sm bkpsdmd-btn-reset"
                            onclick="return confirm('PERHATIAN: Semua data akan DIHAPUS dan diganti dengan struktur default BKPSDMD. Lanjutkan?')">
                            <span class="dashicons dashicons-update"></span> Import Ulang (Reset)
                        </button>
                    </form>
                </div>
            </div>

            <?php if ( empty( $rows ) ) : ?>
            <div class="bkpsdmd-empty-state">
                <span class="dashicons dashicons-networking bkpsdmd-empty-icon"></span>
                <h3>Belum Ada Data</h3>
                <p>Mulai dengan menambahkan jabatan pertama Anda.</p>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-org-add' ) ); ?>" class="bkpsdmd-btn bkpsdmd-btn-primary">
                    Tambah Jabatan Pertama
                </a>
            </div>
            <?php else : ?>

            <!-- Tabel daftar jabatan -->
            <div class="bkpsdmd-table-wrap">
                <table class="bkpsdmd-table">
                    <thead>
                        <tr>
                            <th width="40">#</th>
                            <th>Jabatan</th>
                            <th>Nama Pegawai</th>
                            <th>NIP</th>
                            <th width="80">Foto</th>
                            <th width="80">Urutan</th>
                            <th width="70">Status</th>
                            <th width="160">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php self::render_table_rows( $tree ); ?>
                    </tbody>
                </table>
            </div>

            <!-- Preview mini -->
            <div class="bkpsdmd-preview-section">
                <h3><span class="dashicons dashicons-visibility"></span> Preview Struktur</h3>
                <div class="bkpsdmd-preview-tree">
                    <?php self::render_preview_tree( $tree ); ?>
                </div>
            </div>

            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render baris tabel rekursif (dengan indentasi)
     */
    private static function render_table_rows( $nodes, $depth = 0 ) {
        $no = 1;
        foreach ( $nodes as $node ) {
            $indent = str_repeat( '&nbsp;&nbsp;&nbsp;&nbsp;', $depth );
            $prefix = $depth > 0 ? '└ ' : '';
            $status_cls = $node->aktif ? 'bkpsdmd-status-aktif' : 'bkpsdmd-status-nonaktif';
            $status_txt = $node->aktif ? 'Aktif' : 'Nonaktif';
            $foto_url   = ! empty( $node->foto_url ) ? esc_url( $node->foto_url ) : BKPSDMD_ORG_URL . 'assets/images/default-avatar.png';
            ?>
            <tr>
                <td><?php echo intval( $node->id ); ?></td>
                <td>
                    <span class="bkpsdmd-indent"><?php echo wp_kses_post( $indent . $prefix ); ?></span>
                    <strong><?php echo esc_html( $node->jabatan ); ?></strong>
                </td>
                <td><?php echo esc_html( $node->nama ?: '-' ); ?></td>
                <td><code><?php echo esc_html( $node->nip ?: '-' ); ?></code></td>
                <td>
                    <img src="<?php echo esc_attr( $foto_url ); ?>"
                         alt="foto" class="bkpsdmd-thumb"
                         onerror="this.src='<?php echo esc_js( BKPSDMD_ORG_URL . 'assets/images/default-avatar.png' ); ?>'">
                </td>
                <td class="bkpsdmd-center"><?php echo intval( $node->urutan ); ?></td>
                <td><span class="bkpsdmd-status <?php echo esc_attr( $status_cls ); ?>"><?php echo esc_html( $status_txt ); ?></span></td>
                <td>
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-org-add&edit=' . $node->id ) ); ?>"
                       class="bkpsdmd-btn bkpsdmd-btn-sm bkpsdmd-btn-edit">
                        <span class="dashicons dashicons-edit-page"></span> Edit
                    </a>
                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=bkpsdmd_org_delete&id=' . $node->id ), 'bkpsdmd_org_delete_' . $node->id ) ); ?>"
                       class="bkpsdmd-btn bkpsdmd-btn-sm bkpsdmd-btn-delete"
                       onclick="return confirm('Hapus jabatan ini? Jabatan anak akan dipindah ke root.')">
                        <span class="dashicons dashicons-trash"></span> Hapus
                    </a>
                </td>
            </tr>
            <?php
            if ( ! empty( $node->children ) ) {
                self::render_table_rows( $node->children, $depth + 1 );
            }
            $no++;
        }
    }

    /**
     * Render preview tree sederhana
     */
    private static function render_preview_tree( $nodes, $depth = 0 ) {
        if ( empty( $nodes ) ) return;
        echo '<ul class="bkpsdmd-preview-ul">';
        foreach ( $nodes as $node ) {
            echo '<li>';
            echo '<span class="bkpsdmd-preview-node">';
            echo '<strong>' . esc_html( $node->jabatan ) . '</strong>';
            if ( $node->nama ) echo ' — ' . esc_html( $node->nama );
            echo '</span>';
            if ( ! empty( $node->children ) ) {
                self::render_preview_tree( $node->children, $depth + 1 );
            }
            echo '</li>';
        }
        echo '</ul>';
    }

    /**
     * Halaman form tambah/edit
     */
    public static function page_form() {
        $edit_id = isset( $_GET['edit'] ) ? intval( $_GET['edit'] ) : 0;
        $node    = $edit_id ? BKPSDMD_Org_DB::get_one( $edit_id ) : null;
        $all     = BKPSDMD_Org_DB::get_all();
        $title   = $node ? 'Edit Jabatan' : 'Tambah Jabatan Baru';
        ?>
        <div class="wrap bkpsdmd-admin-wrap">
            <div class="bkpsdmd-admin-header">
                <div class="bkpsdmd-admin-header-left">
                    <span class="dashicons dashicons-<?php echo $node ? 'edit' : 'plus-alt2'; ?> bkpsdmd-header-icon"></span>
                    <div>
                        <h1><?php echo esc_html( $title ); ?></h1>
                        <p><a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-org-chart' ) ); ?>">← Kembali ke daftar</a></p>
                    </div>
                </div>
            </div>

            <div class="bkpsdmd-form-wrap">
                <form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="bkpsdmd-org-form">
                    <?php wp_nonce_field( 'bkpsdmd_org_save', 'bkpsdmd_org_nonce' ); ?>
                    <input type="hidden" name="action" value="bkpsdmd_org_save">
                    <input type="hidden" name="edit_id" value="<?php echo intval( $edit_id ); ?>">

                    <div class="bkpsdmd-form-grid">

                        <!-- Kolom kiri -->
                        <div class="bkpsdmd-form-col">

                            <div class="bkpsdmd-form-group">
                                <label for="jabatan">Nama Jabatan <span class="required">*</span></label>
                                <input type="text" id="jabatan" name="jabatan"
                                       value="<?php echo esc_attr( $node->jabatan ?? '' ); ?>"
                                       placeholder="cth: Kepala Dinas" required>
                                <small>Nama jabatan/posisi dalam struktur organisasi.</small>
                            </div>

                            <div class="bkpsdmd-form-group">
                                <label for="nama">Nama Pegawai</label>
                                <input type="text" id="nama" name="nama"
                                       value="<?php echo esc_attr( $node->nama ?? '' ); ?>"
                                       placeholder="cth: Dr. Bambang Sutrisno, M.Si.">
                            </div>

                            <div class="bkpsdmd-form-group">
                                <label for="nip">NIP</label>
                                <input type="text" id="nip" name="nip"
                                       value="<?php echo esc_attr( $node->nip ?? '' ); ?>"
                                       placeholder="cth: 196501011990031001">
                            </div>

                            <div class="bkpsdmd-form-group">
                                <label for="parent_id">Atasan Langsung (Induk)</label>
                                <select id="parent_id" name="parent_id">
                                    <option value="0">-- Tidak Ada (Level Teratas) --</option>
                                    <?php foreach ( $all as $row ) :
                                        if ( $row->id === $edit_id ) continue; // jangan bisa jadi induk diri sendiri
                                        $selected = ( isset( $node->parent_id ) && intval( $node->parent_id ) === intval( $row->id ) ) ? 'selected' : '';
                                    ?>
                                    <option value="<?php echo intval( $row->id ); ?>" <?php echo esc_attr( $selected ); ?>>
                                        <?php echo esc_html( $row->jabatan . ( $row->nama ? ' (' . $row->nama . ')' : '' ) ); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <small>Pilih jabatan atasan langsung. Biarkan kosong jika ini jabatan tertinggi.</small>
                            </div>

                            <div class="bkpsdmd-form-row-2">
                                <div class="bkpsdmd-form-group">
                                    <label for="urutan">Urutan Tampil</label>
                                    <input type="number" id="urutan" name="urutan" min="0" max="999"
                                           value="<?php echo intval( $node->urutan ?? 0 ); ?>">
                                    <small>Angka kecil tampil lebih awal.</small>
                                </div>
                                <div class="bkpsdmd-form-group">
                                    <label for="aktif">Status</label>
                                    <select id="aktif" name="aktif">
                                        <option value="1" <?php selected( $node->aktif ?? 1, 1 ); ?>>Aktif</option>
                                        <option value="0" <?php selected( $node->aktif ?? 1, 0 ); ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>

                            <div class="bkpsdmd-form-group">
                                <label for="keterangan">Keterangan</label>
                                <textarea id="keterangan" name="keterangan" rows="3"
                                          placeholder="Keterangan tambahan (opsional)"><?php echo esc_textarea( $node->keterangan ?? '' ); ?></textarea>
                            </div>

                        </div><!-- /kolom kiri -->

                        <!-- Kolom kanan: upload foto -->
                        <div class="bkpsdmd-form-col">
                            <div class="bkpsdmd-form-group">
                                <label>Foto Pegawai</label>
                                <div class="bkpsdmd-upload-area" id="bkpsdmd-upload-area">

                                    <!-- Preview foto -->
                                    <div class="bkpsdmd-upload-preview" id="bkpsdmd-upload-preview">
                                        <?php
                                        $foto     = $node->foto_url ?? '';
                                        $foto_src = $foto ?: BKPSDMD_ORG_URL . 'assets/images/default-avatar.svg';
                                        echo '<img src="' . esc_url( $foto_src ) . '" alt="foto" id="bkpsdmd-preview-img">';
                                        ?>
                                    </div>

                                    <!-- Field URL — langsung ber-name="foto_url", disubmit oleh form -->
                                    <div class="bkpsdmd-url-input-wrap">
                                        <label for="foto_url" class="bkpsdmd-url-label">
                                            <span class="dashicons dashicons-admin-links"></span> URL Foto
                                        </label>
                                        <div class="bkpsdmd-url-row">
                                            <input type="url"
                                                   name="foto_url"
                                                   id="foto_url"
                                                   placeholder="https://contoh.com/foto-pegawai.jpg"
                                                   value="<?php echo esc_attr( $foto ); ?>"
                                                   autocomplete="off">
                                            <button type="button" class="bkpsdmd-btn bkpsdmd-btn-sm bkpsdmd-btn-outline" id="bkpsdmd-preview-btn">
                                                <span class="dashicons dashicons-visibility" style="font-size:14px;width:14px;height:14px;margin-top:2px"></span> Preview
                                            </button>
                                        </div>
                                        <small style="display:block;margin-top:4px">Ketik atau tempel URL, lalu klik Preview. Nilai langsung tersimpan saat form di-submit.</small>
                                    </div>

                                    <div class="bkpsdmd-upload-divider"><span>atau pilih dari</span></div>

                                    <div class="bkpsdmd-upload-actions">
                                        <button type="button" class="bkpsdmd-btn bkpsdmd-btn-outline" id="bkpsdmd-upload-btn">
                                            <span class="dashicons dashicons-format-image"></span> Media Library
                                        </button>
                                        <button type="button" class="bkpsdmd-btn bkpsdmd-btn-danger-sm" id="bkpsdmd-remove-foto"
                                                style="<?php echo empty( $foto ) ? 'display:none' : ''; ?>">
                                            <span class="dashicons dashicons-no-alt"></span> Hapus Foto
                                        </button>
                                    </div>
                                    <small>Dukung URL eksternal (simpeg, drive, dll.) atau upload via Media Library.</small>
                                </div>
                            </div>

                            <!-- Card preview -->
                            <div class="bkpsdmd-card-preview">
                                <p class="bkpsdmd-preview-label">Preview Kartu</p>
                                <div class="bkpsdmd-node-card bkpsdmd-node-level-0 bkpsdmd-preview-card">
                                    <div class="bkpsdmd-node-avatar">
                                        <img src="<?php echo esc_attr( ! empty( $node->foto_url ) ? $node->foto_url : BKPSDMD_ORG_URL . 'assets/images/default-avatar.png' ); ?>"
                                             id="bkpsdmd-preview-card-img">
                                    </div>
                                    <div class="bkpsdmd-node-info">
                                        <span class="bkpsdmd-node-jabatan" id="preview-jabatan"><?php echo esc_html( $node->jabatan ?? 'Nama Jabatan' ); ?></span>
                                        <span class="bkpsdmd-node-nama" id="preview-nama"><?php echo esc_html( $node->nama ?? 'Nama Pegawai' ); ?></span>
                                        <span class="bkpsdmd-node-nip" id="preview-nip"><?php echo ! empty( $node->nip ) ? 'NIP: ' . esc_html( $node->nip ) : ''; ?></span>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /kolom kanan -->
                    </div><!-- /form-grid -->

                    <div class="bkpsdmd-form-footer">
                        <button type="submit" class="bkpsdmd-btn bkpsdmd-btn-primary bkpsdmd-btn-lg">
                            <span class="dashicons dashicons-yes-alt"></span>
                            <?php echo $node ? 'Simpan Perubahan' : 'Tambah Jabatan'; ?>
                        </button>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=bkpsdmd-org-chart' ) ); ?>"
                           class="bkpsdmd-btn bkpsdmd-btn-ghost">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Handle form submit (save)
     */
    public static function handle_save() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Akses ditolak.' );
        check_admin_referer( 'bkpsdmd_org_save', 'bkpsdmd_org_nonce' );

        $edit_id = intval( $_POST['edit_id'] ?? 0 );
        $data    = $_POST; // sudah di-sanitize di class DB

        if ( $edit_id ) {
            $result = BKPSDMD_Org_DB::update( $edit_id, $data );
        } else {
            $result = BKPSDMD_Org_DB::insert( $data );
        }

        if ( $result !== false ) {
            wp_redirect( admin_url( 'admin.php?page=bkpsdmd-org-chart&saved=1' ) );
        } else {
            wp_redirect( admin_url( 'admin.php?page=bkpsdmd-org-chart&error=1' ) );
        }
        exit;
    }

    /**
     * Handle hapus
     */
    public static function handle_delete() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Akses ditolak.' );
        $id = intval( $_GET['id'] ?? 0 );
        check_admin_referer( 'bkpsdmd_org_delete_' . $id );

        BKPSDMD_Org_DB::delete( $id );
        wp_redirect( admin_url( 'admin.php?page=bkpsdmd-org-chart&deleted=1' ) );
        exit;
    }

    /**
     * Handle import struktur jabatan BKPSDMD
     */
    public static function handle_import() {
        if ( ! current_user_can( 'manage_options' ) ) wp_die( 'Akses ditolak.' );
        check_admin_referer( 'bkpsdmd_org_import', 'bkpsdmd_import_nonce' );

        $reset = intval( $_POST['reset'] ?? 0 );

        if ( $reset ) {
            // Hapus semua data lama, lalu import ulang
            BKPSDMD_Org_Seeder::truncate();
        } elseif ( BKPSDMD_Org_Seeder::already_imported() ) {
            // Data sudah ada, jangan timpa tanpa konfirmasi reset
            wp_redirect( admin_url( 'admin.php?page=bkpsdmd-org-chart&already=1' ) );
            exit;
        }

        $count = BKPSDMD_Org_Seeder::run();
        wp_redirect( admin_url( 'admin.php?page=bkpsdmd-org-chart&imported=' . $count ) );
        exit;
    }
}

BKPSDMD_Org_Admin::init();

