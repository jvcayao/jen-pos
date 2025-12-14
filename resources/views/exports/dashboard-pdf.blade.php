<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
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
        }
        .stats-grid {
            display: table;
            width: 100%;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .stat-value {
            font-size: 18px;
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
            <h1>Sales Report</h1>
            <p>{{ $period }}</p>
        </div>

        <div class="section">
            <div class="section-title">Summary</div>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">₱{{ number_format($summary['total_sales'], 2) }}</div>
                    <div class="stat-label">Total Sales</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $summary['total_orders'] }}</div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">₱{{ number_format($summary['total_vat'], 2) }}</div>
                    <div class="stat-label">Total VAT</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">₱{{ number_format($summary['average_order_value'], 2) }}</div>
                    <div class="stat-label">Avg. Order Value</div>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Top Products</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%">#</th>
                        <th style="width: 50%">Product Name</th>
                        <th style="width: 20%" class="text-center">Qty Sold</th>
                        <th style="width: 20%" class="text-right">Total Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $index => $product)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $product['name'] }}</td>
                        <td class="text-center">{{ $product['quantity'] }}</td>
                        <td class="text-right">₱{{ number_format($product['sales'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="page-break"></div>

        <div class="section">
            <div class="section-title">Orders ({{ count($orders) }} total)</div>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
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
                        <td>{{ $order['customer'] }}</td>
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

        <div class="footer">
            Generated on {{ now()->format('F d, Y h:i A') }}
        </div>
    </div>
</body>
</html>
