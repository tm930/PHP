<?php
require_once __DIR__ . '/includes/helpers.php';

function safe_text($text) {
    if (empty($text) || is_array($text)) return '';
    return htmlspecialchars(preg_replace('/\[cite.*?\]/', '', (string)$text));
}
$dataRaw = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$sekceDetaily = $dataRaw['stranka2']['sekce_detaily'] ?? [];
function get_sec_icon($kat) {
    $k = mb_strtolower($kat);
    if (str_contains($k, 'nemovit')) return 'fa-house';
    if (str_contains($k, 'finanč')) return 'fa-money-bill-1';
    return 'fa-chart-pie';
}
?>
<style>
    :root { --clr-primary: #927355; --clr-gray: #8c8c8c; --clr-green: #2ecc71; --clr-orange: #e67e22; }
    .page { width: 210mm; min-height: 297mm; padding: 20mm; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; background: white; } 
    .sec-block { margin-bottom: 45px; }
    .sec-title { display: flex; align-items: center; gap: 15px; color: var(--clr-primary); font-family: 'Lora', serif; font-size: 24px; margin-bottom: 8px; }
    .sec-desc { font-size: 13px; color: #777; margin-bottom: 25px; line-height: 1.5; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .card-row { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
    .status-icon { border-radius: 50%; width: 24px; height: 24px; display: flex; justify-content: center; align-items: center; font-size: 11px; flex-shrink: 0; border: 1.2px solid; }
    .detail-card { 
        flex: 1; background: #fff; border: 1px solid #f0f0f0; border-radius: 12px; 
        padding: 18px; display: flex; gap: 20px; align-items: center; justify-content: space-between;
    }
    .price-tag-box { background: #f8f8f8; padding: 12px; border-radius: 8px; min-width: 165px; }
    .val-amount { font-size: 18px; color: var(--clr-primary); margin-bottom: 3px; font-family: 'Lora', serif; }
    .val-label { font-size: 11px; color: #777; font-weight: bold; }
    .middle-texts { flex: 1; min-width: 0; }
    .text-main { font-weight: bold; font-size: 16px; color: #333; margin-bottom: 2px; }
    .text-sub { font-size: 14px; color: var(--clr-green); font-weight: bold; margin-bottom: 4px; }
    .text-minor { font-size: 12px; color: #888; }    
    .tag-cloud { 
        display: flex; 
        flex-direction: column;
        gap: 6px; 
        align-items: flex-end;
        width: 160px; 
    }
    .tag-brown { 
        font-size: 9px; 
        text-transform: uppercase; 
        border: 1px solid #d4c4b5; 
        padding: 5px 10px; 
        border-radius: 6px; 
        color: var(--clr-primary); 
        text-align: center;
        width: 100%;
        box-sizing: border-box;
        white-space: nowrap;
    }
</style>
<div class="page">
    <?php foreach ($sekceDetaily as $sekce): ?>
    <div class="sec-block">
        <div class="sec-title">
            <i class="fa-solid <?= get_sec_icon($sekce['kategorie']) ?>"></i> 
            <?= safe_text($sekce['kategorie']) ?>
        </div>
        <div class="sec-desc"><?= safe_text($sekce['popis'] ?? '') ?></div>
        <?php foreach ($sekce['polozky'] as $item): ?>
            <div class="card-row">
                <div class="status-icon" style="color: var(--clr-green); border-color: var(--clr-green);"><i class="fa-solid fa-check"></i></div>
                <div class="detail-card">
                    <div class="price-tag-box">
                        <div class="val-amount"><?= format_czk($item['aktiva_hodnota'] ?? 0) ?> Kč</div>
                        <div class="val-label"><?= safe_text($item['nazev'] ?? 'Aktiva') ?></div>
                    </div>
                    <div class="middle-texts">
                        <div class="text-main"><?= safe_text($item['lokalita'] ?? $item['banka'] ?? '') ?></div>
                        <div class="text-sub"><?= safe_text($item['vynos'] ?? '') ?></div>
                        <div class="text-minor"><?= safe_text($item['parametry'] ?? $item['typ'] ?? '') ?></div>
                    </div>
                    <div class="tag-cloud">
                        <?php if(!empty($item['kratky_typ'])): ?><div class="tag-brown"><?= safe_text($item['kratky_typ']) ?></div><?php endif; ?>
                        <div class="tag-brown">Aktiva</div>
                        <?php if(!empty($item['horizont'])): ?><div class="tag-brown"><?= safe_text($item['horizont']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if(!empty($item['pasiva_hodnota'])): ?>
            <div class="card-row">
                <div class="status-icon" style="color: var(--clr-orange); border-color: var(--clr-orange);"><i class="fa-solid fa-exclamation"></i></div>
                <div class="detail-card">
                    <div class="price-tag-box">
                        <div class="val-amount"><?= format_czk(abs($item['pasiva_hodnota'])) ?> Kč</div>
                        <div class="val-label"><?= safe_text($item['pasiva_label'] ?? 'Pasiva') ?></div>
                    </div>
                    <div class="middle-texts">
                        <div class="text-main"><?= safe_text($item['banka'] ?? '') ?></div>
                        <div class="text-minor"><?= safe_text($item['obdobi'] ?? '') ?></div>
                    </div>
                    <div class="tag-cloud">
                        <div class="tag-brown">Hypotéka</div>
                        <div class="tag-brown">Pasiva</div>
                        <div class="tag-brown">Dlouhodobý</div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>