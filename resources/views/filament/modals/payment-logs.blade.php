<style>
.pl-row{display:flex;align-items:flex-start;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9}
.pl-row:last-child{border-bottom:none}
.pl-icon{width:32px;height:32px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.pl-icon-created{background:#dcfce7}
.pl-icon-edited{background:#dbeafe}
.pl-icon-employee{background:#fef3c7}
.pl-body{flex:1;min-width:0}
.pl-action{font-size:12px;font-weight:600;color:#111827}
.pl-desc{font-size:12px;color:#6b7280;margin-top:2px}
.pl-meta{font-size:11px;color:#9ca3af;margin-top:3px;display:flex;gap:8px;flex-wrap:wrap}
.pl-amount{font-size:12px;font-weight:700}
.pl-created{color:#16a34a}
.pl-edited{color:#2563eb}
.pl-strikethrough{text-decoration:line-through;color:#9ca3af;font-weight:400}
</style>

<div style="min-width:480px;max-height:70vh;overflow-y:auto">
    @if($project->paymentLogs->isEmpty())
    <div style="text-align:center;padding:32px;color:#9ca3af;font-size:13px">
        <div style="font-size:28px;margin-bottom:8px">📋</div>
        Hech qanday to'lov yozuvi topilmadi
    </div>
    @else

    {{-- Summary header --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:16px;padding-bottom:16px;border-bottom:2px solid #f1f5f9">
        @php
            $totalPaid = $project->payments->sum(fn($p) => (float)$p->amount);
            $editCount = $project->paymentLogs->where('action','edited')->count();
            $users = $project->paymentLogs->pluck('user')->filter()->unique('id');
        @endphp
        <div style="text-align:center;background:#f0fdf4;border-radius:8px;padding:10px">
            <div style="font-size:15px;font-weight:800;color:#16a34a">{{ number_format($totalPaid, 0, '.', ' ') }}</div>
            <div style="font-size:10px;color:#6b7280;margin-top:2px">Jami to'langan (so'm)</div>
        </div>
        <div style="text-align:center;background:#eff6ff;border-radius:8px;padding:10px">
            <div style="font-size:15px;font-weight:800;color:#2563eb">{{ $project->paymentLogs->count() }}</div>
            <div style="font-size:10px;color:#6b7280;margin-top:2px">Amallar soni</div>
        </div>
        <div style="text-align:center;background:#fef3c7;border-radius:8px;padding:10px">
            <div style="font-size:15px;font-weight:800;color:#d97706">{{ $editCount }}</div>
            <div style="font-size:10px;color:#6b7280;margin-top:2px">Tahrirlashlar</div>
        </div>
    </div>

    {{-- Log entries --}}
    @foreach($project->paymentLogs as $log)
    @php
        $iconClass = match($log->action) {
            'created'           => 'pl-icon-created',
            'edited'            => 'pl-icon-edited',
            'employee_assigned' => 'pl-icon-employee',
            default             => 'pl-icon-created',
        };
    @endphp
    <div class="pl-row">
        <div class="pl-icon {{ $iconClass }}">
            @if($log->action === 'created')
            <svg width="14" height="14" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            @elseif($log->action === 'edited')
            <svg width="14" height="14" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            @else
            <svg width="14" height="14" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            @endif
        </div>
        <div class="pl-body">
            <div style="display:flex;align-items:center;justify-content:space-between">
                <span class="pl-action">{{ $log->actionLabel() }}</span>
                @if($log->amount)
                <span class="pl-amount {{ $log->action === 'edited' ? 'pl-edited' : 'pl-created' }}">
                    @if($log->old_amount)
                    <span class="pl-strikethrough">{{ number_format((float)$log->old_amount, 0, '.', ' ') }}</span>
                    →
                    @endif
                    {{ number_format((float)$log->amount, 0, '.', ' ') }} so'm
                </span>
                @endif
            </div>
            @if($log->description)
            <div class="pl-desc">{{ $log->description }}</div>
            @endif
            <div class="pl-meta">
                <span>👤 {{ $log->user?->name ?? '—' }}</span>
                <span>🕐 {{ $log->created_at?->format('d.m.Y H:i') }}</span>
            </div>
        </div>
    </div>
    @endforeach
    @endif
</div>
