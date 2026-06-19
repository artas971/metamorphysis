<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PdfService
{
    private Dompdf $domPdf;

    public function __construct()
    {
        $pdfOptions = new Options();
        // Permet d'utiliser des polices standard et d'activer le chargement distant (images)
        $pdfOptions->set('defaultFont', 'Helvetica');
        $pdfOptions->set('isRemoteEnabled', true); 

        $this->domPdf = new Dompdf($pdfOptions);
    }

    /**
     * Reçoit du HTML et renvoie le contenu binaire du PDF
     */
    public function generateBinaryPdf(string $html): string
    {
        $this->domPdf->loadHtml($html);
        $this->domPdf->setPaper('A4', 'portrait');
        $this->domPdf->render();

        return $this->domPdf->output();
    }
}