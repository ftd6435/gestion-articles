// PRINT MODE SALE - Improved version without new tab
function printModal() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printFrame';

    document.body.appendChild(printFrame);

    // Get the content to print
    const printableArea = document.getElementById('printableArea');
    const contentToPrint = printableArea.innerHTML;

    // Get modal header content
    const modalHeader = document.querySelector('.modal-header');
    const clonedHeader = modalHeader.cloneNode(true);
    const closeBtn = clonedHeader.querySelector('.btn-close');
    if (closeBtn) closeBtn.remove();
    const headerContent = clonedHeader.querySelector('div');

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    /* Print-specific styles */
                    @media print {
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }

                        /* Minimal page margins */
                        @page {
                            margin: 5mm; /* Reduced from 15mm/10mm to 5mm */
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            font-size: 12pt;
                            line-height: 1.4;
                        }

                        /* Container with minimal padding */
                        .container {
                            max-width: 210mm;
                            margin: 0 auto;
                            padding: 10px; /* Reduced padding */
                            background: white;
                        }

                        .print-header {
                            background: linear-gradient(135deg, #667eea, #764ba2) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            color: white !important;
                            padding: 20px;
                            border-radius: 5px 5px 0 0;
                            margin-bottom: 20px;
                        }

                        .table {
                            border-collapse: collapse !important;
                            width: 100% !important;
                            font-size: 10pt;
                        }

                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 8px !important;
                        }

                        .table-sm th,
                        .table-sm td {
                            padding: 5px !important;
                        }

                        .no-print,
                        button,
                        .btn,
                        .modal-footer,
                        .btn-close {
                            display: none !important;
                        }

                        .d-print-block {
                            display: block !important;
                        }

                        .d-print-none {
                            display: none !important;
                        }

                        .badge {
                            border: 1px solid #ddd !important;
                            padding: 3px 6px !important;
                            font-size: 9pt !important;
                        }

                        /* Ensure background colors print */
                        .bg-success { background-color: #d1e7dd !important; }
                        .bg-warning { background-color: #fff3cd !important; }
                        .bg-danger { background-color: #f8d7da !important; }
                        .bg-secondary { background-color: #e2e3e5 !important; }
                        .bg-info { background-color: #d1ecf1 !important; }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            padding: 30px;
                            background: #f8f9fa;
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
                        }

                        .print-header {
                            background: linear-gradient(135deg, #667eea, #764ba2);
                            color: white;
                            padding: 20px;
                            border-radius: 5px;
                            margin-bottom: 25px;
                        }

                        .container {
                            max-width: 210mm; /* A4 width */
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 8px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        }

                        .table {
                            margin-bottom: 20px;
                        }

                        .print-only-footer {
                            margin-top: 40px;
                            padding-top: 15px;
                            border-top: 1px solid #dee2e6;
                            font-size: 9pt;
                            color: #666;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <!-- Print Header -->
                    <div class="print-header">
                        ${headerContent.innerHTML}
                    </div>

                    <!-- Content -->
                    ${contentToPrint}
                </div>

                <script>
                    // Auto-print and close
                    window.onload = function() {
                        // Short delay to ensure all content is loaded
                        setTimeout(function() {
                            window.print();

                            // Close the window after printing (for browsers that support it)
                            setTimeout(function() {
                                window.close();
                            }, 500);
                        }, 500);
                    };

                    // Fallback for browsers that block auto-print
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'p' && (e.ctrlKey || e.metaKey)) {
                            e.preventDefault();
                            window.print();
                        }
                    });
                </script>
            </body>
        </html>
    `;

    // Write content to iframe
    printFrame.contentWindow.document.open();
    printFrame.contentWindow.document.write(printContent);
    printFrame.contentWindow.document.close();

    // Focus on print frame and print
    printFrame.contentWindow.focus();

    // Wait for iframe to load, then print
    printFrame.onload = function() {
        try {
            printFrame.contentWindow.print();
        } catch (error) {
            console.error('Print error:', error);

            // Fallback: Show print dialog manually
            alert('Pour imprimer, utilisez Ctrl+P ou Cmd+P dans la fenêtre d\'impression');
        }

        // Clean up after printing
        setTimeout(() => {
            if (printFrame && printFrame.parentNode) {
                document.body.removeChild(printFrame);
            }
        }, 1000);
    };
}

// PRINT MODE COMMANDE - Fixed version with better CSS targeting
function printCommande() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printFrame';
    printFrame.title = 'Print Document';

    document.body.appendChild(printFrame);

    // Get the content to print - clone it first to avoid modifying original
    const printableArea = document.getElementById('printableArea');
    const clonedArea = printableArea.cloneNode(true);

    // Remove elements that shouldn't be in the print version
    const elementsToRemove = clonedArea.querySelectorAll('.d-print-none, .alert.alert-info, .modal-footer, .btn, button, .btn-close');
    elementsToRemove.forEach(el => el.remove());

    // Show print-only elements
    const printOnlyElements = clonedArea.querySelectorAll('.d-print-block');
    printOnlyElements.forEach(el => {
        el.style.display = 'block';
        el.classList.remove('d-print-block');
    });

    const contentToPrint = clonedArea.innerHTML;

    // Get modal header content
    const modalHeader = document.querySelector('.modal-header');
    const clonedHeader = modalHeader.cloneNode(true);
    const closeBtn = clonedHeader.querySelector('.btn-close');
    if (closeBtn) closeBtn.remove();
    const headerContent = clonedHeader.querySelector('div');

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    /* Reset and base styles */
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 11pt;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        padding: 0;
                        margin: 0;
                    }

                    /* Print-specific styles */
                    @media print {
                        /* Page setup - A4 paper */
                        @page {
                            size: A4 portrait;
                            margin: 5mm;
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            width: 100%;
                            background: white;
                        }

                        /* Main container */
                        .print-container {
                            width: 100%;
                            max-width: 190mm;
                            margin: 0 auto;
                            padding: 10px;
                        }

                        /* Header styling */
                        .print-header {
                            background: #4CAF50 !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            color: white !important;
                            padding: 15px 20px;
                            margin-bottom: 20px;
                            border-radius: 4px;
                        }

                        .print-header h5 {
                            color: white !important;
                            font-weight: bold;
                            margin: 0;
                            font-size: 18pt;
                        }

                        .print-header small {
                            color: rgba(255, 255, 255, 0.95) !important;
                            font-size: 10pt;
                            opacity: 1 !important;
                        }

                        /* Print-only header at top */
                        .text-center.mb-4 {
                            text-align: center !important;
                            margin-bottom: 20px !important;
                            padding-bottom: 10px;
                            border-bottom: 2px solid #4CAF50;
                            display: block !important;
                        }

                        .text-center.mb-4 h2 {
                            color: #2E7D32;
                            font-size: 20pt;
                            margin-bottom: 5px;
                        }

                        .text-center.mb-4 p {
                            margin: 2px 0;
                            font-size: 10pt;
                        }

                        /* Hide elements that shouldn't print */
                        .modal-footer,
                        .btn,
                        button,
                        .btn-close,
                        .d-print-none {
                            display: none !important;
                        }

                        /* Show print-only elements */
                        .d-print-block {
                            display: block !important;
                        }

                        /* GENERAL CARD STYLING - Apply to all cards */
                        .card {
                            border: 1px solid #dee2e6 !important;
                            margin-bottom: 15px;
                            page-break-inside: avoid;
                            background: white !important;
                        }

                        .card-body {
                            padding: 15px !important;
                        }

                        /* FINANCIAL SUMMARY CARDS - Specific styling */
                        .row.mb-4 .card {
                            height: auto !important;
                            min-height: 120px;
                        }

                        .row.mb-4 .card-body {
                            padding: 10px !important;
                            text-align: center;
                        }

                        .row.mb-4 .fw-bold.h4 {
                            font-size: 14pt !important;
                            margin: 10px 0 !important;
                            display: block !important;
                        }

                        .row.mb-4 small.text-muted {
                            font-size: 9pt !important;
                            color: #666 !important;
                            display: block !important;
                            margin-top: 5px;
                        }

                        /* Fix financial cards layout */
                        .row.mb-4 .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                        }

                        .row.mb-4 .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                            padding: 0 5px !important;
                        }

                        /* RECEPTION PROGRESS CARD - Specific styling */
                        .card.border-0.bg-light.mb-4 {
                            display: block !important;
                            border: 1px solid #dee2e6 !important;
                            background-color: #f8f9fa !important;
                            margin-bottom: 20px !important;
                        }

                        .card.border-0.bg-light.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .card.border-0.bg-light.mb-4 h6.fw-bold {
                            font-size: 12pt !important;
                            margin-bottom: 15px !important;
                            color: #2c3e50 !important;
                        }

                        .card.border-0.bg-light.mb-4 .row {
                            display: flex !important;
                            justify-content: space-between !important;
                            margin: 0 -5px !important;
                        }

                        .card.border-0.bg-light.mb-4 .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                            padding: 0 5px !important;
                            text-align: center !important;
                        }

                        .card.border-0.bg-light.mb-4 .fw-bold.fs-4 {
                            font-size: 16pt !important;
                            font-weight: bold !important;
                            margin-bottom: 5px !important;
                            display: block !important;
                        }

                        .card.border-0.bg-light.mb-4 small.text-muted {
                            font-size: 9pt !important;
                            color: #666 !important;
                            display: block !important;
                        }

                        /* Progress bar styling */
                        .progress {
                            height: 10px !important;
                            background-color: #e9ecef !important;
                            border-radius: 5px !important;
                            margin: 10px 0 0 0 !important;
                            border: 1px solid #dee2e6 !important;
                            overflow: hidden !important;
                        }

                        .progress-bar {
                            background-color: #28a745 !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            height: 100% !important;
                        }

                        /* Tables */
                        .table-responsive {
                            overflow: visible !important;
                        }

                        .table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            margin-bottom: 15px;
                            font-size: 9pt;
                            page-break-inside: avoid;
                        }

                        .table-bordered {
                            border: 1px solid #dee2e6 !important;
                        }

                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 6px 8px !important;
                            vertical-align: middle;
                        }

                        .table thead th {
                            background-color: #f5f5f5 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                            font-weight: bold;
                            text-align: center;
                        }

                        .table tfoot td {
                            font-weight: bold;
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Badges */
                        .badge {
                            border: 1px solid #ccc !important;
                            padding: 2px 6px !important;
                            font-size: 8pt !important;
                            border-radius: 3px;
                        }

                        /* Text alignment */
                        .text-end {
                            text-align: right !important;
                        }

                        .text-center {
                            text-align: center !important;
                        }

                        /* Colors for print */
                        .text-success { color: #198754 !important; }
                        .text-warning { color: #ffc107 !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-info { color: #17a2b8 !important; }
                        .text-primary { color: #007bff !important; }
                        .text-muted { color: #6c757d !important; }

                        .bg-success {
                            background-color: #d4edda !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-warning {
                            background-color: #fff3cd !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-light {
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-info {
                            background-color: #d1ecf1 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-danger {
                            background-color: #f8d7da !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Headings */
                        h6.fw-bold {
                            font-size: 11pt;
                            font-weight: bold;
                            margin-bottom: 10px;
                            color: #2c3e50;
                            page-break-after: avoid;
                        }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            background: #f5f5f5;
                            padding: 30px;
                            margin: 0;
                        }

                        .print-container {
                            max-width: 210mm;
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 8px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        }

                        .print-header {
                            background: linear-gradient(135deg, #4CAF50, #2E7D32);
                            color: white;
                            padding: 20px;
                            border-radius: 5px;
                            margin-bottom: 25px;
                        }
                    }

                    /* Utility classes */
                    .mb-4 { margin-bottom: 1.5rem !important; }
                    .mb-3 { margin-bottom: 1rem !important; }
                    .mb-2 { margin-bottom: 0.5rem !important; }
                    .mt-3 { margin-top: 1rem !important; }
                    .mt-4 { margin-top: 1.5rem !important; }
                    .pt-3 { padding-top: 1rem !important; }
                    .p-4 { padding: 1.5rem !important; }
                    .fw-bold { font-weight: 700 !important; }
                    .fw-semibold { font-weight: 600 !important; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- Print Header from modal -->
                    <div class="print-header">
                        ${headerContent.innerHTML}
                    </div>

                    <!-- Content -->
                    ${contentToPrint}
                </div>

                <script>
                    // Auto-print after content loads
                    window.onload = function() {
                        // Small delay to ensure all CSS and fonts are loaded
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (error) {
                                console.log('Print dialog opened');
                            }

                            // Try to close window after printing
                            window.addEventListener('afterprint', function() {
                                setTimeout(function() {
                                    try {
                                        window.close();
                                    } catch (e) {
                                        // Window might not be closable
                                    }
                                }, 500);
                            });
                        }, 800);
                    };

                    // Manual print trigger
                    document.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                            e.preventDefault();
                            window.print();
                        }
                    });
                <\/script>
            </body>
        </html>
    `;

    // Write content to iframe
    try {
        printFrame.contentWindow.document.open();
        printFrame.contentWindow.document.write(printContent);
        printFrame.contentWindow.document.close();
    } catch (error) {
        console.error('Error creating print document:', error);
        alert('Erreur lors de la préparation de l\'impression. Veuillez réessayer.');

        // Clean up iframe
        if (printFrame.parentNode) {
            document.body.removeChild(printFrame);
        }
        return;
    }

    // Wait for iframe to load, then print
    printFrame.onload = function() {
        setTimeout(function() {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch (error) {
                console.error('Print error:', error);
                alert('Pour imprimer, utilisez Ctrl+P dans la fenêtre d\'impression qui s\'est ouverte.');
            }

            // Clean up iframe
            setTimeout(() => {
                if (printFrame && printFrame.parentNode) {
                    document.body.removeChild(printFrame);
                }
            }, 3000);
        }, 1000);
    };
}

// PRINT MODE RECEPTION - Fixed version with better CSS targeting
function printReceptionDetails() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printFrame';
    printFrame.title = 'Print Document';

    document.body.appendChild(printFrame);

    // Get the content to print - clone it first to avoid modifying original
    const printableArea = document.getElementById('receptionDetailsContent');
    const clonedArea = printableArea.cloneNode(true);

    // Remove elements that shouldn't be in the print version
    const elementsToRemove = clonedArea.querySelectorAll('.modal-footer, .btn, button, .btn-close, .alert.alert-info');
    elementsToRemove.forEach(el => el.remove());

    // Get modal header content
    const modalHeader = document.querySelector('.modal-header');
    const clonedHeader = modalHeader.cloneNode(true);
    const closeBtn = clonedHeader.querySelector('.btn-close');
    if (closeBtn) closeBtn.remove();
    const headerContent = clonedHeader.querySelector('div');

    const contentToPrint = clonedArea.innerHTML;

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    /* Reset and base styles */
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 11pt;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        padding: 0;
                        margin: 0;
                    }

                    /* Print-specific styles */
                    @media print {
                        /* Page setup - A4 paper */
                        @page {
                            size: A4 portrait;
                            margin: 5mm;
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            width: 100%;
                            background: white;
                        }

                        /* Main container */
                        .print-container {
                            width: 100%;
                            max-width: 190mm;
                            margin: 0 auto;
                            padding: 10px;
                        }

                        /* Header styling */
                        .print-header {
                            background: #4e54c8 !important;
                            background: linear-gradient(135deg, #4e54c8, #8f94fb) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            color: white !important;
                            padding: 15px 20px;
                            margin-bottom: 20px;
                            border-radius: 4px;
                        }

                        .print-header h5 {
                            color: white !important;
                            font-weight: bold;
                            margin: 0;
                            font-size: 18pt;
                        }

                        .print-header small {
                            color: rgba(255, 255, 255, 0.95) !important;
                            font-size: 10pt;
                            opacity: 1 !important;
                        }

                        /* Hide elements that shouldn't print */
                        .modal-footer,
                        .btn,
                        button,
                        .btn-close,
                        .d-print-none {
                            display: none !important;
                        }

                        /* Show print-only elements */
                        .d-print-block {
                            display: block !important;
                        }

                        /* GENERAL CARD STYLING - Apply to all cards */
                        .card {
                            border: 1px solid #dee2e6 !important;
                            margin-bottom: 15px;
                            page-break-inside: avoid;
                            background: white !important;
                        }

                        .card-body {
                            padding: 15px !important;
                        }

                        /* FINANCIAL SUMMARY CARDS - Specific styling */
                        .row.text-center.mb-4 .card {
                            height: auto !important;
                            min-height: 120px;
                        }

                        .row.text-center.mb-4 .card-body {
                            padding: 10px !important;
                            text-align: center;
                        }

                        .row.text-center.mb-4 .fw-bold.h4 {
                            font-size: 14pt !important;
                            margin: 10px 0 !important;
                            display: block !important;
                        }

                        .row.text-center.mb-4 .text-muted.small {
                            font-size: 9pt !important;
                            color: #666 !important;
                            display: block !important;
                            margin-top: 5px;
                        }

                        /* Fix financial cards layout */
                        .row.text-center.mb-4 .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                        }

                        .row.text-center.mb-4 .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                            padding: 0 5px !important;
                        }

                        /* PAYMENT DETAILS CARDS */
                        .row.mb-4 .card {
                            height: auto !important;
                        }

                        .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .row.mb-4 .fw-bold.h3 {
                            font-size: 16pt !important;
                            margin: 10px 0 !important;
                        }

                        .row.mb-4 h6.fw-bold {
                            font-size: 11pt !important;
                            color: #2c3e50 !important;
                            margin-bottom: 15px !important;
                        }

                        /* Tables */
                        .table-responsive {
                            overflow: visible !important;
                        }

                        .table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            margin-bottom: 15px;
                            font-size: 9pt;
                            page-break-inside: avoid;
                        }

                        .table-bordered {
                            border: 1px solid #dee2e6 !important;
                        }

                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 6px 8px !important;
                            vertical-align: middle;
                        }

                        .table thead th {
                            background-color: #f5f5f5 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                            font-weight: bold;
                            text-align: center;
                        }

                        .table tfoot td {
                            font-weight: bold;
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Badges */
                        .badge {
                            border: 1px solid #ccc !important;
                            padding: 2px 6px !important;
                            font-size: 8pt !important;
                            border-radius: 3px;
                        }

                        /* Progress bar styling */
                        .progress {
                            height: 10px !important;
                            background-color: #e9ecef !important;
                            border-radius: 5px !important;
                            margin: 10px 0 0 0 !important;
                            border: 1px solid #dee2e6 !important;
                            overflow: hidden !important;
                        }

                        .progress-bar {
                            background-color: #28a745 !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            height: 100% !important;
                        }

                        /* Text alignment */
                        .text-end {
                            text-align: right !important;
                        }

                        .text-center {
                            text-align: center !important;
                        }

                        /* Colors for print */
                        .text-success { color: #198754 !important; }
                        .text-warning { color: #ffc107 !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-info { color: #17a2b8 !important; }
                        .text-primary { color: #007bff !important; }
                        .text-muted { color: #6c757d !important; }

                        .bg-success {
                            background-color: #d4edda !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-warning {
                            background-color: #fff3cd !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-light {
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-info {
                            background-color: #d1ecf1 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-danger {
                            background-color: #f8d7da !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Headings */
                        h6.fw-bold {
                            font-size: 11pt;
                            font-weight: bold;
                            margin-bottom: 10px;
                            color: #2c3e50;
                            page-break-after: avoid;
                        }

                        /* Row layouts for print */
                        .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                            margin: 0 -5px !important;
                        }

                        .col-md-3, .col-md-4, .col-md-6, .col-md-12 {
                            padding: 0 5px !important;
                            margin-bottom: 10px !important;
                        }

                        .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; }
                        .col-md-4 { flex: 0 0 33.333333% !important; max-width: 33.333333% !important; }
                        .col-md-6 { flex: 0 0 50% !important; max-width: 50% !important; }
                        .col-md-12 { flex: 0 0 100% !important; max-width: 100% !important; }

                        /* Border styling */
                        .border-top {
                            border-top: 1px solid #dee2e6 !important;
                            padding-top: 15px !important;
                        }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            background: #f5f5f5;
                            padding: 30px;
                            margin: 0;
                        }

                        .print-container {
                            max-width: 210mm;
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 8px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        }

                        .print-header {
                            background: linear-gradient(135deg, #4e54c8, #8f94fb);
                            color: white;
                            padding: 20px;
                            border-radius: 5px;
                            margin-bottom: 25px;
                        }
                    }

                    /* Utility classes */
                    .mb-4 { margin-bottom: 1.5rem !important; }
                    .mb-3 { margin-bottom: 1rem !important; }
                    .mb-2 { margin-bottom: 0.5rem !important; }
                    .mt-2 { margin-top: 0.5rem !important; }
                    .mt-3 { margin-top: 1rem !important; }
                    .mt-4 { margin-top: 1.5rem !important; }
                    .pt-3 { padding-top: 1rem !important; }
                    .pt-4 { padding-top: 1.5rem !important; }
                    .p-4 { padding: 1.5rem !important; }
                    .pb-2 { padding-bottom: 0.5rem !important; }
                    .fw-bold { font-weight: 700 !important; }
                    .fw-semibold { font-weight: 600 !important; }
                    .small { font-size: 0.875em !important; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- Print Header from modal -->
                    <div class="print-header">
                        ${headerContent.innerHTML}
                    </div>

                    <!-- Content -->
                    ${contentToPrint}
                </div>

                <script>
                    // Auto-print after content loads
                    window.onload = function() {
                        // Small delay to ensure all CSS and fonts are loaded
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (error) {
                                console.log('Print dialog opened');
                            }

                            // Try to close window after printing
                            window.addEventListener('afterprint', function() {
                                setTimeout(function() {
                                    try {
                                        window.close();
                                    } catch (e) {
                                        // Window might not be closable
                                    }
                                }, 500);
                            });
                        }, 800);
                    };

                    // Manual print trigger
                    document.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                            e.preventDefault();
                            window.print();
                        }
                    });
                <\/script>
            </body>
        </html>
    `;

    // Write content to iframe
    try {
        printFrame.contentWindow.document.open();
        printFrame.contentWindow.document.write(printContent);
        printFrame.contentWindow.document.close();
    } catch (error) {
        console.error('Error creating print document:', error);
        alert('Erreur lors de la préparation de l\'impression. Veuillez réessayer.');

        // Clean up iframe
        if (printFrame.parentNode) {
            document.body.removeChild(printFrame);
        }
        return;
    }

    // Wait for iframe to load, then print
    printFrame.onload = function() {
        setTimeout(function() {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch (error) {
                console.error('Print error:', error);
                alert('Pour imprimer, utilisez Ctrl+P dans la fenêtre d\'impression qui s\'est ouverte.');
            }

            // Clean up iframe
            setTimeout(() => {
                if (printFrame && printFrame.parentNode) {
                    document.body.removeChild(printFrame);
                }
            }, 3000);
        }, 1000);
    };
}

// PRINT MODE PAIEMENT - Fixed version without duplicate header
function printPaiement() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printFrame';
    printFrame.title = 'Print Document';

    document.body.appendChild(printFrame);

    // Get the content to print - clone it first to avoid modifying original
    const printableArea = document.getElementById('paiementDetailsContent');
    const clonedArea = printableArea.cloneNode(true);

    // REMOVE THE MODAL HEADER FROM THE CLONED CONTENT
    // This prevents the duplicate header issue
    const modalHeaderInCloned = clonedArea.querySelector('.modal-header');
    if (modalHeaderInCloned) {
        modalHeaderInCloned.remove();
    }

    // Remove elements that shouldn't be in the print version
    const elementsToRemove = clonedArea.querySelectorAll('.modal-footer, .btn, button, .btn-close, .alert.alert-info');
    elementsToRemove.forEach(el => el.remove());

    // Get modal header content from the ORIGINAL modal (not the cloned one)
    const modalHeader = document.querySelector('.modal-header');
    const clonedHeader = modalHeader.cloneNode(true);
    const closeBtn = clonedHeader.querySelector('.btn-close');
    if (closeBtn) closeBtn.remove();
    const headerContent = clonedHeader.querySelector('div');

    const contentToPrint = clonedArea.innerHTML;

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    /* Reset and base styles */
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 11pt;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        padding: 0;
                        margin: 0;
                    }

                    /* Print-specific styles */
                    @media print {
                        /* Page setup - A4 paper */
                        @page {
                            size: A4 portrait;
                            margin: 5mm;
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            width: 100%;
                            background: white;
                        }

                        /* Main container */
                        .print-container {
                            width: 100%;
                            max-width: 190mm;
                            margin: 0 auto;
                            padding: 10px;
                        }

                        /* Header styling - SINGLE HEADER */
                        .print-header {
                            background: #4e54c8 !important;
                            background: linear-gradient(135deg, #4e54c8, #8f94fb) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            color: white !important;
                            padding: 15px 20px;
                            margin-bottom: 20px;
                            border-radius: 4px;
                        }

                        .print-header h5 {
                            color: white !important;
                            font-weight: bold;
                            margin: 0;
                            font-size: 18pt;
                        }

                        .print-header small {
                            color: rgba(255, 255, 255, 0.95) !important;
                            font-size: 10pt;
                            opacity: 1 !important;
                        }

                        /* Hide any duplicate headers that might still exist */
                        .modal-header {
                            display: none !important;
                        }

                        /* Hide elements that shouldn't print */
                        .modal-footer,
                        .btn,
                        button,
                        .btn-close,
                        .d-print-none {
                            display: none !important;
                        }

                        /* Show print-only elements */
                        .d-print-block {
                            display: block !important;
                        }

                        /* GENERAL CARD STYLING - Apply to all cards */
                        .card {
                            border: 1px solid #dee2e6 !important;
                            margin-bottom: 15px;
                            page-break-inside: avoid;
                            background: white !important;
                        }

                        .card-body {
                            padding: 15px !important;
                        }

                        /* FINANCIAL SUMMARY CARDS - Specific styling */
                        .row.text-center.mb-4 .card {
                            height: auto !important;
                            min-height: 120px;
                        }

                        .row.text-center.mb-4 .card-body {
                            padding: 10px !important;
                            text-align: center;
                        }

                        .row.text-center.mb-4 .fw-bold.h4 {
                            font-size: 14pt !important;
                            margin: 10px 0 !important;
                            display: block !important;
                        }

                        .row.text-center.mb-4 .text-muted.small {
                            font-size: 9pt !important;
                            color: #666 !important;
                            display: block !important;
                            margin-top: 5px;
                        }

                        /* Fix financial cards layout */
                        .row.text-center.mb-4 .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                        }

                        .row.text-center.mb-4 .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                            padding: 0 5px !important;
                        }

                        /* PAYMENT PROGRESS CARDS */
                        .row.mb-4 .card {
                            height: auto !important;
                        }

                        .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .row.mb-4 .fw-bold.h3 {
                            font-size: 16pt !important;
                            margin: 10px 0 !important;
                        }

                        .row.mb-4 .fw-bold.h4 {
                            font-size: 14pt !important;
                            margin: 10px 0 !important;
                        }

                        .row.mb-4 h6.fw-bold {
                            font-size: 11pt !important;
                            color: #2c3e50 !important;
                            margin-bottom: 15px !important;
                        }

                        /* HEADER INFO CARDS */
                        .row.mb-4:first-of-type + .row.mb-4 .card {
                            height: auto !important;
                            min-height: 100px;
                        }

                        .row.mb-4:first-of-type + .row.mb-4 .card-body {
                            padding: 12px !important;
                        }

                        /* Tables */
                        .table-responsive {
                            overflow: visible !important;
                        }

                        .table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            margin-bottom: 15px;
                            font-size: 9pt;
                            page-break-inside: avoid;
                        }

                        .table-bordered {
                            border: 1px solid #dee2e6 !important;
                        }

                        .table-sm th,
                        .table-sm td {
                            padding: 4px 6px !important;
                        }

                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 6px 8px !important;
                            vertical-align: middle;
                        }

                        .table thead th {
                            background-color: #f5f5f5 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                            font-weight: bold;
                        }

                        .table tfoot td {
                            font-weight: bold;
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Highlight current payment row */
                        .table-primary {
                            background-color: #e7f1ff !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Badges */
                        .badge {
                            border: 1px solid #ccc !important;
                            padding: 2px 6px !important;
                            font-size: 8pt !important;
                            border-radius: 3px;
                        }

                        /* Progress bar styling */
                        .progress {
                            height: 10px !important;
                            background-color: #e9ecef !important;
                            border-radius: 5px !important;
                            margin: 10px 0 0 0 !important;
                            border: 1px solid #dee2e6 !important;
                            overflow: hidden !important;
                        }

                        .progress-bar {
                            background-color: #28a745 !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            height: 100% !important;
                        }

                        /* Text alignment */
                        .text-end {
                            text-align: right !important;
                        }

                        .text-center {
                            text-align: center !important;
                        }

                        /* Colors for print */
                        .text-success { color: #198754 !important; }
                        .text-warning { color: #ffc107 !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-info { color: #17a2b8 !important; }
                        .text-primary { color: #007bff !important; }
                        .text-muted { color: #6c757d !important; }

                        .bg-success {
                            background-color: #d4edda !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-warning {
                            background-color: #fff3cd !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-light {
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-info {
                            background-color: #d1ecf1 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-danger {
                            background-color: #f8d7da !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-secondary {
                            background-color: #e9ecef !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Headings */
                        h6.fw-bold {
                            font-size: 11pt;
                            font-weight: bold;
                            margin-bottom: 10px;
                            color: #2c3e50;
                            page-break-after: avoid;
                        }

                        /* Row layouts for print */
                        .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                            margin: 0 -5px !important;
                        }

                        .col-md-3, .col-md-4, .col-md-6, .col-md-12 {
                            padding: 0 5px !important;
                            margin-bottom: 10px !important;
                        }

                        .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; }
                        .col-md-4 { flex: 0 0 33.333333% !important; max-width: 33.333333% !important; }
                        .col-md-6 { flex: 0 0 50% !important; max-width: 50% !important; }
                        .col-md-12 { flex: 0 0 100% !important; max-width: 100% !important; }

                        /* Border styling */
                        .border-top {
                            border-top: 1px solid #dee2e6 !important;
                            padding-top: 15px !important;
                        }

                        .border-bottom {
                            border-bottom: 1px solid #dee2e6 !important;
                            padding-bottom: 8px !important;
                        }

                        /* Notes section */
                        .card .card-body:only-child {
                            padding: 10px !important;
                            font-style: italic;
                        }

                        /* Small text */
                        .small {
                            font-size: 0.8em !important;
                        }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            background: #f5f5f5;
                            padding: 30px;
                            margin: 0;
                        }

                        .print-container {
                            max-width: 210mm;
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 8px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        }

                        .print-header {
                            background: linear-gradient(135deg, #4e54c8, #8f94fb);
                            color: white;
                            padding: 20px;
                            border-radius: 5px;
                            margin-bottom: 25px;
                        }
                    }

                    /* Utility classes */
                    .mb-4 { margin-bottom: 1.5rem !important; }
                    .mb-3 { margin-bottom: 1rem !important; }
                    .mb-2 { margin-bottom: 0.5rem !important; }
                    .mt-1 { margin-top: 0.25rem !important; }
                    .mt-2 { margin-top: 0.5rem !important; }
                    .mt-3 { margin-top: 1rem !important; }
                    .mt-4 { margin-top: 1.5rem !important; }
                    .pt-3 { padding-top: 1rem !important; }
                    .pt-4 { padding-top: 1.5rem !important; }
                    .p-4 { padding: 1.5rem !important; }
                    .pb-2 { padding-bottom: 0.5rem !important; }
                    .fw-bold { font-weight: 700 !important; }
                    .fw-semibold { font-weight: 600 !important; }
                    .fw-medium { font-weight: 500 !important; }
                    .small { font-size: 0.875em !important; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- SINGLE Print Header from modal -->
                    <div class="print-header">
                        ${headerContent.innerHTML}
                    </div>

                    <!-- Content without duplicate header -->
                    ${contentToPrint}
                </div>

                <script>
                    // Auto-print after content loads
                    window.onload = function() {
                        // Small delay to ensure all CSS and fonts are loaded
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (error) {
                                console.log('Print dialog opened');
                            }

                            // Try to close window after printing
                            window.addEventListener('afterprint', function() {
                                setTimeout(function() {
                                    try {
                                        window.close();
                                    } catch (e) {
                                        // Window might not be closable
                                    }
                                }, 500);
                            });
                        }, 800);
                    };

                    // Manual print trigger
                    document.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                            e.preventDefault();
                            window.print();
                        }
                    });
                <\/script>
            </body>
        </html>
    `;

    // Write content to iframe
    try {
        printFrame.contentWindow.document.open();
        printFrame.contentWindow.document.write(printContent);
        printFrame.contentWindow.document.close();
    } catch (error) {
        console.error('Error creating print document:', error);
        alert('Erreur lors de la préparation de l\'impression. Veuillez réessayer.');

        // Clean up iframe
        if (printFrame.parentNode) {
            document.body.removeChild(printFrame);
        }
        return;
    }

    // Wait for iframe to load, then print
    printFrame.onload = function() {
        setTimeout(function() {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch (error) {
                console.error('Print error:', error);
                alert('Pour imprimer, utilisez Ctrl+P dans la fenêtre d\'impression qui s\'est ouverte.');
            }

            // Clean up iframe
            setTimeout(() => {
                if (printFrame && printFrame.parentNode) {
                    document.body.removeChild(printFrame);
                }
            }, 3000);
        }, 1000);
    };
}

// PRINT MODE CLIENT - Fixed version without duplicate header
function printClientDetails() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printFrame';
    printFrame.title = 'Print Document';

    document.body.appendChild(printFrame);

    // Get the content to print - clone it first to avoid modifying original
    const printableArea = document.getElementById('clientDetails');
    const clonedArea = printableArea.cloneNode(true);

    // REMOVE THE MODAL HEADER FROM THE CLONED CONTENT
    // This prevents the duplicate header issue
    const modalHeaderInCloned = clonedArea.querySelector('.modal-header');
    if (modalHeaderInCloned) {
        modalHeaderInCloned.remove();
    }

    // Remove elements that shouldn't be in the print version
    const elementsToRemove = clonedArea.querySelectorAll('.modal-footer, .btn, button, .btn-close, .alert.alert-info');
    elementsToRemove.forEach(el => el.remove());

    // Get modal header content from the ORIGINAL modal (not the cloned one)
    const modalHeader = document.querySelector('.modal-header');
    const clonedHeader = modalHeader.cloneNode(true);
    const closeBtn = clonedHeader.querySelector('.btn-close');
    if (closeBtn) closeBtn.remove();
    const headerContent = clonedHeader.querySelector('div');

    const contentToPrint = clonedArea.innerHTML;

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    /* Reset and base styles */
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 11pt;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        padding: 0;
                        margin: 0;
                    }

                    /* Print-specific styles */
                    @media print {
                        /* Page setup - A4 paper */
                        @page {
                            size: A4 portrait;
                            margin: 5mm;
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            width: 100%;
                            background: white;
                        }

                        /* Main container */
                        .print-container {
                            width: 100%;
                            max-width: 190mm;
                            margin: 0 auto;
                            padding: 10px;
                        }

                        /* Header styling - SINGLE HEADER */
                        .print-header {
                            background: #11998e !important;
                            background: linear-gradient(135deg, #11998e, #38ef7d) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            color: white !important;
                            padding: 15px 20px;
                            margin-bottom: 20px;
                            border-radius: 4px;
                        }

                        .print-header h5 {
                            color: white !important;
                            font-weight: bold;
                            margin: 0;
                            font-size: 18pt;
                        }

                        .print-header small {
                            color: rgba(255, 255, 255, 0.95) !important;
                            font-size: 10pt;
                            opacity: 1 !important;
                        }

                        /* Hide any duplicate headers that might still exist */
                        .modal-header {
                            display: none !important;
                        }

                        /* Hide elements that shouldn't print */
                        .modal-footer,
                        .btn,
                        button,
                        .btn-close,
                        .d-print-none {
                            display: none !important;
                        }

                        /* Show print-only elements */
                        .d-print-block {
                            display: block !important;
                        }

                        /* Avatar styling */
                        .avatar-circle {
                            width: 80px !important;
                            height: 80px !important;
                            border-radius: 50% !important;
                            background: linear-gradient(135deg, #11998e, #38ef7d) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            margin: 0 auto 10px auto !important;
                            color: white !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                        }

                        .avatar-circle i {
                            font-size: 2.5rem !important;
                            color: white !important;
                        }

                        /* GENERAL CARD STYLING - Apply to all cards */
                        .card {
                            border: 1px solid #dee2e6 !important;
                            margin-bottom: 15px;
                            page-break-inside: avoid;
                            background: white !important;
                        }

                        .card-body {
                            padding: 15px !important;
                        }

                        /* PROFILE INFO CARDS - Specific styling */
                        .row.mb-4 .card {
                            height: auto !important;
                            min-height: 150px;
                        }

                        .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        /* STATISTICS CARDS - Specific styling */
                        .row.mb-4 + .row.mb-4 .card {
                            height: auto !important;
                            min-height: 140px;
                        }

                        .row.mb-4 + .row.mb-4 .card-body {
                            padding: 10px !important;
                            text-align: center;
                        }

                        .row.mb-4 + .row.mb-4 .fw-bold.h3 {
                            font-size: 16pt !important;
                            margin: 10px 0 !important;
                            display: block !important;
                        }

                        .row.mb-4 + .row.mb-4 .text-muted.small {
                            font-size: 9pt !important;
                            color: #666 !important;
                            display: block !important;
                            margin-top: 5px;
                        }

                        /* Fix statistics cards layout */
                        .row.mb-4 + .row.mb-4 .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                        }

                        .row.mb-4 + .row.mb-4 .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                            padding: 0 5px !important;
                        }

                        /* PAYMENT STATUS DISTRIBUTION */
                        .row.mb-4 + .row.mb-4 + .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 .col-md-4 {
                            flex: 0 0 33.333333% !important;
                            max-width: 33.333333% !important;
                            padding: 0 10px !important;
                            text-align: center !important;
                        }

                        /* FINANCIAL SUMMARY CARDS */
                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .card {
                            height: auto !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .fw-bold.h4 {
                            font-size: 14pt !important;
                            margin: 5px 0 !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .fw-bold.h5 {
                            font-size: 12pt !important;
                            margin: 5px 0 !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 h6.fw-bold {
                            font-size: 11pt !important;
                            color: #2c3e50 !important;
                            margin-bottom: 15px !important;
                        }

                        /* Tables */
                        .table-responsive {
                            overflow: visible !important;
                        }

                        .table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            margin-bottom: 15px;
                            font-size: 9pt;
                            page-break-inside: avoid;
                        }

                        .table-sm th,
                        .table-sm td {
                            padding: 4px 6px !important;
                        }

                        .table-bordered {
                            border: 1px solid #dee2e6 !important;
                        }

                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 6px 8px !important;
                            vertical-align: middle;
                        }

                        .table thead th {
                            background-color: #f5f5f5 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                            font-weight: bold;
                        }

                        .table tfoot td {
                            font-weight: bold;
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Badges */
                        .badge {
                            border: 1px solid #ccc !important;
                            padding: 2px 6px !important;
                            font-size: 8pt !important;
                            border-radius: 3px;
                        }

                        /* Progress bar styling */
                        .progress {
                            height: 10px !important;
                            background-color: #e9ecef !important;
                            border-radius: 5px !important;
                            margin: 10px 0 0 0 !important;
                            border: 1px solid #dee2e6 !important;
                            overflow: hidden !important;
                        }

                        .progress-bar {
                            background-color: #28a745 !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            height: 100% !important;
                        }

                        /* Text alignment */
                        .text-end {
                            text-align: right !important;
                        }

                        .text-center {
                            text-align: center !important;
                        }

                        /* Colors for print */
                        .text-success { color: #198754 !important; }
                        .text-warning { color: #ffc107 !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-info { color: #17a2b8 !important; }
                        .text-primary { color: #007bff !important; }
                        .text-muted { color: #6c757d !important; }

                        .bg-success {
                            background-color: #d4edda !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-warning {
                            background-color: #fff3cd !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-light {
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-info {
                            background-color: #d1ecf1 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-danger {
                            background-color: #f8d7da !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-secondary {
                            background-color: #e9ecef !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Headings */
                        h6.fw-bold {
                            font-size: 11pt;
                            font-weight: bold;
                            margin-bottom: 10px;
                            color: #2c3e50;
                            page-break-after: avoid;
                        }

                        h5.fw-bold {
                            font-size: 14pt;
                            font-weight: bold;
                            margin-bottom: 15px;
                            color: #2c3e50;
                        }

                        /* Row layouts for print */
                        .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                            margin: 0 -5px !important;
                        }

                        .col-md-3, .col-md-4, .col-md-6, .col-md-9, .col-md-12 {
                            padding: 0 5px !important;
                            margin-bottom: 10px !important;
                        }

                        .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; }
                        .col-md-4 { flex: 0 0 33.333333% !important; max-width: 33.333333% !important; }
                        .col-md-6 { flex: 0 0 50% !important; max-width: 50% !important; }
                        .col-md-9 { flex: 0 0 75% !important; max-width: 75% !important; }
                        .col-md-12 { flex: 0 0 100% !important; max-width: 100% !important; }

                        /* Border styling */
                        .border-top {
                            border-top: 1px solid #dee2e6 !important;
                            padding-top: 15px !important;
                        }

                        .border-bottom {
                            border-bottom: 1px solid #dee2e6 !important;
                            padding-bottom: 8px !important;
                        }

                        /* Small text */
                        .small {
                            font-size: 0.8em !important;
                        }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            background: #f5f5f5;
                            padding: 30px;
                            margin: 0;
                        }

                        .print-container {
                            max-width: 210mm;
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 8px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        }

                        .print-header {
                            background: linear-gradient(135deg, #11998e, #38ef7d);
                            color: white;
                            padding: 20px;
                            border-radius: 5px;
                            margin-bottom: 25px;
                        }

                        .avatar-circle {
                            width: 100px;
                            height: 100px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, #11998e, #38ef7d);
                            margin: 0 auto 15px auto;
                            color: white;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                    }

                    /* Utility classes */
                    .mb-4 { margin-bottom: 1.5rem !important; }
                    .mb-3 { margin-bottom: 1rem !important; }
                    .mb-2 { margin-bottom: 0.5rem !important; }
                    .mt-1 { margin-top: 0.25rem !important; }
                    .mt-2 { margin-top: 0.5rem !important; }
                    .mt-3 { margin-top: 1rem !important; }
                    .mt-4 { margin-top: 1.5rem !important; }
                    .pt-3 { padding-top: 1rem !important; }
                    .pt-4 { padding-top: 1.5rem !important; }
                    .p-4 { padding: 1.5rem !important; }
                    .pb-2 { padding-bottom: 0.5rem !important; }
                    .fw-bold { font-weight: 700 !important; }
                    .fw-semibold { font-weight: 600 !important; }
                    .fw-medium { font-weight: 500 !important; }
                    .small { font-size: 0.875em !important; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- SINGLE Print Header from modal -->
                    <div class="print-header">
                        ${headerContent.innerHTML}
                    </div>

                    <!-- Content without duplicate header -->
                    ${contentToPrint}
                </div>

                <script>
                    // Auto-print after content loads
                    window.onload = function() {
                        // Small delay to ensure all CSS and fonts are loaded
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (error) {
                                console.log('Print dialog opened');
                            }

                            // Try to close window after printing
                            window.addEventListener('afterprint', function() {
                                setTimeout(function() {
                                    try {
                                        window.close();
                                    } catch (e) {
                                        // Window might not be closable
                                    }
                                }, 500);
                            });
                        }, 800);
                    };

                    // Manual print trigger
                    document.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                            e.preventDefault();
                            window.print();
                        }
                    });
                <\/script>
            </body>
        </html>
    `;

    // Write content to iframe
    try {
        printFrame.contentWindow.document.open();
        printFrame.contentWindow.document.write(printContent);
        printFrame.contentWindow.document.close();
    } catch (error) {
        console.error('Error creating print document:', error);
        alert('Erreur lors de la préparation de l\'impression. Veuillez réessayer.');

        // Clean up iframe
        if (printFrame.parentNode) {
            document.body.removeChild(printFrame);
        }
        return;
    }

    // Wait for iframe to load, then print
    printFrame.onload = function() {
        setTimeout(function() {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch (error) {
                console.error('Print error:', error);
                alert('Pour imprimer, utilisez Ctrl+P dans la fenêtre d\'impression qui s\'est ouverte.');
            }

            // Clean up iframe
            setTimeout(() => {
                if (printFrame && printFrame.parentNode) {
                    document.body.removeChild(printFrame);
                }
            }, 3000);
        }, 1000);
    };
}

// PRINT MODE FOURNISSEUR - Fixed version without duplicate header
function printFournisseurDetails() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printFrame';
    printFrame.title = 'Print Document';

    document.body.appendChild(printFrame);

    // Get the content to print - clone it first to avoid modifying original
    const printableArea = document.getElementById('fournisseurDetails');
    const clonedArea = printableArea.cloneNode(true);

    // REMOVE THE MODAL HEADER FROM THE CLONED CONTENT
    // This prevents the duplicate header issue
    const modalHeaderInCloned = clonedArea.querySelector('.modal-header');
    if (modalHeaderInCloned) {
        modalHeaderInCloned.remove();
    }

    // Remove elements that shouldn't be in the print version
    const elementsToRemove = clonedArea.querySelectorAll('.modal-footer, .btn, button, .btn-close, .alert.alert-info');
    elementsToRemove.forEach(el => el.remove());

    // Get modal header content from the ORIGINAL modal (not the cloned one)
    const modalHeader = document.querySelector('.modal-header');
    const clonedHeader = modalHeader.cloneNode(true);
    const closeBtn = clonedHeader.querySelector('.btn-close');
    if (closeBtn) closeBtn.remove();
    const headerContent = clonedHeader.querySelector('div');

    const contentToPrint = clonedArea.innerHTML;

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html>
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
                <style>
                    /* Reset and base styles */
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 11pt;
                        line-height: 1.4;
                        color: #000;
                        background: #fff;
                        padding: 0;
                        margin: 0;
                    }

                    /* Print-specific styles */
                    @media print {
                        /* Page setup - A4 paper */
                        @page {
                            size: A4 portrait;
                            margin: 5mm;
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            width: 100%;
                            background: white;
                        }

                        /* Main container */
                        .print-container {
                            width: 100%;
                            max-width: 190mm;
                            margin: 0 auto;
                            padding: 10px;
                        }

                        /* Header styling - SINGLE HEADER */
                        .print-header {
                            background: #6a11cb !important;
                            background: linear-gradient(135deg, #6a11cb, #2575fc) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            color: white !important;
                            padding: 15px 20px;
                            margin-bottom: 20px;
                            border-radius: 4px;
                        }

                        .print-header h5 {
                            color: white !important;
                            font-weight: bold;
                            margin: 0;
                            font-size: 18pt;
                        }

                        .print-header small {
                            color: rgba(255, 255, 255, 0.95) !important;
                            font-size: 10pt;
                            opacity: 1 !important;
                        }

                        /* Hide any duplicate headers that might still exist */
                        .modal-header {
                            display: none !important;
                        }

                        /* Hide elements that shouldn't print */
                        .modal-footer,
                        .btn,
                        button,
                        .btn-close,
                        .d-print-none {
                            display: none !important;
                        }

                        /* Show print-only elements */
                        .d-print-block {
                            display: block !important;
                        }

                        /* Avatar styling */
                        .avatar-circle {
                            width: 80px !important;
                            height: 80px !important;
                            border-radius: 50% !important;
                            background: linear-gradient(135deg, #6a11cb, #2575fc) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            margin: 0 auto 10px auto !important;
                            color: white !important;
                            display: flex !important;
                            align-items: center !important;
                            justify-content: center !important;
                        }

                        .avatar-circle i {
                            font-size: 2.5rem !important;
                            color: white !important;
                        }

                        /* GENERAL CARD STYLING - Apply to all cards */
                        .card {
                            border: 1px solid #dee2e6 !important;
                            margin-bottom: 15px;
                            page-break-inside: avoid;
                            background: white !important;
                        }

                        .card-body {
                            padding: 15px !important;
                        }

                        /* PROFILE INFO CARDS - Specific styling */
                        .row.mb-4 .card {
                            height: auto !important;
                            min-height: 150px;
                        }

                        .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        /* STATISTICS CARDS - Specific styling */
                        .row.mb-4 + .row.mb-4 .card {
                            height: auto !important;
                            min-height: 140px;
                        }

                        .row.mb-4 + .row.mb-4 .card-body {
                            padding: 10px !important;
                            text-align: center;
                        }

                        .row.mb-4 + .row.mb-4 .fw-bold.h3 {
                            font-size: 16pt !important;
                            margin: 10px 0 !important;
                            display: block !important;
                        }

                        .row.mb-4 + .row.mb-4 .text-muted.small {
                            font-size: 9pt !important;
                            color: #666 !important;
                            display: block !important;
                            margin-top: 5px;
                        }

                        /* Fix statistics cards layout */
                        .row.mb-4 + .row.mb-4 .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                        }

                        .row.mb-4 + .row.mb-4 .col-md-3 {
                            flex: 0 0 25% !important;
                            max-width: 25% !important;
                            padding: 0 5px !important;
                        }

                        /* PAYMENT STATUS DISTRIBUTION */
                        .row.mb-4 + .row.mb-4 + .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 .col-md-4 {
                            flex: 0 0 33.333333% !important;
                            max-width: 33.333333% !important;
                            padding: 0 10px !important;
                            text-align: center !important;
                        }

                        /* FINANCIAL SUMMARY CARDS */
                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .card {
                            height: auto !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .card-body {
                            padding: 15px !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .fw-bold.h4 {
                            font-size: 14pt !important;
                            margin: 5px 0 !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 .fw-bold.h5 {
                            font-size: 12pt !important;
                            margin: 5px 0 !important;
                        }

                        .row.mb-4 + .row.mb-4 + .row.mb-4 + .row.mb-4 h6.fw-bold {
                            font-size: 11pt !important;
                            color: #2c3e50 !important;
                            margin-bottom: 15px !important;
                        }

                        /* Tables */
                        .table-responsive {
                            overflow: visible !important;
                        }

                        .table {
                            width: 100% !important;
                            border-collapse: collapse !important;
                            margin-bottom: 15px;
                            font-size: 9pt;
                            page-break-inside: avoid;
                        }

                        .table-sm th,
                        .table-sm td {
                            padding: 4px 6px !important;
                        }

                        .table-bordered {
                            border: 1px solid #dee2e6 !important;
                        }

                        .table-bordered th,
                        .table-bordered td {
                            border: 1px solid #dee2e6 !important;
                            padding: 6px 8px !important;
                            vertical-align: middle;
                        }

                        .table thead th {
                            background-color: #f5f5f5 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                            font-weight: bold;
                        }

                        .table tfoot td {
                            font-weight: bold;
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Badges */
                        .badge {
                            border: 1px solid #ccc !important;
                            padding: 2px 6px !important;
                            font-size: 8pt !important;
                            border-radius: 3px;
                        }

                        /* Progress bar styling */
                        .progress {
                            height: 10px !important;
                            background-color: #e9ecef !important;
                            border-radius: 5px !important;
                            margin: 10px 0 0 0 !important;
                            border: 1px solid #dee2e6 !important;
                            overflow: hidden !important;
                        }

                        .progress-bar {
                            background-color: #28a745 !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            height: 100% !important;
                        }

                        /* Text alignment */
                        .text-end {
                            text-align: right !important;
                        }

                        .text-center {
                            text-align: center !important;
                        }

                        /* Colors for print */
                        .text-success { color: #198754 !important; }
                        .text-warning { color: #ffc107 !important; }
                        .text-danger { color: #dc3545 !important; }
                        .text-info { color: #17a2b8 !important; }
                        .text-primary { color: #007bff !important; }
                        .text-muted { color: #6c757d !important; }

                        .bg-success {
                            background-color: #d4edda !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-warning {
                            background-color: #fff3cd !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-light {
                            background-color: #f8f9fa !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-info {
                            background-color: #d1ecf1 !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-danger {
                            background-color: #f8d7da !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }
                        .bg-secondary {
                            background-color: #e9ecef !important;
                            -webkit-print-color-adjust: exact;
                            color-adjust: exact;
                        }

                        /* Headings */
                        h6.fw-bold {
                            font-size: 11pt;
                            font-weight: bold;
                            margin-bottom: 10px;
                            color: #2c3e50;
                            page-break-after: avoid;
                        }

                        h5.fw-bold {
                            font-size: 14pt;
                            font-weight: bold;
                            margin-bottom: 15px;
                            color: #2c3e50;
                        }

                        /* Row layouts for print */
                        .row {
                            display: flex !important;
                            flex-wrap: wrap !important;
                            margin: 0 -5px !important;
                        }

                        .col-md-3, .col-md-4, .col-md-6, .col-md-9, .col-md-12 {
                            padding: 0 5px !important;
                            margin-bottom: 10px !important;
                        }

                        .col-md-3 { flex: 0 0 25% !important; max-width: 25% !important; }
                        .col-md-4 { flex: 0 0 33.333333% !important; max-width: 33.333333% !important; }
                        .col-md-6 { flex: 0 0 50% !important; max-width: 50% !important; }
                        .col-md-9 { flex: 0 0 75% !important; max-width: 75% !important; }
                        .col-md-12 { flex: 0 0 100% !important; max-width: 100% !important; }

                        /* Border styling */
                        .border-top {
                            border-top: 1px solid #dee2e6 !important;
                            padding-top: 15px !important;
                        }

                        .border-bottom {
                            border-bottom: 1px solid #dee2e6 !important;
                            padding-bottom: 8px !important;
                        }

                        /* Small text */
                        .small {
                            font-size: 0.8em !important;
                        }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            background: #f5f5f5;
                            padding: 30px;
                            margin: 0;
                        }

                        .print-container {
                            max-width: 210mm;
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 8px;
                            box-shadow: 0 0 20px rgba(0,0,0,0.1);
                        }

                        .print-header {
                            background: linear-gradient(135deg, #6a11cb, #2575fc);
                            color: white;
                            padding: 20px;
                            border-radius: 5px;
                            margin-bottom: 25px;
                        }

                        .avatar-circle {
                            width: 100px;
                            height: 100px;
                            border-radius: 50%;
                            background: linear-gradient(135deg, #6a11cb, #2575fc);
                            margin: 0 auto 15px auto;
                            color: white;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                        }
                    }

                    /* Utility classes */
                    .mb-4 { margin-bottom: 1.5rem !important; }
                    .mb-3 { margin-bottom: 1rem !important; }
                    .mb-2 { margin-bottom: 0.5rem !important; }
                    .mt-1 { margin-top: 0.25rem !important; }
                    .mt-2 { margin-top: 0.5rem !important; }
                    .mt-3 { margin-top: 1rem !important; }
                    .mt-4 { margin-top: 1.5rem !important; }
                    .pt-3 { padding-top: 1rem !important; }
                    .pt-4 { padding-top: 1.5rem !important; }
                    .p-4 { padding: 1.5rem !important; }
                    .pb-2 { padding-bottom: 0.5rem !important; }
                    .fw-bold { font-weight: 700 !important; }
                    .fw-semibold { font-weight: 600 !important; }
                    .fw-medium { font-weight: 500 !important; }
                    .small { font-size: 0.875em !important; }
                </style>
            </head>
            <body>
                <div class="print-container">
                    <!-- SINGLE Print Header from modal -->
                    <div class="print-header">
                        ${headerContent.innerHTML}
                    </div>

                    <!-- Content without duplicate header -->
                    ${contentToPrint}
                </div>

                <script>
                    // Auto-print after content loads
                    window.onload = function() {
                        // Small delay to ensure all CSS and fonts are loaded
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (error) {
                                console.log('Print dialog opened');
                            }

                            // Try to close window after printing
                            window.addEventListener('afterprint', function() {
                                setTimeout(function() {
                                    try {
                                        window.close();
                                    } catch (e) {
                                        // Window might not be closable
                                    }
                                }, 500);
                            });
                        }, 800);
                    };

                    // Manual print trigger
                    document.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                            e.preventDefault();
                            window.print();
                        }
                    });
                <\/script>
            </body>
        </html>
    `;

    // Write content to iframe
    try {
        printFrame.contentWindow.document.open();
        printFrame.contentWindow.document.write(printContent);
        printFrame.contentWindow.document.close();
    } catch (error) {
        console.error('Error creating print document:', error);
        alert('Erreur lors de la préparation de l\'impression. Veuillez réessayer.');

        // Clean up iframe
        if (printFrame.parentNode) {
            document.body.removeChild(printFrame);
        }
        return;
    }

    // Wait for iframe to load, then print
    printFrame.onload = function() {
        setTimeout(function() {
            try {
                printFrame.contentWindow.focus();
                printFrame.contentWindow.print();
            } catch (error) {
                console.error('Print error:', error);
                alert('Pour imprimer, utilisez Ctrl+P dans la fenêtre d\'impression qui s\'est ouverte.');
            }

            // Clean up iframe
            setTimeout(() => {
                if (printFrame && printFrame.parentNode) {
                    document.body.removeChild(printFrame);
                }
            }, 3000);
        }, 1000);
    };
}

// Helper function to transform content for print
// function transformForPrint(element) {
//     // Add no-print class to specific elements
//     const noPrintElements = element.querySelectorAll('.alert');
//     noPrintElements.forEach(el => el.classList.add('no-print'));

//     // Fix row and column display for print
//     const rows = element.querySelectorAll('.row');
//     rows.forEach(row => {
//         row.style.display = 'flex';
//         row.style.flexWrap = 'wrap';
//         row.style.margin = '0 -8px';
//     });

//     // Fix column widths for print
//     const cols = element.querySelectorAll('[class*="col-"]');
//     cols.forEach(col => {
//         // Check if it's a financial or payment card (should be 2 per row in print)
//         const isFinancialCard = col.classList.contains('col-md-3') &&
//                                col.closest('.row.text-center.mb-4');
//         const isPaymentCard = col.classList.contains('col-md-4') &&
//                             col.closest('.row.mb-4') &&
//                             !col.closest('.row.mb-4 .col-md-12');

//         if (isFinancialCard || isPaymentCard) {
//             col.style.flex = '0 0 50%';
//             col.style.maxWidth = '50%';
//         } else {
//             col.style.flex = '0 0 100%';
//             col.style.maxWidth = '100%';
//         }
//         col.style.padding = '0 8px';
//         col.style.marginBottom = '12px';
//     });

//     // Ensure cards are visible
//     const cards = element.querySelectorAll('.card');
//     cards.forEach(card => {
//         card.style.display = 'block';
//         card.style.breakInside = 'avoid';
//     });

//     // Fix table responsiveness
//     const tables = element.querySelectorAll('.table-responsive');
//     tables.forEach(table => {
//         table.style.overflow = 'visible';
//     });
// }

// Get period labels and date ranges
function getPeriodInfo(period) {
    const now = new Date();

    switch(period) {
        case 'aujourdhui': // Today
            const today = now.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            return {
                label: "Aujourd'hui",
                dateRange: `(${today})`
            };

        case 'hier': // Yesterday
            const yesterday = new Date(now);
            yesterday.setDate(now.getDate() - 1);
            const yesterdayStr = yesterday.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            return {
                label: 'Hier',
                dateRange: `(${yesterdayStr})`
            };

        case 'semaine': // This week
            const startOfWeek = new Date(now);
            startOfWeek.setDate(now.getDate() - now.getDay() + (now.getDay() === 0 ? -6 : 1)); // Monday
            const endOfWeek = new Date(startOfWeek);
            endOfWeek.setDate(startOfWeek.getDate() + 6); // Sunday

            const startWeekStr = startOfWeek.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const endWeekStr = endOfWeek.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            return {
                label: 'Cette semaine',
                dateRange: `(${startWeekStr} au ${endWeekStr})`
            };

        case 'mois': // This month
            const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);
            const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0);

            const startMonthStr = startOfMonth.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const endMonthStr = endOfMonth.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            return {
                label: 'Ce mois',
                dateRange: `(${startMonthStr} au ${endMonthStr})`
            };

        case 'annee': // This year (if you need it)
            const startOfYear = new Date(now.getFullYear(), 0, 1);
            const endOfYear = new Date(now.getFullYear(), 11, 31);

            const startYearStr = startOfYear.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            const endYearStr = endOfYear.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            return {
                label: 'Cette année',
                dateRange: `(${startYearStr} au ${endYearStr})`
            };

        default:
            const defaultDate = now.toLocaleDateString('fr-FR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
            return {
                label: period,
                dateRange: `(${defaultDate})`
            };
    }
}

/**
 * Print Sales Report Function
 * Call this function when clicking the print button in VentesJour component
 */

function printSalesReport() {
    // Create a hidden iframe for printing
    const printFrame = document.createElement('iframe');
    printFrame.style.position = 'fixed';
    printFrame.style.right = '0';
    printFrame.style.bottom = '0';
    printFrame.style.width = '0';
    printFrame.style.height = '0';
    printFrame.style.border = '0';
    printFrame.style.opacity = '0';
    printFrame.name = 'printSalesFrame';

    document.body.appendChild(printFrame);

    // Get the entire report section including stats cards
    const reportSection = document.getElementById('ventes-jour-report');
    if (!reportSection) {
        console.error('Element #ventes-jour-report not found');
        alert('Erreur: Section rapport non trouvée');
        return;
    }

    // Clone the content
    const contentToPrint = reportSection.cloneNode(true);

    // Show print-only elements
    const printHeaders = contentToPrint.querySelectorAll('.print-header, .print-footer');
    printHeaders.forEach(el => {
        el.classList.remove('d-none');
        el.style.display = 'block';
    });

    // Remove interactive elements but keep styling
    const noPrintElements = contentToPrint.querySelectorAll(
        '.btn, .dropdown, [wire\\:click], .no-print, .dropdown-toggle'
    );
    noPrintElements.forEach(el => {
        if (!el.classList.contains('card') && !el.classList.contains('table')) {
            el.remove();
        }
    });

    // Get the current period from Livewire component
    const currentPeriod = document.querySelector('#periodLabels')?.getAttribute('data-selectedPeriode') || 'aujourdhui';

    // Get period info
    const periodInfo = getPeriodInfo(currentPeriod);

    // Format current date
    const now = new Date();
    const formattedDate = now.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Create HTML structure for printing
    const printContent = `
        <!DOCTYPE html>
        <html lang="fr">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">

                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

                <!-- Font Awesome -->
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

                <style>
                    /* Print-specific styles */
                    @media print {
                        * {
                            margin: 0;
                            padding: 0;
                            box-sizing: border-box;
                        }

                        /* Minimal page margins */
                        @page {
                            margin: 5mm;
                            size: A4 landscape;
                        }

                        body {
                            padding: 0 !important;
                            margin: 0 !important;
                            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                            font-size: 11pt;
                            line-height: 1.4;
                            background: white !important;
                        }

                        /* Container styling */
                        .container-print {
                            width: 100%;
                            padding: 15px;
                            background: white;
                        }

                        /* Header styling */
                        .print-header h2 {
                            color: #0d6efd !important;
                            font-size: 22pt !important;
                            margin-bottom: 10px !important;
                            font-weight: bold !important;
                        }

                        .print-header h4 {
                            color: #6c757d !important;
                            font-size: 14pt !important;
                            margin-bottom: 20px !important;
                        }

                        .print-header p {
                            color: #6c757d !important;
                            font-size: 10pt !important;
                        }

                        /* Statistics cards styling */
                        .card {
                            border: 1px solid #dee2e6 !important;
                            border-radius: 8px !important;
                            margin-bottom: 15px !important;
                            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                        }

                        .bg-primary.bg-opacity-10 {
                            background-color: rgba(13, 110, 253, 0.1) !important;
                            border-left: 4px solid #0d6efd !important;
                        }

                        .bg-success.bg-opacity-10 {
                            background-color: rgba(25, 135, 84, 0.1) !important;
                            border-left: 4px solid #198754 !important;
                        }

                        .bg-danger.bg-opacity-10 {
                            background-color: rgba(220, 53, 69, 0.1) !important;
                            border-left: 4px solid #dc3545 !important;
                        }

                        .bg-info.bg-opacity-10 {
                            background-color: rgba(13, 202, 240, 0.1) !important;
                            border-left: 4px solid #0dcaf0 !important;
                        }

                        .bg-warning.bg-opacity-10 {
                            background-color: rgba(255, 193, 7, 0.1) !important;
                            border-left: 4px solid #ffc107 !important;
                        }

                        .card .rounded-circle {
                            background-color: inherit !important;
                            color: white !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                        }

                        .bg-primary {
                            background-color: #0d6efd !important;
                        }

                        .bg-success {
                            background-color: #198754 !important;
                        }

                        .bg-danger {
                            background-color: #dc3545 !important;
                        }

                        .bg-info {
                            background-color: #0dcaf0 !important;
                        }

                        .bg-warning {
                            background-color: #ffc107 !important;
                        }

                        /* Table styling */
                        .table {
                            border-collapse: collapse !important;
                            width: 100% !important;
                            font-size: 10pt;
                            margin-top: 20px;
                            margin-bottom: 25px;
                            border: 1px solid #dee2e6 !important;
                        }

                        .table th,
                        .table td {
                            border: 1px solid #dee2e6 !important;
                            padding: 8px 6px !important;
                            vertical-align: middle;
                        }

                        .table th {
                            background-color: #0d6efd !important;
                            color: white !important;
                            font-weight: bold !important;
                            -webkit-print-color-adjust: exact !important;
                            color-adjust: exact !important;
                            border-color: #0a58ca !important;
                        }

                        .table thead th {
                            border-bottom: 2px solid #0a58ca !important;
                        }

                        .table tbody tr:nth-child(odd) {
                            background-color: rgba(0,0,0,0.02) !important;
                        }

                        .table tbody tr:hover {
                            background-color: rgba(0,0,0,0.04) !important;
                        }

                        /* Text alignment */
                        .text-end {
                            text-align: right !important;
                        }

                        .text-center {
                            text-align: center !important;
                        }

                        .text-start {
                            text-align: left !important;
                        }

                        /* Colors for print */
                        .text-success {
                            color: #198754 !important;
                        }

                        .text-danger {
                            color: #dc3545 !important;
                        }

                        .text-warning {
                            color: #ffc107 !important;
                        }

                        .text-info {
                            color: #0dcaf0 !important;
                        }

                        .text-primary {
                            color: #0d6efd !important;
                        }

                        .text-muted {
                            color: #6c757d !important;
                        }

                        /* Badges */
                        .badge {
                            padding: 0.25em 0.5em !important;
                            font-size: 0.8em !important;
                            font-weight: 600 !important;
                            border: 1px solid transparent !important;
                            border-radius: 4px !important;
                        }

                        .bg-success.bg-opacity-10.text-success.border-success {
                            background-color: rgba(25, 135, 84, 0.15) !important;
                            color: #198754 !important;
                            border-color: #198754 !important;
                        }

                        .bg-warning.bg-opacity-10.text-warning.border-warning {
                            background-color: rgba(255, 193, 7, 0.15) !important;
                            color: #ffc107 !important;
                            border-color: #ffc107 !important;
                        }

                        .bg-danger.bg-opacity-10.text-danger.border-danger {
                            background-color: rgba(220, 53, 69, 0.15) !important;
                            color: #dc3545 !important;
                            border-color: #dc3545 !important;
                        }

                        /* Summary row */
                        .table-active {
                            background-color: rgba(13, 110, 253, 0.1) !important;
                            font-weight: bold !important;
                        }

                        .table-active td {
                            border-top: 2px solid #0d6efd !important;
                        }

                        /* Hide unnecessary elements */
                        .no-print,
                        button,
                        .btn,
                        .dropdown,
                        [wire\\:click],
                        .btn-group,
                        .dropdown-toggle,
                        .dropdown-menu {
                            display: none !important;
                        }

                        /* Ensure background colors print */
                        .bg-opacity-10 {
                            opacity: 1 !important;
                        }

                        .border-opacity-25 {
                            border-opacity: 1 !important;
                        }

                        /* Font weights */
                        .fw-bold {
                            font-weight: bold !important;
                        }

                        .fw-semibold {
                            font-weight: 600 !important;
                        }

                        /* Spacing */
                        .mb-4 {
                            margin-bottom: 1.5rem !important;
                        }

                        .mt-4 {
                            margin-top: 1.5rem !important;
                        }

                        .pt-4 {
                            padding-top: 1.5rem !important;
                        }

                        .border-top {
                            border-top: 2px solid #dee2e6 !important;
                        }

                        hr {
                            margin: 25px 0;
                            border-color: #dee2e6;
                            border-width: 1px;
                        }

                        /* Print footer */
                        .print-footer {
                            margin-top: 30px;
                            padding-top: 20px;
                            border-top: 2px solid #dee2e6;
                            font-size: 9pt;
                            color: #6c757d;
                        }

                        .print-footer div[style*="border-bottom"] {
                            border-bottom: 1px solid #000 !important;
                            height: 50px !important;
                        }
                    }

                    /* Screen preview styles */
                    @media screen {
                        body {
                            padding: 25px;
                            background: #f8f9fa;
                            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                        }

                        .container-print {
                            max-width: 297mm;
                            margin: 0 auto;
                            background: white;
                            padding: 25px;
                            border-radius: 10px;
                            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
                        }

                        .table {
                            margin-bottom: 25px;
                        }

                        .print-header {
                            margin-bottom: 25px;
                            padding-bottom: 20px;
                            border-bottom: 2px solid #dee2e6;
                        }

                        .print-footer {
                            margin-top: 40px;
                            padding-top: 25px;
                            border-top: 2px solid #dee2e6;
                        }
                    }

                    /* Common styles for both screen and print */
                    h2 {
                        font-size: 24pt;
                        margin-bottom: 15px;
                        color: #0d6efd;
                        font-weight: bold;
                    }

                    h4 {
                        font-size: 16pt;
                        margin-bottom: 20px;
                        color: #495057;
                    }

                    h6 {
                        font-size: 11pt;
                        color: #6c757d;
                        margin-bottom: 8px;
                    }

                    .small {
                        font-size: 9pt;
                    }

                    .row {
                        display: flex;
                        flex-wrap: wrap;
                        margin-right: -10px;
                        margin-left: -10px;
                    }

                    .col-md-3, .col-md-4 {
                        padding-right: 10px;
                        padding-left: 10px;
                        flex: 0 0 auto;
                        width: 25%;
                    }

                    .col-md-4 {
                        width: 33.333333%;
                    }

                    .d-flex {
                        display: flex !important;
                    }

                    .justify-content-between {
                        justify-content: space-between !important;
                    }

                    .align-items-center {
                        align-items: center !important;
                    }

                    .rounded-circle {
                        border-radius: 50% !important;
                    }

                    .p-3 {
                        padding: 1rem !important;
                    }

                    .fa-lg {
                        font-size: 1.5rem !important;
                    }
                </style>
            </head>
            <body>
                <div class="container-print">
                    <!-- Print Header -->
                    <div class="print-header">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold">Rapport des Ventes</h2>
                            <h4 class="text-muted">
                                ${periodInfo.label}
                                ${periodInfo.dateRange}
                            </h4>
                            <p class="text-muted">Généré le ${formattedDate}</p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    ${contentToPrint.querySelector('.row.g-3.mb-4')?.outerHTML || ''}

                    <!-- Sales Table -->
                    ${contentToPrint.querySelector('.card.shadow-sm.border-0 .table-responsive')?.outerHTML || ''}

                    <!-- Print Footer -->
                    <div class="print-footer">
                        <div class="row">
                            <div class="col-6">
                                <p class="mb-1"><strong>Signature du responsable:</strong></p>
                                <div style="height: 50px; border-bottom: 1px solid #000;"></div>
                            </div>
                            <div class="col-6 text-end">
                                <p class="mb-1"><strong>Cachet de l'entreprise:</strong></p>
                                <div style="height: 50px;"></div>
                            </div>
                        </div>
                        <div class="text-center text-muted small mt-4">
                            <p>Document généré le ${formattedDate} | © ${now.getFullYear()}, Développer par Pathé-PK</p>
                        </div>
                    </div>
                </div>

                <script>
                    // Auto-print when loaded
                    window.onload = function() {
                        // Short delay to ensure all content is loaded
                        setTimeout(function() {
                            try {
                                window.print();
                            } catch (error) {
                                console.log('Print initiated');
                            }
                        }, 800);
                    };

                    // Manual print trigger with Ctrl+P
                    document.addEventListener('keydown', function(e) {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                            e.preventDefault();
                            window.print();
                        }
                    });

                    // Close window after printing (optional)
                    window.onafterprint = function() {
                        setTimeout(function() {
                            try {
                                window.close();
                            } catch (error) {
                                // Window might not be closable in some browsers
                            }
                        }, 500);
                    };
                <\/script>
            </body>
        </html>
    `;

    // Write content to iframe
    printFrame.contentWindow.document.open();
    printFrame.contentWindow.document.write(printContent);
    printFrame.contentWindow.document.close();

    // Focus on print frame
    printFrame.contentWindow.focus();

    // Wait for iframe to load, then handle printing
    printFrame.onload = function() {
        try {
            printFrame.contentWindow.print();
        } catch (error) {
            console.error('Print error:', error);

            // Fallback: Provide instructions
            alert(
                'L\'impression a été lancée. Si la fenêtre d\'impression ne s\'ouvre pas automatiquement:\n\n' +
                '1. Utilisez Ctrl+P (Windows/Linux) ou Cmd+P (Mac)\n' +
                '2. Assurez-vous que les popups ne sont pas bloqués\n' +
                '3. Vérifiez vos paramètres d\'impression'
            );
        }

        // Clean up after a delay
        setTimeout(() => {
            if (printFrame && printFrame.parentNode) {
                document.body.removeChild(printFrame);
            }
        }, 2000);
    };
}

/**
 * Alternative: Simple print function (no iframe)
 */
function printSalesReportSimple() {
    // Store original body content
    const originalBody = document.body.innerHTML;

    // Get printable content
    const printableSection = document.getElementById('printable-section');
    if (!printableSection) {
        console.error('Element #printable-section not found');
        return;
    }

    // Clone content
    const printContent = printableSection.cloneNode(true);

    // Show print-only elements
    printContent.querySelectorAll('.print-header, .print-footer').forEach(el => {
        el.classList.remove('d-none');
        el.style.display = 'block';
    });

    // Replace body with printable content
    document.body.innerHTML = printContent.outerHTML;

    // Print
    window.print();

    // Restore original content
    document.body.innerHTML = originalBody;

    // Dispatch event to let Livewire know we restored the page
    setTimeout(() => {
        window.dispatchEvent(new Event('livewire:init'));
    }, 100);
}

