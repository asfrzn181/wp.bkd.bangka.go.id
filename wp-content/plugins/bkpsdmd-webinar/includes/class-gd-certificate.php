<?php
/**
 * GD Image Certificate Generator
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class WBR_GD_Certificate {

    public static function generate_image( $cert_id ) {
        global $wpdb;

        $cert = WBR_Certificate::get_by_id( $cert_id );
        if ( ! $cert ) return [ 'success' => false, 'message' => 'Petikan tidak ditemukan.' ];

        $reg  = json_decode( $cert->reg_data ?? '{}', true ) ?: [];

        // Ambil data Webinar
        $webinar_post = get_post( $cert->webinar_id );
        $meta = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}webinar_meta WHERE post_id = %d", $cert->webinar_id
        ) );

        if ( ! $webinar_post || ! $meta ) {
            return [ 'success' => false, 'message' => 'Data webinar tidak ditemukan.' ];
        }

        $nama = self::extract_field( $reg, 'nama' ) ?: $cert->holder_name;

        // Siapkan path template dan font
        $template_file = WBR_UPLOAD . 'templates/' . basename( $meta->petikan_template_file ?? 'template-sertifikat.jpg' );
        if ( ! file_exists( $template_file ) || is_dir( $template_file ) ) {
            $template_file = WBR_PATH . 'assets/images/default-certificate.jpg';
            self::ensure_default_template( $template_file );
        }

        // Cek font — JANGAN download dari internet (server hosting sering tidak bisa)
        $font_file = WBR_PATH . 'assets/fonts/Roboto-Regular.ttf';
        $has_font  = file_exists( $font_file ) && filesize( $font_file ) > 1000;

        // Buat image resource
        $ext = strtolower( pathinfo( $template_file, PATHINFO_EXTENSION ) );
        if ( $ext === 'png' ) {
            $image = @imagecreatefrompng( $template_file );
        } else {
            $image = @imagecreatefromjpeg( $template_file );
        }

        if ( ! $image ) {
             // Fallback buat blank image
             $image = imagecreatetruecolor( 1200, 800 );
             $bg_color = imagecolorallocate( $image, 255, 255, 255 );
             imagefill( $image, 0, 0, $bg_color );
        }

        // Pastikan alpha blending aktif untuk overlay transparan
        imagealphablending( $image, true );

        $width = imagesx( $image );
        $height = imagesy( $image );

        $text_color = imagecolorallocate( $image, 0, 0, 0 );
        $gray_color = imagecolorallocate( $image, 100, 100, 100 );

        // Tambah teks (Nama)
        if ( $has_font ) {
            // Tulis Nama (Tengah) - dengan Auto Scaling
            $font_size_nama = 60; // Start ukuran besar
            $max_width_nama = $width * 0.8; // Maksimal 80% lebar gambar
            
            while ( $font_size_nama > 10 ) {
                $bbox = imagettfbbox( $font_size_nama, 0, $font_file, $nama );
                $text_width = $bbox[2] - $bbox[0];
                if ( $text_width <= $max_width_nama ) break;
                $font_size_nama -= 2;
            }
            
            $x_nama = (int) (( $width - $text_width ) / 2);
            $y_nama = (int) ($height / 2);
            imagettftext( $image, $font_size_nama, 0, $x_nama, $y_nama, $text_color, $font_file, $nama );

            // Tulis Judul Webinar
            $font_size_webinar = 24;
            $bbox2 = imagettfbbox( $font_size_webinar, 0, $font_file, $webinar_post->post_title );
            $text_width2 = $bbox2[2] - $bbox2[0];
            $x_webinar = (int) (( $width - $text_width2 ) / 2);
            $y_webinar = (int) ($y_nama + 80);
            imagettftext( $image, $font_size_webinar, 0, $x_webinar, $y_webinar, $gray_color, $font_file, $webinar_post->post_title );
            
            // Tulis Nomor Sertifikat
            $font_size_no = 18;
            imagettftext( $image, $font_size_no, 0, 50, 50, $gray_color, $font_file, 'No: ' . $cert->petikan_number );
            
            // Signature PNG overlay
            if ( ! empty( $meta->signature_image_file ) ) {
                $sig_file = WBR_UPLOAD . 'templates/' . basename( $meta->signature_image_file );
                if ( file_exists( $sig_file ) ) {
                    $sig_image = @imagecreatefrompng( $sig_file );
                    if ( $sig_image ) {
                        $sig_w = imagesx( $sig_image );
                        $sig_h = imagesy( $sig_image );
                        
                        // Maksimal lebar ttd = 300px (disesuaikan dgn resolusi)
                        $max_sig_w = $width * 0.25; 
                        if ( $sig_w > $max_sig_w ) {
                            $ratio = $max_sig_w / $sig_w;
                            $new_sig_w = (int) $max_sig_w;
                            $new_sig_h = (int) ($sig_h * $ratio);
                            
                            $resized_sig = imagecreatetruecolor( $new_sig_w, $new_sig_h );
                            imagealphablending( $resized_sig, false );
                            imagesavealpha( $resized_sig, true );
                            $transparent = imagecolorallocatealpha($resized_sig, 255, 255, 255, 127);
                            imagefilledrectangle($resized_sig, 0, 0, $new_sig_w, $new_sig_h, $transparent);
                            
                            imagecopyresampled( $resized_sig, $sig_image, 0, 0, 0, 0, $new_sig_w, $new_sig_h, $sig_w, $sig_h );
                            imagedestroy( $sig_image );
                            $sig_image = $resized_sig;
                            $sig_w = $new_sig_w;
                            $sig_h = $new_sig_h;
                        }
                        
                        // Tempel di kanan bawah (agak ke atas QR)
                        imagecopy( $image, $sig_image, (int)($width - $sig_w - ($width*0.1)), (int)($height - $sig_h - ($height*0.1)), 0, 0, $sig_w, $sig_h );
                        imagedestroy( $sig_image );
                    }
                }
            }

            // QR Code Placeholder jika ada hash
            if ( $cert->qr_verification_hash ) {
                $verify_url   = home_url( '/verifikasi-petikan/' . $cert->qr_verification_hash );
                $qr_dir       = WBR_UPLOAD . 'certificates/qr/';
                wp_mkdir_p( $qr_dir );
                $qr_file = $qr_dir . 'qr-' . $cert_id . '.png';
                WBR_QRCode::generate_file( $verify_url, $qr_file, 4 );
                
                if ( file_exists( $qr_file ) ) {
                    $qr_image = @imagecreatefrompng( $qr_file );
                    if ( $qr_image ) {
                        // Tempel di pojok kanan bawah
                        $qr_w = imagesx( $qr_image );
                        $qr_h = imagesy( $qr_image );
                        imagecopy( $image, $qr_image, $width - $qr_w - 50, $height - $qr_h - 50, 0, 0, $qr_w, $qr_h );
                        imagedestroy( $qr_image );
                    }
                }
            }
        } else {
            // Fallback tanpa TTF
            imagestring( $image, 5, 50, 50, 'No: ' . $cert->petikan_number, $text_color );
            imagestring( $image, 5, (int)($width/2 - strlen($nama)*4), (int)($height/2), $nama, $text_color );
            imagestring( $image, 5, (int)($width/2 - strlen($webinar_post->post_title)*4), (int)($height/2 + 50), $webinar_post->post_title, $gray_color );
        }

        // Simpan gambar ke disk sebagai format sementara atau final
        $cert_dir  = WBR_UPLOAD . 'certificates/';
        wp_mkdir_p( $cert_dir );
        
        $img_name = 'petikan-' . $cert_id . '-' . time() . '.jpg';
        $img_path = $cert_dir . $img_name;

        imagejpeg( $image, $img_path, 100 ); // Kualitas 100
        imagedestroy( $image );

        if ( ! file_exists( $img_path ) ) {
            return [ 'success' => false, 'message' => 'Gagal menyimpan file gambar sertifikat.' ];
        }

        // Jika TCPDF tersedia, konversi ke PDF dan tandatangani secara digital
        if ( class_exists( 'TCPDF' ) ) {
            try {
                $pdf_name = 'petikan-' . $cert_id . '-' . time() . '.pdf';
                $pdf_path = $cert_dir . $pdf_name;

                // Konversi px ke mm untuk dimensi PDF (asumsi 72dpi, 1px = 0.352778 mm)
                $w_mm = $width * 0.264583;
                $h_mm = $height * 0.264583;
                $orientation = $w_mm > $h_mm ? 'L' : 'P';

                $pdf = new TCPDF( $orientation, 'mm', [$w_mm, $h_mm], true, 'UTF-8', false );
                $pdf->setPrintHeader(false);
                $pdf->setPrintFooter(false);
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false, 0);

                // Setup Digital Signature
                // $cert_info = self::ensure_self_signed_cert();
                // if ( $cert_info ) {
                //     $info = array(
                //         'Name' => 'Sistem Webinar BKPSDMD',
                //         'Location' => 'BKPSDMD Kabupaten Bangka',
                //         'Reason' => 'Sertifikat Elektronik (Terverifikasi Sistem)',
                //         'ContactInfo' => 'bkpsdmd@bangka.go.id',
                //     );
                //     // Tambah signature invisible
                //     $pdf->setSignature( $cert_info['crt'], $cert_info['key'], '', '', 2, $info );
                // }

                $pdf->AddPage();
                
                // Tempel gambar menutupi halaman PDF
                $pdf->Image( $img_path, 0, 0, $w_mm, $h_mm, 'JPG', '', '', false, 300, '', false, false, 0 );
                
                // Simpan PDF
                $pdf->Output( $pdf_path, 'F' );

                // Hapus file JPG sementara karena sudah ada PDF
                @unlink( $img_path );

                return [ 'success' => true, 'filename' => $pdf_name, 'path' => $pdf_path ];
            } catch ( Exception $e ) {
                // Abaikan error PDF, lanjutkan dengan mengembalikan format JPG
            }
        }

        // Kembalikan JPG sebagai default (jika TCPDF belum diinstall)
        return [ 'success' => true, 'filename' => $img_name, 'path' => $img_path ];
    }

    private static function ensure_self_signed_cert() {
        $dir = WBR_UPLOAD . 'certs/';
        wp_mkdir_p( $dir );
        $crt_file = $dir . 'tcpdf.crt';
        $key_file = $dir . 'tcpdf.pem';

        if ( file_exists( $crt_file ) && file_exists( $key_file ) ) {
            return [ 'crt' => 'file://' . $crt_file, 'key' => 'file://' . $key_file ];
        }

        if ( ! function_exists( 'openssl_pkey_new' ) ) return false;

        $dn = array(
            "countryName" => "ID",
            "stateOrProvinceName" => "Bangka Belitung",
            "localityName" => "Sungailiat",
            "organizationName" => "BKPSDMD Kabupaten Bangka",
            "organizationalUnitName" => "Sistem Webinar",
            "commonName" => "Sertifikat Elektronik BKPSDMD"
        );

        $privkey = openssl_pkey_new(array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ));
        
        if ( !$privkey ) return false;

        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha256'));
        if ( !$csr ) return false;
        
        $x509 = openssl_x509_create($csr, null, $privkey, 3650, array('digest_alg' => 'sha256'));
        if ( !$x509 ) return false;

        openssl_x509_export($x509, $certout);
        openssl_pkey_export($privkey, $pkeyout);

        file_put_contents( $crt_file, $certout );
        file_put_contents( $key_file, $pkeyout );

        return [ 'crt' => 'file://' . $crt_file, 'key' => 'file://' . $key_file ];
    }

    private static function extract_field( $data, $hint ) {
        foreach ( (array) $data as $key => $val ) {
            if ( is_string( $val ) && stripos( $key, $hint ) !== false ) return $val;
        }
        return '';
    }

    private static function ensure_default_template( $file_path ) {
        if ( file_exists( $file_path ) ) return;
        wp_mkdir_p( dirname( $file_path ) );
        // Buat blank template image dengan tulisan SERTIFIKAT
        $image = imagecreatetruecolor( 1200, 800 );
        $bg = imagecolorallocate( $image, 240, 248, 255 ); // AliceBlue
        imagefill( $image, 0, 0, $bg );
        $border = imagecolorallocate( $image, 70, 130, 180 ); // SteelBlue
        imagerectangle( $image, 20, 20, 1180, 780, $border );
        imagejpeg( $image, $file_path, 90 );
        imagedestroy( $image );
    }

    private static function ensure_default_font( $font_file ) {
        // Font harus sudah tersedia di dalam direktori plugin.
        // Fungsi ini tidak lagi mendownload dari internet karena server hosting
        // umumnya tidak memiliki akses keluar (outbound) yang diperlukan.
        if ( file_exists( $font_file ) && filesize( $font_file ) > 1000 ) {
            return; // Font sudah ada dan valid
        }
        wp_mkdir_p( dirname( $font_file ) );
        // Coba download hanya jika benar-benar tidak ada (lokal dev)
        if ( function_exists( 'curl_init' ) ) {
            $font_url  = 'https://github.com/google/fonts/raw/main/ofl/roboto/Roboto-Regular.ttf';
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $font_url );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
            curl_setopt( $ch, CURLOPT_TIMEOUT, 10 ); // Timeout 10 detik
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            $font_data = curl_exec( $ch );
            curl_close( $ch );
            if ( $font_data && strlen( $font_data ) > 10000 ) {
                file_put_contents( $font_file, $font_data );
            }
        }
    }
}
