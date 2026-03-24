<?php
require_once __DIR__ . '/includes/helpers.php';
$json = 'data.json';
if (!file_exists($json)) die('Chyba: data.json nenalezen');

$dataRaw = json_decode(file_get_contents($json), true);
$data = $dataRaw['stranka2'] ?? [];
?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strana 32 – Detailní přehled</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <?php include 'includes/print-a4.php'; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: white;
            color: var(--clr-text);
            padding: 3.5rem 2.5rem;
        }

        .main-container { max-width: 900px; margin: 0 auto; }
        .section-header {
            font-family: 'Lora', serif;
            font-size: 2.1rem;
            font-weight: 700;
            color: #5d4037;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .section-header i { font-size: 1.75rem; color: var(--clr-primary); width: 2.5rem; text-align: center; }
        .description { font-size: 0.9rem; color: var(--clr-gray); line-height: 1.65; margin-bottom: 2.5rem; }
        .row-item {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 1.25rem;
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-md);
            background: white;
            border: 1px solid #f5f5f5;
            box-shadow: 0 6px 18px rgba(0,0,0,0.03);
        }

        .status-circle {
            width: 38px; height: 38px;
            border-radius: 50%;
            border: 1.5px solid currentColor;
            display: grid; place-items: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .amount-box {
            background: var(--clr-bg-light);
            border-radius: var(--radius-sm);
            padding: 1rem 1.25rem;
            min-width: 210px;
            border: 1px solid #f0efeb;
        }

        .amount-value { font-size: 1.25rem; font-weight: 700; color: var(--clr-primary); }
        .info-center { flex: 1; padding: 0 0.75rem; }
        .tag-pill {
            border: 1px solid #d1cfc7;
            border-radius: var(--radius-sm);
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            color: #777;
            text-align: center;
            white-space: nowrap;
        }

        .divider { border-top: 1px solid #f0efeb; margin: 2.5rem 0; }
    </style>
</head>
<body>

<div class="main-container">
    <?php
    foreach ($data['sekce_detaily'] ?? [] as $idx => $sekce):
        if ($idx > 0) echo '<div class="divider"></div>';
        $ikona = $sekce['ikona'] ?? '';
        if (!empty($ikona) && strpos($ikona, 'fa-') !== 0) {
            $ikona = 'fa-' . $ikona;
        }
        $kategorie = $sekce['kategorie'] ?? '';
    ?>
    <h2 class="section-header">
        <i class="fa-solid <?= htmlspecialchars($ikona ?: 'fa-wallet') ?>"></i>
        <?= htmlspecialchars($kategorie) ?>
    </h2>
    <p class="description"><?= format_czech_text($sekce['popis'] ?? '') ?></p>

    <?php foreach ($sekce['polozky'] ?? [] as $item):
        $nazev = $item['nazev'] ?? '';

        if ($kategorie === 'Nemovitosti') {
            $cls = 'text-[var(--clr-success)]';
            $ico = 'fa-check';
        } else {
            $lower = mb_strtolower($nazev);
            if (str_contains($lower, 'investika')) {
                $cls = 'text-[var(--clr-success)]';
                $ico = 'fa-check';
            } elseif (str_contains($lower, 'komerční banka')) {
                $cls = 'text-[var(--clr-warning)]';
                $ico = 'fa-exclamation';
            } else {
                $cls = 'text-gray-400';
                $ico = 'fa-xmark';
            }
        }
    ?>
    <div class="row-item">
        <div class="status-circle <?= $cls ?>">
            <i class="fa-solid <?= $ico ?>"></i>
        </div>

        <div class="amount-box">
            <div class="amount-value"><?= format_czk($item['aktiva_hodnota'] ?? 0) ?> Kč</div>
            <div class="text-sm text-[var(--clr-gray)] mt-0.5"><?= htmlspecialchars($nazev) ?></div>
        </div>

        <div class="info-center">
            <div class="font-bold text-base"><?= htmlspecialchars($item['lokalita'] ?? $item['typ'] ?? '') ?></div>
            <div class="text-[var(--clr-primary)] font-semibold"><?= htmlspecialchars($item['vynos'] ?? '') ?></div>
            
            <?php 
            if (!empty($item['obdobi']) && $kategorie !== 'Nemovitosti'): 
            ?>
                <div class="text-xs text-[var(--clr-gray)] mt-1">
                    <?= htmlspecialchars($item['obdobi']) ?>
                </div>
            <?php endif; ?>

            <div class="text-xs text-[var(--clr-gray)] mt-1"><?= htmlspecialchars($item['parametry'] ?? '') ?></div>
        </div>

        <div class="w-72 flex-shrink-0">
            <div class="<?= ($kategorie === 'Nemovitosti') ? 'flex flex-col' : 'grid grid-cols-2' ?> gap-1.5">
                <div class="tag-pill">
                    <?= htmlspecialchars($item['kratky_typ'] ?? $item['typ'] ?? $nazev) ?>
                </div>
                <div class="tag-pill">Aktiva</div>
                <?php if ($kategorie !== 'Nemovitosti'): ?>
                    <div class="tag-pill"><?= htmlspecialchars($item['likvidita'] ?? 'Běžná') ?></div>
                <?php endif; ?>
                <div class="tag-pill"><?= htmlspecialchars($item['horizont'] ?? '') ?></div>
            </div>
        </div>
    </div>

    <?php if (!empty($item['pasiva_hodnota'])): ?>
    <div class="row-item">
        <div class="status-circle text-[var(--clr-warning)]">
            <i class="fa-solid fa-exclamation"></i>
        </div>
        <div class="amount-box">
            <div class="amount-value"><?= format_czk($item['pasiva_hodnota']) ?> Kč</div>
            <div class="text-sm text-[var(--clr-gray)] mt-0.5"><?= htmlspecialchars($item['pasiva_label'] ?? 'Dluh/Závazek') ?></div>
        </div>
        <div class="info-center">
            <div class="font-bold"><?= htmlspecialchars($item['banka'] ?? '') ?></div>
            <?php if (!empty($item['obdobi'])): ?>
                <div class="text-xs text-[var(--clr-gray)] mt-1">
                    <?= htmlspecialchars($item['obdobi']) ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="w-72 grid grid-cols-2 gap-1.5">
            <div class="tag-pill"><?= htmlspecialchars($item['pasiva_label'] ?? 'Závazek') ?></div>
            <div class="tag-pill">Pasiva</div>
            <div class="tag-pill">Dlouhodobý</div>
            <div class="tag-pill">Splatnost</div>
        </div>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
    <?php endforeach; ?>
</div>

<div class="fixed bottom-8 left-8 right-8 flex justify-between no-print pointer-events-none z-50">
    <a href="31.php" class="pointer-events-auto bg-[var(--clr-primary)] text-white px-8 py-4 rounded-xl font-semibold shadow-xl hover:bg-[#7a6046] transition-colors flex items-center gap-2">
        <i class="fa-solid fa-chevron-left text-sm"></i> Předchozí
    </a>
    <a href="33.php" class="pointer-events-auto bg-[var(--clr-primary)] text-white px-8 py-4 rounded-xl font-semibold shadow-xl hover:bg-[#7a6046] transition-colors flex items-center gap-2">
        Další <i class="fa-solid fa-chevron-right text-sm"></i>
    </a>
</div>
</body>
</html>