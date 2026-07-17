<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;

/**
 * Ghostscript orqali PDF hajmini kichraytiradi — faqat ichidagi rasmlarni
 * siqadi (pasaytirilgan DPI/JPEG sifati), vektor chiziqlar va matnga
 * tegmaydi. Server Ghostscript'siz yoki xatolik bo'lsa, xavfsiz tarzda
 * null qaytaradi — chaqiruvchi original baytlarni ishlata beradi.
 */
class PdfCompressor
{
    public static function compress(string $bytes, string $preset = '/screen'): ?string
    {
        $dir = sys_get_temp_dir();
        $in  = $dir . '/' . Str::random(12) . '_gs_in.pdf';
        $out = $dir . '/' . Str::random(12) . '_gs_out.pdf';

        try {
            file_put_contents($in, $bytes);

            $result = Process::timeout(90)->run([
                'gs', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4',
                '-dPDFSETTINGS=' . $preset,
                '-dNOPAUSE', '-dBATCH', '-dQUIET',
                '-sOutputFile=' . $out, $in,
            ]);

            if ($result->successful() && is_file($out) && filesize($out) > 100) {
                return file_get_contents($out);
            }

            Log::warning('PdfCompressor: Ghostscript failed', [
                'exitCode' => $result->exitCode(),
                'errorOutput' => $result->errorOutput(),
            ]);
            return null;
        } catch (\Throwable $e) {
            Log::warning('PdfCompressor: exception — ' . $e->getMessage());
            return null;
        } finally {
            @unlink($in);
            @unlink($out);
        }
    }
}
