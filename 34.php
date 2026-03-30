<?php
require_once __DIR__ . '/includes/helpers.php';
$dataRaw = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$data = $dataRaw['stranka4'] ?? [];
?>
<style>
    .page { width: 210mm; height: 297mm; padding: 20mm; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; background: white; }
    .analysis-section { margin-bottom: 40px; }
    .analysis-title { font-family: 'Lora', serif; color: #927355; font-size: 20px; margin-bottom: 10px; }
    .analysis-desc { font-size: 12px; color: #777; line-height: 1.5; margin-bottom: 20px; }
    .bar-container { margin-bottom: 15px; }
    .bar-label-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 5px; }
    .bar-outer { background: #f3f3f3; height: 12px; border-radius: 6px; overflow: hidden; display: flex; }
    .bar-inner { height: 100%; }
    .diverz-box { background: #927355; color: white; border-radius: 20px; padding: 30px; display: flex; align-items: center; gap: 30px; margin-top: 50px; }
    .diverz-perc { font-size: 48px; font-weight: bold; font-family: 'Lora', serif; }
</style>
<div class="page">
    <?php 
    $sekce = ['sekce_horizont', 'sekce_aktiva_pasiva', 'sekce_likvidita'];
    foreach ($sekce as $key): 
        $s = $data[$key];
    ?>
    <div class="analysis-section">
        <div class="analysis-title"><?= $s['titulek'] ?></div>
        <div class="analysis-desc"><?= format_czech_text($s['popis']) ?></div>
        
        <?php foreach ($s['data'] as $row): ?>
        <div class="bar-container">
            <div class="bar-label-row">
                <span><?= $row['label'] ?> (<?= $row['hodnota'] ?>)</span>
                <span style="font-weight: bold;"><?= $row['procent'] ?>%</span>
            </div>
            <div class="bar-outer">
                <div class="bar-inner" style="width: <?= $row['procent'] ?>%; background: <?= $row['color'] ?>;"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
    <div class="diverz-box">
        <div class="diverz-perc"><?= $data['diverzifikace']['procent'] ?></div>
        <div>
            <div style="font-size: 18px; font-weight: bold; margin-bottom: 10px;"><?= $data['diverzifikace']['titulek'] ?></div>
            <div style="font-size: 13px; opacity: 0.9; line-height: 1.4;"><?= $data['diverzifikace']['text'] ?></div>
        </div>
    </div>
</div>