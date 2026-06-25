<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UMKM Financial API</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: ui-sans-serif, system-ui, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            text-align: center;
            padding: 48px 40px;
            max-width: 480px;
            width: 100%;
        }

        .badge {
            display: inline-block;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 999px;
            font-size: 12px;
            color: #94a3b8;
            padding: 4px 14px;
            margin-bottom: 28px;
            letter-spacing: 0.02em;
        }

        h1 {
            font-size: 26px;
            font-weight: 600;
            color: #f1f5f9;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 36px;
        }

        .stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-bottom: 36px;
        }

        .stack-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            border: 1px solid #1e293b;
            border-radius: 8px;
            padding: 10px 16px;
            font-size: 13px;
        }

        .stack-item .label { color: #64748b; }
        .stack-item .value { color: #e2e8f0; font-weight: 500; }

        .divider {
            border: none;
            border-top: 1px solid #1e293b;
            margin-bottom: 28px;
        }

        .links {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: opacity .15s;
        }

        .btn:hover { opacity: .8; }

        .btn-primary {
            background: #6366f1;
            color: #fff;
        }

        .btn-secondary {
            background: #1e293b;
            color: #94a3b8;
            border: 1px solid #334155;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: #22c55e;
            margin-top: 28px;
        }

        .dot {
            width: 6px;
            height: 6px;
            background: #22c55e;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="card">

        <div class="badge">REST API &middot; v1</div>

        <h1>UMKM Financial API</h1>
        <p class="subtitle">Laporan keuangan untuk bisnis UMKM Indonesia</p>

        <div class="stack">
            <div class="stack-item">
                <span class="label">Framework</span>
                <span class="value">Laravel {{ app()->version() }}</span>
            </div>
            <div class="stack-item">
                <span class="label">PHP</span>
                <span class="value">{{ PHP_VERSION }}</span>
            </div>
            <div class="stack-item">
                <span class="label">Database</span>
                <span class="value">PostgreSQL</span>
            </div>
            <div class="stack-item">
                <span class="label">Cache &amp; Queue</span>
                <span class="value">Redis</span>
            </div>
            <div class="stack-item">
                <span class="label">Environment</span>
                <span class="value">{{ app()->environment() }}</span>
            </div>
        </div>

        <hr class="divider">

        <div class="links">
            <a href="/docs/api" class="btn btn-primary">API Docs</a>
            <a href="/api/health" class="btn btn-secondary">Health Check</a>
        </div>

        <div class="status">
            <span class="dot"></span>
            Server is running
        </div>

    </div>
</body>
</html>