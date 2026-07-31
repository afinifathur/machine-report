<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title')</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 8mm 10mm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 7.5pt;
            line-height: 1.15;
            color: #1f2937;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
        .uppercase { text-transform: uppercase; }
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        td, th {
            padding: 2px 4px;
            vertical-align: top;
        }
        
        /* Utilities */
        .border-all {
            border: 1px solid #d1d5db;
        }
        .border-b {
            border-bottom: 1px solid #e5e7eb;
        }
        .bg-gray {
            background-color: #f9fafb;
        }
        .bg-soft-blue {
            background-color: #f0f7ff;
        }
        .text-blue { color: #2563eb; }
        .text-amber { color: #d97706; }
        .text-red { color: #dc2626; }
        .text-green { color: #16a34a; }
        
        /* Layout elements */
        .header-table {
            border-bottom: 2px solid #2563eb;
            margin-bottom: 5px;
        }
        .header-logo {
            font-size: 10.5pt;
            font-weight: 800;
            color: #2563eb;
            letter-spacing: 0.5px;
        }
        .header-title {
            font-size: 11pt;
            font-weight: bold;
            color: #111827;
        }
        .header-wo {
            font-size: 9pt;
            font-weight: bold;
            color: #374151;
        }
        
        .section-title {
            font-size: 7pt;
            font-weight: 800;
            text-transform: uppercase;
            color: #4b5563;
            border-bottom: 1px solid #9ca3af;
            padding-bottom: 1px;
            margin-top: 4px;
            margin-bottom: 2px;
        }
        
        .summary-table td {
            width: 25%;
            padding: 2px 4px;
            border: 1px solid #e5e7eb;
        }
        .summary-label {
            font-size: 6.5pt;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
        }
        .summary-value {
            font-size: 7.5pt;
            font-weight: bold;
            color: #1f2937;
        }
        
        .readiness-item {
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            padding: 2px 4px;
            font-weight: bold;
            font-size: 7pt;
            text-align: center;
        }
        
        .instruction-box {
            border: 1px solid #d1d5db;
            padding: 4px;
            min-height: 40px;
        }
        
        .write-line {
            border-bottom: 1px dashed #d1d5db;
            margin-top: 12px;
            height: 1px;
        }
        
        .approval-table {
            margin-top: 4px;
        }
        .approval-table th {
            font-size: 6.5pt;
            color: #4b5563;
            text-transform: uppercase;
            font-weight: bold;
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            text-align: center;
        }
        .approval-table td {
            height: 35px;
            border: 1px solid #d1d5db;
            text-align: center;
            vertical-align: bottom;
            padding-bottom: 2px;
            font-size: 7pt;
        }
        
        .footer-text {
            font-size: 6pt;
            color: #9ca3af;
        }
        
        .checklist-table th {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            font-weight: bold;
            font-size: 6.5pt;
            padding: 2px 4px;
        }
        .checklist-table td {
            border: 1px solid #e5e7eb;
            padding: 2px 4px;
            font-size: 7pt;
        }
        
        /* Small badges */
        .badge {
            display: inline-block;
            padding: 1px 3px;
            font-size: 6.5pt;
            font-weight: bold;
            border-radius: 2px;
            text-transform: uppercase;
        }
        .badge-red { background-color: #fee2e2; color: #b91c1c; }
        .badge-orange { background-color: #ffedd5; color: #c2410c; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-green { background-color: #dcfce7; color: #166534; }
    </style>
</head>
<body>
    @yield('content')
</body>
</html>
