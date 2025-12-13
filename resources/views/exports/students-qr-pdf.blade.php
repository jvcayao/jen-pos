<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student QR Codes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #333;
        }
        .container {
            padding: 10px;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 3px;
        }
        .header p {
            color: #666;
            font-size: 10px;
        }
        .qr-grid {
            width: 100%;
        }
        .qr-row {
            display: table;
            width: 100%;
            table-layout: fixed;
        }
        .qr-card {
            display: table-cell;
            width: {{ $columns == 4 ? '25%' : ($columns == 3 ? '33.33%' : '50%') }};
            padding: 8px;
            text-align: center;
            vertical-align: top;
        }
        .qr-card-inner {
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 10px 8px;
            background: #fff;
        }
        .qr-image {
            width: {{ $qr_size }}px;
            height: {{ $qr_size }}px;
            margin: 0 auto 8px;
        }
        .qr-image img {
            width: 100%;
            height: 100%;
        }
        .student-name {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            margin-bottom: 2px;
            word-wrap: break-word;
            overflow: hidden;
        }
        .student-id {
            font-size: 10px;
            color: #666;
            font-family: monospace;
        }
        .student-grade {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }
        .page-break {
            page-break-after: always;
        }
        .footer {
            margin-top: 15px;
            text-align: center;
            font-size: 9px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 8px;
        }
        .cut-line {
            border-top: 1px dashed #ccc;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student QR Codes</h1>
            <p>Generated on {{ now()->format('F d, Y h:i A') }} | Total: {{ count($students) }} student(s)</p>
        </div>

        <div class="qr-grid">
            @foreach(array_chunk($students, $columns) as $rowIndex => $row)
                <div class="qr-row">
                    @foreach($row as $student)
                        <div class="qr-card">
                            <div class="qr-card-inner">
                                <div class="qr-image">
                                    <img src="{{ $student['qr_svg'] }}" alt="QR Code">
                                </div>
                                <div class="student-name">{{ Str::limit($student['full_name'], 20) }}</div>
                                <div class="student-id">{{ $student['student_id'] }}</div>
                                @if($student['grade_level'] || $student['section'])
                                    <div class="student-grade">
                                        {{ $student['grade_level'] ?? '' }}{{ $student['grade_level'] && $student['section'] ? ' - ' : '' }}{{ $student['section'] ?? '' }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @for($i = count($row); $i < $columns; $i++)
                        <div class="qr-card"></div>
                    @endfor
                </div>
                @if(($rowIndex + 1) % $rows_per_page === 0 && $rowIndex + 1 < ceil(count($students) / $columns))
                    <div class="page-break"></div>
                @endif
            @endforeach
        </div>

        <div class="footer">
            Scan QR code during checkout to quickly select the student
        </div>
    </div>
</body>
</html>
