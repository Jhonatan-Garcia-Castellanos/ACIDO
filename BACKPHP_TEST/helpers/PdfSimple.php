<?php
// helpers/PdfSimple.php — Generador PDF mínimo sin dependencias (la copia
// public/tmp/fpdf.php exige font/*.json que no existe en el proyecto).
// Usa fuentes base PDF (Helvetica/Helvetica-Bold, sin embeber) => siempre funciona.
// Cumple lista 4.2: encabezado corporativo, tabla paginada y numeración.
class PdfSimple
{
    private $pages = [];
    private $cur = [];
    private $pw = 841.89;     // A4 horizontal en pt (297mm)
    private $ph = 595.28;     // (210mm)
    private $ml = 36; private $mb = 44;
    private $mtFirst = 52; private $mtNext = 40;
    private $y = 0;
    private $bold = false; private $size = 10;
    private $headCells = null; private $headWidths = null; private $headH = 18;
    private $footLeft = 'ACIDO COLOMBIA';
    private $rowNum = 0;
    private $company = [];
    private $logo = null; // ['w','h','rgb','aspect'] si hay logo incrustado

    // Datos de la empresa que van en el encabezado de los reportes.
    public const COMPANY = [
        'nombre'    => 'ACIDO COLOMBIA',
        'razon'     => 'ACIDO COLOMBIA S.A.S.',
        'nit'       => 'NIT 901.234.567-8',
        'direccion' => 'Cra 15 # 78-33, Bogota D.C., Colombia',
        'tel'       => 'PBX (601) 555 0000',
        'cel'       => 'Cel. 320 450 7890',
        'email'     => 'ventas@acidocolombia.com',
        'web'       => 'www.acidocolombia.com',
    ];

    public function __construct() {
        $this->y = $this->mtFirst;
        $this->company = self::COMPANY;
    }

    public function setFooter($left) { $this->footLeft = (string)$left; }

    /** Sobrescribe total o parcialmente los datos de la empresa (vacío = valor por defecto). */
    public function setCompany(array $c) {
        $this->company = array_merge(self::COMPANY, array_filter($c, fn($v) => $v !== null && $v !== ''));
    }

    /** Incrusta el logo (PNG 8-bit RGB/RGBA, sin entrelazado). Si no se puede, sigue sin logo. */
    public function setLogo($path) {
        $dec = $this->pngDecode((string)$path);
        if ($dec === false) return;
        $this->logo = $dec + ['aspect' => $dec['w'] / max(1, $dec['h'])];
    }

    /** Línea "Razón social - NIT | dirección | teléfonos" del encabezado corporativo. */
    private function companyLine() {
        $nom = trim((string)($this->company['razon'] ?? ''));
        $line = trim((string)($this->company['nit'] ?? ''));
        if ($nom !== '') $line = ($line !== '') ? ($nom . ' - ' . $line) : $nom;
        foreach (['direccion', 'tel', 'cel'] as $k) {
            $v = trim((string)($this->company[$k] ?? ''));
            if ($v === '') continue;
            $line .= ($line !== '' ? ' | ' : '') . $v;
        }
        return $line;
    }

    private function newPage()
    {
        if (!empty($this->cur)) $this->pages[] = $this->cur;
        $this->cur = [];
        $this->y = $this->mtNext;
        // Repetir encabezado corporativo mini + header de tabla en cada página nueva
        $this->cur[] = ['t' => 'rect', 'x' => $this->ml, 'y' => $this->y, 'w' => $this->pw - 72, 'h' => 16, 'fill' => [28, 59, 74]];
        $this->cur[] = ['t' => 'txtw', 'x' => $this->ml, 'y' => $this->y + 12, 'w' => $this->pw - 72, 'a' => 'C', 's' => 'ACIDO COLOMBIA - Reporte de Ventas (cont.)', 'f' => 'Helvetica-Bold', 'z' => 9];
        $this->y += 20;
        if (is_array($this->headCells)) {
            $this->emitHeaderRow();
        }
    }

    public function finish() { $this->pages[] = $this->cur; $this->cur = []; }

    private function esc($s)
    {
        $s = (string)$s;
        $s = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
        if ($s === false) $s = '';
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    /** Recorta con "…" para que el texto nunca invada la celda vecina. */
    private function fit($s, $availPt, $fontSize)
    {
        $s = (string)$s;
        if ($availPt <= 10 || $s === '') return $s;
        $maxChars = (int)floor($availPt / ($fontSize * 0.52));
        if ($maxChars < 4) $maxChars = 4;
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($s, 'UTF-8') <= $maxChars) return $s;
            return mb_substr($s, 0, $maxChars - 1, 'UTF-8') . '.';
        }
        if (strlen($s) <= $maxChars) return $s;
        return substr($s, 0, $maxChars - 1) . '.';
    }

    /** Barra corporativa + título + subtítulo (solo primera página). */
    public function title($line1, $line2)
    {
        // Bloque superior: logo a la izquierda + datos de la empresa a la derecha
        if ($this->logo !== null) {
            $logoH = 34;
            $logoW = (int)round($logoH * $this->logo['aspect'], 0);
            $x0 = $this->ml;
            $this->cur[] = ['t' => 'rect', 'x' => $x0, 'y' => $this->y, 'w' => $logoW, 'h' => $logoH, 'fill' => [28, 59, 74]];
            $this->cur[] = ['t' => 'img', 'x' => $x0, 'y' => $this->y, 'w' => $logoW, 'h' => $logoH];
            $tx = $x0 + $logoW + 12;
            $this->cur[] = ['t' => 'txt', 'x' => $tx, 'y' => $this->y + 4, 'w' => 0, 'a' => 'L', 's' => $this->company['nombre'], 'f' => 'Helvetica-Bold', 'z' => 12];
            $this->cur[] = ['t' => 'txt', 'x' => $tx, 'y' => $this->y + 17, 'w' => 0, 'a' => 'L', 's' => $this->companyLine(), 'f' => 'Helvetica', 'z' => 8];
            $this->cur[] = ['t' => 'txt', 'x' => $tx, 'y' => $this->y + 26, 'w' => 0, 'a' => 'L', 's' => trim(($this->company['email'] ?? '') . ' | ' . ($this->company['web'] ?? ''), ' |'), 'f' => 'Helvetica', 'z' => 8];
            $this->y += ($logoH + 8);
        }
        $this->cur[] = ['t' => 'rect', 'x' => $this->ml, 'y' => $this->y, 'w' => $this->pw - 72, 'h' => 22, 'fill' => [28, 59, 74]];
        $this->cur[] = ['t' => 'txtw', 'x' => $this->ml, 'y' => $this->y + 17, 'w' => $this->pw - 72, 'a' => 'L', 's' => '  ' . $line1, 'f' => 'Helvetica-Bold', 'z' => 14];
        $this->y += 26;
        $this->cur[] = ['t' => 'txt', 'x' => $this->ml, 'y' => $this->y, 'w' => 0, 'a' => 'L', 's' => $line2, 'f' => 'Helvetica', 'z' => 9.5];
        $this->y += 8;
    }

    /** Bloque resumen: 4 mini-tarjetas en una fila. */
    public function summary($items)
    {
        $n = count($items);
        if ($n === 0) return;
        $gap = 8;
        $w = (($this->pw - 72) - $gap * ($n - 1)) / $n;
        $x = $this->ml;
        $h = 26;
        if ($this->y + $h > $this->ph - $this->mb) $this->newPage();
        foreach ($items as $it) {
            $this->cur[] = ['t' => 'box', 'x' => $x, 'y' => $this->y, 'w' => $w, 'h' => $h];
            $this->cur[] = ['t' => 'txt', 'x' => $x + 6, 'y' => $this->y + 7, 'w' => $w - 12, 'a' => 'L', 's' => $it[0], 'f' => 'Helvetica', 'z' => 7.5];
            $this->cur[] = ['t' => 'txt', 'x' => $x + 6, 'y' => $this->y + 20, 'w' => $w - 12, 'a' => 'L', 's' => $it[1], 'f' => 'Helvetica-Bold', 'z' => 11];
            $x += $w + $gap;
        }
        $this->y += ($h + 10);
    }

    private function emitHeaderRow()
    {
        $widths = $this->headWidths;
        $x = $this->ml;
        $h = $this->headH;
        $this->cur[] = ['t' => 'rect', 'x' => $x, 'y' => $this->y, 'w' => array_sum($widths), 'h' => $h, 'fill' => [28, 59, 74]];
        foreach ($this->headCells as $i => $c) {
            $this->cur[] = ['t' => 'txtw', 'x' => $x, 'y' => $this->y + $h - 5, 'w' => $widths[$i], 'a' => 'C', 's' => $c, 'f' => 'Helvetica-Bold', 'z' => 9];
            $this->cur[] = ['t' => 'box', 'x' => $x, 'y' => $this->y, 'w' => $widths[$i], 'h' => $h];
            $x += $widths[$i];
        }
        $this->y += $h;
    }

    public function row($cells, $widths, $h = 16, $header = false)
    {
        if ($header) {
            $this->headCells = $cells;
            $this->headWidths = $widths;
            $this->headH = $h;
            if ($this->y + $h > $this->ph - $this->mb) $this->newPage();
            $this->emitHeaderRow();
            return;
        }
        if ($this->y + $h > $this->ph - $this->mb) {
            $this->newPage();
        }
        $x = $this->ml;
        $zebra = ($this->rowNum % 2 === 1);
        if ($zebra) {
            $this->cur[] = ['t' => 'rect', 'x' => $x, 'y' => $this->y, 'w' => array_sum($widths), 'h' => $h, 'fill' => [237, 242, 247]];
        }
        foreach ($cells as $i => $c) {
            $w = $widths[$i];
            // Recorta el texto al ancho útil para que Pago/Factura no invadan la celda vecina
            $c = $this->fit($c, $w - 8, 8);
            $a = ($i === 0 || $i === 3) ? 'C' : (($i === 4) ? 'R' : 'L');
            $this->cur[] = ['t' => 'txt', 'x' => $x + 3, 'y' => $this->y + $h - 4, 'w' => $w - 6, 'a' => $a, 's' => $c, 'f' => 'Helvetica', 'z' => 8];
            $this->cur[] = ['t' => 'box', 'x' => $x, 'y' => $this->y, 'w' => $w, 'h' => $h];
            $x += $w;
        }
        $this->y += $h;
        $this->rowNum++;
    }

    /**
     * Decodifica un PNG 8-bit RGB (tipo 2) o RGBA (tipo 6), sin entrelazado,
     * y devuelve RGB plano (RGBA compuesto sobre blanco). false si no aplica.
     * Implementación mínima con zlib para no depender de GD/ImageMagick.
     */
    private function pngDecode($path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false || substr($raw, 0, 8) !== "\x89PNG\x0d\x0a\x1a\x0a") return false;
        $len = strlen($raw);
        $p = 8; $w = 0; $h = 0; $bit = 0; $ct = 0; $idat = '';
        while ($p + 8 <= $len) {
            $cl = unpack('N', substr($raw, $p, 4))[1];
            $typ = substr($raw, $p + 4, 4);
            $data = substr($raw, $p + 8, $cl);
            if ($typ === 'IHDR' && $cl >= 13) {
                $u = unpack('Nw/Nh/Cbit/Cct/Cc/Cf/Ci', $data);
                $w = $u['w']; $h = $u['h']; $bit = $u['bit']; $ct = $u['ct'];
                if ($bit !== 8 || $u['i'] !== 0) return false;
                if ($ct !== 2 && $ct !== 6) return false;
            } elseif ($typ === 'IDAT') {
                $idat .= $data;
            } elseif ($typ === 'IEND') {
                break;
            }
            $p += 12 + $cl;
        }
        if ($w === 0 || $h === 0 || $idat === '') return false;
        $z = @gzuncompress($idat);
        if ($z === false) return false;
        $bpp = ($ct === 6) ? 4 : 3;
        $stride = $w * $bpp;
        if (strlen($z) < ($stride + 1) * $h) return false;
        $out = '';
        $prev = str_repeat("\x00", $stride);
        $o = 0;
        for ($yy = 0; $yy < $h; $yy++) {
            $f = ord($z[$o]); $o++;
            $line = substr($z, $o, $stride); $o += $stride;
            $rec = '';
            for ($i = 0; $i < $stride; $i++) {
                $a = ($i >= $bpp) ? ord($rec[$i - $bpp]) : 0;
                $b = ord($prev[$i]);
                $c = ($i >= $bpp) ? ord($prev[$i - $bpp]) : 0;
                switch ($f) {
                    case 0: $v = ord($line[$i]); break;
                    case 1: $v = ord($line[$i]) + $a; break;
                    case 2: $v = ord($line[$i]) + $b; break;
                    case 3: $v = ord($line[$i]) + intdiv($a + $b, 2); break;
                    case 4:
                        $pp = $a + $b - $c;
                        $pa = abs($pp - $a); $pb = abs($pp - $b); $pc = abs($pp - $c);
                        $pr = ($pa <= $pb && $pa <= $pc) ? $a : (($pb <= $pc) ? $b : $c);
                        $v = ord($line[$i]) + $pr;
                        break;
                    default: return false;
                }
                $rec .= chr($v & 0xFF);
            }
            if ($ct === 6) {
                $rowRgb = '';
                $bg = [28, 59, 74];
                for ($i = 0; $i < $stride; $i += 4) {
                    $al = ord($rec[$i + 3]);
                    if ($al === 255) {
                        $rowRgb .= substr($rec, $i, 3);
                        continue;
                    }
                    $rowRgb .= chr((int)round((ord($rec[$i]) * $al + $bg[0] * (255 - $al)) / 255))
                             . chr((int)round((ord($rec[$i + 1]) * $al + $bg[1] * (255 - $al)) / 255))
                             . chr((int)round((ord($rec[$i + 2]) * $al + $bg[2] * (255 - $al)) / 255));
                }
                $out .= $rowRgb;
            } else {
                $out .= $rec;
            }
            $prev = $rec;
        }
        return ['w' => $w, 'h' => $h, 'rgb' => $out];
    }

    /** Construye el PDF y devuelve los bytes (para adjuntar por correo). */
    public function render()
    {
        $this->finish();
        $n = max(1, count($this->pages));
        $objId = 1;
        $catalog = $objId++; $pagesObj = $objId++;
        $fontReg = $objId++; $fontBold = $objId++;
        $imgId = null; $imgRaw = null; $imgW = 0; $imgH = 0;
        if ($this->logo !== null) {
            $imgId = $objId++;
            $imgW = (int)$this->logo['w']; $imgH = (int)$this->logo['h'];
            $imgRaw = gzcompress((string)$this->logo['rgb'], 9);
            if ($imgRaw === false) $imgId = null;
        }
        $pageIds = []; $contentIds = [];
        for ($i = 0; $i < $n; $i++) { $pageIds[] = $objId++; $contentIds[] = $objId++; }
        $streams = [];
        foreach ($this->pages as $pi => $ops) {
            $s = "BT /F1 10 Tf ET\n";
            foreach ($ops as $op) {
                if ($op['t'] === 'txt' || $op['t'] === 'txtw') {
                    $fid = ($op['f'] === 'Helvetica-Bold') ? 'F2' : 'F1';
                    $sz = $op['z'];
                    $py = $this->ph - $op['y'] + 4;
                    $txt = $this->esc($op['s']);
                    if (!empty($op['w']) && ($op['a'] ?? 'L') === 'C') {
                        $est = strlen($txt) * $sz * 0.52;
                        $px = $op['x'] + max(0, ($op['w'] - $est) / 2);
                    } elseif (!empty($op['w']) && ($op['a'] ?? 'L') === 'R') {
                        $est = strlen($txt) * $sz * 0.5;
                        $px = $op['x'] + max(0, $op['w'] - $est);
                    } else {
                        $px = $op['x'];
                    }
                    $col = ($op['t'] === 'txtw') ? "1 1 1 rg\n" : "0 0 0 rg\n";
                    $s .= $col . "BT /$fid $sz Tf $px $py Td ($txt) Tj ET\n";
                } elseif ($op['t'] === 'rect') {
                    [$r, $g, $b] = $op['fill'];
                    $s .= sprintf("%.3f %.3f %.3f rg\n", $r / 255, $g / 255, $b / 255);
                    $s .= sprintf("%.2f %.2f %.2f %.2f re f\n", $op['x'], $this->ph - $op['y'] - $op['h'], $op['w'], $op['h']);
                } elseif ($op['t'] === 'box') {
                    $s .= "0.6 G 0.5 w\n";
                    $s .= sprintf("%.2f %.2f %.2f %.2f re S\n", $op['x'], $this->ph - $op['y'] - $op['h'], $op['w'], $op['h']);
                } elseif ($op['t'] === 'img') {
                    $s .= sprintf("q %.2f 0 0 %.2f %.2f %.2f cm /Im1 Do Q\n", $op['w'], $op['h'], $op['x'], $this->ph - $op['y'] - $op['h']);
                }
            }
            $foot = 'Pagina ' . ($pi + 1) . '/' . $n . ' - ' . $this->footLeft;
            $s .= "0 0 0 rg\nBT /F1 8 Tf " . ($this->pw / 2 - 90) . " 28 Td (" . $this->esc($foot) . ") Tj ET\n";
            $streams[] = $s;
        }
        $pdf = "%PDF-1.4\n";
        $offsets = [];
        $add = function ($id, $body) use (&$pdf, &$offsets) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "$id 0 obj\n$body\nendobj\n";
        };
        $kids = implode(' ', array_map(fn($id) => "$id 0 R", $pageIds));
        $add($catalog, "<< /Type /Catalog /Pages $pagesObj 0 R >>");
        $add($pagesObj, "<< /Type /Pages /Kids [$kids] /Count $n >>");
        $add($fontReg, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>");
        $add($fontBold, "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>");
        if ($imgId !== null) {
            $add($imgId, "<< /Type /XObject /Subtype /Image /Width $imgW /Height $imgH /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /FlateDecode /Length " . strlen($imgRaw) . " >>\nstream\n" . $imgRaw . "\nendstream");
        }
        foreach ($pageIds as $i => $pid) {
            $cid = $contentIds[$i];
            $res = "/Font << /F1 $fontReg 0 R /F2 $fontBold 0 R >>";
            if ($imgId !== null) $res .= " /XObject << /Im1 $imgId 0 R >>";
            $add($pid, "<< /Type /Page /Parent $pagesObj 0 R /MediaBox [0 0 {$this->pw} {$this->ph}] /Resources << $res >> /Contents $cid 0 R >>");
            $st = $streams[$i];
            $add($cid, "<< /Length " . strlen($st) . " >>\nstream\n$st\nendstream");
        }
        $xrefPos = strlen($pdf);
        $maxId = $objId - 1;
        $pdf .= "xref\n0 " . ($maxId + 1) . "\n0000000000 65535 f \n";
        for ($i = 1; $i <= $maxId; $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i] ?? 0);
        }
        $pdf .= "trailer\n<< /Size " . ($maxId + 1) . " /Root $catalog 0 R >>\nstartxref\n$xrefPos\n%%EOF";
        return $pdf;
    }

    public function output($filename)
    {
        $pdf = $this->render();
        while (ob_get_level()) { ob_end_clean(); }
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit();
    }
}
