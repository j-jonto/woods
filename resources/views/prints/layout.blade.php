<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'طباعة' }}</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                margin: 0;
                padding: 20px;
            }
            .page-break {
                page-break-before: always;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f8f9fa;
        }
        
        .print-container {
            background: white;
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        
        .document-title {
            font-size: 18px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .document-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .info-item {
            margin-bottom: 10px;
        }
        
        .info-label {
            font-weight: bold;
            color: #555;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .table th,
        .table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: right;
        }
        
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #333;
        }
        
        .table-striped tbody tr:nth-child(odd) {
            background-color: #f8f9fa;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-left {
            text-align: left;
        }
        
        .text-right {
            text-align: right;
        }
        
        .amount {
            font-weight: bold;
            color: #007bff;
        }
        
        .total-row {
            background-color: #e9ecef !important;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            text-align: center;
            width: 200px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 10px;
        }
        
        .print-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }
        
        .print-button:hover {
            background-color: #0056b3;
        }
        
        .back-button {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
            margin-right: 10px;
        }
        
        .back-button:hover {
            background-color: #545b62;
        }
        
        .button-group {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="button-group no-print">
        <button class="back-button" onclick="history.back()">رجوع</button>
        <button class="print-button" onclick="window.print()">طباعة</button>
    </div>
    
    <div class="print-container">
        <div class="header">
            <div class="company-name">مصنع الأخشاب</div>
            <div class="document-title">{{ $title ?? 'مستند' }}</div>
            <div style="font-size: 14px; color: #666;">
                {{ now()->format('Y-m-d H:i') }}
            </div>
        </div>
        
        @yield('content')
        
        <div class="footer">
            <p>تم إنشاء هذا المستند بواسطة نظام إدارة مصنع الأخشاب</p>
            <p>تاريخ الطباعة: {{ now()->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>
</body>
</html> 