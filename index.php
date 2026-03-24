TADY JSEM JA A UPRAVUJU TENTO SOUBOR
<?php
// Tímto řádkem PHP zjistí, že má použít vše, co Composer stáhl
require 'vendor/autoload.php';
$parser = new \Smalot\PdfParser\Parser();
// do ('') je potřeba doplnit název PDF souboru
$pdf = $parser->parseFile('');
$text = $pdf->getText();
// Jednoduchý výpis, abychom viděli, co se podařilo přečíst
echo "<h2>Surový text z PDF:</h2>";
echo "<pre>" . htmlspecialchars($text) . "</pre>";