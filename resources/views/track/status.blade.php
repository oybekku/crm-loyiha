<!DOCTYPE html>
<html lang="uz">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Loyiha holati — {{ $project->number }}</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family: Arial, sans-serif; background: #f3f4f6; color: #111827; min-height: 100vh; }

.container { max-width: 480px; margin: 0 auto; padding: 24px 16px 40px; }

.header { text-align: center; margin-bottom: 28px; }
.brand { font-size: 13px; font-weight: 700; color: #6b7280; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
.num-badge { display: inline-block; background: #1d4ed8; color: #fff; font-size: 18px; font-weight: 800; padding: 6px 18px; border-radius: 8px; letter-spacing: 1px; margin-bottom: 10px; }
.owner { font-size: 20px; font-weight: 800; color: #111827; }
.meta { font-size: 13px; color: #6b7280; margin-top: 4px; }

.card { background: #fff; border-radius: 16px; box-shadow: 0 2px 16px rgba(0,0,0,.07); overflow: hidden; margin-bottom: 20px; }

.card-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: #9ca3af; padding: 16px 20px 0; }

.info-row { display: flex; justify-content: space-between; padding: 12px 20px; border-bottom: 1px solid #f3f4f6; }
.info-row:last-child { border-bottom: none; }
.info-label { font-size: 13px; color: #6b7280; }
.info-val { font-size: 13px; font-weight: 600; color: #111827; text-align: right; }

/* Timeline */
.timeline { padding: 16px 20px; }
.tl-item { display: flex; gap: 14px; margin-bottom: 0; position: relative; }
.tl-item:not(:last-child) .tl-line { position: absolute; left: 17px; top: 36px; width: 2px; height: calc(100% - 4px); background: #e5e7eb; z-index: 0; }
.tl-item.qilindi:not(:last-child) .tl-line { background: #22c55e; }
.tl-item.jarayonda:not(:last-child) .tl-line { background: #e5e7eb; }

.tl-icon { width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; position: relative; z-index: 1; }
.tl-icon.qilindi   { background: #dcfce7; color: #16a34a; border: 2px solid #22c55e; }
.tl-icon.jarayonda { background: #dbeafe; color: #2563eb; border: 2px solid #3b82f6; animation: pulse 2s ease-in-out infinite; }
.tl-icon.kutilmoqda{ background: #f9fafb; color: #d1d5db; border: 2px solid #e5e7eb; }

@keyframes pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(59,130,246,.3); }
    50%      { box-shadow: 0 0 0 8px rgba(59,130,246,0); }
}

.tl-body { flex: 1; padding: 6px 0 20px; }
.tl-label { font-size: 15px; font-weight: 700; color: #111827; }
.tl-label.kutilmoqda { color: #9ca3af; font-weight: 500; }
.tl-status { font-size: 12px; margin-top: 3px; font-weight: 600; }
.tl-status.qilindi    { color: #16a34a; }
.tl-status.jarayonda  { color: #2563eb; }
.tl-status.kutilmoqda { color: #9ca3af; }
.tl-date { font-size: 11px; color: #9ca3af; margin-top: 2px; }

.footer { text-align: center; font-size: 12px; color: #9ca3af; margin-top: 20px; }
</style>
</head>
<body>
<div class="container">

    <div class="header">
        <div class="brand">BestHome CRM</div>
        <div class="num-badge">#{{ $project->number }}</div>
        <div class="owner">{{ $project->owner_name }}</div>
        <div class="meta">{{ $project->address }}</div>
    </div>

    <div class="card">
        <div class="card-title">Loyiha ma'lumotlari</div>
        <div class="info-row">
            <span class="info-label">Kategoriya</span>
            <span class="info-val">{{ \App\Models\Project::categoryOptions()[$project->category] ?? $project->category }}</span>
        </div>
        @if($project->deadline_date)
        <div class="info-row">
            <span class="info-label">Muddati</span>
            <span class="info-val">{{ $project->deadline_date->format('d.m.Y') }}</span>
        </div>
        @endif
        @if($project->assignedUsers->isNotEmpty())
        <div class="info-row">
            <span class="info-label">Mas'ul xodim</span>
            <span class="info-val">{{ $project->assignedUsers->pluck('name')->join(', ') }}</span>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-title">Jarayon holati</div>
        <div class="timeline">
            @foreach($timeline as $item)
            <div class="tl-item {{ $item['state'] }}">
                <div class="tl-line"></div>
                <div class="tl-icon {{ $item['state'] }}">
                    @if($item['state'] === 'qilindi')   ✓
                    @elseif($item['state'] === 'jarayonda') •
                    @else ○
                    @endif
                </div>
                <div class="tl-body">
                    <div class="tl-label {{ $item['state'] === 'kutilmoqda' ? 'kutilmoqda' : '' }}">
                        {{ $item['label'] }}
                    </div>
                    <div class="tl-status {{ $item['state'] }}">
                        @if($item['state'] === 'qilindi')    Qilindi
                        @elseif($item['state'] === 'jarayonda') Qilinmoqda
                        @else Kutilmoqda
                        @endif
                    </div>
                    @if($item['date'])
                    <div class="tl-date">{{ $item['date'] }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="footer">
        Sahifa avtomatik yangilanadi &middot; {{ now()->format('d.m.Y H:i') }}
    </div>

</div>
</body>
</html>
