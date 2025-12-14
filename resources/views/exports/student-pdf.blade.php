<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Report - {{ $student['full_name'] }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        .container {
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 14px;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ddd;
            color: #0066cc;
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
            padding: 5px 10px;
            font-weight: bold;
            width: 150px;
            background: #f5f5f5;
            border: 1px solid #ddd;
        }
        .info-value {
            display: table-cell;
            padding: 5px 10px;
            border: 1px solid #ddd;
        }
        .stats-grid {
            display: table;
            width: 100%;
            margin-top: 15px;
        }
        .stat-item {
            display: table-cell;
            width: 33.33%;
            padding: 15px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #0066cc;
        }
        .stat-label {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #fafafa;
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
            font-size: 9px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Student Report</h1>
            <p>{{ $student['full_name'] }} ({{ $student['student_id'] }})</p>
            @if($period)
                <p style="margin-top: 5px;">{{ $period }}</p>
            @endif
        </div>

        <div class="section">
            <div class="section-title">Student Information</div>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Student ID</div>
                    <div class="info-value">{{ $student['student_id'] }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Full Name</div>
                    <div class="info-value">{{ $student['full_name'] }}</div>
                </div>
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
                    <div class="info-label">Wallet Type</div>
                    <div class="info-value">{{ $student['wallet_type'] ? ucfirst(str_replace('-', ' ', $student['wallet_type'])) : 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Wallet Balance</div>
                    <div class="info-value">₱{{ number_format($student['wallet_balance'], 2) }}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Summary Statistics</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $analytics['total_orders'] }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">₱{{ number_format($analytics['total_spent'], 2) }}</div>
                    <div class="stat-label">Total Spent</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">₱{{ number_format($analytics['average_order_value'], 2) }}</div>
                    <div class="stat-label">Avg. Order Value</div>
                </div>
            </div>
        </div>

        @if(count($analytics['top_items']) > 0)
        <div class="section">
            <div class="section-title">Most Purchased Items</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%">#</th>
                        <th style="width: 50%">Product Name</th>
                        <th style="width: 20%" class="text-center">Quantity</th>
                        <th style="width: 20%" class="text-right">Total Spent</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analytics['top_items'] as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item['name'] }}</td>
                        <td class="text-center">{{ $item['quantity'] }}</td>
                        <td class="text-right">₱{{ number_format($item['total'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if(count($orders) > 0)
        <div class="page-break"></div>

        <div class="section">
            <div class="section-title">Order History ({{ count($orders) }} orders)</div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th class="text-center">Items</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Status</th>
                        <th>Payment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                    <tr>
                        <td style="font-family: monospace; font-size: 9px;">{{ Str::limit($order['uuid'], 12) }}</td>
                        <td class="text-center">{{ $order['items_count'] }}</td>
                        <td class="text-right">₱{{ number_format($order['total'], 2) }}</td>
                        <td class="text-center">
                            <span class="badge badge-{{ $order['status'] === 'confirm' ? 'success' : ($order['status'] === 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </td>
                        <td>{{ ucfirst($order['payment_method'] ?? 'N/A') }}</td>
                        <td>{{ $order['created_at'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="footer">
            Generated on {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>
</body>
</html>
