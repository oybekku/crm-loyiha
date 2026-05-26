<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BESTHOME CRM</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f1f5f9;min-height:100vh;display:flex;align-items:center;justify-content:center}
        .card{background:#fff;border-radius:20px;padding:48px 40px;max-width:480px;width:90%;text-align:center;box-shadow:0 4px 32px rgba(0,0,0,.08)}
        .logo{font-size:15px;font-weight:800;color:#2563eb;letter-spacing:.5px;margin-bottom:32px}
        .icon{font-size:56px;margin-bottom:20px;animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}
        .title{font-size:22px;font-weight:700;color:#111827;margin-bottom:10px}
        .desc{font-size:14px;color:#6b7280;line-height:1.7;margin-bottom:28px}
        .code-badge{display:inline-block;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:8px;padding:5px 14px;font-size:12px;color:#9ca3af;font-family:monospace;margin-bottom:28px}
        .btn{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;border-radius:10px;padding:11px 28px;font-size:14px;font-weight:600;transition:background .15s}
        .btn:hover{background:#1d4ed8}
        .dots{display:flex;justify-content:center;gap:6px;margin-top:24px}
        .dot{width:8px;height:8px;border-radius:50%;background:#2563eb;animation:bounce 1.2s infinite}
        .dot:nth-child(2){animation-delay:.2s}
        .dot:nth-child(3){animation-delay:.4s}
        @keyframes bounce{0%,80%,100%{transform:translateY(0)}40%{transform:translateY(-8px)}}
    </style>
</head>
<body>
    <div class="card">
        <div class="logo">BESTHOME CRM</div>
        @yield('content')
    </div>
</body>
</html>
