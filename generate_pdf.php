<?php
require 'vendor/autoload.php';
use Spatie\Browsershot\Browsershot;
$jsonFile = __DIR__ . '/data.json';
if (!file_exists($jsonFile)) {
    die("Chyba: Soubor data.json nebyl nalezen ve složce " . __DIR__);
}

ob_start();
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Majetkový Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        body { 
            margin: 0; 
            padding: 0; 
            -webkit-print-color-adjust: exact; 
        }
        .no-print { 
            display: none !important; 
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="page-break"><?php include '31.php'; ?></div>
    <div class="page-break"><?php include '32.php'; ?></div>
    <div class="page-break"><?php include '33.php'; ?></div>
    <div><?php include '34.php'; ?></div>
</body>
</html>
<?php
$html = ob_get_clean();
try {
    $pdfContent = Browsershot::html($html)
        ->setChromePath('/Applications/Google Chrome.app/Contents/MacOS/Google Chrome')
        ->showBackground() 
        ->format('A4')
        ->margins(0, 0, 0, 0)
        ->waitUntilNetworkIdle()
        ->pdf();
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="Finalni_Majetkovy_Report.pdf"');
    header('Content-Length: ' . strlen($pdfContent));
    echo $pdfContent;
    exit;

    
} catch (\Exception $e) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>Chyba při generování</h1>";
    echo "<pre>" . $e->getMessage() . "</pre>";
}