<?php
        $data['recu'];
        $data['date1'];
        $data['date2'];
        ob_start();
        $imprime='pdfbordereauretrait.php';
        include("$imprime");
        $content = ob_get_clean();

        // convert in PDF
        //require_once('../../lib/html2pdf/html2pdf.class.php');
        require_once __DIR__.'/../../../assets/html2pdf/html2pdf.class.php';
        try
        {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr', true, 'UTF-8', 0);
            //$html2pdf = new HTML2PDF('P', array(75,200), 'fr', true, 'UTF-8', 0);
            $html2pdf->setDefaultFont('Times',8);
            $html2pdf->writeHTML($content);
            $html2pdf->Output('pdfbordereauretrait.pdf','I');
        }
        catch(HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
?>