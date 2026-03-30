<?php
require_once __DIR__ . '/includes/helpers.php';
if (!function_exists('safe_text')) {
    function safe_text($text) {
        if (empty($text) || is_array($text)) return '';
        return htmlspecialchars(preg_replace('/\[cite.*?\]/', '', (string)$text));
    }
}

$dataRaw = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$stranka3 = $dataRaw['stranka3'] ?? [];
$polozky = $stranka3['movity_majetek'] ?? [];
$bilance = $stranka3['bilance_majetku'] ?? [];
$maxVal = max($bilance['aktiva'] ?? 1, $bilance['pasiva'] ?? 1);
$hAktiva = (($bilance['aktiva'] ?? 0) / $maxVal) * 100;
$hPasiva = (($bilance['pasiva'] ?? 0) / $maxVal) * 100;
?>
<style>
    :root { --clr-primary: #927355; --clr-green: #2ecc71; --clr-orange: #e67e22; --clr-dark-brown: #8d6e53; }
    .page { width: 210mm; min-height: 297mm; padding: 20mm; box-sizing: border-box; font-family: 'Plus Jakarta Sans', sans-serif; background: white; color: #333; }    
    .sec-title { display: flex; align-items: center; gap: 15px; color: var(--clr-primary); font-family: 'Lora', serif; font-size: 24px; margin-bottom: 8px; }
    .sec-desc { font-size: 13px; color: #777; margin-bottom: 25px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .card-row { display: flex; align-items: center; gap: 15px; margin-bottom: 12px; }
    .status-icon { border-radius: 50%; width: 24px; height: 24px; display: flex; justify-content: center; align-items: center; font-size: 11px; border: 1.2px solid; }
    .detail-card { flex: 1; border: 1px solid #f0f0f0; border-radius: 12px; padding: 15px; display: flex; gap: 20px; align-items: center; }
    .price-tag-box { background: #f8f8f8; padding: 10px; border-radius: 8px; min-width: 150px; }
    .tag-cloud { display: flex; flex-direction: column; gap: 4px; width: 140px; align-items: flex-end; }
    .tag-brown { font-size: 9px; text-transform: uppercase; border: 1px solid #d4c4b5; padding: 4px 8px; border-radius: 5px; color: var(--clr-primary); width: 100%; text-align: center; }
    .bilance-section { margin-top: 50px; }
    .bilance-title { font-family: 'Lora', serif; font-size: 32px; color: #333; margin-bottom: 30px; }
    .bilance-flex { display: flex; gap: 40px; align-items: flex-start; margin-bottom: 40px; }
    .chart-container { flex: 1; height: 250px; display: flex; align-items: flex-end; gap: 40px; border-bottom: 2px solid #eee; padding: 0 40px 10px 40px; }
    .chart-bar { flex: 1; border-radius: 8px 8px 0 0; position: relative; }
    .bar-aktiva { background-color: #ededed; }
    .bar-pasiva { background-color: var(--clr-dark-brown); }
    .bar-label { position: absolute; bottom: -30px; left: 50%; transform: translateX(-50%); font-size: 14px; color: #666; }    
    .table-container { flex: 1; }
    .bil-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; margin-bottom: 10px; }
    .bil-row.total { background: var(--clr-dark-brown); color: white; border: none; }
    .bil-subrow { display: flex; justify-content: space-between; padding: 0 15px 15px 15px; font-size: 13px; color: #666; }
    .bottom-boxes { display: flex; gap: 20px; }
    .warning-box { width: 76%; background: #fff5f5; border: 1px solid #f8d7da; border-radius: 15px; padding: 20px; display: flex; align-items: center; gap: 20px; }
    .perc-circle { background: #b56565; color: white; width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 18px; flex-shrink: 0; }
    .warning-text h4 { margin: 0 0 5px 0; font-size: 18px; }
    .warning-text p { margin: 0; font-size: 12px; color: #666; line-height: 1.4; }
    .insurance-box { width: 24%; background: #fff; border: 1px solid #f0f0f0; border-radius: 15px; padding: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.02); }
    .insurance-box h4 { margin: 0 0 10px 0; font-size: 16px; color: #444; }
    .insurance-box p { font-size: 12px; color: var(--clr-dark-brown); font-weight: bold; margin: 0; }
</style>

<div class="page">
    <div class="sec-title"><i class="fa-solid fa-car"></i> <?= safe_text($stranka3['titulek']) ?></div>
    <div class="sec-desc"><?= safe_text($stranka3['popis']) ?></div>
    <?php foreach ($polozky as $item): 
        $val = (float)$item['hodnota'];
        $isPasiva = ($val < 0);
    ?>
    <div class="card-row">
        <div class="status-icon" style="color: <?= $isPasiva ? 'var(--clr-orange)' : 'var(--clr-green)' ?>; border-color: <?= $isPasiva ? 'var(--clr-orange)' : 'var(--clr-green)' ?>;">
            <i class="fa-solid <?= $isPasiva ? 'fa-exclamation' : 'fa-check' ?>"></i>
        </div>
        <div class="detail-card">
            <div class="price-tag-box">
                <div class="val-amount"><?= format_czk(abs($val)) ?> Kč</div>
                <div class="val-label"><?= safe_text($item['titulek']) ?></div>
            </div>
            <div class="middle-texts">
                <div class="text-main"><?= safe_text($item['sub_text']) ?></div>
                <div class="text-sub <?= $isPasiva ? 'pasiva' : '' ?>"><?= safe_text($item['vynos']) ?></div>
            </div>
            <div class="tag-cloud">
                <div class="tag-brown"><?= safe_text($item['kratky_typ']) ?></div>
                <?php foreach(($item['tagy'] ?? []) as $t): ?>
                    <div class="tag-brown"><?= safe_text($t) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="bilance-section">
        <h2 class="bilance-title">Bilance majetku</h2>
        <div class="bilance-flex">
            <div class="chart-container">
                <div class="chart-bar bar-aktiva" style="height: <?= $hAktiva ?>%;"><span class="bar-label">Aktiva</span></div>
                <div class="chart-bar bar-pasiva" style="height: <?= $hPasiva ?>%;"><span class="bar-label">Pasiva</span></div>
            </div>
            <div class="table-container">
                <div class="bil-row"><span>Aktiva</span><span class="val"><?= format_czk($bilance['aktiva']) ?> Kč</span></div>
                <div class="bil-subrow"><span>Ziskovost aktiv</span><span><?= safe_text($bilance['ziskovost']) ?></span></div>
                <div class="bil-row"><span>Pasiva</span><span class="val"><?= format_czk($bilance['pasiva']) ?> Kč</span></div>
                <div class="bil-subrow"><span>Nákladovost pasiv</span><span><?= safe_text($bilance['nakladovost']) ?></span></div>
                <div class="bil-row total"><span>Čistý majetek</span><span class="val"><?= format_czk($bilance['cisty_majetek']) ?> Kč</span></div>
            </div>
        </div>
        <div class="bottom-boxes">
            <div class="warning-box">
                <div class="perc-circle"><?= safe_text($bilance['pomer_pasiv_procento']) ?></div>
                <div class="warning-text">
                    <h4>Pozor! Vaše pasiva jsou vysoká</h4>
                    <p><?= safe_text($bilance['varovani_text']) ?></p>
                </div>
            </div>
            <div class="insurance-box">
                <h4>Jak se proti tomu pojistit?</h4>
                <p>Řešení naleznete na straně č. <?= (int)$bilance['odkaz_strana'] ?>.</p>
            </div>
        </div>
    </div>
</div>