<?php
require_once __DIR__ . '/includes/helpers.php';
$dataRaw = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$data = $dataRaw['stranka1'] ?? [];
function get_header_icon($nazev) {
    $n = mb_strtolower($nazev);
    if (str_contains($n, 'finanční')) return 'fa-solid fa-money-bill-1';
    if (str_contains($n, 'nemovitosti')) return 'fa-solid fa-house';
    if (str_contains($n, 'movitý')) return 'fa-solid fa-car';
    return 'fa-solid fa-chart-pie';
}
function get_status_icon($emojiKey) {
    $map = [
        '✅' => ['icon' => 'fa-solid fa-check', 'color' => '#2ecc71'], 
        '⚠️' => ['icon' => 'fa-solid fa-exclamation', 'color' => '#e67e22'], 
        '❌' => ['icon' => 'fa-solid fa-xmark', 'color' => '#8c8c8c'], 
    ];
    return $map[$emojiKey] ?? ['icon' => '', 'color' => '#eee'];
}
?>
<style>
    :root { --clr-primary: #927355; --clr-gray: #8c8c8c; }
    .gray { color: var(--clr-gray); }
    .page { width: 210mm; height: 297mm; padding: 20mm; box-sizing: border-box; background: white; font-family: 'Plus Jakarta Sans', sans-serif; }
    .header-flex { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 40px; }
    .main-title { font-family: 'Lora', serif; font-size: 45px; line-height: 1.1; margin: 0; }    
    .chart-box { width: 200px; text-align: center; display: flex; flex-direction: column; align-items: center; }
    .asset-bar { background: var(--clr-primary); color: white; padding: 10px; border-radius: 10px; margin-top: 15px; font-weight: bold; width: 100%; box-sizing: border-box; }
    .columns-grid { display: flex; gap: 15px; }
    .column { flex: 1; min-width: 0; }    
    .column-header { 
        border: 1px solid var(--clr-primary); 
        border-radius: 12px; 
        padding: 12px; 
        margin-bottom: 15px; 
        background: #fcfaf8; 
        display: flex; 
        align-items: center; 
        gap: 10px;
    }
    .header-icon { color: var(--clr-primary); font-size: 18px; width: 24px; text-align: center; }
    .item-card { background: #fff; border: 1px solid #f0f0f0; padding: 12px; border-radius: 10px; margin-bottom: 8px; display: flex; align-items: center; gap: 10px; }
    .status-circle {
        border-radius: 50%; 
        width: 17px; 
        height: 17px; 
        display: flex; 
        justify-content: center; 
        align-items: center; 
        font-size: 9px;
        flex-shrink: 0;
        border: 1.2px solid;
    }
    .val-text { font-weight: normal; font-size: 13px; }
    .neg { color: #c0392b; }
</style>

<div class="page">
    <div class="header-flex">
        <div style="flex: 1;">
            <h1 class="main-title">
                <?php 
                $titulek = $data['titulek'] ?? 'Přehled vašeho majetku';
                $slova = explode(' ', $titulek);
                $prvniSlovo = array_shift($slova);
                $zbytek = implode(' ', $slova);
                ?>
                <span class="gray"><?= htmlspecialchars($prvniSlovo) ?></span><br>
                <?= htmlspecialchars($zbytek) ?>
            </h1>
            <p style="color: #666; margin-top: 20px; line-height: 1.5; font-size: 14px;">
                <?= format_czech_text($data['uvodni_text']) ?>
            </p>
        </div>
        <div class="chart-box">
            <svg width="180" height="180" viewBox="0 0 42 42">
                <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#f3f3f3" stroke-width="4"></circle>
                <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="#927355" stroke-width="4" stroke-dasharray="75 25" stroke-dashoffset="25"></circle>
            </svg>
            <div class="asset-bar"><?= format_czk($data['cisla_hodnota']) ?> Kč</div>
        </div>
    </div>

    <div class="columns-grid">
        <?php foreach ($data['sekce'] as $sec): ?>
        <div class="column">
            <div class="column-header">
                <div class="header-icon"><i class="<?= get_header_icon($sec['nazev']) ?>"></i></div>
                <div>
                    <div style="font-weight: bold; color: var(--clr-primary); font-size: 14px; font-family: 'Lora', serif;"><?= $sec['nazev'] ?></div>
                    <div style="font-size: 12px; color: #666;"><?= format_czk($sec['hodnota']) ?> Kč</div>
                </div>
            </div>
            
            <?php foreach ($sec['polozky'] as $item): 
                $status = get_status_icon($item['ikona']); ?>
            <div class="item-card">
                <div class="status-circle" style="color: <?= $status['color'] ?>; border-color: <?= $status['color'] ?>;">
                    <i class="<?= $status['icon'] ?>"></i>
                </div>
                <div style="overflow: hidden;">
                    <div style="font-size: 10px; color: #888; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= $item['label'] ?></div>
                    <div class="val-text <?= $item['val'] < 0 ? 'neg' : '' ?>"><?= format_czk($item['val']) ?> Kč</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>