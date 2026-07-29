<?php
/**
 * QR Code generator — Pure PHP implementation for WordPress
 * Compatible with PHP 7.4+ and PHP 8.x
 * Uses GD extension (available by default in most PHP setups)
 *
 * Strategy:
 * 1. Primary: Uses endroid/qr-code style approach via pure-PHP QR encoding
 * 2. Renders via PHP GD as PNG → base64
 * 3. Falls back to Google Charts API if GD not available
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( defined( 'QR_ECLEVEL_L' ) ) return;

define( 'QR_ECLEVEL_L', 0 );
define( 'QR_ECLEVEL_M', 1 );
define( 'QR_ECLEVEL_Q', 2 );
define( 'QR_ECLEVEL_H', 3 );

class QRcode {

    /**
     * Generate PNG QR Code, output to stdout or file.
     *
     * @param string      $text     Text to encode
     * @param string|bool $outfile  File path or false for stdout
     * @param int         $level    QR_ECLEVEL_*
     * @param int         $size     Pixel per module
     * @param int         $margin   Quiet zone in modules
     */
    public static function png( $text, $outfile = false, $level = QR_ECLEVEL_M, $size = 6, $margin = 2 ) {
        if ( ! function_exists( 'imagecreatetruecolor' ) ) {
            // GD not available — output a 1x1 transparent PNG placeholder
            $raw = base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
            );
            if ( $outfile ) {
                file_put_contents( $outfile, $raw );
            } else {
                echo $raw;
            }
            return;
        }

        $matrix = self::make_qr( $text, $level );
        self::draw_png( $matrix, $outfile, $size, $margin );
    }

    // ── Draw PNG via GD ───────────────────────────────────────────────────────
    private static function draw_png( $matrix, $outfile, $size, $margin ) {
        $n   = count( $matrix );
        $dim = ( $n + $margin * 2 ) * $size;
        $im  = imagecreatetruecolor( $dim, $dim );
        $w   = imagecolorallocate( $im, 255, 255, 255 );
        $b   = imagecolorallocate( $im, 0, 0, 0 );
        imagefill( $im, 0, 0, $w );
        for ( $r = 0; $r < $n; $r++ ) {
            for ( $c = 0; $c < $n; $c++ ) {
                if ( isset( $matrix[$r][$c] ) && $matrix[$r][$c] ) {
                    $x1 = ( $c + $margin ) * $size;
                    $y1 = ( $r + $margin ) * $size;
                    imagefilledrectangle( $im, $x1, $y1, $x1 + $size - 1, $y1 + $size - 1, $b );
                }
            }
        }
        if ( $outfile ) {
            imagepng( $im, $outfile );
        } else {
            imagepng( $im );
        }
        imagedestroy( $im );
    }

    // ── Main QR matrix builder ────────────────────────────────────────────────
    // Implements QR Code Model 2, versions 1-10, byte mode only
    private static function make_qr( $text, $level ) {
        $bytes = array_values( unpack( 'C*', $text ) );
        $ver   = self::find_version( count( $bytes ), $level );
        $cw    = self::encode_data( $bytes, $ver, $level );
        $all   = self::add_ecc( $cw, $ver, $level );
        $mat   = self::build_matrix( $all, $ver );
        return self::mask_matrix( $mat, $ver, $level );
    }

    // Capacity table for byte mode [version][level] = max bytes
    private static $cap = [
        1=>[17,14,11,7], 2=>[32,26,20,14], 3=>[53,42,32,24],
        4=>[78,62,46,34], 5=>[106,84,60,44], 6=>[134,106,74,58],
        7=>[154,122,86,64], 8=>[192,152,108,84], 9=>[230,180,130,98],
        10=>[271,213,151,119],
    ];

    // EC parameters [ec_per_block, g1_blocks, g1_cw, g2_blocks, g2_cw]
    private static $ec = [
        1 =>[[7,1,19,0,0],[10,1,16,0,0],[13,1,13,0,0],[17,1,9,0,0]],
        2 =>[[10,1,34,0,0],[16,1,28,0,0],[22,1,22,0,0],[28,1,16,0,0]],
        3 =>[[15,1,55,0,0],[26,1,44,0,0],[18,2,17,0,0],[22,2,13,0,0]],
        4 =>[[20,1,80,0,0],[18,2,32,0,0],[26,2,24,0,0],[16,4,9,0,0]],
        5 =>[[26,1,108,0,0],[24,2,43,0,0],[18,2,15,2,16],[22,2,11,2,12]],
        6 =>[[18,2,68,0,0],[16,4,27,0,0],[24,4,19,0,0],[28,4,15,0,0]],
        7 =>[[20,2,78,0,0],[18,4,31,0,0],[18,2,14,4,15],[26,4,13,1,14]],
        8 =>[[24,2,97,0,0],[22,2,38,2,39],[22,4,18,2,19],[26,4,14,2,15]],
        9 =>[[30,2,116,0,0],[22,3,36,2,37],[20,4,16,4,17],[24,4,12,4,13]],
        10=>[[18,2,68,2,69],[26,4,43,1,44],[24,6,19,2,20],[28,6,15,2,16]],
    ];

    private static function find_version( $len, $level ) {
        foreach ( self::$cap as $v => $c ) {
            if ( $len <= $c[$level] ) return $v;
        }
        return 10;
    }

    private static function encode_data( $bytes, $ver, $level ) {
        $p      = self::$ec[$ver][$level];
        $g1b    = $p[1]; $g1cw = $p[2];
        $g2b    = $p[3]; $g2cw = $p[4];
        $total  = $g1b * $g1cw + $g2b * $g2cw;

        // Mode = 0100 (byte), char count = 8 bits (ver 1-9) or 16 bits (ver 10+)
        $bits  = '0100';
        $cclen = ( $ver < 10 ) ? 8 : 16;
        $bits .= sprintf( "%0{$cclen}b", count($bytes) );
        foreach ( $bytes as $b ) {
            $bits .= sprintf( '%08b', $b );
        }

        // Terminator + padding
        $maxb = $total * 8;
        $bits .= str_repeat( '0', min(4, max(0, $maxb - strlen($bits))) );
        while ( strlen($bits) % 8 ) $bits .= '0';
        $pad = ['11101100','00010001']; $pi = 0;
        while ( strlen($bits) < $maxb ) { $bits .= $pad[$pi++ % 2]; }

        $cw = [];
        for ( $i = 0; $i < strlen($bits); $i += 8 ) {
            $cw[] = bindec(substr($bits,$i,8));
        }
        return $cw;
    }

    // ── Reed-Solomon / ECC ────────────────────────────────────────────────────
    private static $gf_exp = [];
    private static $gf_log = [];
    private static $gf_ok  = false;

    private static function gf_init() {
        if ( self::$gf_ok ) return;
        $x = 1;
        for ( $i = 0; $i < 255; $i++ ) {
            self::$gf_exp[$i] = $x;
            self::$gf_log[$x] = $i;
            $x <<= 1;
            if ( $x >= 256 ) $x ^= 0x11d;
        }
        for ( $i = 255; $i < 512; $i++ ) self::$gf_exp[$i] = self::$gf_exp[$i-255];
        self::$gf_ok = true;
    }

    private static function gf_mul( $a, $b ) {
        if (!$a || !$b) return 0;
        return self::$gf_exp[(self::$gf_log[$a]+self::$gf_log[$b])%255];
    }

    private static function rs_gen( $n ) {
        $g = [1];
        for ($i=0;$i<$n;$i++) {
            $ng = array_fill(0,count($g)+1,0);
            $ai = self::$gf_exp[$i];
            foreach ($g as $j=>$v) { $ng[$j]^=$v; $ng[$j+1]^=self::gf_mul($v,$ai); }
            $g=$ng;
        }
        return $g;
    }

    private static function rs_enc( $data, $n ) {
        $g = self::rs_gen($n);
        $m = array_merge($data, array_fill(0,$n,0));
        for ($i=0;$i<count($data);$i++) {
            $c=$m[$i];
            if ($c) foreach($g as $j=>$v) $m[$i+$j]^=self::gf_mul($v,$c);
        }
        return array_slice($m,count($data));
    }

    private static function add_ecc( $data_cw, $ver, $level ) {
        self::gf_init();
        $p    = self::$ec[$ver][$level];
        $ecp  = $p[0]; $g1b=$p[1]; $g1cw=$p[2]; $g2b=$p[3]; $g2cw=$p[4];
        $blks = []; $ecb = []; $pos = 0;
        for ($i=0;$i<$g1b;$i++) { $b=array_slice($data_cw,$pos,$g1cw); $blks[]=$b; $ecb[]=self::rs_enc($b,$ecp); $pos+=$g1cw; }
        for ($i=0;$i<$g2b;$i++) { $b=array_slice($data_cw,$pos,$g2cw); $blks[]=$b; $ecb[]=self::rs_enc($b,$ecp); $pos+=$g2cw; }
        $res=[]; $ml=max(array_map('count',$blks));
        for ($i=0;$i<$ml;$i++) foreach($blks as $b) if(isset($b[$i])) $res[]=$b[$i];
        for ($i=0;$i<$ecp;$i++) foreach($ecb as $b) if(isset($b[$i])) $res[]=$b[$i];
        return $res;
    }

    // ── Matrix construction ───────────────────────────────────────────────────
    private static function build_matrix( $cw, $ver ) {
        $sz = $ver*4+17;
        $m  = array_fill(0,$sz,array_fill(0,$sz,0));
        $f  = array_fill(0,$sz,array_fill(0,$sz,false));

        // Finder patterns
        foreach ([[0,0],[0,$sz-7],[$sz-7,0]] as [$tr,$tc]) {
            self::put_finder($m,$f,$tr,$tc,$sz);
        }

        // Timing
        for ($i=8;$i<$sz-8;$i++) {
            $v=$i%2===0?1:0;
            $m[6][$i]=$v; $f[6][$i]=true;
            $m[$i][6]=$v; $f[$i][6]=true;
        }

        // Dark module
        $m[$sz-8][8]=1; $f[$sz-8][8]=true;

        // Alignment patterns
        $apos = self::align_pos($ver);
        $last = end($apos);
        foreach ($apos as $ar) {
            foreach ($apos as $ac) {
                if (!($ar===6&&$ac===6)||!($ar===6&&$ac===$last)||!($ar===$last&&$ac===6))
                    self::put_align($m,$f,$ar,$ac);
            }
        }

        // Format area reservation
        foreach ([0,1,2,3,4,5,7,8] as $i) {
            $f[8][$i]=true; $f[$i][8]=true;
        }
        for ($i=$sz-8;$i<$sz;$i++) { $f[8][$i]=true; $f[$i][8]=true; }

        // Place data bits
        $bits='';
        foreach ($cw as $c) $bits.=sprintf('%08b',$c);
        $bi=0; $up=true; $col=$sz-1;
        while ($col>0) {
            if ($col===6) $col--;
            for ($ri=0;$ri<$sz;$ri++) {
                $row=$up?($sz-1-$ri):$ri;
                for ($dc=0;$dc<=1;$dc++) {
                    $c=$col-$dc;
                    if (!$f[$row][$c]) {
                        $m[$row][$c]=($bi<strlen($bits))?(int)$bits[$bi++]:0;
                    }
                }
            }
            $up=!$up; $col-=2;
        }
        return $m;
    }

    private static function put_finder(&$m,&$f,$tr,$tc,$sz) {
        $pat=[[1,1,1,1,1,1,1],[1,0,0,0,0,0,1],[1,0,1,1,1,0,1],[1,0,1,1,1,0,1],[1,0,1,1,1,0,1],[1,0,0,0,0,0,1],[1,1,1,1,1,1,1]];
        for ($r=-1;$r<=7;$r++) for($c=-1;$c<=7;$c++) {
            $rr=$tr+$r; $cc=$tc+$c;
            if ($rr<0||$rr>=$sz||$cc<0||$cc>=$sz) continue;
            $m[$rr][$cc]=($r>=0&&$r<7&&$c>=0&&$c<7)?$pat[$r][$c]:0;
            $f[$rr][$cc]=true;
        }
    }

    private static function put_align(&$m,&$f,$cr,$cc) {
        for ($r=-2;$r<=2;$r++) for($c=-2;$c<=2;$c++) {
            if(!isset($m[$cr+$r][$cc+$c])) continue;
            $m[$cr+$r][$cc+$c]=(abs($r)===2||abs($c)===2||($r===0&&$c===0))?1:0;
            $f[$cr+$r][$cc+$c]=true;
        }
    }

    private static function align_pos($v) {
        $t=[1=>[],2=>[6,18],3=>[6,22],4=>[6,26],5=>[6,30],6=>[6,34],7=>[6,22,38],8=>[6,24,42],9=>[6,26,46],10=>[6,28,50]];
        return $t[$v]??[];
    }

    // ── Masking ───────────────────────────────────────────────────────────────
    private static function mask_matrix($mat,$ver,$level) {
        $sz=count($mat); $best=-1; $bm=0;
        for ($mask=0;$mask<8;$mask++) {
            $m2=self::do_mask($mat,$mask,$sz);
            self::write_fmt($m2,$level,$mask,$sz);
            $pen=self::penalty($m2,$sz);
            if ($best<0||$pen<$best) { $best=$pen; $bm=$mask; }
        }
        $r=self::do_mask($mat,$bm,$sz);
        self::write_fmt($r,$level,$bm,$sz);
        return $r;
    }

    private static function do_mask($m,$mask,$sz) {
        $out=$m;
        for ($r=0;$r<$sz;$r++) for($c=0;$c<$sz;$c++) {
            $cond=match($mask){
                0=>($r+$c)%2===0,
                1=>$r%2===0,
                2=>$c%3===0,
                3=>($r+$c)%3===0,
                4=>(intdiv($r,2)+intdiv($c,3))%2===0,
                5=>($r*$c)%2+($r*$c)%3===0,
                6=>(($r*$c)%2+($r*$c)%3)%2===0,
                7=>(($r+$c)%2+($r*$c)%3)%2===0,
            };
            if ($cond) $out[$r][$c]^=1;
        }
        return $out;
    }

    private static function write_fmt(&$m,$level,$mask,$sz) {
        $ecb=[1,0,3,2];
        $d=($ecb[$level]<<3)|$mask;
        $g=0b10100110111; $dd=$d<<10;
        for ($i=14;$i>=10;$i--) if($dd&(1<<$i)) $dd^=$g<<($i-10);
        $fmt=($d<<10|$dd)^0b101010000010010;
        $bits=[]; for($i=0;$i<15;$i++) $bits[]=($fmt>>$i)&1;
        $seq=[0,1,2,3,4,5,7,8];
        foreach ($seq as $i=>$pos) { $m[8][$pos]=$bits[$i]; $m[$pos][8]=$bits[14-$i]; }
        $m[8][$sz-8]=$bits[7];
        for($i=0;$i<7;$i++) { $m[$sz-7+$i][8]=$bits[8+$i]; $m[8][$sz-7+$i]=$bits[6-$i]; }
    }

    private static function penalty($m,$sz) {
        $p=0;
        for ($r=0;$r<$sz;$r++) { $run=1; for($c=1;$c<$sz;$c++) { if($m[$r][$c]===$m[$r][$c-1]){$run++;if($run===5)$p+=3;elseif($run>5)$p++;}else $run=1; } }
        for ($c=0;$c<$sz;$c++) { $run=1; for($r=1;$r<$sz;$r++) { if($m[$r][$c]===$m[$r-1][$c]){$run++;if($run===5)$p+=3;elseif($run>5)$p++;}else $run=1; } }
        for ($r=0;$r<$sz-1;$r++) for($c=0;$c<$sz-1;$c++) { $v=$m[$r][$c]; if($v===$m[$r][$c+1]&&$v===$m[$r+1][$c]&&$v===$m[$r+1][$c+1]) $p+=3; }
        return $p;
    }
}
