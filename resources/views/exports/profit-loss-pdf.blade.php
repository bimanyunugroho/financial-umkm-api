<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Keuangan - {{ $user->business_name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #1e293b; line-height: 1.6; }

        .header { background: #6366f1; color: white; padding: 24px 32px; }
        .header h1 { font-size: 20px; font-weight: bold; }
        .header p { opacity: 0.85; font-size: 11px; margin-top: 4px; }
        .header .meta { margin-top: 12px; font-size: 11px; opacity: 0.9; }

        .content { padding: 24px 32px; }

        .summary-grid { display: table; width: 100%; margin-bottom: 24px; border-collapse: collapse; }
        .summary-card { display: table-cell; width: 25%; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; }
        .summary-card + .summary-card { border-left: none; }
        .summary-card .label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .summary-card .value { font-size: 18px; font-weight: bold; margin-top: 4px; }
        .summary-card.income .value { color: #10b981; }
        .summary-card.expense .value { color: #ef4444; }
        .summary-card.profit .value { color: #6366f1; }
        .summary-card.margin .value { color: #f59e0b; }

        h2 { font-size: 14px; font-weight: bold; color: #1e293b; margin: 20px 0 12px; padding-bottom: 6px; border-bottom: 2px solid #6366f1; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        thead tr { background: #f1f5f9; }
        th { padding: 10px 12px; text-align: left; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
        td { padding: 9px 12px; border-bottom: 1px solid #f1f5f9; }
        tr:last-child td { border-bottom: none; }
        tr:nth-child(even) td { background: #fafafa; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge.income { background: #d1fae5; color: #065f46; }
        .badge.expense { background: #fee2e2; color: #991b1b; }

        .comparison { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 20px; }
        .comparison h3 { font-size: 12px; font-weight: 600; margin-bottom: 10px; color: #475569; }
        .comp-row { display: table; width: 100%; margin-bottom: 6px; }
        .comp-label { display: table-cell; width: 60%; }
        .comp-val { display: table-cell; width: 20%; text-align: right; font-weight: 600; }
        .comp-change { display: table-cell; width: 20%; text-align: right; }
        .up { color: #10b981; }
        .down { color: #ef4444; }

        .footer { border-top: 1px solid #e2e8f0; padding: 16px 32px; font-size: 10px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ $user->business_name }}</h1>
    <p>{{ $user->business_type }} · {{ $user->address }}</p>
    <div class="meta">
        Laporan Laba Rugi &nbsp;|&nbsp; Periode: {{ $data['period'] }}
        &nbsp;|&nbsp; Dibuat: {{ $generated }}
    </div>
</div>

<div class="content">

    {{-- Summary Cards --}}
    <table class="summary-grid" style="margin-top: 20px;">
        <tr>
            <td class="summary-card income" style="border:1px solid #d1fae5; background:#f0fdf4;">
                <div class="label">Total Pemasukan</div>
                <div class="value" style="color:#10b981;">Rp {{ number_format($data['gross_income'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card expense" style="border:1px solid #fee2e2; background:#fff5f5; border-left:none;">
                <div class="label">Total Pengeluaran</div>
                <div class="value" style="color:#ef4444;">Rp {{ number_format($data['total_expense'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card profit" style="border:1px solid #e0e7ff; background:#f5f3ff; border-left:none;">
                <div class="label">Laba Bersih</div>
                <div class="value" style="color:#6366f1;">Rp {{ number_format($data['net_profit'], 0, ',', '.') }}</div>
            </td>
            <td class="summary-card margin" style="border:1px solid #fef3c7; background:#fffbeb; border-left:none;">
                <div class="label">Margin Laba</div>
                <div class="value" style="color:#f59e0b;">{{ $data['profit_margin'] }}%</div>
            </td>
        </tr>
    </table>

    {{-- Comparison --}}
    @if(isset($data['comparison']))
    <div class="comparison">
        <h3>📊 Perbandingan dengan Periode Sebelumnya ({{ $data['comparison']['prev_date_from'] }} – {{ $data['comparison']['prev_date_to'] }})</h3>
        <div class="comp-row">
            <div class="comp-label">Pemasukan</div>
            <div class="comp-val">Rp {{ number_format($data['comparison']['prev_income'], 0, ',', '.') }}</div>
            <div class="comp-change {{ $data['comparison']['income_change'] >= 0 ? 'up' : 'down' }}">
                {{ $data['comparison']['income_change'] >= 0 ? '▲' : '▼' }} {{ abs($data['comparison']['income_change']) }}%
            </div>
        </div>
        <div class="comp-row">
            <div class="comp-label">Pengeluaran</div>
            <div class="comp-val">Rp {{ number_format($data['comparison']['prev_expense'], 0, ',', '.') }}</div>
            <div class="comp-change {{ $data['comparison']['expense_change'] <= 0 ? 'up' : 'down' }}">
                {{ $data['comparison']['expense_change'] >= 0 ? '▲' : '▼' }} {{ abs($data['comparison']['expense_change']) }}%
            </div>
        </div>
        <div class="comp-row" style="border-top:1px solid #e2e8f0; padding-top:6px; margin-top:4px;">
            <div class="comp-label" style="font-weight:600;">Laba Bersih</div>
            <div class="comp-val">Rp {{ number_format($data['comparison']['prev_profit'], 0, ',', '.') }}</div>
            <div class="comp-change {{ $data['comparison']['profit_change'] >= 0 ? 'up' : 'down' }}" style="font-weight:600;">
                {{ $data['comparison']['profit_change'] >= 0 ? '▲' : '▼' }} {{ abs($data['comparison']['profit_change']) }}%
            </div>
        </div>
    </div>
    @endif

    {{-- By Category --}}
    <h2>Rincian per Kategori</h2>
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Tipe</th>
                <th class="text-right">Jml Transaksi</th>
                <th class="text-right">Total (Rp)</th>
                <th class="text-right">Rata-rata (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['by_category'] as $row)
            <tr>
                <td>{{ $row['category_name'] }}</td>
                <td>
                    <span class="badge {{ $row['type'] }}">
                        {{ $row['type'] === 'income' ? 'Pemasukan' : 'Pengeluaran' }}
                    </span>
                </td>
                <td>{{ number_format($row['transaction_count'], 0, ',', '.') }}</td>
                <td>{{ number_format($row['total_amount'], 0, ',', '.') }}</td>
                <td>{{ number_format($row['avg_amount'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

<div class="footer">
    Laporan ini dibuat otomatis oleh UMKM Financial API &nbsp;·&nbsp; {{ $generated }}
    &nbsp;·&nbsp; {{ $user->business_name }}
</div>

</body>
</html>
