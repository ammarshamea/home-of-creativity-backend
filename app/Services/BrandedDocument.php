<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;

class BrandedDocument
{
    public function quotationPdf(ServiceRequest $request, float $amount, ?string $notes, int $version): string
    {
        $html = View::make('pdf.quotation', [
            'request' => $request,
            'amount' => $amount,
            'notes' => $notes,
            'version' => $version,
        ])->render();

        return $this->storePdf("quotations/{$request->number}-v{$version}.pdf", $html);
    }

    public function invoicePdf(ServiceRequest $request, string $invoiceNumber, float $amount, string $paymentMethod): string
    {
        $html = View::make('pdf.invoice', [
            'request' => $request,
            'invoiceNumber' => $invoiceNumber,
            'amount' => $amount,
            'paymentMethod' => $paymentMethod,
        ])->render();

        return $this->storePdf("invoices/{$invoiceNumber}.pdf", $html);
    }

    private function storePdf(string $relativePath, string $html): string
    {
        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadHTML($html);
            Storage::disk('local')->put($relativePath, $pdf->output());
        } else {
            Storage::disk('local')->put(str_replace('.pdf', '.html', $relativePath), $html);
            Storage::disk('local')->put($relativePath, $this->minimalPdfFromHtml($html));
        }

        return $relativePath;
    }

    private function minimalPdfFromHtml(string $html): string
    {
        $text = strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>', '</div>'], "\n", $html));
        $lines = explode("\n", $text);
        $content = "BT /F1 12 Tf 50 750 Td\n";
        $y = 750;
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $content .= "({$escaped}) Tj 0 -16 Td\n";
            $y -= 16;
            if ($y < 50) {
                break;
            }
        }
        $content .= 'ET';

        $objects = [
            "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n",
            "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n",
            "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj\n",
            '4 0 obj << /Length '.strlen($content)." >> stream\n{$content}\nendstream endobj\n",
            "5 0 obj << /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];
        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }
        $pdf .= 'trailer << /Size '.(count($objects) + 1)." /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }
}
