<?php
require_once __DIR__ . '/includes/helpers.php';
$jsonFile = 'data.json';
if (!file_exists($jsonFile)) { die('Chyba: data.json nenalezen'); }
$jsonContent = file_get_contents($jsonFile);
$dataRaw = json_decode($jsonContent, true);
$p4 = $dataRaw['stranka4'];

function formatCZ($text) {
    $text = preg_replace('/(?<=^| )([a-z]{1,2})( )/i', '$1&nbsp;', $text);
    return $text;
}

function renderDonut($data, $sectionIndex) {
    $size = 230; $r = 90; $sw = 16;
    $circ = 2 * pi() * $r;
    $svg = '<svg width="'.$size.'" height="'.$size.'" viewBox="0 0 '.$size.' '.$size.'" style="transform: rotate(-90deg); overflow: visible;">';
    $offset = 0;
    foreach ($data as $rowIndex => $item) {
        if (!isset($item['procent']) || $item['procent'] <= 0) continue;
        $dash = ($item['procent'] / 100) * $circ;
        $svg .= '<circle cx="'.($size/2).'" cy="'.($size/2).'" r="'.$r.'" 
                 fill="none" 
                 stroke="'.$item['color'].'" 
                 stroke-width="'.$sw.'" 
                 stroke-dasharray="'.$dash.' '.$circ.'" 
                 stroke-dashoffset="-'.$offset.'" 
                 class="donut-segment cursor-pointer transition-all duration-300 hover:stroke-width-[22px]"
                 data-section="'.$sectionIndex.'" 
                 data-row="'.$rowIndex.'" />';
        $offset += $dash;
    }
    return $svg . '</svg>';
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Strana 34 - Analýza portfolia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lora:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        
        body { font-family: 'Plus Jakarta Sans', sans-serif; color: #3d3229; padding: 40px 40px 140px 40px; background: #fff; }
        .lora { font-family: 'Lora', serif; }
        .container-a4 { max-width: 900px; margin: 0 auto; }
        
        .row-section { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 70px; 
            gap: 60px; 
            transition: opacity 0.3s ease;
        }
        
        .text-part { flex: 1; }
        .graph-part { flex-shrink: 0; display: flex; align-items: center; justify-content: center; width: 230px; }
        .data-row { 
            border: 1.5px solid #F0EFEB; 
            border-radius: 8px; 
            padding: 14px 20px; 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 10px; 
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .data-row.inactive { opacity: 0.15; filter: grayscale(1); transform: scale(0.98); }
        .footer-banner-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 40px;
            gap: 60px;
            transition: opacity 0.3s ease;
        }

        .banner-summary { 
            background: #F1F9F0; 
            border: 1px solid #E2F0E1; 
            border-radius: 15px; 
            padding: 25px 35px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            flex: 1;
        }

        .trophy-space {
            width: 230px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .donut-segment { transition: stroke-width 0.3s ease, opacity 0.3s ease; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="container-a4">

    <?php 
    $sekce_klice = ['sekce_horizont', 'sekce_aktiva_pasiva', 'sekce_likvidita'];

    foreach ($sekce_klice as $sIdx => $klic): 
        $s = $p4[$klic];
        $tag = ($sIdx === 0) ? 'h1' : 'h2';
    ?>
    <div class="row-section" id="section-<?php echo $sIdx; ?>">
        <div class="text-part">
            <<?php echo $tag; ?> class="lora text-4xl mb-4"><?php echo $s['titulek']; ?></<?php echo $tag; ?>>
            <p class="text-gray-400 text-[12px] leading-relaxed mb-8 text-justify">
                <?php echo formatCZ($s['popis']); ?>
            </p>
            
            <div class="rows-container">
                <?php foreach ($s['data'] as $rIdx => $item): ?>
                <div class="data-row" 
                     id="row-<?php echo $sIdx . '-' . $rIdx; ?>"
                     style="border-left: 6px solid <?php echo $item['color']; ?>; padding-left: 16px;">
                    <span class="font-medium"><?php echo $item['label']; ?></span>
                    <span class="font-bold"><?php echo $item['hodnota']; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="graph-part">
            <?php echo renderDonut($s['data'], $sIdx); ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="footer-banner-container" id="footer-summary">
        <div class="banner-summary items-start">
            <div class="pr-8">
                <h3 class="font-bold text-xl mb-1 text-[#3d3229] leading-tight"><?php echo $p4['diverzifikace']['titulek']; ?></h3>
                <p class="text-gray-400 text-sm leading-relaxed">
                    <?php 
                        $puvodniText = formatCZ($p4['diverzifikace']['text']);
                        echo str_replace('. ', '.<br>', $puvodniText); 
                    ?>
                </p>
            </div>
            <div class="bg-[#82C374] text-white font-bold px-3 py-1 rounded-lg text-lg shrink-0 mt-1">
                <?php echo $p4['diverzifikace']['procent']; ?>
            </div>
        </div>

        <div class="trophy-space">
            <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M22 15H78V48C78 63.464 65.464 76 50 76C34.536 76 22 63.464 22 48V15Z" stroke="#927355" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22 18H78" stroke="#927355" stroke-width="3" stroke-linecap="round"/>
                <path d="M22 48H16C12.6863 48 10 45.3137 10 42V28C10 24.6863 12.6863 22 16 22H22" stroke="#927355" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M78 48H84C87.3137 48 90 45.3137 90 42V28C90 24.6863 87.3137 22 84 22H78" stroke="#927355" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M50 76V92" stroke="#927355" stroke-width="3" stroke-linecap="round"/>
                <path d="M35 92H65" stroke="#927355" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>
    </div>

</div>

<div class="fixed bottom-8 left-8 right-8 flex justify-between items-center no-print pointer-events-none">
    <a href="33.php" class="pointer-events-auto bg-[#927355] text-white px-8 py-4 rounded-xl font-bold shadow-2xl hover:bg-[#7a6046] hover:-translate-y-1 transition-all flex items-center gap-3 no-underline">
        <i class="fa-solid fa-chevron-left text-sm"></i> Předchozí stránka
    </a>
    <a href="35.php" class="pointer-events-auto bg-[#927355] text-white px-8 py-4 rounded-xl font-bold shadow-2xl hover:bg-[#7a6046] hover:-translate-y-1 transition-all flex items-center gap-3 no-underline">
        Další stránka <i class="fa-solid fa-chevron-right text-sm"></i>
    </a>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const segments = document.querySelectorAll('.donut-segment');
    const sections = document.querySelectorAll('.row-section');
    const footerSummary = document.getElementById('footer-summary');

    segments.forEach(seg => {
        seg.addEventListener('mouseenter', () => {
            const sIdx = seg.getAttribute('data-section');
            const rIdx = seg.getAttribute('data-row');
            
            sections.forEach((section, i) => {
                if (i.toString() === sIdx) {
                    section.style.opacity = '1';
                    const rows = section.querySelectorAll('.data-row');
                    const targetRowId = `row-${sIdx}-${rIdx}`;
                    rows.forEach(row => {
                        if (row.id === targetRowId) row.classList.remove('inactive');
                        else row.classList.add('inactive');
                    });
                } else {
                    section.style.opacity = '0.15';
                }
            });
            footerSummary.style.opacity = '0.15';
        });

        seg.addEventListener('mouseleave', () => {
            sections.forEach(s => {
                s.style.opacity = '1';
                s.querySelectorAll('.data-row').forEach(r => r.classList.remove('inactive'));
            });
            footerSummary.style.opacity = '1';
        });
    });
});
</script>
</body>
</html>