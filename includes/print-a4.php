<style>
:root {
    --clr-primary:    #927355;
    --clr-text:       #3d3229;
    --clr-text-light: #554a42;
    --clr-gray:       #8c8c8c;
    --clr-bg-light:   #f9f7f5;
    --clr-success:    #2ecc71;
    --clr-warning:    #f39c12;
    --clr-danger:     #e74c3c;
    --radius-sm:      12px;
    --radius-md:      16px;
    --radius-lg:      20px;
}

@page {
    size: A4 portrait;
    margin: 15mm 12mm 20mm 12mm;
}

@media print {
    body {
        -webkit-print-color-adjust: exact; 
        print-color-adjust: exact; 
        background: white !important;
        margin: 0;
        padding: 0 !important;
    }
    .main-container, .container-a4 {
        width: 900px !important; 
        max-width: 900px !important;
        margin: 0 auto !important;
        padding: 0 10mm !important;
        box-shadow: none !important;
        background: transparent !important;
    }

    .fixed, .no-print, [class*="fixed"] {
        display: none !important;
    }

    h1, h2, h3, .section-header { color: black !important; }
    a { color: black !important; text-decoration: none; }
    .row-item, 
    .section-column, 
    .bilance-layout, 
    .row-section,
    .chart-section,
    .warning-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
}
</style>