<?php
require_once __DIR__ . '/includes/helpers.php';
$json = 'data.json';
if (!file_exists($json)) die('Chyba: data.json nenalezen');
$dataRaw = json_decode(file_get_contents($json), true);
$data = $dataRaw['stranka1'] ?? [];
$hodnotyProGraf = array_map('abs', $data['graf_data'] ?? [0, 0, 0]);
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Strana 31 – <?= htmlspecialchars($data['titulek'] ?? 'Přehled majetku') ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php include 'includes/print-a4.php'; ?>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Lora:wght@400;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: white;
            color: var(--clr-text);
            padding: 2.5rem 1.25rem;
        }
        .main-container { max-width: 900px; margin: 0 auto; }
        .main-title {
            font-family: 'Lora', serif;
            font-size: 3.8rem;
            font-weight: 700;
            line-height: 1.05;
            letter-spacing: -0.025em;
        }
        .main-title .gray { color: var(--clr-gray); font-weight: 700; }
        .intro-text {
            font-size: 1.25rem;
            line-height: 1.7;
            max-width: 38rem;
            color: var(--clr-text-light);
            margin-top: 2.5rem;
        }
        .asset-bar {
            background: var(--clr-primary);
            color: white;
            border-radius: var(--radius-lg);
            padding: 0.75rem 2.25rem;
            max-width: 28rem;
            margin: 2rem auto 0;
            font-size: 1.2rem;
        }
        .section-column { transition: opacity 0.25s ease; }
        .section-column.inactive { opacity: 0.25; filter: grayscale(0.7); }
        .item-card {
            border-radius: var(--radius-md);
            padding: 1.125rem 1.375rem;
            margin-bottom: 0.75rem;
            background: white;
            border: 1px solid #f0f0f0;
            box-shadow: 0 3px 10px rgba(0,0,0,0.04);
        }
        .status-icon {
            width: 28px; height: 28px;
            border-radius: 50%;
            border: 1.5px solid currentColor;
            display: grid; place-items: center;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="main-container">
    <div class="flex flex-col lg:flex-row gap-10 mb-16 items-start">
        <div class="flex-1">
            <h1 class="main-title">
                <span class="gray">Přehled</span><br>vašeho majetku
            </h1>
            <p class="intro-text"><?= format_czech_text($data['uvodni_text'] ?? '') ?></p>
        </div>

        <div class="shrink-0 text-center">
            <div style="width:320px;height:320px;">
                <canvas id="assetChart"></canvas>
            </div>
            <div class="asset-bar flex items-center justify-center gap-4">
                <span>Čistá hodnota majetku</span>
                <strong><?= format_czk($data['cisla_hodnota'] ?? 0) ?> Kč</strong>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <?php
        $icons = [
            'financni_aktiva' => 'fa-money-bill-1',
            'nemovitosti'     => 'fa-house',
            'movity_majetek'  => 'fa-car'
        ];
        $i = 0;
        foreach ($data['sekce'] ?? [] as $key => $sec):
        ?>
        <div class="flex flex-col section-column" id="col-<?= $i ?>">
            <div class="flex items-center gap-3 p-3 border-2 border-[var(--clr-primary)] rounded-xl mb-5 bg-white">
                <i class="fa-solid <?= $icons[$key] ?? 'fa-wallet' ?> text-2xl text-[var(--clr-primary)] w-8 text-center"></i>
                <div class="flex-1 text-right">
                    <div class="font-bold text-[var(--clr-primary)] text-base"><?= htmlspecialchars($sec['nazev'] ?? '') ?></div>
                    <div class="text-sm text-[var(--clr-gray)]"><?= format_czk($sec['hodnota'] ?? 0) ?> Kč</div>
                </div>
            </div>

            <?php foreach ($sec['polozky'] ?? [] as $item):
                $raw = $item['ikona'] ?? '';
                $val = $item['val'] ?? 0;

                if ($raw === '✅') {
                    $cls = 'text-[var(--clr-success)]';
                    $ico = 'fa-check';
                } elseif (in_array($raw, ['❌', 'x', 'X'])) {
                    $cls = 'text-gray-400';
                    $ico = 'fa-xmark';
                } else {
                    $cls = 'text-[var(--clr-warning)]';
                    $ico = 'fa-exclamation';
                }
            ?>
            <div class="item-card flex items-center gap-4">
                <div class="status-icon <?= $cls ?>">
                    <i class="fa-solid <?= $ico ?>"></i>
                </div>
                <div class="flex-1">
                    <div class="text-sm text-gray-500 mb-0.5"><?= htmlspecialchars($item['label'] ?? '') ?></div>
                    <div class="text-lg font-semibold text-gray-800"><?= format_czk($val) ?> Kč</div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php $i++; endforeach; ?>
    </div>

    <div class="fixed bottom-8 right-8 no-print z-50">
        <a href="32.php" class="bg-[var(--clr-primary)] text-white px-8 py-4 rounded-xl font-semibold shadow-xl hover:bg-[#7a6046] transition-colors flex items-center gap-2">
            Další stránka <i class="fa-solid fa-chevron-right text-sm"></i>
        </a>
    </div>
</div>

 <script>
        const chartData = <?php echo json_encode($hodnotyProGraf); ?>;
        const canvas = document.getElementById('assetChart');
        const ctx = canvas.getContext('2d');
        
        function resetColumns() {
            const columns = document.querySelectorAll('.section-column');
            columns.forEach(col => col.classList.remove('inactive'));
        }
        const myChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: chartData,
                    backgroundColor: '#f9f9f9', 
                    borderColor: '#927355',    
                    borderWidth: 6,
                    hoverBackgroundColor: '#927355', 
                    hoverBorderColor: '#927355',
                    spacing: 0
                }]
            },
            options: {
                circumference: 310,
                rotation: 0,
                cutout: '60%',
                responsive: true,
                maintainAspectRatio: true,
                plugins: { 
                    legend: { display: false }, 
                    tooltip: { enabled: false } 
                },
                onHover: (event, chartElement) => {
                    const columns = document.querySelectorAll('.section-column');
                    if (chartElement.length) {
                        const activeIndex = chartElement[0].index;
                        columns.forEach((col, i) => {
                            if (i === activeIndex) col.classList.remove('inactive');
                            else col.classList.add('inactive');
                        });
                    } else {
                        resetColumns();
                    }
                }
            }
        });

        canvas.addEventListener('mouseleave', () => {
            resetColumns();
        });
    </script>
</body>
</html>