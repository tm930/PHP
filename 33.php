<?php
require_once __DIR__ . '/includes/helpers.php';
$jsonFile = 'data.json';
if (!file_exists($jsonFile)) { die('Chyba: data.json nenalezen'); }
$jsonContent = file_get_contents($jsonFile);
$dataRaw = json_decode($jsonContent, true);
$page3 = $dataRaw['stranka3'];

function parsePrice($priceString) {
    return (float)preg_replace('/[^-0-9,.]/', '', str_replace(',', '.', $priceString));
}

$valAktiva = parsePrice($page3['bilance']['aktiva']); 
$valPasiva = parsePrice($page3['bilance']['pasiva']); 
$maxOsaGrafu = 4500000; 
$heightAktiva = ($valAktiva / $maxOsaGrafu) * 100;
$heightPasiva = ($valPasiva / $maxOsaGrafu) * 100;

function formatCzechText($text) {
    return preg_replace('/(?<=^| )([a-z]{1,2})( )/i', '$1&nbsp;', $text);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strana 33 - Movitý majetek</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #ffffff; color: #3d3229; padding: 40px 40px 140px 40px; }
        .main-container { max-width: 900px; margin: 0 auto; }
        .section-header { 
            font-family: 'Lora', serif; font-size: 34px; font-weight: 700; color: #5d4037; 
            margin-bottom: 12px; display: flex; align-items: center; gap: 15px; 
        }
        
        .description { font-size: 14px; color: #8c8c8c; line-height: 1.6; margin-bottom: 30px; text-align: justify; }
        .row-item { 
            display: flex; align-items: center; gap: 20px; margin-bottom: 15px; 
            padding: 20px; border-radius: 15px; background: #fff;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02); border: 1px solid #f5f5f5;
        }
        .status-circle { width: 44px; height: 44px; border-radius: 50%; border: 1px solid currentColor; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 18px; }
        .icon-check { color: #2ecc71; } .icon-warn { color: #f39c12; }
        .amount-box { background: #F9F7F5; border-radius: 10px; padding: 15px 20px; min-width: 200px; border: 1px solid #f0efeb; }
        .amount-value { font-size: 20px; font-weight: 700; color: #927355; }
        .amount-sub { font-size: 14px; color: #8c8c8c; margin-top: 2px; }
        .info-center { flex: 1; padding-left: 10px; }
        .info-title { font-size: 16px; font-weight: 700; color: #3d3229; }
        .info-subtitle { font-size: 14px; color: #927355; font-weight: 600; margin-top: 2px; }
        .info-date { font-size: 12px; color: #8c8c8c; margin-top: 2px; }
        .tags-wrapper { width: 320px; display: flex; justify-content: flex-end; }
        .tags-grid { display: grid; grid-template-columns: 1fr 1fr; width: 100%; gap: 8px; }
        .tags-column { display: flex; flex-direction: column; width: 100%; gap: 8px; }
        .tag-pill { border: 1px solid #D1CFC7; border-radius: 8px; padding: 6px 12px; font-size: 12px; color: #7a7a7a; text-align: center; background: #fff; white-space: nowrap; }
        .bilance-layout { display: flex; gap: 40px; margin-top: 50px; align-items: stretch; transition: opacity 0.3s ease; }
        .chart-section { flex: 1; background: #FDFBFA; border-radius: 20px; padding: 40px; border: 1px solid #F0EFEB; display: flex; flex-direction: column; justify-content: center; }
        .chart-container-relative { position: relative; height: 320px; width: 100%; }
        .chart-y-axis { position: absolute; left: 0; top: 0; height: 280px; display: flex; flex-direction: column; justify-content: space-between; font-size: 11px; color: #B0B0B0; text-align: right; width: 85px; }
        .chart-area { margin-left: 110px; height: 280px; border-bottom: 2px solid #E0E0E0; display: flex; align-items: flex-end; justify-content: space-evenly; position: relative; }
        .chart-line { position: absolute; width: 100%; border-top: 1px solid #F0EFEB; z-index: 0; }
        .bar { width: 110px; border-radius: 5px 5px 0 0; position: relative; z-index: 1; transition: all 0.3s ease; cursor: pointer; }
        .bar:hover { filter: brightness(0.9); transform: scaleX(1.05); }
        .bar.inactive { opacity: 0.2; }      
        .bar-label { position: absolute; bottom: -35px; left: 50%; transform: translateX(-50%); font-weight: 700; color: #8C8C8C; font-size: 14px; }
        .stats-section { width: 420px; display: flex; flex-direction: column; justify-content: space-between; }
        .stats-section h3 { font-family: 'Lora'; font-size: 32px; font-weight: 700; margin-bottom: 20px; }
        .stat-row { display: flex; justify-content: space-between; align-items: center; padding: 14px 0; border-bottom: 1px solid #f0efeb; font-size: 15px; transition: all 0.3s ease; }
        .stat-row.main { border: 1.5px solid #927355; border-radius: 12px; padding: 14px 20px; margin: 8px 0; font-weight: 700; }
        .stat-row.highlight { background: #826344; color: white; border-radius: 12px; padding: 16px 20px; margin-top: auto; font-weight: 700; }
        .stat-row.inactive { opacity: 0.15; filter: grayscale(1); transform: scale(0.98); }
        .bottom-section { display: flex; gap: 40px; margin-top: 40px; transition: opacity 0.3s ease; }
        .warning-card { flex: 1; background: #FFF5F5; border: 1px solid #FED7D7; border-radius: 20px; padding: 30px; position: relative; }
        .warning-badge { position: absolute; top: 25px; right: 25px; background: #E53E3E; color: white; padding: 8px 18px; border-radius: 30px; font-weight: 700; font-size: 18px; }

        @media print { .no-print { display: none; } body { padding: 20px; } }
    </style>
</head>
<body>
    <div class="main-container">
        <h2 class="section-header"><i class="fa-solid fa-car"></i> Movitý majetek</h2>
        
        <p class="description">
            <?php echo formatCzechText("Pojmem movitý majetek se označují všechny věci, které nejsou pevně spojeny se zemí. Jedná se o majetek, který lze přemístit, aniž by došlo k jeho poškození nebo znehodnocení."); ?>
        </p>

        <?php foreach ($page3['movity_majetek'] as $item): ?>
        <div class="row-item">
            <div class="status-circle icon-<?php echo $item['ikona']; ?>"><i class="fa-solid fa-<?php echo $item['ikona'] == 'check' ? 'check' : 'exclamation'; ?>"></i></div>
            <div class="amount-box">
                <div class="amount-value"><?php echo $item['hodnota']; ?></div>
                <div class="amount-sub"><?php echo $item['sub_text'] ?? ''; ?></div>
            </div>
            <div class="info-center">
                <div class="info-title"><?php echo $item['titulek']; ?></div>
                <div class="info-subtitle"><?php echo $item['vynos']; ?></div>
                <?php if (!empty($item['obdobi'])): ?>
                    <div class="info-date"><?php echo $item['obdobi']; ?></div>
                <?php endif; ?>
            </div>
            <div class="tags-wrapper">
                <div class="<?php echo (count($item['tagy']) === 3) ? 'tags-column' : 'tags-grid'; ?>">
                    <?php foreach ($item['tagy'] as $tag): ?><div class="tag-pill"><?php echo $tag; ?></div><?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="bilance-layout">
            <div class="chart-section">
                <div class="chart-container-relative">
                    <div class="chart-y-axis">
                        <span>4 500 000</span><span>4 000 000</span><span>3 500 000</span><span>3 000 000</span><span>2 500 000</span><span>2 000 000</span><span>1 500 000</span><span>1 000 000</span><span>500 000</span><span>0</span>
                    </div>
                    <div class="chart-area">
                        <?php for($i=0; $i<=100; $i+=(100/9)): ?>
                            <div class="chart-line" style="top: <?php echo $i; ?>%"></div>
                        <?php endfor; ?>
                        <div class="bar interactive-bar" data-type="aktiva" style="height: <?php echo $heightAktiva; ?>%; background: #D6A97E;"><span class="bar-label">Aktiva</span></div>
                        <div class="bar interactive-bar" data-type="pasiva" style="height: <?php echo $heightPasiva; ?>%; background: #927355;"><span class="bar-label">Pasiva</span></div>
                    </div>
                </div>
            </div>
            <div class="stats-section">
                <h3>Bilance majetku</h3>
                <div class="rows-container">
                    <div class="stat-row main" id="row-aktiva"><span>Aktiva</span><span><?php echo $page3['bilance']['aktiva']; ?></span></div>
                    <div class="stat-row sub-aktiva"><span class="text-gray-500">Ziskovost aktiv</span><span><?php echo $page3['bilance']['ziskovost']; ?></span></div>
                    <div class="stat-row main" id="row-pasiva" style="margin-top: 15px;"><span>Pasiva</span><span><?php echo $page3['bilance']['pasiva']; ?></span></div>
                    <div class="stat-row sub-pasiva"><span class="text-gray-500">Nákladovost pasiv</span><span><?php echo $page3['bilance']['nakladovost']; ?></span></div>
                </div>
                <div class="stat-row highlight"><span>Čistý majetek</span><span><?php echo $page3['bilance']['cisty_majetek']; ?></span></div>
            </div>
        </div>

        <div class="bottom-section" id="footer-summary">
            <div class="warning-card">
                <div class="warning-badge"><?php echo $page3['bilance']['pomer_pasiv']; ?></div>
                <h4 class="font-bold text-xl mb-2">Pozor! Vaše pasiva jsou vysoká</h4>
                <p class="text-gray-500 text-sm leading-relaxed max-w-[80%]">Výše vašich aktiv se blíží hodnotě vašich pasiv. Kvůli tomu budete méně odolní v případě tržního výkyvu.</p>
            </div>
            <div class="w-[350px] bg-white border border-[#F0EFEB] rounded-[15px] p-8 flex flex-col justify-center text-center">
                <h4 class="font-medium text-lg mb-1">Jak se proti tomu pojistit?</h4>
                <p class="text-[#927355] font-semibold">Řešení naleznete na <a href="#" class="underline">straně č. 15.</a></p>
            </div>
        </div>
    </div> 
    
    <div class="fixed bottom-8 left-8 right-8 flex justify-between items-center no-print pointer-events-none">
        <a href="32.php" class="pointer-events-auto bg-[#927355] text-white px-8 py-4 rounded-xl font-bold shadow-2xl hover:bg-[#7a6046] hover:-translate-y-1 transition-all flex items-center gap-3 no-underline">
            <i class="fa-solid fa-chevron-left text-sm"></i> Předchozí stránka
        </a>
        <a href="34.php" class="pointer-events-auto bg-[#927355] text-white px-8 py-4 rounded-xl font-bold shadow-2xl hover:bg-[#7a6046] hover:-translate-y-1 transition-all flex items-center gap-3 no-underline">
            Další stránka <i class="fa-solid fa-chevron-right text-sm"></i>
        </a>
    </div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const bars = document.querySelectorAll('.interactive-bar');
    const statRows = document.querySelectorAll('.stat-row');
    const footerSummary = document.getElementById('footer-summary');

    bars.forEach(bar => {
        bar.addEventListener('mouseenter', () => {
            const type = bar.getAttribute('data-type'); 
            bars.forEach(b => {
                if (b !== bar) b.classList.add('inactive');
            });

            statRows.forEach(row => {
                const isTarget = row.id === `row-${type}` || row.classList.contains(`sub-${type}`);
                const isHighlight = row.classList.contains('highlight');

                if (!isTarget && !isHighlight) {
                    row.classList.add('inactive');
                }
            });
            footerSummary.style.opacity = '0.15';
        });

        bar.addEventListener('mouseleave', () => {
            bars.forEach(b => b.classList.remove('inactive'));
            statRows.forEach(row => row.classList.remove('inactive'));
            footerSummary.style.opacity = '1';
        });
    });
});
</script>
</body>
</html>