<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Students List Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 12px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .summary-item {
            display: inline-block;
            margin-right: 30px;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
        }
        .summary-value {
            font-size: 14px;
            color: #0066cc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 6px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #0066cc;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f0f0f0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .badge-info {
            background-color: #cce5ff;
            color: #004085;
        }
        .badge-secondary {
            background-color: #e2e3e5;
            color: #383d41;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .student-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            page-break-inside: avoid;
        }
        .student-card-header {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .student-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
        }
        .student-id {
            font-size: 12px;
            color: #666;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 4px 8px;
            font-weight: bold;
            width: 120px;
            background: #f8f9fa;
            border: 1px solid #eee;
            font-size: 10px;
        }
        .info-value {
            display: table-cell;
            padding: 4px 8px;
            border: 1px solid #eee;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Students List</h1>
            <p>Generated on {{ now()->format('F d, Y h:i A') }}</p>
        </div>

        <div class="summary">
            <div class="summary-item">
                <span class="summary-label">Total Students:</span>
                <span class="summary-value">{{ count($students) }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Active:</span>
                <span class="summary-value">{{ collect($students)->where('is_active', true)->count() }}</span>
            </div>
            <div class="summary-item">
                <span class="summary-label">Inactive:</span>
                <span class="summary-value">{{ collect($students)->where('is_active', false)->count() }}</span>
            </div>
        </div>

        @if($view_type === 'table')
            {{-- Table View --}}
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%">#</th>
                        <th style="width: 12%">Student ID</th>
                        <th style="width: 18%">Name</th>
                        <th style="width: 10%">Grade</th>
                        <th style="width: 10%">Section</th>
                        <th style="width: 15%">Guardian</th>
                        <th style="width: 10%">Wallet Type</th>
                        <th style="width: 10%" class="text-right">Balance</th>
                        <th style="width: 10%" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td style="font-family: monospace;">{{ $student['student_id'] }}</td>
                        <td>{{ $student['full_name'] }}</td>
                        <td>{{ $student['grade_level'] ?? '-' }}</td>
                        <td>{{ $student['section'] ?? '-' }}</td>
                        <td>{{ $student['guardian_name'] ?? '-' }}</td>
                        <td>
                            @if($student['wallet_type'])
                                <span class="badge badge-info">
                                    {{ ucfirst(str_replace('-', ' ', $student['wallet_type'])) }}
                                </span>
                            @else
                                <span class="badge badge-secondary">None</span>
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($student['wallet_balance'], 2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $student['is_active'] ? 'success' : 'danger' }}">
                                {{ $student['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            {{-- Card View --}}
            @foreach($students as $index => $student)
                <div class="student-card">
                    <div class="student-card-header">
                        <div class="student-name">{{ $student['full_name'] }}</div>
                        <div class="student-id">Student ID: {{ $student['student_id'] }}</div>
                    </div>
                    <div class="info-grid">
                        <div class="info-row">
                            <div class="info-label">Grade Level</div>
                            <div class="info-value">{{ $student['grade_level'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Section</div>
                            <div class="info-value">{{ $student['section'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Email</div>
                            <div class="info-value">{{ $student['email'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Phone</div>
                            <div class="info-value">{{ $student['phone'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Guardian</div>
                            <div class="info-value">{{ $student['guardian_name'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Guardian Phone</div>
                            <div class="info-value">{{ $student['guardian_phone'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Address</div>
                            <div class="info-value">{{ $student['address'] ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Wallet Type</div>
                            <div class="info-value">
                                {{ $student['wallet_type'] ? ucfirst(str_replace('-', ' ', $student['wallet_type'])) : 'N/A' }}
                            </div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Wallet Balance</div>
                            <div class="info-value">{{ number_format($student['wallet_balance'], 2) }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="badge badge-{{ $student['is_active'] ? 'success' : 'danger' }}">
                                    {{ $student['is_active'] ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                @if(($index + 1) % 3 === 0 && $index + 1 < count($students))
                    <div class="page-break"></div>
                @endif
            @endforeach
        @endif

        <div class="footer">
            Generated on {{ now()->format('F d, Y h:i A') }} | Total: {{ count($students) }} student(s)
        </div>
    </div>
</body>
</html>
