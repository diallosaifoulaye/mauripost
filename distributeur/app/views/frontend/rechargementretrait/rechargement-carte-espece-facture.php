<?php
$data['benef']; $data['transaction'];
    //get the HTML
    ob_start();
    $imprime = 'recu-rechargement-espece.php';
    include("$imprime");
    $content = ob_get_clean();
    // convert in PDF
    require_once __DIR__.'/../../../../assets/html2pdf/html2pdf.class.php';
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
        $html2pdf->setDefaultFont('Times', 8);
        $html2pdf->writeHTML($content);
        ob_end_clean();
        $html2pdf->Output('RecuRechargeEspece.pdf', 'I');
    }
    catch (HTML2PDF_exception $e)
    {
        echo $e;
        exit;
    }
?>