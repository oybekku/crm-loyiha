<x-filament-panels::page>

<style>
/* ===== KANBAN ===== */
.kanban-wrap{display:flex;gap:14px;overflow-x:auto;padding-bottom:16px;align-items:flex-start;min-height:200px}
/* Tepadagi sinxron gorizontal scroll paneli */
.kanban-scroll-top{overflow-x:auto;overflow-y:hidden;position:sticky;top:0;z-index:6;height:16px;margin-bottom:4px;background:rgba(0,0,0,0.03);border-radius:6px}
.kanban-scroll-top > div{height:1px}
.dark .kanban-scroll-top{background:rgba(255,255,255,0.06)}
.kanban-col{min-width:500px;max-width:500px;flex-shrink:0;border-radius:10px;overflow:hidden}
.col-head{display:flex;align-items:center;justify-content:space-between;padding:10px 14px;font-weight:700;font-size:13px;letter-spacing:0.01em}
.col-count{background:rgba(255,255,255,.15);border-radius:12px;padding:2px 9px;font-size:11px;font-weight:600;color:#cbd5e1}
.col-body{background:transparent;min-height:80px;padding:8px;display:flex;flex-direction:column;gap:8px;transition:background .15s}

/* ===== GRID REJIM (bitta status filtri) ===== */
.kanban-grid-mode .kanban-wrap{display:block;overflow-x:visible;padding-bottom:0}
.kanban-grid-mode .kanban-col{min-width:100%;max-width:100%;border-radius:12px}
.kanban-grid-mode .col-body{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;padding:12px;flex-direction:unset}
.kanban-grid-mode .col-body > div{margin-bottom:0 !important}
@media(max-width:1400px){.kanban-grid-mode .col-body{grid-template-columns:repeat(3,1fr)}}
@media(max-width:900px){.kanban-grid-mode .col-body{grid-template-columns:repeat(2,1fr)}}
@media(max-width:600px){.kanban-grid-mode .col-body{grid-template-columns:1fr}}
.dark .col-body{background:transparent}
.col-body.drag-over{background:#dbeafe;outline:2px dashed #3b82f6;outline-offset:-4px}
/* Card */
.p-card{background:#fff;border-radius:12px;padding:12px 14px;box-shadow:0 1px 4px rgba(0,0,0,.07);cursor:grab;border:1.5px solid #e5e7eb;transition:border-color .15s,box-shadow .15s,opacity .15s}
.kb-wcard{display:flex;align-items:stretch}   /* yig'ilgan keng karta — display:flex klassda (x-show buzmasligi uchun) */
.kb-frow{display:flex;align-items:center}      /* ochilgan header qatori */

/* ══ NEON yig'ilgan karta ══ */
@keyframes kbn-pulse{0%,100%{opacity:1}50%{opacity:.72}}
@keyframes kbn-sweep{0%{transform:translateX(-130%)}100%{transform:translateX(330%)}}
.kbn-host{padding:0!important;border:none!important;background:transparent!important;box-shadow:none!important;overflow:visible!important}
.kbn-card{position:relative;display:flex;align-items:stretch;min-height:82px;background:rgba(255,255,255,.60);-webkit-backdrop-filter:blur(9px);backdrop-filter:blur(9px);border:1.5px solid color-mix(in srgb,var(--acc) 60%,#e2e8f0);border-radius:14px;overflow:visible;box-shadow:0 0 0 1px color-mix(in srgb,var(--acc) 15%,transparent),0 0 20px -6px color-mix(in srgb,var(--acc) 60%,transparent),0 8px 24px -12px rgba(15,23,42,.4)}
.kbn-vside{position:relative;flex-shrink:0;width:58px;align-self:stretch;display:flex;align-items:center;justify-content:center;cursor:pointer;border-radius:13px 0 0 13px;background:linear-gradient(180deg,color-mix(in srgb,var(--acc) 90%,#000),color-mix(in srgb,var(--acc) 62%,#000));box-shadow:inset 0 0 18px color-mix(in srgb,var(--acc) 60%,transparent),0 0 22px -3px var(--acc);overflow:hidden}
.kbn-vside svg{filter:drop-shadow(0 0 6px rgba(255,255,255,.85))}
.kbn-vside::after{content:"";position:absolute;top:0;bottom:0;width:36%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.35),transparent);animation:kbn-sweep 3.6s ease-in-out infinite}
.kbn-name{color:#0f172a!important}
.kbn-emp{color:#64748b!important}
.kbn-tag{color:var(--acc)!important;border:1px solid color-mix(in srgb,var(--acc) 45%,#e2e8f0)!important;background:color-mix(in srgb,var(--acc) 10%,#fff)!important}
.kbn-tag.done{color:#94a3b8!important;border-color:#e2e8f0!important;background:#f8fafc!important}
.kbn-st{color:#64748b!important;border:1px solid #e2e8f0!important;background:transparent!important}
.kbn-st.ok{color:#16a34a!important;border-color:#bbf7d0!important;background:#f0fdf4!important}
.kbn-badge{color:#0a0e17!important;box-shadow:0 0 14px -1px var(--acc),inset 0 0 8px rgba(255,255,255,.25);animation:kbn-pulse 2.4s ease-in-out infinite}
.kbn-paid{color:#16a34a!important}
.kbn-debt{color:#e11d48!important}
.kbn-muted{color:#94a3b8!important}
@media(prefers-reduced-motion:reduce){.kbn-vside::after,.kbn-badge{animation:none}}
/* ══ ZUDLIK (bayroq + qizil neon) ══ */
@keyframes kbn-redneon{0%,100%{box-shadow:0 0 0 1.5px #cd201f,0 0 16px -2px rgba(205,32,31,.55),0 0 34px -6px rgba(205,32,31,.45)}50%{box-shadow:0 0 0 1.5px #a01518,0 0 28px 0 rgba(205,32,31,.8),0 0 54px -4px rgba(160,20,24,.6)}}
.kbn-card.kbn-fire{border-color:#cd201f!important;animation:kbn-redneon 1.8s ease-in-out infinite}
/* Bayroq (zudlik belgisi) — sozlangan joy: right 96, top -28, size 92 */
.kbn-flag{position:absolute;top:-28px;right:96px;z-index:6;line-height:0;border:none;background:none;padding:0;margin:0}
.kbn-flag.clickable{cursor:pointer}
.kbn-flag img{display:block;height:92px;width:auto;pointer-events:none;filter:drop-shadow(0 4px 6px rgba(0,0,0,.28))}
.kbn-card.kbn-fire .kbn-flag img{filter:drop-shadow(0 0 11px rgba(205,32,31,.9))}
.dark .p-card{background:#1e2533;border-color:#2d3748}
.p-card:hover{border-color:#93c5fd;box-shadow:0 3px 10px rgba(0,0,0,.10)}
.p-card.dragging{opacity:.4;cursor:grabbing}
.p-card.card-overdue{border-color:#fca5a5;box-shadow:0 0 0 2px rgba(239,68,68,.15)}
.p-card.card-warn{border-color:#fcd34d;box-shadow:0 0 0 2px rgba(245,158,11,.12)}
/* Avatar */
.p-avatar{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:#fff;flex-shrink:0}
/* Progress bar */
.p-bar-wrap{background:#e5e7eb;border-radius:6px;height:7px;margin-bottom:5px;overflow:hidden}
.dark .p-bar-wrap{background:#374151}
.p-bar{height:7px;border-radius:6px;transition:width .3s}
/* Floating button (mobil) */
.kb-fab{display:none;position:fixed;bottom:24px;right:20px;z-index:500;background:#2563eb;color:#fff;border:none;border-radius:50%;width:54px;height:54px;font-size:26px;cursor:pointer;box-shadow:0 4px 16px rgba(37,99,235,.45);align-items:center;justify-content:center;transition:background .15s}
.kb-fab:hover{background:#1d4ed8}
/* Stat yangi */
.kb-stat-danger .kb-stat-num{color:#ef4444}
.kb-stat-warn .kb-stat-num{color:#f59e0b}
/* Move button */
.p-move-btn{position:relative;display:inline-flex;align-items:center;gap:4px;font-size:10px;padding:3px 8px;border-radius:6px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;cursor:pointer;white-space:nowrap}
.p-move-btn:hover{background:#eff6ff;border-color:#93c5fd;color:#2563eb}
.p-move-dropdown{position:absolute;bottom:calc(100% + 4px);left:0;z-index:200;background:#fff;border:1px solid #e5e7eb;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,.12);min-width:180px;padding:4px}
.p-move-item{display:block;width:100%;text-align:left;padding:6px 10px;font-size:11px;font-weight:500;border-radius:6px;border:none;background:none;cursor:pointer;color:#374151}
.p-move-item:hover{background:#f3f4f6}
.p-num{font-size:11px;color:#6b7280;font-family:monospace}
.p-owner{font-weight:400;font-size:13px;margin:4px 0 8px;color:#374151;letter-spacing:0.01em}
.dark .p-owner{color:#f9fafb}
.p-addr{font-size:11px;color:#6b7280;margin-bottom:6px;line-height:1.4;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.p-services{display:flex;flex-wrap:wrap;gap:3px;margin-bottom:6px}
.p-srv-tag{background:#fef2f2;color:#dc2626;font-size:10px;padding:2px 7px;border-radius:4px;font-weight:500}
.dark .p-srv-tag{background:#7f1d1d;color:#fca5a5}
.p-phone{font-size:11px;color:#6b7280;margin-bottom:6px;display:flex;align-items:center;gap:4px}
.p-money{font-size:12px;margin-bottom:3px}
.p-money-total{color:#2563eb;font-weight:600}
.p-money-paid{color:#6b7280;font-size:11px}
/* p-bar-wrap va p-bar — yuqorida qayta aniqlangan */
.p-footer{display:flex;justify-content:space-between;align-items:center;font-size:10px;color:#9ca3af;margin-top:4px}
/* Top bar */
.kb-topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px}
.kb-title{font-size:18px;font-weight:800;color:#111827}
.dark .kb-title{color:#f9fafb}
.kb-stats{display:flex;gap:8px}
.kb-stat{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:6px 12px;text-align:center}
.dark .kb-stat{background:#1f2937;border-color:#374151}
.kb-stat-num{font-size:16px;font-weight:800;color:#2563eb}
.kb-stat-lbl{font-size:10px;color:#6b7280}
.btn-new{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 16px;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .15s}
.btn-new:hover{background:#1d4ed8}

/* ===== MODAL ===== */
.kb-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;display:flex;align-items:center;justify-content:center;padding:12px}
.kb-modal{background:#fff;border-radius:16px;width:100%;max-width:960px;max-height:94vh;display:flex;flex-direction:column;box-shadow:0 25px 80px rgba(0,0,0,.25);overflow:hidden}
.dark .kb-modal{background:#18181b}
/* Header */
.kb-head{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb;flex-shrink:0}
.dark .kb-head{border-color:#27272a}
.kb-head h3{font-size:16px;font-weight:700;color:#111827}
.dark .kb-head h3{color:#f4f4f5}
.kb-close{background:none;border:none;cursor:pointer;color:#6b7280;padding:6px;border-radius:6px;line-height:1;font-size:18px;transition:background .15s}
.kb-close:hover{background:#f3f4f6}
/* Steps */
.kb-steps{display:flex;align-items:center;padding:10px 20px;border-bottom:1px solid #e5e7eb;gap:6px;flex-shrink:0;background:#fafafa}
.dark .kb-steps{border-color:#27272a;background:#09090b}
.kb-step-pill{display:flex;align-items:center;gap:6px;padding:5px 14px;border-radius:99px;font-size:13px;font-weight:500;color:#9ca3af;background:transparent}
.kb-step-pill.active{background:#2563eb;color:#fff}
.kb-step-pill.done{background:#dcfce7;color:#16a34a}
.kb-step-line{flex:1;height:1px;background:#e5e7eb}
.dark .kb-step-line{background:#27272a}
/* Body */
.kb-body{flex:1;overflow-y:auto;padding:20px}
.kb-split{display:flex;gap:20px}
.kb-left{flex:1;display:flex;flex-direction:column;gap:14px;min-width:0}
.kb-right{width:380px;flex-shrink:0;display:flex;flex-direction:column;gap:10px}
/* Form elements */
.kb-label{font-size:13px;font-weight:500;color:#374151;margin-bottom:5px;display:block}
.dark .kb-label{color:#d1d5db}
.kb-input{width:100%;border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;font-size:13px;outline:none;transition:border .15s,box-shadow .15s;background:#fff;color:#111827;box-sizing:border-box}
.dark .kb-input{background:#09090b;border-color:#3f3f46;color:#f4f4f5}
.kb-input:focus{border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.1)}
.kb-input.error{border-color:#ef4444;box-shadow:0 0 0 3px rgba(239,68,68,.1)}
.kb-textarea{resize:vertical;min-height:70px}
select.kb-input{-webkit-appearance:none;-moz-appearance:none;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 10px center;background-size:16px;padding-right:34px}
/* Phone row */
.kb-phone-row{display:flex;gap:0;align-items:stretch;margin-bottom:6px}
.kb-phone-input{flex:1;border-radius:8px 0 0 8px;border-right:0}
.kb-phone-add{background:#f3f4f6;border:1px solid #e2e8f0;border-left:none;border-radius:0 8px 8px 0;padding:0 12px;font-size:18px;color:#2563eb;cursor:pointer;line-height:1;font-weight:400}
.dark .kb-phone-add{background:#27272a;border-color:#3f3f46;color:#60a5fa}
.kb-phone-del{background:#fef2f2;border:1px solid #fecaca;border-radius:8px;padding:0 10px;font-size:13px;color:#ef4444;cursor:pointer;margin-left:6px}
/* Address confirmed badge */
.addr-confirmed{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px;margin-top:6px}
.dark .addr-confirmed{background:#052e16;border-color:#166534}
.addr-confirmed-txt{font-size:12px;color:#16a34a;display:flex;align-items:center;gap:6px;font-weight:500}
.dark .addr-confirmed-txt{color:#4ade80}
.addr-clear-btn{font-size:12px;color:#dc2626;cursor:pointer;background:none;border:none;padding:0;font-weight:500}
/* Map */
.map-section-label{font-size:12px;font-weight:600;color:#374151;display:flex;align-items:center;gap:5px;margin-bottom:6px}
.dark .map-section-label{color:#d1d5db}
/* Selected location box — blue like reference */
.sel-loc-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px 14px}
.dark .sel-loc-box{background:#1e3a5f;border-color:#1d4ed8}
.sel-loc-title{font-size:13px;font-weight:600;color:#1d4ed8;margin-bottom:4px}
.dark .sel-loc-title{color:#60a5fa}
.sel-loc-addr{font-size:12px;color:#1e40af;margin-bottom:3px;line-height:1.5}
.dark .sel-loc-addr{color:#93c5fd}
.sel-loc-coords{font-size:11px;color:#3b82f6}
/* File upload */
.kb-file-drop{border:2px dashed #d1d5db;border-radius:10px;padding:20px;text-align:center;cursor:pointer;transition:all .15s;position:relative}
.kb-file-drop:hover{border-color:#3b82f6;background:#eff6ff}
.dark .kb-file-drop{border-color:#3f3f46}
.dark .kb-file-drop:hover{background:#1e3a5f}
.kb-file-list{margin-top:8px;display:flex;flex-direction:column;gap:4px}
.kb-file-item{display:flex;align-items:center;gap:6px;font-size:11px;background:#f8fafc;border-radius:6px;padding:5px 8px;border:1px solid #e2e8f0}
.dark .kb-file-item{background:#27272a;border-color:#3f3f46}
/* Services */
.srv-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.srv-card{border:2px solid #e5e7eb;border-radius:10px;padding:10px 12px;cursor:pointer;transition:all .15s}
.dark .srv-card{border-color:#3f3f46}
.srv-card:hover{border-color:#93c5fd;background:#eff6ff}
.dark .srv-card:hover{background:#1e3a5f}
.srv-card.selected{border-color:#2563eb;background:#eff6ff}
.dark .srv-card.selected{background:#1e3a5f;border-color:#3b82f6}
.srv-name{font-size:13px;font-weight:600;color:#374151}
.dark .srv-name{color:#e5e7eb}
/* Tier tabs */
.tier-tabs{display:flex;gap:0;overflow-x:auto;border-bottom:1px solid #e5e7eb;margin-bottom:10px;scrollbar-width:none}
.tier-tab{padding:6px 14px;font-size:12px;font-weight:500;cursor:pointer;white-space:nowrap;border-bottom:2px solid transparent;color:#6b7280;background:none;border-top:none;border-left:none;border-right:none;transition:all .15s}
.tier-tab.active{color:#2563eb;border-bottom-color:#2563eb;font-weight:600}
.tier-tab:hover:not(.active){color:#374151;background:#f3f4f6}
/* Tier radio options */
.tier-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px}
.tier-item{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;border-radius:8px;border:1px solid #e5e7eb;cursor:pointer;transition:all .15s}
.tier-item:hover{border-color:#93c5fd;background:#f0f9ff}
.tier-item.selected{border-color:#16a34a;background:#f0fdf4}
.tier-radio{width:16px;height:16px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0;display:flex;align-items:center;justify-content:center;margin-right:8px}
.tier-item.selected .tier-radio{border-color:#16a34a;background:#16a34a}
.tier-label{font-size:11px;color:#374151;flex:1;line-height:1.3}
.tier-price{font-size:11px;font-weight:700;color:#111827;white-space:nowrap;margin-left:6px}
/* Footer */
.kb-footer{display:flex;justify-content:space-between;align-items:center;padding:14px 20px;border-top:1px solid #e5e7eb;flex-shrink:0}
.dark .kb-footer{border-color:#27272a}
.btn-back{background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;border-radius:8px;padding:9px 18px;font-size:13px;font-weight:600;cursor:pointer}
.dark .btn-back{background:#27272a;color:#f4f4f5;border-color:#3f3f46}
.btn-next{background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:600;cursor:pointer}
.btn-next:hover{background:#1d4ed8}
.btn-save{background:#16a34a;color:#fff;border:none;border-radius:8px;padding:9px 22px;font-size:13px;font-weight:600;cursor:pointer}
.btn-save:hover{background:#15803d}
/* Confirm */
.confirm-section{background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:12px}
.dark .confirm-section{background:#09090b}
.confirm-title{font-size:11px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px}
.confirm-row{display:flex;gap:12px;margin-bottom:6px;font-size:13px}
.confirm-key{color:#6b7280;min-width:110px;flex-shrink:0}
.confirm-val{color:#111827;font-weight:500}
.dark .confirm-val{color:#f4f4f5}
/* Map type buttons */
.kb-maptype{border:1px solid #e2e8f0;border-radius:6px;padding:5px 14px;font-size:12px;font-weight:500;cursor:pointer;background:#f3f4f6;color:#374151;transition:all .15s}
.kb-maptype:hover{background:#e5e7eb}
.kb-maptype.active{background:#111827;color:#fff;border-color:#111827}
.dark .kb-maptype{background:#27272a;color:#e5e7eb;border-color:#3f3f46}
.dark .kb-maptype.active{background:#f4f4f5;color:#09090b}
/* Notify */
.kb-notify{position:fixed;bottom:24px;right:24px;background:#16a34a;color:#fff;padding:12px 20px;border-radius:8px;font-size:13px;font-weight:600;z-index:9999;box-shadow:0 4px 14px rgba(0,0,0,.2);animation:slideIn .3s ease}
@keyframes slideIn{from{transform:translateY(16px);opacity:0}to{transform:translateY(0);opacity:1}}
[x-cloak]{display:none!important}

/* ===== MOBIL RESPONSIVE ===== */
@media (max-width: 640px) {
  /* Filament page wrapper overflow fix */
  .fi-main, .fi-page, [class*="fi-page"], .fi-body-container { overflow-x: visible !important; }


  /* Top bar: 2 qatorga ajratish */
  .kb-topbar{flex-wrap:wrap;gap:10px}
  .kb-title{font-size:16px;font-weight:800}
  .kb-stats{gap:5px;flex-wrap:wrap}
  .kb-stat{padding:5px 8px}
  .kb-stat-num{font-size:14px}
  .kb-stat-lbl{font-size:9px}
  .btn-new{width:100%;justify-content:center;padding:10px;font-size:13px}

  /* Kanban: to'liq ekran kengligi, bitta ustun ko'rinadi */
  .kanban-wrap{gap:12px;padding:4px 16px 80px;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;margin:0 -16px;width:calc(100% + 32px)}
  .kanban-col{min-width:calc(100vw - 56px);max-width:calc(100vw - 56px);scroll-snap-align:start}
  /* Floating button */
  .kb-fab{display:flex}
  .btn-new{display:none}
  /* Katta tugmalar */
  .p-move-btn{padding:7px 12px;font-size:12px}

  /* Karta: yirikroq tap zona */
  .p-card{padding:12px}
  .p-owner{font-size:14px}
  .p-phone{font-size:12px}
  .p-money{font-size:13px}

  /* Modal: to'liq ekran */
  .kb-overlay{padding:0;align-items:flex-end}
  .kb-modal{max-width:100%;width:100%;max-height:96vh;border-radius:20px 20px 0 0}

  /* Modal ichki: ustma-ust */
  .kb-split{flex-direction:column;gap:12px}
  .kb-right{width:100%;min-width:0}
  .kb-body{padding:14px}

  /* Xaritani mobilga moslashtirish */
  #modal-map{height:220px!important}

  /* Steps: kichikroq */
  .kb-steps{padding:8px 12px;gap:4px;overflow-x:auto}
  .kb-step-pill{font-size:11px;padding:4px 10px}
  .kb-step-line{min-width:16px}

  /* Xizmatlar: 1 ustun */
  .srv-grid{grid-template-columns:1fr}

  /* Narx tierlari: 1 ustun */
  .tier-grid{grid-template-columns:1fr}

  /* Footer tugmalar: kattaroq */
  .kb-footer{padding:10px 14px;gap:8px}
  .btn-back,.btn-next,.btn-save{padding:11px 18px;font-size:13px;flex:1;text-align:center}

  /* To'lov modal */
  .kb-stat{min-width:60px}

  /* Karta tugmalari: qulay touch */
  .p-move-btn{padding:7px 11px;font-size:12px}
  .card-actions{gap:6px}
  .card-actions > *{flex:1 1 auto;justify-content:center;text-align:center;min-width:calc(50% - 6px);max-width:calc(50% - 3px)}
  .card-actions > div[x-data]{flex:1 1 auto;min-width:calc(50% - 6px)}
  .card-actions > div[x-data] .p-move-btn{width:100%;justify-content:center}
  .card-actions a{text-align:center;justify-content:center}

  /* Dropdown mobilda pastga emas, tepaga chiqadi - ok */
  .p-move-dropdown{min-width:160px}

  /* Bildirish (notify) */
  .kb-notify{left:12px;right:12px;bottom:16px;text-align:center}
}

@media (max-width: 400px) {
  .kanban-col{min-width:calc(100vw - 32px);max-width:calc(100vw - 32px)}
  .kb-modal{max-height:98vh}
}

/* ===== CARD DARK MODE ===== */
.p-owner{font-weight:700;font-size:15px;margin-bottom:9px;line-height:1.3;color:#111827}
.dark .p-owner{color:#f1f5f9}
.p-info-text{font-size:12px;color:#4b5563;line-height:1.45}
.dark .p-info-text{color:#94a3b8}
.p-money-label{font-size:12px;color:#6b7280}
.dark .p-money-label{color:#94a3b8}
.p-money-main{font-size:14px;font-weight:700;color:#2563eb}
.dark .p-money-main{color:#60a5fa}
.p-money-paid-amt{font-size:13px;font-weight:600;color:#16a34a}
.dark .p-money-paid-amt{color:#4ade80}
.p-srv-tag-v2{background:#fff4ed;color:#c2410c;font-size:11px;padding:2px 7px;border-radius:4px;font-weight:500;border:1px solid #fed7aa}
.dark .p-srv-tag-v2{background:#431407;color:#fb923c;border-color:#7c2d12}
.p-card-divider{border-top:1px solid #f1f5f9;margin-bottom:9px}
@keyframes blink-new{0%,100%{opacity:1;box-shadow:0 0 4px #16a34a}50%{opacity:.6;box-shadow:none}}
@keyframes blink-warn{0%,100%{opacity:1}50%{opacity:.5}}
.dark .p-card-divider{border-top-color:#374151}
.p-status-pill{font-size:11px;font-weight:600;color:#374151;background:#f1f5f9;border-radius:5px;padding:3px 9px;display:inline-block}
.dark .p-status-pill{color:#d1d5db;background:#1e293b}
.p-worker-name{font-size:11px;color:#6b7280;font-weight:500}
.dark .p-worker-name{color:#94a3b8}
.p-pct-txt{font-size:11px;color:#9ca3af;margin-bottom:4px}
.dark .p-pct-txt{color:#64748b}
.p-date-txt{font-size:11px;color:#9ca3af}
.dark .p-date-txt{color:#64748b}
.p-delay-warn{font-size:10px;background:#fee2e2;color:#dc2626;border-radius:5px;padding:3px 7px;margin-bottom:7px;font-weight:600;display:inline-block}
.dark .p-delay-warn{background:#4c0519;color:#f87171}
.p-delay-info{font-size:10px;color:#6b7280;background:#f3f4f6;border-radius:5px;padding:3px 7px;margin-bottom:7px;display:inline-block}
.dark .p-delay-info{color:#94a3b8;background:#1e293b}
</style>


{{-- TO'LOV NAVBATI (KASSIR — tepaда) --}}
@if(auth()->user()?->isHisobchi() && $paymentQueue->count() > 0)
<div style="margin-bottom:20px;background:#fff;border-radius:10px;padding:14px 18px;box-shadow:0 1px 6px rgba(0,0,0,.07)">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
        <svg width="15" height="15" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        <span style="font-size:13px;font-weight:700;color:#111827">To'lov navbati</span>
        <span style="background:#dcfce7;color:#16a34a;font-size:11px;font-weight:700;border-radius:10px;padding:1px 8px">{{ $paymentQueue->count() }} ta</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:6px">
        @foreach($paymentQueue as $qp)
        @php $remaining = $qp->total_price - $qp->paid_amount; @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:10px 14px;border-radius:8px;background:#f0fdf4;border:1px solid #bbf7d0">
            <div style="flex:1;min-width:180px">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                    <span style="font-size:11px;color:#9ca3af;font-family:monospace">{{ $qp->number }}</span>
                    <span style="font-size:13px;font-weight:600;color:#111827">{{ $qp->owner_name }}</span>
                    <span style="font-size:11px;color:#6b7280">— {{ $qp->address }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:12px;font-size:11px;color:#9ca3af">
                    <span style="color:#dc2626;font-weight:600">Qoldiq: {{ number_format($remaining, 0, '.', ' ') }} so'm</span>
                    @if($qp->paymentRequester)
                    <span>{{ $qp->paymentRequester->name }} yubordi</span>
                    @endif
                    <span>{{ $qp->payment_requested_at?->format('d/m H:i') }}</span>
                </div>
            </div>
            <button class="p-move-btn" style="background:#16a34a;border-color:#16a34a;color:#fff;font-size:11px;padding:6px 14px;font-weight:600"
                    onclick="event.stopPropagation()"
                    wire:click.stop="openPaymentModal({{ $qp->id }}, true)">
                To'lovni qabul qilish
            </button>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Qidiruv + Yangi loyiha --}}
<div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
    <div style="position:relative;flex:1;max-width:360px">
        <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);pointer-events:none" width="15" height="15" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input wire:model.live.debounce.300ms="search"
               type="text"
               placeholder="Ism, raqam yoki manzil..."
               style="width:100%;padding:8px 12px 8px 34px;border:1.5px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;background:#fff;transition:border-color .15s"
               onfocus="this.style.borderColor='#2563eb'"
               onblur="this.style.borderColor='#e5e7eb'">
        @if($search)
        <button wire:click="$set('search','')"
                style="position:absolute;right:8px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;line-height:1;padding:2px">×</button>
        @endif
    </div>

    @if($search)
    <span style="font-size:12px;color:#6b7280;white-space:nowrap">
        {{ collect($projects->all())->sum(fn($c) => $c->count()) }} natija
    </span>
    @endif

    {{-- Oy/yil tanlash (loyiha ochilgan oyiga qarab) --}}
    @if(!$search)
    <div style="display:flex;align-items:center;gap:4px;margin-left:auto;background:#fff;border:1.5px solid #e5e7eb;border-radius:8px;padding:3px 5px">
        <button wire:click="kbChangeMonth(-1)" title="Oldingi oy" style="background:#f3f4f6;border:none;border-radius:6px;width:26px;height:26px;cursor:pointer;font-size:15px;color:#374151;line-height:1">‹</button>
        <span style="font-size:13px;font-weight:700;color:#2563eb;min-width:110px;text-align:center;white-space:nowrap">📅 {{ $kbMonthLabel }}</span>
        <button wire:click="kbChangeMonth(1)" title="Keyingi oy" style="background:#f3f4f6;border:none;border-radius:6px;width:26px;height:26px;cursor:pointer;font-size:15px;color:#374151;line-height:1">›</button>
    </div>
    @endif

    <div style="{{ $search ? 'margin-left:auto' : '' }}">
        @if(!auth()->user()?->isHisobchi())
        <button class="btn-new" wire:click="openModal">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            Yangi loyiha
        </button>
        @endif
    </div>
</div>

{{-- MOBIL STATUS TAB BAR --}}
<div id="kb-tab-bar" style="display:none;overflow-x:scroll;white-space:nowrap;padding:4px 4px 10px;-webkit-overflow-scrolling:touch;scrollbar-width:none;margin-bottom:8px;">
    @foreach($allStatuses as $sk => $st)
    @php $isActive = request()->get('status') === $sk || (!request()->get('status') && $loop->first); @endphp
    <a href="/admin/kanban-board?status={{ $sk }}"
       wire:navigate
       id="tab-{{ $sk }}"
       style="display:inline-block;padding:7px 14px;margin-right:6px;border-radius:20px;border:1.5px solid {{ $isActive ? '#2563eb' : '#e5e7eb' }};background:{{ $isActive ? '#2563eb' : '#f9fafb' }};color:{{ $isActive ? '#fff' : '#374151' }};font-size:12px;font-weight:600;cursor:pointer;white-space:nowrap;text-decoration:none;">
        {{ $st['label'] }}
        <span style="background:{{ $isActive ? 'rgba(255,255,255,0.25)' : '#e5e7eb' }};color:{{ $isActive ? '#fff' : '#6b7280' }};border-radius:10px;padding:1px 6px;font-size:10px;margin-left:3px;">{{ $projects->get($sk, collect())->count() }}</span>
    </a>
    @endforeach
</div>

{{-- KANBAN --}}
<div class="{{ $filterStatus ? 'kanban-grid-mode' : '' }}">

{{-- QIDIRUV — TEKIS RO'YXAT --}}
@if($search)
<div style="max-width:920px;display:flex;flex-direction:column;gap:8px;padding:2px">
    @php $flatResults = collect($projects)->flatten(1)->sortByDesc('created_at'); @endphp
    @forelse($flatResults as $p)
    @php $stm = $statusMap[$p->status] ?? ['label' => $p->status, 'color' => '#9ca3af']; @endphp
    <div wire:click="$dispatch('open-edit-modal', { id: {{ $p->id }} })"
         style="display:flex;align-items:center;justify-content:space-between;gap:12px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;cursor:pointer;transition:all .15s"
         onmouseover="this.style.borderColor='#93c5fd';this.style.boxShadow='0 2px 12px rgba(0,0,0,.06)'"
         onmouseout="this.style.borderColor='#e5e7eb';this.style.boxShadow='none'">
        <div style="min-width:0;flex:1">
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                <span style="font-family:monospace;font-weight:700;color:#2563eb;font-size:13px">{{ $p->number }}</span>
                <span style="font-weight:700;color:#111827;font-size:14px">{{ $p->owner_name }}</span>
            </div>
            @if($p->address)
            <div style="font-size:12px;color:#6b7280;margin-top:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:520px">📍 {{ $p->address }}</div>
            @endif
        </div>
        <div style="text-align:right;flex-shrink:0">
            <span style="display:inline-block;font-size:11px;font-weight:700;color:#fff;background:{{ $stm['color'] }};border-radius:6px;padding:3px 10px;white-space:nowrap">{{ $stm['label'] }}</span>
            <div style="font-size:12px;color:#374151;font-weight:600;margin-top:4px">{{ number_format($p->total_price, 0, '.', ' ') }} so'm</div>
        </div>
    </div>
    @empty
    <div style="text-align:center;color:#9ca3af;padding:30px;font-size:13px">Natija topilmadi</div>
    @endforelse
</div>
@endif

@if(!$filterStatus)
<div class="kanban-scroll-top" id="kanban-scroll-top" style="{{ $search ? 'display:none' : '' }}"><div></div></div>
@endif
<div class="kanban-wrap" id="kanban-wrap" style="{{ $search ? 'display:none' : '' }}">
@foreach($statuses as $statusKey => $status)
<div class="kanban-col" x-data="{ colCollapsed: localStorage.getItem('col_v1_{{ $statusKey }}_u{{ auth()->id() }}') === 'true' }"
     x-effect="localStorage.setItem('col_v1_{{ $statusKey }}', colCollapsed ? 'true' : 'false')"
     :style="colCollapsed ? 'min-width:48px;max-width:48px' : ''">
    <div class="col-head" style="background:{{ $status['head_bg'] ?? 'rgba(30,41,59,1)' }};color:{{ $status['head_text'] ?? '#f1f5f9' }};cursor:pointer;user-select:none"
         @click="colCollapsed=!colCollapsed">
        <button style="background:rgba(255,255,255,0.15);border:none;border-radius:6px;cursor:pointer;padding:3px 5px;display:flex;align-items:center;color:inherit;flex-shrink:0">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
                 :style="colCollapsed ? 'transform:rotate(-90deg)' : ''" style="transition:transform .2s">
                <path d="M6 9l6 6 6-6"/>
            </svg>
        </button>
        <span x-show="!colCollapsed">{{ $status['label'] }}</span>
        <span class="col-count" x-show="!colCollapsed">{{ $projects->get($statusKey, collect())->count() }}</span>
    </div>
    <div class="col-body" x-show="!colCollapsed"
         id="col-{{ $statusKey }}"
         ondragover="kbDragOver(event)"
         ondragleave="kbDragLeave(event)"
         ondrop="kbDrop(event,'{{ $statusKey }}')">
        @forelse($projects->get($statusKey, collect()) as $project)
        <div x-data="{ collapsed: localStorage.getItem('card_v2_{{ $project->id }}_u{{ auth()->id() }}') === null ? true : localStorage.getItem('card_v2_{{ $project->id }}_u{{ auth()->id() }}') === 'true' }"
             x-effect="localStorage.setItem('card_v2_{{ $project->id }}', collapsed ? 'true' : 'false')"
             style="position:relative;margin-bottom:8px">
        @php
            $daysLeft     = $project->deadline_days_left;
            $isOverdue    = $daysLeft !== null && $daysLeft < 0;
            $isWarn       = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 3;
            $cardClass    = $isOverdue ? 'card-overdue' : ($isWarn ? 'card-warn' : '');
            $currentLog   = $project->currentStatusLog;
            $daysInStatus = $currentLog ? (int)$currentLog->entered_at->diffInDays(now()) : 0;
            $allocDays    = $currentLog?->allocated_days ?? 0;
            $statusDelay  = ($allocDays > 0) ? max(0, $daysInStatus - $allocDays) : 0;
            // Joriy bo'lim muddatiga qolgan kun (allocated_days asosida)
            $deptDaysLeft = ($allocDays > 0) ? ($allocDays - $daysInStatus) : null;
            $isPaused     = $project->timer_paused_at !== null;
            $deptOverdue  = !$isPaused && $deptDaysLeft !== null && $deptDaysLeft <= 0;
            $deptOneDay   = !$isPaused && $deptDaysLeft === 1;
            // Karta ramkasi ham bo'lim muddatiga qarab (qizil = o'tgan, sariq = ≤3 kun); kutishda — neytral
            $cardClass    = $isPaused ? '' : ($deptOverdue ? 'card-overdue' : (($deptDaysLeft !== null && $deptDaysLeft <= 3) ? 'card-warn' : ''));
            $payPct       = $project->payment_percent;
            $barColor     = $payPct >= 100 ? '#10b981' : ($payPct >= 50 ? '#f59e0b' : $status['color']);
            $ownerInitial = mb_strtoupper(mb_substr($project->owner_name, 0, 1));
            // Joriy statusga mos xizmatda hodim biriktirilmagan tekshiruvi
            $currentStatusServices = $project->services->filter(
                fn($s) => strtolower($s->service_name) === strtolower($statusKey)
            );
            $hasUnassigned = $project->paid_amount > 0
                && $currentStatusServices->isNotEmpty()
                && $currentStatusServices->whereNull('assigned_user_id')->isNotEmpty();

            // Yig'ilgan kartada ko'rsatish uchun — eng shoshilinch (kam kun qolgan) xizmat muddati
            $urgentDaysLeft = null;
            $urgentLate     = false;
            $urgentLateDays = 0;
            foreach ($project->services as $s) {
                if ($s->completed_at) continue;
                if ($s->deadline_days && $s->assigned_user_id && $s->work_started_at) {
                    $dl   = $s->days_left;   // submitted bo'lsa muzlatilgan
                    $late = $s->is_late;
                    if ($urgentDaysLeft === null || $dl < $urgentDaysLeft) {
                        $urgentDaysLeft = $dl;
                        $urgentLate     = $late;
                        $urgentLateDays = $s->late_days;
                    }
                }
            }
            // Muddati o'tgan yoki ≤3 kun qolgan bo'lsa qizil belgi
            $showUrgent  = $urgentDaysLeft !== null && ($urgentLate || $urgentDaysLeft <= 3);
            $urgentLabel = $urgentDaysLeft === null ? ''
                : ($urgentLate ? $urgentLateDays . 'k kech' : $urgentDaysLeft . 'k');

            // Muddat ko'rsatilmagan — faol (tugatilmagan) xizmatlardan birortasida ham muddat yo'q bo'lsa
            $activeServices = $project->services->filter(fn($s) => !$s->completed_at);
            $hasAnyDeadline = $activeServices->contains(fn($s) => (int) $s->deadline_days > 0);
            $showNoDeadline = $activeServices->isNotEmpty() && !$hasAnyDeadline;
        @endphp
        <div class="p-card {{ $cardClass }}"
             :class="collapsed ? 'kbn-host' : ''"
             draggable="true"
             data-id="{{ $project->id }}"
             ondragstart="kbDragStart(event,{{ $project->id }})"
             ondragend="kbDragEnd(event)"
             @click="if(!window._kbDragged && !$event.target.closest('button,a,input,select,label,textarea')) $wire.dispatch('open-edit-modal', { id: {{ $project->id }} })"
             style="margin-bottom:0;padding:8px 10px">

            {{-- ══ YIG'ILGAN: keng horizontal karta (mockup) ══ --}}
            @php
                $wsC  = \App\Models\Project::workStatusOptions()[$project->work_status ?? 'yangi'] ?? ['label'=>'Yangi','color'=>'#3b82f6'];
                $empsC = $project->services->map(fn($s)=>$s->assignedUser?->name)->filter()->unique()->values();
                $qcC   = max(0,(float)$project->total_price-(float)$project->paid_amount);
                $isUrgent = (bool) $project->is_urgent;
                $uAuth = auth()->user();
                $canAcceptUrgent = $isUrgent && $uAuth && ($uAuth->canSeeAllProjects()
                    || $project->services->contains(fn($s)=>$s->assigned_user_id === $uAuth->id));
                $canToggleUrgent = $uAuth && $uAuth->canSeeAllProjects(); // admin/menejer bayroqni bosadi
                $acceptedName = null;
                if ($project->urgent_accepted_by && $project->urgent_accepted_at) {
                    $acceptedName = optional($project->assignedUsers->firstWhere('id', $project->urgent_accepted_by))->name
                        ?? \App\Models\User::find($project->urgent_accepted_by)?->name;
                }
            @endphp
            <div x-show="collapsed" class="kb-wcard kbn-card {{ $isUrgent ? 'kbn-fire' : '' }}" style="--acc:{{ $wsC['color'] }}">
                {{-- Zudlik qizil bayrog'i — faqat zudlik yoqilgan bo'lsa ko'rinadi (yoqish/o'chirish ochilgan kartada) --}}
                @if($isUrgent)
                <span class="kbn-flag"><img src="{{ route('pechat.asset','flag-red.png') }}" alt="Zudlik"></span>
                @endif
                {{-- V blok (bosilsa to'liq ochiladi) — neon yonuvchi --}}
                <div @click.stop="collapsed=false" class="kbn-vside" title="To'liq ochish">
                    <svg width="30" height="30" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="5 9 12 17 19 9"/></svg>
                </div>
                {{-- Kontent: info | narxlar --}}
                <div style="flex:1;min-width:0;padding:9px 13px;display:flex;justify-content:space-between;align-items:center;gap:10px;position:relative;z-index:1">
                    <div style="min-width:0;flex:1">
                        <div class="kbn-name" style="font-size:14.5px;font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-bottom:2px">{{ $project->owner_name }}</div>
                        @if($empsC->isNotEmpty())
                        <div class="kbn-emp" style="font-size:11.5px;font-weight:500;margin-bottom:6px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $empsC->join(', ') }}</div>
                        @endif
                        <div style="display:flex;flex-direction:column;gap:3px">
                            @foreach($project->services->take(3) as $srv)
                            <div style="display:flex;align-items:center;gap:5px;flex-wrap:wrap">
                                <span class="kbn-tag {{ $srv->completed_at ? 'done' : '' }}" style="font-size:10px;font-weight:600;border-radius:6px;padding:2px 8px;white-space:nowrap">{{ $serviceOptions[$srv->service_name] ?? $srv->service_name }}</span>
                                <span class="kbn-st {{ $srv->completed_at ? 'ok' : '' }}" style="font-size:9px;font-weight:600;border-radius:5px;padding:2px 7px;white-space:nowrap">{{ $srv->completed_at ? '✓ Tugallandi' : '○ Tugalmagan' }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div style="flex-shrink:0;text-align:right;font-variant-numeric:tabular-nums">
                        <div style="margin-bottom:6px">
                            <span class="kbn-badge" style="background:{{ $wsC['color'] }};display:inline-block;padding:3px 12px;border-radius:20px;font-size:10px;font-weight:800;letter-spacing:.03em;white-space:nowrap">{{ $wsC['label'] }}</span>
                        </div>
                        @if($project->total_price > 0)
                        <div style="font-size:11px;white-space:nowrap"><span class="kbn-muted">Umumiy</span> <b style="color:#334155">{{ number_format($project->total_price,0,'.',' ') }}</b></div>
                        <div style="font-size:11px;white-space:nowrap"><span class="kbn-muted">To'langan</span> <b class="kbn-paid">{{ number_format($project->paid_amount,0,'.',' ') }}</b></div>
                        @if($qcC>0)<div style="font-size:11px;white-space:nowrap"><span class="kbn-muted">Qoldiq</span> <b class="kbn-debt">{{ number_format($qcC,0,'.',' ') }}</b></div>
                        @else<div style="font-size:11px;white-space:nowrap"><span class="kbn-paid">✓ To'liq to'langan</span></div>@endif
                        @endif
                    </div>
                </div>
            </div>

            {{-- TOP ROW (ochilgan header): barmoq + ism + muddat + sana --}}
            <div x-show="!collapsed" class="kb-frow" style="gap:5px;margin-bottom:5px">
                @php $ws = \App\Models\Project::workStatusOptions()[$project->work_status ?? 'yangi'] ?? ['label'=>'Yangi','color'=>'#3b82f6']; @endphp
                <button @click.stop="collapsed=!collapsed"
                        style="flex-shrink:0;width:44px;align-self:stretch;min-height:40px;border:none;border-radius:9px;cursor:pointer;background:{{ $ws['color'] }};color:#fff;display:flex;align-items:center;justify-content:center;box-shadow:0 1px 4px rgba(0,0,0,.12);transition:all .15s"
                        :title="collapsed ? 'To\'liq ochish' : 'Yopish'">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" :style="collapsed ? '' : 'transform:rotate(180deg)'" style="transition:transform .2s"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="p-owner" style="flex:1;min-width:0;margin:0;font-size:12.5px;display:flex;align-items:center;gap:6px;overflow:hidden">
                    <span style="font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $project->owner_name }}</span>
                    <span style="flex-shrink:0;font-size:9px;font-weight:700;color:#fff;background:{{ $ws['color'] }};border-radius:11px;padding:2px 9px;white-space:nowrap">{{ $ws['label'] }}</span>
                    @if($project->created_at->diffInHours(now()) < 24)
                    <span style="font-size:9px;font-weight:700;background:#dcfce7;color:#16a34a;border-radius:4px;padding:1px 5px;white-space:nowrap;animation:blink-new 1.5s ease-in-out infinite;flex-shrink:0">Yangi</span>
                    @endif
                    @if($showUrgent)
                    {{-- Yig'ilgan kartada muddat ogohlantirishi (FISH dan keyin) --}}
                    <span x-show="collapsed" style="font-size:9px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px;white-space:nowrap;animation:blink-warn 1.5s infinite;flex-shrink:0">⏰ {{ $urgentLabel }}</span>
                    @endif
                </div>
                @if($showNoDeadline)
                {{-- Hech bir ishga muddat qo'yilmagan --}}
                <span style="font-size:9px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 6px;white-space:nowrap;animation:blink-warn 1.5s infinite;flex-shrink:0">⏰ Muddat ko'rsatilmagan</span>
                @endif
                <div style="flex-shrink:0;display:flex;align-items:center;gap:3px">
                    <button wire:click.stop="toggleTimer({{ $project->id }})"
                            onclick="event.stopPropagation()"
                            title="{{ $isPaused ? 'Vaqt hisobini yoqish' : 'Vaqt hisobini to‘xtatish (kutish)' }}"
                            style="border:none;background:none;padding:0;cursor:pointer;display:flex;align-items:center">
                        @if($isPaused)
                            {{-- Kutishda — soat --}}
                            <span style="font-size:9px;font-weight:700;background:#f1f5f9;color:#64748b;border-radius:3px;padding:1px 5px;white-space:nowrap;display:inline-flex;align-items:center;gap:2px">🕐 kutish</span>
                        @elseif($deptOverdue)
                            <span style="font-size:9px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:3px;padding:1px 5px;white-space:nowrap;animation:blink-warn 1s ease-in-out infinite">kechikkan</span>
                        @elseif($deptOneDay)
                            <span style="font-size:9px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:3px;padding:1px 5px;white-space:nowrap;animation:blink-warn 1s ease-in-out infinite">1 kun</span>
                        @elseif($deptDaysLeft !== null && $deptDaysLeft <= 3)
                            <span style="font-size:9px;font-weight:700;background:#fef3c7;color:#d97706;border-radius:3px;padding:1px 4px;white-space:nowrap">{{ $deptDaysLeft }} kun</span>
                        @elseif($deptDaysLeft !== null)
                            <span style="font-size:9px;font-weight:600;background:#ecfdf5;color:#059669;border-radius:3px;padding:1px 4px;white-space:nowrap">{{ $deptDaysLeft }} kun</span>
                        @else
                            {{-- Muddat belgilanmagan / aktivlashmagan — soat --}}
                            <span style="font-size:9px;font-weight:600;background:#f1f5f9;color:#94a3b8;border-radius:3px;padding:1px 5px;white-space:nowrap">🕐</span>
                        @endif
                    </button>
                    <span style="font-size:9px;color:#9ca3af;white-space:nowrap">{{ $project->created_at->format('d-M') }}</span>
                </div>
            </div>

            <div x-show="!collapsed" x-collapse>

            {{-- ZUDLIK boshqaruvi (ochilgan kartada) --}}
            @if($canToggleUrgent)
            {{-- Admin/menejer: Zudlik bilan — yoqish/o'chirish --}}
            <div style="margin-bottom:8px">
                <button type="button" wire:click="toggleUrgent({{ $project->id }})"
                        style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:800;border-radius:9px;padding:7px 14px;cursor:pointer;{{ $isUrgent ? 'border:1.5px solid #b91c1c;background:linear-gradient(180deg,#cd201f,#a01518);color:#fff;box-shadow:0 0 12px -2px rgba(205,32,31,.7)' : 'border:1.5px solid #fca5a5;background:#fff7ed;color:#b91c1c' }}">
                    🚩 {{ $isUrgent ? 'Zudlik yoqilgan — bosib o‘chirish' : 'Zudlik bilan' }}
                </button>
            </div>
            @elseif($isUrgent && $canAcceptUrgent)
            {{-- Biriktirilgan hodim: Zudlik + Qabul qildim --}}
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:8px;padding:8px 11px;border:1.5px solid #fca5a5;border-radius:10px;background:linear-gradient(90deg,#fff7ed,#fef2f2)">
                <span style="font-size:12px;font-weight:800;color:#b91c1c;display:inline-flex;align-items:center;gap:5px">🚩 Zudlik bilan qilinsin!</span>
                <button type="button" wire:click="acceptUrgent({{ $project->id }})" style="display:inline-flex;align-items:center;gap:5px;background:#16a34a;color:#fff;border:none;border-radius:8px;padding:6px 13px;font-size:12px;font-weight:800;cursor:pointer;box-shadow:0 2px 8px -2px rgba(22,163,74,.6)">✅ Qabul qildim</button>
            </div>
            @endif

            {{-- Qabul qilingan — kim/qachon (zudlik o'chgach) --}}
            @if(!$isUrgent && $acceptedName)
            <div style="margin-bottom:8px;padding:7px 11px;border:1.5px solid #bbf7d0;border-radius:10px;background:#f0fdf4;font-size:12px;font-weight:700;color:#15803d;display:inline-flex;align-items:center;gap:6px">
                ✅ {{ $acceptedName }} ishni qabul qildi — {{ $project->urgent_accepted_at->translatedFormat('d-M, H:i') }}
            </div>
            @endif

            {{-- Raqam (katta holatda) --}}
            <div style="margin-bottom:6px">
                <span style="background:{{ $status['color'] }};color:#fff;border-radius:6px;font-size:11px;font-weight:800;padding:2px 9px">{{ $project->number }}</span>
            </div>

            {{-- ADDRESS --}}
            @if($project->address)
            <div style="display:flex;align-items:flex-start;gap:6px;margin-bottom:5px">
                <svg width="13" height="13" fill="none" stroke="#f97316" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5" fill="#f97316" stroke="none"/></svg>
                <span class="p-info-text">{{ $project->address }}</span>
            </div>
            @endif

            {{-- SERVICES + qolgan kunlar --}}
            @if($project->services->count())
            <div style="display:flex;flex-direction:column;gap:3px;margin-bottom:5px">
                @foreach($project->services->take(4) as $srv)
                @php
                    $srvLabel = $serviceOptions[$srv->service_name] ?? $srv->service_name;
                    $daysLeft  = null;
                    $isLate    = false;
                    $isWaiting = false;
                    if ($srv->deadline_days && $srv->assigned_user_id && !$srv->completed_at) {
                        if ($srv->work_started_at) {
                            // Muzlatishni hisobga olib (submitted_at) — model accessorlari orqali
                            $daysLeft = $srv->days_left;
                            $isLate   = $srv->is_late;
                        } else {
                            // Kutmoqda — status hali kelmagan
                            $isWaiting = true;
                        }
                    }
                @endphp
                <div style="display:flex;align-items:center;gap:4px;flex-wrap:wrap">
                    <span class="p-srv-tag-v2" style="{{ $srv->completed_at ? 'text-decoration:line-through;opacity:.6' : '' }}">{{ $srvLabel }}</span>
                    @if($isWaiting)
                        <span style="font-size:10px;font-weight:600;background:#f3f4f6;color:#6b7280;border-radius:4px;padding:1px 5px;white-space:nowrap">⌛ {{ $srv->deadline_days }}k</span>
                    @elseif($daysLeft !== null)
                        @if($isLate)
                        <span style="font-size:10px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px;white-space:nowrap;animation:blink-warn 1.5s infinite">{{ $srv->late_days }}k kech</span>
                        @elseif($daysLeft <= 3)
                        <span style="font-size:10px;font-weight:700;background:#fee2e2;color:#dc2626;border-radius:4px;padding:1px 5px;white-space:nowrap;animation:blink-warn 1.5s infinite">{{ $daysLeft }}k</span>
                        @else
                        <span style="font-size:10px;font-weight:600;background:#f0fdf4;color:#16a34a;border-radius:4px;padding:1px 5px;white-space:nowrap">{{ $daysLeft }}k</span>
                        @endif
                    @endif
                    @if(auth()->user()?->isAdmin())
                    <button onclick="event.stopPropagation()"
                            wire:click.stop="toggleServiceComplete({{ $srv->id }})"
                            title="{{ $srv->completed_at ? 'Tugallanmagan deb belgilash' : 'Tugallangan deb belgilash' }}"
                            style="display:inline-flex;align-items:center;gap:3px;padding:1px 6px;border-radius:4px;border:1px solid {{ $srv->completed_at ? '#86efac' : '#d1d5db' }};background:{{ $srv->completed_at ? '#f0fdf4' : '#f9fafb' }};color:{{ $srv->completed_at ? '#16a34a' : '#9ca3af' }};font-size:10px;font-weight:600;cursor:pointer;white-space:nowrap">
                        @if($srv->completed_at)
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Tugallandi
                        @else
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> Tugalmagan
                        @endif
                    </button>
                    @endif
                </div>
                @endforeach
                @if($project->services->count() > 4)
                <span class="p-srv-tag-v2">+{{ $project->services->count() - 4 }}</span>
                @endif
            </div>
            @endif

            {{-- PHONE --}}
            @if(!empty($project->phones[0]['phone']))
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px">
                <svg width="13" height="13" fill="none" stroke="#8b5cf6" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 11.5 19.79 19.79 0 012 2.84 2 2 0 014 2.68h3a2 2 0 012 1.72c.22.83.46 1.63.7 2.81a2 2 0 01-.45 2.11L8.09 10.18a16 16 0 006.29 6.29l1.27-1.27a2 2 0 012.11-.45c1.18.24 1.98.48 2.81.7A2 2 0 0122 16.92z"/></svg>
                <span class="p-info-text">{{ $project->phones[0]['phone'] }}</span>
            </div>
            @endif

            {{-- Status delay badge --}}
            @if($statusDelay > 0)
            <div class="p-delay-warn">Bu bosqichda {{ $daysInStatus }} kun ({{ $statusDelay }} kun kechikdi)</div>
            @elseif($allocDays > 0)
            <div class="p-delay-info">Bu bosqichda {{ $daysInStatus }}/{{ $allocDays }} kun</div>
            @endif


            {{-- DIVIDER --}}
            <div class="p-card-divider"></div>

            {{-- MONEY --}}
            @if($project->total_price > 0)
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                <span style="font-size:11px;color:#9ca3af">Umumiy</span>
                <span style="font-size:12px;font-weight:600;color:#374151">{{ number_format($project->total_price, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3px">
                <span style="font-size:11px;color:#9ca3af">To'langan</span>
                <span style="font-size:12px;font-weight:600;color:#16a34a">{{ number_format($project->paid_amount, 0, '.', ' ') }} so'm</span>
            </div>
            @if($project->paid_amount < $project->total_price)
            @php $qoldiq = $project->total_price - $project->paid_amount; @endphp
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
                <span style="font-size:11px;color:#ef4444">Qoldiq</span>
                <span style="font-size:12px;font-weight:600;color:#ef4444">{{ number_format($qoldiq, 0, '.', ' ') }} so'm</span>
            </div>
            @endif
            <div class="p-bar-wrap" style="margin-bottom:8px">
                <div class="p-bar" style="width:{{ $payPct }}%;background:{{ $barColor }}"></div>
            </div>
            @endif

            {{-- TEKSHIRISH STATUS BELGISI --}}
            @if($project->status === 'tekshirish')
            <div style="display:flex;align-items:center;justify-content:center;margin-bottom:8px">
                <span style="font-size:11px;font-weight:900;letter-spacing:1px;color:#dc2626;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:6px;padding:4px 12px;animation:blink-warn 1s ease-in-out infinite">
                    ⚠ TEKSHIRILMAGAN
                </span>
            </div>
            @elseif($project->status === 'tugallangan')
            <div style="display:flex;align-items:center;justify-content:center;margin-bottom:8px">
                <span style="font-size:11px;font-weight:800;letter-spacing:1px;color:#fff;background:#16a34a;border-radius:6px;padding:4px 12px">
                    ✓ TEKSHIRILGAN
                </span>
            </div>
            @endif

            {{-- OGOHLANTIRISH: xizmatda hodim biriktirilmagan --}}
            @if($hasUnassigned)
            <div style="display:flex;align-items:center;gap:5px;background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:4px 8px;margin-bottom:8px;animation:blink-warn 1.5s ease-in-out infinite">
                <svg width="12" height="12" fill="none" stroke="#dc2626" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span style="font-size:11px;font-weight:700;color:#dc2626">
                    Hodim biriktirilmagan
                </span>
            </div>
            @endif

            {{-- FOOTER: status label + per-service workers --}}
            @php
                $srvWorkers = $project->services
                    ->filter(fn($s) => $s->assignedUser)
                    ->map(function($s) use ($serviceOptions, $statusKey) {
                        $daysLeft = null;
                        $isLate   = false;

                        // Faqat joriy status ga mos xizmat uchun timer ko'rsatamiz
                        if ($s->work_started_at && $s->deadline_days && $s->service_name === $statusKey) {
                            $daysLeft = $s->days_left;   // muzlatishni hisobga oladi
                            $isLate   = $s->is_late;
                        }

                        return [
                            'label'    => $serviceOptions[$s->service_name] ?? $s->service_name,
                            'name'     => $s->assignedUser->name,
                            'daysLeft' => $daysLeft,
                            'isLate'   => $isLate,
                        ];
                    });
            @endphp
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:8px;gap:8px">
                <div style="display:flex;flex-wrap:wrap;gap:5px;flex-shrink:0">
                    <span class="p-status-pill">{{ $status['label'] }}</span>
                    @php $ws = \App\Models\Project::workStatusOptions()[$project->work_status ?? 'yangi'] ?? null; @endphp
                    @if($ws)
                    <span style="font-size:11px;font-weight:700;color:#fff;background:{{ $ws['color'] }};border-radius:5px;padding:3px 9px;display:inline-block;white-space:nowrap">{{ $ws['label'] }}</span>
                    @endif
                </div>
                @if($srvWorkers->count() > 0)
                <div style="text-align:right">
                    @foreach($srvWorkers as $sw)
                    <div style="font-size:10px;line-height:1.6;display:flex;align-items:center;justify-content:flex-end;gap:4px">
                        <span style="color:#9ca3af">{{ $sw['label'] }}:</span>
                        <span style="font-weight:600;color:#374151">{{ $sw['name'] }}</span>
                        @if($sw['daysLeft'] !== null)
                                @if($sw['isLate'])
                            <span style="font-size:10px;font-weight:800;color:#dc2626;animation:blink-warn 1s ease-in-out infinite">{{ abs($sw['daysLeft']) }}k kech</span>
                            @elseif($sw['daysLeft'] <= 3)
                            <span style="font-size:10px;font-weight:800;color:#dc2626;animation:blink-warn 1s ease-in-out infinite">{{ $sw['daysLeft'] }}k</span>
                            @else
                            <span style="font-size:10px;font-weight:600;color:#16a34a">{{ $sw['daysLeft'] }}k</span>
                            @endif
                        @endif
                    </div>
                    @endforeach
                </div>
                @elseif($project->assignedUsers->count())
                <span class="p-worker-name">{{ $project->assignedUsers->pluck('name')->join(', ') }}</span>
                @endif
            </div>

            {{-- ACTION BUTTONS — kartada yashirilgan (amallar edit modalda bor) --}}
            <div class="card-actions" style="display:none">
                @if(auth()->user()?->canSeeAllProjects())
                <div x-data="{ open: false }" style="position:relative" @click.outside="open=false">
                    <button class="p-move-btn" @click.stop="open=!open" ondragstart="event.stopPropagation();event.preventDefault()">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        O'tkazish
                        <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                    </button>
                    <div class="p-move-dropdown" x-show="open" x-cloak>
                        @foreach($statuses as $sk => $st)
                        @if($sk !== $statusKey)
                        <button class="p-move-item"
                                onclick="event.stopPropagation()"
                                wire:click.stop="moveProject({{ $project->id }},'{{ $sk }}')"
                                @click="open=false">
                            <span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:{{ $st['color'] }};margin-right:6px;vertical-align:middle"></span>
                            {{ $st['label'] }}
                        </button>
                        @endif
                        @endforeach
                    </div>
                </div>
                @endif
                @if(auth()->user()?->isHisobchi() || auth()->user()?->canSeeAllProjects())
                <button class="p-move-btn" style="background:#f0fdf4;border-color:#86efac;color:#16a34a"
                        onclick="event.stopPropagation()"
                        wire:click.stop="openPaymentModal({{ $project->id }})">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                    To'lov
                </button>
                @endif
                @if(auth()->user()?->canSeeAllProjects())
                    @if($project->payment_requested_at)
                    <span style="font-size:10px;background:#fef3c7;color:#d97706;border-radius:5px;padding:3px 8px;font-weight:600;display:inline-flex;align-items:center;gap:4px;cursor:pointer"
                          onclick="event.stopPropagation()"
                          wire:click.stop="cancelPaymentRequest({{ $project->id }})"
                          title="Bekor qilish uchun bosing">
                        <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        Navbatda
                    </span>
                    @else
                    <button class="p-move-btn" style="background:#fef3c7;border-color:#fcd34d;color:#b45309"
                            onclick="event.stopPropagation()"
                            wire:click.stop="requestPayment({{ $project->id }})">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M12 10v4M10 12h4"/></svg>
                        To'lovga
                    </button>
                    @endif
                @endif
                @if(!auth()->user()?->isHisobchi())
                <button class="p-move-btn" style="background:#eff6ff;border-color:#93c5fd;color:#2563eb"
                        onclick="event.stopPropagation()"
                        wire:click.stop="openRouteModal({{ $project->id }},'{{ $statusKey }}')">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                    Yuborish
                </button>
                @endif
                <a href="{{ route('print.project.ariza', $project) }}"
                   target="_blank"
                   onclick="event.stopPropagation()"
                   style="display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:5px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:11px;font-weight:500;text-decoration:none;cursor:pointer;transition:background .15s"
                   onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#fff'">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Ariza
                </a>
                @if(auth()->user()?->isAdmin() || auth()->user()?->isMenejer())
                @if($project->status === 'tugallangan')
                <button wire:click.stop="markUncomplete({{ $project->id }})"
                        wire:confirm="Loyihani jarayonga qaytarmoqchimisiz?"
                        style="background:#dcfce7;border:1px solid #86efac;color:#16a34a;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Tugallandi
                </button>
                @else
                <button wire:click.stop="markComplete({{ $project->id }})"
                        wire:confirm="Loyihani tugallangan deb belgilaysizmi?"
                        style="background:#f3f4f6;border:1px solid #d1d5db;color:#6b7280;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Tekshirildi
                </button>
                @endif
                @endif

                @if(!auth()->user()?->isHisobchi())
                <button wire:click.stop="openServiceAssignModal({{ $project->id }})"
                        style="background:#f3f4f6;border:1px solid #e5e7eb;color:#374151;border-radius:6px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:4px">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Hodim
                </button>
                @endif
            </div>

            </div>{{-- /collapsed section end --}}

        </div>{{-- /p-card --}}
        </div>{{-- /wrapper --}}
        @empty
        @if(!auth()->user()?->isHisobchi())
        <button wire:click="openModal"
                style="width:100%;padding:28px 12px;background:transparent;border:2px dashed #d1d5db;border-radius:10px;cursor:pointer;color:#9ca3af;font-size:28px;font-weight:300;transition:all .15s;display:flex;align-items:center;justify-content:center"
                onmouseover="this.style.borderColor='#6b7280';this.style.color='#374151';this.style.background='rgba(0,0,0,0.03)'"
                onmouseout="this.style.borderColor='#d1d5db';this.style.color='#9ca3af';this.style.background='transparent'">
            +
        </button>
        @else
        <div style="text-align:center;padding:28px 12px;color:#d1d5db;font-size:22px">+</div>
        @endif
        @endforelse
    </div>
</div>
@endforeach
</div>
</div>{{-- /kanban-grid-mode wrapper --}}

{{-- Tepa va past gorizontal scroll panellarini sinxronlash --}}
<script>
(function(){
    function sync(){
        var wrap = document.getElementById('kanban-wrap');
        var top  = document.getElementById('kanban-scroll-top');
        if(!wrap || !top) return;
        var inner = top.firstElementChild;
        // Tepa panel kengligini doska kengligiga tenglashtiramiz
        inner.style.width = wrap.scrollWidth + 'px';
        if(top._bhBound) return;
        top._bhBound = true;
        var lock = false;
        top.addEventListener('scroll', function(){
            if(lock) return; lock = true; wrap.scrollLeft = top.scrollLeft; lock = false;
        });
        wrap.addEventListener('scroll', function(){
            if(lock) return; lock = true; top.scrollLeft = wrap.scrollLeft; lock = false;
        });
    }
    function init(){ setTimeout(sync, 80); }
    if(document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
    document.addEventListener('livewire:navigated', init);
    window.addEventListener('resize', sync);

    // ── Gorizontal scroll holatini saqlash ──────────────────────────────
    // Har qanday tugma (hodim, to'lov, o'tkazish...) bosilganda doska
    // boshiga qaytmasin — ko'rib turgan loyiha joyida qolsin.
    var savedLeft = 0;
    var restoring = false;
    function saveScroll(){ var w = document.getElementById('kanban-wrap'); if(w) savedLeft = w.scrollLeft; }

    // Foydalanuvchi scroll qilganda holatni eslab boramiz —
    // LEKIN qaytarish paytida emas (morph 0 ga reset qilganda savedLeft buzilmasin)
    document.addEventListener('scroll', function(e){
        if(!restoring && e.target && e.target.id === 'kanban-wrap') savedLeft = e.target.scrollLeft;
    }, true);

    // Har kadrda ~1 soniya davomida qaytaramiz — DOM/kenglik tayyor bo'lguncha urinadi
    function restoreSeq(){
        restoring = true;
        var attempts = 0;
        function tick(){
            var w = document.getElementById('kanban-wrap');
            if(w && savedLeft && w.scrollWidth > w.clientWidth + 5){
                if(Math.abs(w.scrollLeft - savedLeft) > 2) w.scrollLeft = savedLeft;
            }
            attempts++;
            if(attempts === 8) sync();
            if(attempts < 60){ requestAnimationFrame(tick); }
            else { restoring = false; }
        }
        requestAnimationFrame(tick);
    }

    // Livewire hook'larini ro'yxatga olish — DARHOL (agar Livewire tayyor bo'lsa),
    // aks holda event + poll orqali (livewire:init allaqachon o'tib ketgan bo'lishi mumkin).
    var _hooked = false;
    function registerHooks(){
        if(_hooked) return true;
        if(typeof Livewire === 'undefined' || !Livewire.hook) return false;
        _hooked = true;
        Livewire.hook('morph',   function(){ saveScroll(); });
        Livewire.hook('morphed', function(){ restoreSeq(); });
        Livewire.hook('commit',  function(p){ saveScroll(); if(p && typeof p.succeed === 'function'){ p.succeed(function(){ restoreSeq(); }); } });
        return true;
    }
    if(!registerHooks()){
        document.addEventListener('livewire:init', registerHooks);
        document.addEventListener('livewire:initialized', registerHooks);
        var _t = 0, _iv = setInterval(function(){ if(registerHooks() || ++_t > 60) clearInterval(_iv); }, 100);
    }
    document.addEventListener('livewire:update', function(){ restoreSeq(); });

    // Eng ishonchli zaxira: doska ichida biror narsa bosilsa — holatni saqlab,
    // qayta render'dan keyin bir necha marta qaytaramiz (Livewire hook'iga bog'liq emas).
    document.addEventListener('click', function(e){
        var w = document.getElementById('kanban-wrap');
        if(w && w.contains(e.target)){
            saveScroll();
            restoreSeq();
        }
    }, true);
})();
</script>

{{-- TO'LOV NAVBATI (ADMIN/MENEJER — pastda) --}}
@if(auth()->user()?->canSeeAllProjects() && $paymentQueue->count() > 0)
<div style="margin-top:28px;padding-top:20px;border-top:1px solid #e5e7eb">
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
        <svg width="15" height="15" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
        <span style="font-size:13px;font-weight:600;color:#374151">To'lov navbati</span>
        <span style="font-size:12px;color:#9ca3af">({{ $paymentQueue->count() }} ta)</span>
    </div>
    <div style="display:flex;flex-direction:column;gap:6px">
        @foreach($paymentQueue as $qp)
        @php $remaining = $qp->total_price - $qp->paid_amount; @endphp
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:10px 14px;border-radius:8px;background:#f9fafb">
            <div style="flex:1;min-width:200px">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
                    <span style="font-size:11px;color:#9ca3af;font-family:monospace">{{ $qp->number }}</span>
                    <span style="font-size:13px;font-weight:600;color:#111827">{{ $qp->owner_name }}</span>
                    <span style="font-size:11px;color:#6b7280">— {{ $qp->address }}</span>
                </div>
                <div style="display:flex;align-items:center;gap:12px;font-size:11px;color:#9ca3af">
                    <span style="color:#dc2626;font-weight:600">Qoldiq: {{ number_format($remaining, 0, '.', ' ') }} so'm</span>
                    @if($qp->paymentRequester)
                    <span>{{ $qp->paymentRequester->name }} yubordi</span>
                    @endif
                    <span>{{ $qp->payment_requested_at?->format('d/m H:i') }}</span>
                </div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-shrink:0">
                @if(auth()->user()?->isHisobchi())
                <button class="p-move-btn" style="background:#16a34a;border-color:#16a34a;color:#fff;font-size:11px;padding:5px 12px"
                        onclick="event.stopPropagation()"
                        wire:click.stop="openPaymentModal({{ $qp->id }}, true)">
                    To'lovni qabul qilish
                </button>
                @endif
                @if(auth()->user()?->canSeeAllProjects())
                <button class="p-move-btn" style="font-size:11px;color:#9ca3af;padding:4px 9px"
                        onclick="event.stopPropagation()"
                        wire:click.stop="cancelPaymentRequest({{ $qp->id }})">
                    Bekor
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- MODAL --}}
<div class="kb-overlay" style="display:{{ $showModal ? 'flex' : 'none' }}">
<div class="kb-modal" @click.stop>

    {{-- Header --}}
    <div class="kb-head">
        <h3>Yangi loyiha yaratish</h3>
        <button class="kb-close" wire:click="closeModal">✕</button>
    </div>

    {{-- Steps (pill style like reference) --}}
    <div class="kb-steps">
        <div class="kb-step-pill {{ $step >= 1 ? ($step > 1 ? 'done' : 'active') : '' }}">
            <span>{{ $step > 1 ? '✓' : '1' }}</span>
            <span>Ma'lumotlar</span>
        </div>
        <div class="kb-step-line"></div>
        <div class="kb-step-pill {{ $step >= 2 ? ($step > 2 ? 'done' : 'active') : '' }}">
            <span>{{ $step > 2 ? '✓' : '2' }}</span>
            <span>Xizmatlar</span>
        </div>
        <div class="kb-step-line"></div>
        <div class="kb-step-pill {{ $step >= 3 ? 'active' : '' }}">
            <span>3</span>
            <span>Tasdiqlash</span>
        </div>
    </div>

    {{-- Body --}}
    <div class="kb-body">

    @if($step === 1)
    <div class="kb-split">

        {{-- Chap: Forma --}}
        <div class="kb-left">

            <div>
                <label class="kb-label">Egasining ismi *</label>
                <datalist id="kb-owner-names">
                    @foreach($existingOwners as $ownerName)
                    <option value="{{ $ownerName }}">
                    @endforeach
                </datalist>
                <input wire:model.live="owner_name"
                       list="kb-owner-names"
                       autocomplete="off"
                       class="kb-input {{ $errors->has('owner_name') ? 'error' : '' }}"
                       placeholder="Loyiha egasi ism-familyasini kiriting...">
                @error('owner_name')<div style="color:#ef4444;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="kb-label">Nomi</label>
                <input wire:model.live="proj_title" class="kb-input" placeholder="Loyiha nomini kiriting...">
            </div>

            <div>
                <label class="kb-label">🏛 Kim orqali keldi (FISH)</label>
                <input wire:model.live="mygov_fish" list="kb-fish-names" autocomplete="off" class="kb-input" placeholder="Masalan: Umarov Oybek (ixtiyoriy)">
                <datalist id="kb-fish-names">
                    @foreach($mygovFishList ?? [] as $fn)<option value="{{ $fn }}"></option>@endforeach
                </datalist>
                <div style="font-size:11px;color:#6b7280;margin-top:3px">Kim orqali kelganini yozing — Oylik hisobotdagi MyGOV statistikasiga o'tadi.</div>
            </div>

            <div>
                <label class="kb-label">Manzil *</label>
                <input wire:model.live="address"
                       id="kb-address-input"
                       class="kb-input {{ $errors->has('address') ? 'error' : '' }}"
                       placeholder="Loyiha manzilini kiriting...">
                @error('address')<div style="color:#ef4444;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
                @if($address)
                <div class="addr-confirmed">
                    <div class="addr-confirmed-txt">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                        Xaritadan tanlangan
                    </div>
                    <button class="addr-clear-btn" wire:click="$set('address','')">Tozalash</button>
                </div>
                @endif
            </div>

            {{-- Koordinatalar --}}
            <div>
                <label class="kb-label" style="display:flex;align-items:center;gap:6px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/></svg>
                    Koordinatalar
                </label>
                {{-- Yashirin Livewire fieldlari --}}
                <input wire:model.live="latitude"  id="kb-lat-input" type="hidden">
                <input wire:model.live="longitude" id="kb-lng-input" type="hidden">
                {{-- Ko'rinadigan birlashgan input + copy tugmasi --}}
                <div style="display:flex;gap:6px;align-items:center">
                    <input id="kb-coords-combined"
                           class="kb-input"
                           placeholder="Masalan: 41.299800, 69.240100"
                           oninput="kbOnCombinedCoord(this.value)"
                           style="flex:1;font-family:monospace">
                    <button type="button" onclick="kbCopyCoords(this)"
                            title="Koordinatalarni nusxalash"
                            style="flex-shrink:0;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:7px;padding:7px 10px;cursor:pointer;color:#6b7280;font-size:12px;display:flex;align-items:center;gap:4px;white-space:nowrap">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        Nusxalash
                    </button>
                </div>
                <div style="font-size:11px;color:#9ca3af;margin-top:3px">Koordinata kiriting — xarita avtomatik yangilanadi</div>
            </div>

            <div>
                <label class="kb-label">Telefon raqamlar</label>
                @foreach($phones as $i => $phone)
                <div class="kb-phone-row" style="margin-bottom:6px">
                    <input wire:model.live="phones.{{ $i }}"
                           class="kb-input kb-phone-input {{ $i === 0 && $errors->has('phones.0') ? 'error' : '' }}"
                           placeholder="+998XXXXXXXXX"
                           maxlength="13">
                    @if($i === 0 && count($phones) < 5)
                    <button class="kb-phone-add" wire:click="addPhone" title="Raqam qo'shish">+</button>
                    @elseif($i > 0)
                    <button class="kb-phone-del" wire:click="removePhone({{ $i }})">✕</button>
                    @endif
                </div>
                @endforeach
                @error('phones.0')<div style="color:#ef4444;font-size:11px;margin-top:3px">{{ $message }}</div>@enderror
            </div>

            <div>
                <label class="kb-label">Qo'shimcha ma'lumot</label>
                <textarea wire:model.live="description" class="kb-input kb-textarea"
                          placeholder="Loyiha haqida batafsil ma'lumot kiriting..."></textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div>
                    <label class="kb-label">Kategoriya</label>
                    <select wire:model.live="category" class="kb-input">
                        @foreach($categoryOptions as $k => $v)
                        <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
            </div>


            {{-- Fayl yuklash --}}
            <div>
                <label class="kb-label">Hujjatlar yuklash</label>
                <div class="kb-file-drop" onclick="document.getElementById('kb-file-input').click()">
                    <svg width="30" height="30" fill="none" stroke="#9ca3af" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 8px;display:block"><path d="M12 3v12m5-5l-5-5-5 5"/><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/></svg>
                    <div style="font-size:13px;color:#4b5563;font-weight:500">Fayllarni tanlang yoki bu yerga tashlang</div>
                    <div style="font-size:11px;color:#9ca3af;margin-top:3px">PDF, Word, Excel, rasm fayllari (maks 20MB)</div>
                    <input id="kb-file-input" type="file" wire:model="uploadedFiles"
                           multiple accept=".pdf,.doc,.docx,.xls,.xlsx,image/*"
                           style="display:none">
                </div>
                <div wire:loading wire:target="uploadedFiles" style="font-size:12px;color:#2563eb;margin-top:6px;display:flex;align-items:center;gap:6px">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation:spin 1s linear infinite"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                    Yuklanmoqda...
                </div>
                @if(count($uploadedFiles) > 0)
                <div class="kb-file-list">
                    @foreach($uploadedFiles as $file)
                    <div class="kb-file-item">
                        <span style="color:#6b7280">📄</span>
                        <span style="flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#374151">{{ $file->getClientOriginalName() }}</span>
                        <span style="color:#9ca3af;flex-shrink:0">{{ round($file->getSize()/1024) }} KB</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

        </div>

        {{-- O'ng: Xarita --}}
        <div class="kb-right">
            <div class="map-section-label">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
                Xaritadan manzil tanlash
            </div>

            <div wire:ignore>
                <div style="display:flex;gap:6px;margin-bottom:6px">
                    <button onclick="kbLocateMe()" id="kb-locate-btn" title="Joylashuvimni aniqlash"
                            style="background:#059669;color:#fff;border:none;border-radius:8px;padding:7px 12px;font-size:13px;cursor:pointer;display:flex;align-items:center;gap:5px;white-space:nowrap">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="6"/><line x1="12" y1="18" x2="12" y2="22"/><line x1="2" y1="12" x2="6" y2="12"/><line x1="18" y1="12" x2="22" y2="12"/>
                        </svg>
                        Mening joyim
                    </button>
                </div>
                <div id="modal-map" style="width:100%;height:340px;border-radius:10px;border:1px solid #e2e8f0;background:#f3f4f6;overflow:hidden"></div>
                <div style="display:flex;gap:8px;margin-top:8px">
                    <button onclick="kbSetMapType('hybrid')" id="kb-btn-hybrid" class="kb-maptype active">Gibrid</button>
                    <button onclick="kbSetMapType('map')" id="kb-btn-map" class="kb-maptype">Xarita</button>
                    <button onclick="kbSetMapType('sat')" id="kb-btn-sat" class="kb-maptype">Sputnik</button>
                </div>
                <div id="selected-location-box" class="sel-loc-box" style="display:none;margin-top:10px">
                    <div class="sel-loc-title">Tanlangan joylashuv</div>
                    <div class="sel-loc-addr" id="selected-location-text"></div>
                    <div class="sel-loc-coords" id="selected-location-coords"></div>
                </div>
            </div>

            <div id="kb-map-hint" style="display:none;font-size:12px;color:#dc2626;text-align:center;padding:4px 0;font-weight:500">
                Ko'chalar ko'rinsin — xaritani kattalashtiring, keyin bosing
            </div>
            <div style="font-size:11px;color:#9ca3af;text-align:center;padding:4px 0">
                Qidiring → yaqinlashtiring → uyga bosing
            </div>
        </div>
    </div>
    @endif

    {{-- STEP 2 --}}
    @if($step === 2)
    @php
        $selectedCount = count(array_filter($services, fn($s) => !empty($s['selected'])));
        $totalPrice = array_sum(array_map(fn($s) => !empty($s['selected']) ? (float)str_replace([' ',','],'',$s['price']??'0') : 0, $services));
    @endphp
    <div>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div>
                <div style="font-size:15px;font-weight:700;color:#111827">Xizmatlarni tanlang</div>
                <div style="font-size:12px;color:#6b7280;margin-top:2px">{{ $selectedCount }} ta xizmat tanlandi</div>
            </div>
            @if($selectedCount > 0)
            <div style="background:#2563eb;color:#fff;border-radius:8px;padding:6px 14px;font-size:13px;font-weight:700">
                {{ number_format($totalPrice, 0, '.', ' ') }} so'm
            </div>
            @endif
        </div>

        <div style="display:flex;flex-direction:column;gap:8px">
            @foreach($services as $key => $srv)
            @php
                $sel      = !empty($srv['selected']);
                $hasTiers = !empty($srv['has_tiers']) && isset($priceTiers[$key]);
                $activeSub = $activeSubTab[$key] ?? (isset($priceTiers[$key]) ? array_key_first($priceTiers[$key]) : null);
                $hasSelectedTiers = !empty($srv['selected_tiers']);
            @endphp

            @if($hasTiers)
            {{-- Tier service: accordion open/close via Alpine (no Livewire round trip) --}}
            <div x-data="{ open: {{ $sel || $hasSelectedTiers ? 'true' : 'false' }} }"
                 :style="({{ $sel || $hasSelectedTiers ? 'true' : 'false' }}) ? 'border:1px solid #86efac;background:#f0fdf4' : ''"
                 style="border:1px solid {{ $hasSelectedTiers ? '#86efac' : '#e5e7eb' }};border-radius:10px;overflow:hidden;background:{{ $hasSelectedTiers ? '#f0fdf4' : '#fff' }};transition:all .15s">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer" @click="open = !open">
                    <div style="display:flex;align-items:center;gap:12px">
                        @if($hasSelectedTiers)
                        <div style="width:22px;height:22px;border-radius:50%;background:#16a34a;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        @else
                        <div style="width:22px;height:22px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0"></div>
                        @endif
                        <span style="font-size:14px;font-weight:600;color:#111827">{{ $srv['label'] }}</span>
                        <span style="font-size:10px;color:#9ca3af;background:#f3f4f6;border-radius:4px;padding:2px 6px">Narxlar</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        @if($hasSelectedTiers && !empty($srv['price']))
                        @php $hasArea = !empty($srv['area_m2']); @endphp
                        <span style="font-size:12px;font-weight:700;background:#111827;color:#fff;border-radius:6px;padding:3px 10px">
                            {{ number_format((float)$srv['price'], 0, '.', ' ') }} so'm
                            @if($hasArea)<span style="font-weight:400;opacity:.7;font-size:11px"> ({{ (float)$srv['area_m2'] }}m²)</span>@endif
                        </span>
                        @endif
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" :style="open ? 'transform:rotate(180deg)' : ''" style="transition:transform .2s"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                <div x-show="open" style="padding:12px 16px;border-top:1px solid #bbf7d0;background:#fff" wire:click.stop x-cloak>
                    <div style="margin-bottom:10px">
                        <label style="font-size:12px;color:#6b7280;font-weight:500;margin-bottom:5px;display:block">Mas'ul hodim</label>
                        <select wire:model="services.{{ $key }}.assigned_user_id" class="kb-input" style="max-width:260px">
                            <option value="">— Tanlang —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($activeSub)
                    {{-- Sub-service tabs --}}
                    <div class="tier-tabs">
                        @foreach($priceTiers[$key] as $subKey => $subTiers)
                        @php $subLabel = $subTiers[0]['sub_service_label'] ?? $subKey; @endphp
                        <button class="tier-tab {{ $activeSub === $subKey ? 'active' : '' }}"
                                wire:click.stop="setSubTab('{{ $key }}', '{{ $subKey }}')">
                            {{ $subLabel }}
                            @if(isset($srv['selected_tiers'][$subKey]))
                            <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:#16a34a;margin-left:5px;vertical-align:middle"></span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                    {{-- Tier options grid --}}
                    <div class="tier-grid">
                        @foreach($priceTiers[$key][$activeSub] as $tier)
                        @php $tierSel = isset($srv['selected_tiers'][$activeSub]) && $srv['selected_tiers'][$activeSub] === $tier['id']; @endphp
                        <div style="display:flex;align-items:center;gap:4px">
                        <div class="tier-item {{ $tierSel ? 'selected' : '' }}"
                             style="flex:1"
                             wire:click.stop="{{ $tierSel ? 'deselectTier(\''.$key.'\', \''.$activeSub.'\')' : 'selectTier(\''.$key.'\', \''.$activeSub.'\', '.$tier['id'].')' }}">
                            <div class="tier-radio">
                                @if($tierSel)<svg width="8" height="8" viewBox="0 0 8 8" fill="#fff"><circle cx="4" cy="4" r="3"/></svg>@endif
                            </div>
                            <span class="tier-label">{{ $tier['label'] }}</span>
                            <span class="tier-price">{{ number_format($tier['price'], 0, '.', ' ') }}</span>
                        </div>
                        @if($tierSel)
                        @php $hasArea = !empty($srv['area_m2']); @endphp
                        <button wire:click.stop="openAreaModal('{{ $key }}')"
                                title="kv.m kiritish"
                                style="width:28px;height:28px;border-radius:6px;border:1.5px solid {{ $hasArea ? '#2563eb' : '#e5e7eb' }};background:{{ $hasArea ? '#eff6ff' : '#f9fafb' }};cursor:pointer;display:flex;align-items:center;justify-content:center;flex-shrink:0;color:{{ $hasArea ? '#2563eb' : '#9ca3af' }};transition:all .15s">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 1.93 1.47l.3 1.11a7 7 0 0 1 .87.51l1.09-.4a2 2 0 0 1 2.29.9l1 1.72a2 2 0 0 1-.34 2.39l-.81.72a7 7 0 0 1 0 1l.81.72a2 2 0 0 1 .34 2.39l-1 1.72a2 2 0 0 1-2.29.9l-1.09-.4a7 7 0 0 1-.87.51l-.3 1.11A2 2 0 0 1 12 22a2 2 0 0 1-1.93-1.47l-.3-1.11a7 7 0 0 1-.87-.51l-1.09.4a2 2 0 0 1-2.29-.9l-1-1.72a2 2 0 0 1 .34-2.39l.81-.72a7 7 0 0 1 0-1l-.81-.72a2 2 0 0 1-.34-2.39l1-1.72a2 2 0 0 1 2.29-.9l1.09.4a7 7 0 0 1 .87-.51l.3-1.11A2 2 0 0 1 12 2z"/><circle cx="12" cy="12" r="3"/></svg>
                        </button>
                        @endif
                        </div>
                        @endforeach
                    </div>

                    @endif

                    {{-- Ixtiyoriy narx (tier xizmatlar uchun) --}}
                    <div style="margin-top:10px;padding-top:10px;border-top:1px solid #f0fdf4" wire:click.stop>
                        <label style="font-size:12px;color:#6b7280;font-weight:500;margin-bottom:6px;display:flex;align-items:center;gap:5px">
                            <svg width="12" height="12" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Ixtiyoriy narx (tanlansa ustun turadi)
                        </label>
                        <div style="display:flex;align-items:center;gap:8px">
                            <input wire:model.live="services.{{ $key }}.custom_price"
                                   type="number" min="0" placeholder="Masalan: 500000"
                                   style="flex:1;border:1.5px solid #e5e7eb;border-radius:8px;padding:7px 10px;font-size:13px;outline:none"
                                   onfocus="this.style.borderColor='#2563eb'" onblur="this.style.borderColor='#e5e7eb'">
                            <span style="font-size:12px;color:#6b7280;white-space:nowrap">so'm</span>
                            @if(!empty($srv['custom_price']))
                            <button wire:click.stop="$set('services.{{ $key }}.custom_price', '')"
                                    style="background:none;border:none;cursor:pointer;color:#9ca3af;font-size:16px;padding:2px">×</button>
                            @endif
                        </div>
                        @if(!empty($srv['custom_price']))
                        <div style="font-size:11px;color:#2563eb;margin-top:4px">
                            Ixtiyoriy narx: {{ number_format((float)$srv['custom_price'], 0, '.', ' ') }} so'm
                        </div>
                        @endif
                    </div>

                </div>
            </div>

            @else
            {{-- Non-tier service: simple toggle --}}
            <div style="border:1px solid {{ $sel ? '#86efac' : '#e5e7eb' }};border-radius:10px;overflow:hidden;background:{{ $sel ? '#f0fdf4' : '#fff' }};transition:all .15s">
                <div style="display:flex;align-items:center;justify-content:space-between;padding:13px 16px;cursor:pointer"
                     wire:click="$set('services.{{ $key }}.selected', {{ $sel ? 'false' : 'true' }})">
                    <div style="display:flex;align-items:center;gap:12px">
                        @if($sel)
                        <div style="width:22px;height:22px;border-radius:50%;background:#16a34a;flex-shrink:0;display:flex;align-items:center;justify-content:center">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                        @else
                        <div style="width:22px;height:22px;border-radius:50%;border:2px solid #d1d5db;flex-shrink:0"></div>
                        @endif
                        <span style="font-size:14px;font-weight:600;color:#111827">{{ $srv['label'] }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        @if($sel && !empty($srv['price']))
                        <span style="font-size:12px;font-weight:700;background:#111827;color:#fff;border-radius:6px;padding:3px 10px">
                            {{ number_format((float)str_replace([' ',','],'',$srv['price']), 0, '.', ' ') }} so'm
                        </span>
                        @endif
                        <svg width="16" height="16" fill="none" stroke="#9ca3af" stroke-width="2" viewBox="0 0 24 24" style="transition:transform .2s;{{ $sel ? 'transform:rotate(180deg)' : '' }}"><path d="M6 9l6 6 6-6"/></svg>
                    </div>
                </div>
                @if($sel)
                <div style="padding:12px 16px;border-top:1px solid #bbf7d0;background:#fff" wire:click.stop>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:end">
                        <div>
                            <label style="font-size:12px;color:#6b7280;font-weight:500;margin-bottom:6px;display:block">Narxini kiriting (so'm)</label>
                            <input wire:model.live="services.{{ $key }}.price"
                                   class="kb-input"
                                   placeholder="0" type="number" min="0">
                        </div>
                        <div>
                            <label style="font-size:12px;color:#6b7280;font-weight:500;margin-bottom:6px;display:block">Mas'ul hodim</label>
                            <select wire:model="services.{{ $key }}.assigned_user_id" class="kb-input">
                                <option value="">— Tanlang —</option>
                                @foreach($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @endif

            @endforeach
        </div>
    </div>
    @endif

    {{-- STEP 3 --}}
    @if($step === 3)
    @php
        $hasTiersSrv = fn($s) => !empty($s['has_tiers'])
            ? (((float)($s['custom_price'] ?? 0) > 0) || (!empty($s['selected_tiers']) && !empty($s['price'])))
            : !empty($s['selected']);
        $selectedSrvs = array_filter($services, $hasTiersSrv);
        // Ixtiyoriy narx ustun turadi
        $getPrice = fn($s) => (float)($s['custom_price'] ?? 0) > 0 ? (float)$s['custom_price'] : (float)($s['price'] ?? 0);
        $totalNoDiscount = array_sum(array_map($getPrice, $selectedSrvs));
        $totalDiscount   = array_sum(array_map(fn($s) => (float)($s['discount_amount'] ?? 0), $selectedSrvs));
        $totalFinal      = $totalNoDiscount - $totalDiscount;
    @endphp
    <div>
        {{-- Asosiy ma'lumotlar --}}
        <div class="confirm-section">
            <div class="confirm-title">Asosiy ma'lumotlar</div>
            <div class="confirm-row"><span class="confirm-key">Egasi:</span><span class="confirm-val">{{ $owner_name }}</span></div>
            @if($proj_title)<div class="confirm-row"><span class="confirm-key">Nomi:</span><span class="confirm-val">{{ $proj_title }}</span></div>@endif
            <div class="confirm-row"><span class="confirm-key">Manzil:</span><span class="confirm-val">{{ $address }}</span></div>
            <div class="confirm-row"><span class="confirm-key">Kategoriya:</span><span class="confirm-val">{{ $categoryOptions[$category] ?? $category }}</span></div>
            @if(!empty($assigned_user_ids))<div class="confirm-row"><span class="confirm-key">Hodimlar:</span><span class="confirm-val">{{ $users->whereIn('id', $assigned_user_ids)->pluck('name')->join(', ') }}</span></div>@endif
            @foreach($phones as $phone)@if(strlen($phone) > 4)
            <div class="confirm-row"><span class="confirm-key">Telefon:</span><span class="confirm-val">{{ $phone }}</span></div>
            @endif@endforeach
            @if($deadline_days > 0)<div class="confirm-row"><span class="confirm-key">Muddat:</span><span class="confirm-val">{{ $deadline_days }} kun</span></div>@endif
            @if(count($uploadedFiles) > 0)<div class="confirm-row"><span class="confirm-key">Fayllar:</span><span class="confirm-val">{{ count($uploadedFiles) }} ta fayl</span></div>@endif
        </div>

        {{-- Xizmatlar with discount --}}
        @if(count($selectedSrvs) > 0)
        <div class="confirm-section">
            <div class="confirm-title" style="margin-bottom:12px">Tanlangan xizmatlar ({{ count($selectedSrvs) }} ta)</div>
            @foreach($selectedSrvs as $key => $srv)
            @php
                $customP     = (float)($srv['custom_price'] ?? 0);
                $price       = $customP > 0 ? $customP : (float)($srv['price'] ?? 0);
                $discType    = $customP > 0 ? 'none' : ($srv['discount_type'] ?? 'none');
                $discAmount  = $customP > 0 ? 0 : (float)($srv['discount_amount'] ?? 0);
                $discValue   = $srv['discount_value']  ?? '';
                $finalPrice  = ($discType !== 'none') ? ($price - $discAmount) : $price;
                $hasDiscount = $discType !== 'none' && $discAmount > 0;
            @endphp
            <div style="border:1px solid #e5e7eb;border-radius:10px;padding:14px;margin-bottom:10px">
                {{-- Service header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px">
                    <span style="font-size:14px;font-weight:600;color:#111827">{{ $srv['label'] }}</span>
                    @if(!empty($srv['selected_tiers']))
                    <span style="font-size:11px;color:#6b7280;background:#f3f4f6;border-radius:4px;padding:2px 8px">{{ count($srv['selected_tiers']) }} ta tanlangan</span>
                    @endif
                </div>

                {{-- Selected tiers list --}}
                @if(!empty($srv['selected_tiers']) && isset($priceTiers[$key]))
                @php $srvArea = (float)($srv['area_m2'] ?? 0); @endphp
                <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-bottom:10px">
                    @foreach($srv['selected_tiers'] as $subKey => $tierId)
                    @php
                        $tierData = collect($priceTiers[$key][$subKey] ?? [])->firstWhere('id', $tierId);
                    @endphp
                    @if($tierData)
                    <div style="padding:4px 0;{{ !$loop->last ? 'border-bottom:1px solid #e5e7eb;margin-bottom:4px' : '' }}">
                        <div style="display:flex;justify-content:space-between;align-items:center">
                            <span style="font-size:12px;color:#374151">{{ $tierData['label'] }}</span>
                            <span style="font-size:12px;font-weight:600;color:#111827">{{ number_format($tierData['price'], 0, '.', ' ') }} so'm/m²</span>
                        </div>
                        @if($srvArea > 0)
                        <div style="font-size:11px;color:#2563eb;margin-top:3px;text-align:right">
                            {{ number_format($tierData['price'], 0, '.', ' ') }} × {{ $srvArea }}m² = {{ number_format($tierData['price'] * $srvArea, 0, '.', ' ') }} so'm
                        </div>
                        @endif
                    </div>
                    @endif
                    @endforeach
                    @if($srvArea > 0)
                    <div style="margin-top:6px;padding-top:6px;border-top:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center">
                        <span style="font-size:11px;color:#6b7280">Maydon:</span>
                        <span style="font-size:12px;font-weight:600;color:#2563eb">{{ $srvArea }} m²</span>
                    </div>
                    @endif
                </div>
                @endif

                {{-- Price row --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding-top:8px;border-top:1px solid #f3f4f6">
                    <span style="font-size:12px;color:#6b7280">Jami:</span>
                    <div style="text-align:right">
                        @if($hasDiscount)
                        <span style="font-size:12px;color:#9ca3af;text-decoration:line-through;margin-right:6px">{{ number_format($price, 0, '.', ' ') }} so'm</span>
                        <span style="font-size:14px;font-weight:700;color:#16a34a">{{ number_format($finalPrice, 0, '.', ' ') }} so'm</span>
                        @else
                        <span style="font-size:14px;font-weight:700;color:#111827">{{ number_format($price, 0, '.', ' ') }} so'm</span>
                        @endif
                    </div>
                </div>

                {{-- Discount row --}}
                <div style="margin-top:10px;background:#f8fafc;border-radius:8px;padding:10px">
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <div style="display:flex;align-items:center;gap:6px">
                            <svg width="14" height="14" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01M12 10h.01M8 10h.01M12 14h.01M8 14h.01M12 18h.01M8 18h.01"/></svg>
                            <span style="font-size:13px;font-weight:600;color:#111827">Chegirma</span>
                        </div>
                        <div style="display:flex;align-items:center;gap:6px">
                            @if($hasDiscount)
                            <span style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:3px 10px;font-size:12px;font-weight:600;color:#2563eb">
                                {{ $discType === 'percent' ? $discValue.'%' : number_format($discAmount, 0, '.', ' ')." so'm" }}
                            </span>
                            <button wire:click="removeDiscount('{{ $key }}')" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:16px;padding:0;line-height:1" title="Chegirmani o'chirish">✕</button>
                            @else
                            <button wire:click="openDiscountModal('{{ $key }}')"
                                    style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:6px;padding:5px 12px;font-size:12px;font-weight:500;color:#0369a1;cursor:pointer;display:flex;align-items:center;gap:4px">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                                Chegirma qo'llash
                            </button>
                            @endif
                        </div>
                    </div>
                    @if($hasDiscount)
                    <div style="margin-top:8px;padding-top:8px;border-top:1px solid #e5e7eb">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:#f59e0b;font-weight:500">
                            <span>Chegirma miqdori:</span>
                            <span>-{{ number_format($discAmount, 0, '.', ' ') }} so'm</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach

            {{-- Grand total --}}
            <div style="padding:12px;background:#f8fafc;border-radius:8px">
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#6b7280;margin-bottom:4px">
                    <span>Jami (chegirmasiz):</span>
                    <span>{{ number_format($totalNoDiscount, 0, '.', ' ') }} so'm</span>
                </div>
                @if($totalDiscount > 0)
                <div style="display:flex;justify-content:space-between;font-size:13px;color:#f59e0b;font-weight:500;margin-bottom:4px">
                    <span>Jami chegirma:</span>
                    <span>-{{ number_format($totalDiscount, 0, '.', ' ') }} so'm</span>
                </div>
                @endif
                <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;color:#111827;padding-top:8px;border-top:1px solid #e5e7eb">
                    <span>Umumiy summa:</span>
                    <span style="color:#16a34a">{{ number_format($totalFinal, 0, '.', ' ') }} so'm</span>
                </div>
            </div>
        </div>
        @endif

        <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px;font-size:13px;color:#166534">
            ✅ Barcha ma'lumotlar to'g'ri bo'lsa, "Saqlash" tugmasini bosing.
        </div>
    </div>
    @endif

    </div>{{-- /kb-body --}}

    <div class="kb-footer">
        <div>
            @if($step > 1)
            <button class="btn-back" wire:click="prevStep">← Orqaga</button>
            @else
            <button class="btn-back" wire:click="closeModal">Bekor qilish</button>
            @endif
        </div>
        <div>
            @if($step < 3)
            <button class="btn-next" wire:click="nextStep" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="nextStep">Keyingisi →</span>
                <span wire:loading wire:target="nextStep">Yuklanmoqda...</span>
            </button>
            @else
            <button class="btn-save" wire:click="createProject" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="createProject">💾 Saqlash</span>
                <span wire:loading wire:target="createProject">Saqlanmoqda...</span>
            </button>
            @endif
        </div>
    </div>

</div>
</div>

{{-- YUBORISH (ROUTE) MODAL --}}
@if($showRouteModal)
@php $routeProj = \App\Models\Project::find($routeProjectId); @endphp
<div style="position:fixed;inset:0;z-index:1300;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5)">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0">Loyihani yo'naltirish</h3>
            <button wire:click="closeRouteModal" style="border:none;background:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">×</button>
        </div>
        @if($routeProj)
        <div style="background:#f9fafb;border-radius:8px;padding:10px 14px;margin-bottom:18px;font-size:12px;color:#374151">
            <strong>{{ $routeProj->owner_name }}</strong> — {{ $routeProj->address }}
        </div>
        @endif
        <div style="display:flex;flex-direction:column;gap:14px">
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Yuborilayotgan bosqich *</label>
                <select wire:model="routeNewStatus" style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box">
                    <option value="">— Tanlang —</option>
                    @foreach($routeStatuses as $sk => $st)
                    @if($routeProj && $sk !== $routeProj->status)
                    <option value="{{ $sk }}">{{ $st['label'] }}</option>
                    @endif
                    @endforeach
                </select>
                @error('routeNewStatus')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Bu bosqich uchun muddat (kun)</label>
                <input wire:model="routeAllocDays" type="number" min="0" max="365"
                       style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;box-sizing:border-box"
                       placeholder="0 = cheklanmagan">
                <span style="font-size:10px;color:#9ca3af">0 kiritsangiz muddat belgilanmaydi</span>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Mas'ul xodim</label>
                <select wire:model="routeAssignedUserId" style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;background:#fff;box-sizing:border-box">
                    <option value="">— O'zgartirmaslik —</option>
                    @foreach($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role_name }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:20px">
            <button wire:click="closeRouteModal"
                    style="flex:1;padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            <button wire:click="confirmRoute"
                    style="flex:2;padding:11px;border-radius:8px;border:none;background:#3b82f6;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Yuborish
            </button>
        </div>
    </div>
</div>
@endif

{{-- XIZMAT HODIM TAYINLASH MODAL --}}
@if($showServiceAssignModal)
@php $saProject = \App\Models\Project::with('services')->find($serviceAssignProjectId); @endphp
@if($saProject)
<div style="position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:9000;display:flex;align-items:center;justify-content:center;padding:16px">
<div style="background:#fff;border-radius:16px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.3)">

    <div style="display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-bottom:1px solid #e5e7eb">
        <div>
            <div style="font-size:15px;font-weight:800;color:#111827">Hodim tayinlash</div>
            <div style="font-size:12px;color:#6b7280;margin-top:2px">{{ $saProject->owner_name }}</div>
        </div>
        <button wire:click="closeServiceAssignModal" style="background:none;border:none;cursor:pointer;font-size:22px;color:#9ca3af">×</button>
    </div>

    <div style="padding:16px 20px;display:flex;flex-direction:column;gap:12px">
        @foreach($saProject->services as $svc)
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px">
            <div style="font-size:13px;font-weight:700;color:#374151;margin-bottom:10px">
                {{ \App\Models\Project::serviceOptions()[$svc->service_name] ?? $svc->service_name }}
                @if(isset($serviceAssignData[$svc->id]['user_id']) && $serviceAssignData[$svc->id]['user_id'])
                <span style="font-size:10px;background:#dcfce7;color:#16a34a;border-radius:4px;padding:1px 7px;margin-left:6px;font-weight:600">Biriktirilgan</span>
                @endif
            </div>
            <div style="display:grid;grid-template-columns:1fr 100px;gap:8px">
                <div>
                    <label style="display:block;font-size:11px;color:#6b7280;margin-bottom:4px">Mas'ul hodim</label>
                    <select wire:model.live="serviceAssignData.{{ $svc->id }}.user_id"
                            style="width:100%;border:1px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:13px;background:#fff;outline:none">
                        <option value="">— Tanlang —</option>
                        @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display:block;font-size:11px;color:#6b7280;margin-bottom:4px">Kun</label>
                    <input type="number" min="1" max="365"
                           wire:model.live="serviceAssignData.{{ $svc->id }}.days"
                           style="width:100%;border:1px solid #d1d5db;border-radius:7px;padding:7px 10px;font-size:13px;text-align:center;outline:none">
                </div>
            </div>
            @if(isset($serviceAssignData[$svc->id]['user_id']) && $serviceAssignData[$svc->id]['user_id'] && isset($serviceAssignData[$svc->id]['days']))
            <div style="font-size:11px;color:#6b7280;margin-top:6px">
                Muddat: {{ $serviceAssignData[$svc->id]['days'] }} kun
                @if($svc->work_started_at)
                · Boshlangan: {{ $svc->work_started_at->format('d.m.Y') }}
                · Tugash: {{ $svc->work_started_at->copy()->addDays((int)$serviceAssignData[$svc->id]['days'])->format('d.m.Y') }}
                @endif
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div style="display:flex;justify-content:space-between;padding:14px 20px;border-top:1px solid #e5e7eb">
        <button wire:click="closeServiceAssignModal" style="background:#f3f4f6;color:#374151;border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;cursor:pointer">Bekor qilish</button>
        <button wire:click="saveServiceAssign" style="background:#2563eb;color:#fff;border:none;border-radius:8px;padding:9px 24px;font-size:13px;font-weight:700;cursor:pointer">Saqlash</button>
    </div>

</div>
</div>
@endif
@endif

{{-- TO'LOV MODAL --}}
@if($showPaymentModal)
@php $payProj = \App\Models\Project::with('payments')->find($paymentProjectId); @endphp
<div style="position:fixed;inset:0;z-index:1300;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.5)">
    <div style="background:#fff;border-radius:16px;width:100%;max-width:460px;max-height:92vh;overflow-y:auto;padding:28px;box-shadow:0 20px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h3 style="font-size:16px;font-weight:700;color:#111827;margin:0">To'lov qo'shish</h3>
            <button wire:click="closePaymentModal" style="border:none;background:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">×</button>
        </div>

        @if($payProj)
        {{-- Project summary --}}
        <div style="background:#f9fafb;border-radius:10px;padding:12px 16px;margin-bottom:20px">
            <div style="font-size:13px;font-weight:600;color:#111827;margin-bottom:4px">{{ $payProj->owner_name }}</div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:6px">
                <span>Umumiy summa:</span>
                <span style="font-weight:600;color:#111827">{{ number_format($payProj->total_price, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280;margin-bottom:6px">
                <span>To'langan:</span>
                <span style="font-weight:600;color:#16a34a">{{ number_format($payProj->paid_amount, 0, '.', ' ') }} so'm ({{ $payProj->payment_percent }}%)</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:12px;color:#6b7280">
                <span>Qoldiq:</span>
                <span style="font-weight:600;color:#dc2626">{{ number_format($payProj->remaining_amount, 0, '.', ' ') }} so'm</span>
            </div>
            <div style="background:#e5e7eb;border-radius:4px;height:6px;margin-top:8px;overflow:hidden">
                <div style="background:#16a34a;height:100%;width:{{ $payProj->payment_percent }}%;border-radius:4px"></div>
            </div>
        </div>

        {{-- Form --}}
        <div style="display:flex;flex-direction:column;gap:14px">

            {{-- Xizmat tanlash --}}
            @if($payProj->services->count())
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:8px">Qaysi xizmat uchun to'lov?</label>
                <div style="display:flex;flex-direction:column;gap:6px">
                    @foreach($payProj->services as $svc)
                    <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:8px 12px;transition:border-color .15s"
                         :style="$wire.paymentSelectedServices.includes('{{ $svc->service_name }}') ? 'border-color:#2563eb;background:#eff6ff' : ''">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
                            <input type="checkbox"
                                   wire:model.live="paymentSelectedServices"
                                   value="{{ $svc->service_name }}"
                                   style="width:15px;height:15px;cursor:pointer;accent-color:#2563eb">
                            <span style="font-weight:600">{{ \App\Models\Project::serviceOptions()[$svc->service_name] ?? $svc->service_name }}</span>
                            <span style="margin-left:auto;font-size:12px;color:#6b7280">{{ number_format($svc->final_price, 0, '.', ' ') }} so'm</span>
                        </label>
                        @if(auth()->user()?->isAdmin())
                        <div style="margin-top:6px">
                            <button type="button"
                                    wire:click.stop="openServicePrice({{ $svc->id }})"
                                    onclick="event.stopPropagation()"
                                    style="font-size:11px;padding:4px 10px;border-radius:6px;border:1px solid #c7d2fe;background:#eef2ff;color:#4338ca;cursor:pointer;font-weight:600">
                                🔧 Joriy narxni o'rnatish (PIN)
                            </button>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Summa (so'm) *</label>
                <input wire:model.live="paymentAmount" type="number" min="1"
                       style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                       placeholder="Masalan: 350000"
                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
                @error('paymentAmount')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
                @if($paymentAmount && $payProj->total_price > 0)
                @php $pct = min(100, round((float)$paymentAmount / (float)$payProj->total_price * 100)); @endphp
                <div style="font-size:11px;color:#6b7280;margin-top:4px">
                    ≈ {{ $pct }}% (jami: {{ number_format($payProj->paid_amount + (float)$paymentAmount, 0, '.', ' ') }} so'm)
                </div>
                @endif
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Sana *</label>
                    <input wire:model="paymentDate" type="date"
                           style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box">
                    @error('paymentDate')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">To'lov usuli</label>
                    <select wire:model="paymentMethod"
                            style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;box-sizing:border-box;background:#fff">
                        <option value="naqd">Naqd pul</option>
                        <option value="bank">Bank o'tkazma</option>
                        <option value="karta">Karta</option>
                    </select>
                </div>
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;color:#374151;display:block;margin-bottom:6px">Izoh</label>
                <textarea wire:model="paymentNote" rows="2"
                          style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:13px;outline:none;resize:none;box-sizing:border-box"
                          placeholder="Ixtiyoriy..."></textarea>
            </div>
            {{-- Xizmat mas'ullari --}}
            <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:12px 14px">
                <div style="font-size:12px;font-weight:600;color:#374151;margin-bottom:10px;display:flex;align-items:center;gap:6px">
                    <svg width="13" height="13" fill="none" stroke="#6b7280" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    Xizmat mas'ullari
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                    <div>
                        <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:4px;font-weight:500">Toposyomka</label>
                        <select wire:model="paymentToposyomkaUserId"
                                style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;background:#fff;box-sizing:border-box;color:#111827">
                            <option value="">— Tanlang —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="font-size:11px;color:#6b7280;display:block;margin-bottom:4px;font-weight:500">Eskiz loyiha</label>
                        <select wire:model="paymentEskizUserId"
                                style="width:100%;padding:7px 10px;border:1.5px solid #e5e7eb;border-radius:7px;font-size:12px;background:#fff;box-sizing:border-box;color:#111827">
                            <option value="">— Tanlang —</option>
                            @foreach($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            @if($paymentFromQueue)
            <div style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f0fdf4;border:1px solid #86efac;border-radius:8px">
                <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
                <span style="font-size:13px;font-weight:500;color:#166534">To'lov saqlanadi va loyiha <strong>To'langan</strong> bo'limiga o'tkaziladi</span>
            </div>
            @elseif($payProj && $payProj->status === 'tolov_jarayonida')
            <label style="display:flex;align-items:center;gap:10px;padding:10px 14px;background:#f5f3ff;border-radius:8px;cursor:pointer;border:1px solid #ddd6fe">
                <input type="checkbox" wire:model="paymentMoveToEskiz" style="width:16px;height:16px;accent-color:#7c3aed">
                <span style="font-size:13px;font-weight:500;color:#5b21b6">To'lovdan keyin → <strong>Toposyomka</strong> bo'limiga o'tkazish</span>
            </label>
            @endif
        </div>
        @endif

        {{-- Existing payments list --}}
        @if($payProj && $payProj->payments->count() > 0)
        <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:8px;padding:10px 14px">
            <div style="font-size:11px;font-weight:600;color:#6b7280;margin-bottom:8px;text-transform:uppercase;letter-spacing:.5px">
                Kiritilgan to'lovlar
            </div>
            @foreach($payProj->payments->sortByDesc('payment_date') as $pmt)
            <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9' : '' }}">
                <div style="font-size:12px;color:#374151">
                    <span style="font-weight:600;color:#111827">{{ number_format((float)$pmt->amount, 0, '.', ' ') }} so'm</span>
                    <span style="color:#9ca3af;margin-left:6px">{{ $pmt->payment_date?->format('d/m/Y') }}</span>
                    @if($pmt->createdBy)
                    <span style="color:#9ca3af;margin-left:4px">· {{ $pmt->createdBy->name }}</span>
                    @endif
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    <button onclick="event.stopPropagation()"
                            wire:click.stop="openEditPayment({{ $pmt->id }})"
                            style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #e5e7eb;background:#fff;color:#2563eb;cursor:pointer;white-space:nowrap">
                        ✏️ Tahrirlash
                    </button>
                    <button onclick="event.stopPropagation()"
                            wire:click.stop="openDeletePayment({{ $pmt->id }})"
                            style="font-size:10px;padding:3px 8px;border-radius:5px;border:1px solid #fecaca;background:#fff;color:#dc2626;cursor:pointer;white-space:nowrap">
                        🗑 O'chirish
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        {{-- Amount confirm warning --}}
        @if($paymentAmountConfirm)
        <div style="background:#fffbeb;border:1.5px solid #fcd34d;border-radius:8px;padding:14px 16px">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
                <span style="font-size:18px">⚠️</span>
                <span style="font-size:13px;font-weight:600;color:#92400e">Summa kiritilmadi!</span>
            </div>
            <p style="font-size:13px;color:#78350f;margin:0 0 12px">Summasisiz faqat hodim biriktirma ma'lumotlari saqlanadi. Davom etasizmi?</p>
            <div style="display:flex;gap:8px">
                <button wire:click="savePayment"
                        style="flex:1;padding:9px;border-radius:7px;border:none;background:#16a34a;color:#fff;font-size:13px;font-weight:600;cursor:pointer">
                    Ha, saqlash
                </button>
                <button wire:click="cancelPaymentAmountConfirm"
                        style="flex:1;padding:9px;border-radius:7px;border:1px solid #d1d5db;background:#fff;color:#374151;font-size:13px;cursor:pointer">
                    Yo'q, qaytish
                </button>
            </div>
        </div>
        @endif

        <div style="display:flex;gap:10px;margin-top:20px;position:sticky;bottom:-28px;background:#fff;padding:14px 0 4px;border-top:1px solid #eef2f7;z-index:2">
            <button wire:click="closePaymentModal"
                    style="flex:1;padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            @if(!$paymentAmountConfirm)
            <button wire:click="savePayment"
                    style="flex:2;padding:11px;border-radius:8px;border:none;background:#16a34a;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Saqlash
            </button>
            @endif
        </div>
    </div>
</div>
@endif

{{-- TAHRIRLASH MODAL (to'lov summasi) --}}
@if($showEditPaymentModal)
@php $editPmt = \App\Models\Payment::with('project')->find($editPaymentId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1400;display:flex;align-items:center;justify-content:center;padding:16px">
    <div style="background:#fff;border-radius:14px;width:100%;max-width:380px;max-height:90vh;overflow-y:auto;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <div style="display:flex;align-items:center;gap:8px">
                <svg width="18" height="18" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                <span style="font-size:15px;font-weight:700;color:#111827">Summani tahrirlash</span>
            </div>
            <button wire:click="closeEditPayment" style="background:none;border:none;cursor:pointer;color:#6b7280;font-size:20px;line-height:1">✕</button>
        </div>
        @if($editPmt)
        <div style="background:#f9fafb;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12px;color:#374151">
            <strong>{{ $editPmt->project?->owner_name }}</strong>
            <span style="color:#9ca3af;margin-left:8px">{{ $editPmt->payment_date?->format('d/m/Y') }}</span>
        </div>
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
            <span style="font-size:12px;color:#c2410c">Hozirgi summa:</span>
            <span style="font-size:13px;font-weight:700;color:#c2410c">{{ number_format((float)$editPmt->amount, 0, '.', ' ') }} so'm</span>
        </div>
        @endif
        <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Yangi summa (so'm)</label>
            <input wire:model.live="editPaymentAmount" type="number" min="1"
                   style="width:100%;padding:10px 12px;border:2px solid #e5e7eb;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box"
                   placeholder="Yangi summa kiriting"
                   onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e5e7eb'">
            @error('editPaymentAmount')<span style="font-size:11px;color:#dc2626">{{ $message }}</span>@enderror
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <button wire:click="closeEditPayment"
                    style="padding:11px;border-radius:8px;border:1px solid #e5e7eb;background:#fff;color:#374151;cursor:pointer;font-size:13px;font-weight:500">
                Bekor qilish
            </button>
            <button wire:click="saveEditPayment"
                    style="padding:11px;border-radius:8px;border:none;background:#2563eb;color:#fff;cursor:pointer;font-size:13px;font-weight:600">
                Saqlash
            </button>
        </div>
    </div>
</div>
@endif

{{-- TO'LOVNI O'CHIRISH — PIN MODAL --}}
@if($showDeletePaymentModal)
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1500;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="closeDeletePayment">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:320px;box-shadow:0 25px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:6px">🔐 PIN kod kiriting</div>
        <div style="font-size:13px;color:#6b7280;margin-bottom:16px">To'lovni butunlay o'chirish uchun PIN kod talab etiladi</div>
        <input type="password" wire:model="deletePaymentPin"
               wire:keydown.enter="confirmDeletePayment"
               style="width:100%;border:1.5px solid {{ $deletePaymentPinError ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:10px 14px;font-size:18px;letter-spacing:6px;text-align:center;outline:none;margin-bottom:8px"
               placeholder="····" autofocus maxlength="4">
        @if($deletePaymentPinError)
        <div style="font-size:12px;color:#ef4444;margin-bottom:10px">❌ Noto'g'ri PIN kod</div>
        @endif
        <div style="display:flex;gap:8px;margin-top:12px">
            <button wire:click="closeDeletePayment"
                    style="flex:1;padding:10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:13px;cursor:pointer">
                Bekor
            </button>
            <button wire:click="confirmDeletePayment"
                    style="flex:1;padding:10px;border-radius:8px;border:none;background:#ef4444;color:#fff;font-size:13px;font-weight:600;cursor:pointer">
                O'chirish
            </button>
        </div>
    </div>
</div>
@endif

{{-- XIZMAT NARXINI O'RNATISH — PIN MODAL --}}
@if($showServicePriceModal)
@php $spSvc = \App\Models\ProjectService::find($servicePriceId); @endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:1500;display:flex;align-items:center;justify-content:center;padding:16px" wire:click.self="closeServicePrice">
    <div style="background:#fff;border-radius:16px;padding:28px 32px;width:340px;box-shadow:0 25px 60px rgba(0,0,0,.2)" wire:click.stop>
        <div style="font-size:16px;font-weight:700;color:#111827;margin-bottom:6px">🔧 Joriy narxni o'rnatish</div>
        @if($spSvc)
        <div style="font-size:13px;color:#6b7280;margin-bottom:14px">
            {{ \App\Models\Project::serviceOptions()[$spSvc->service_name] ?? $spSvc->service_name }} —
            hozirgi: <strong>{{ number_format((float)$spSvc->final_price, 0, '.', ' ') }} so'm</strong>
        </div>
        @endif
        <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">Yangi narx (so'm)</label>
        <input wire:model="servicePriceValue" type="number" min="0"
               style="width:100%;border:1.5px solid #e2e8f0;border-radius:8px;padding:10px 14px;font-size:15px;outline:none;margin-bottom:12px;box-sizing:border-box"
               placeholder="Masalan: 4078800">
        <label style="font-size:12px;font-weight:500;color:#374151;display:block;margin-bottom:6px">PIN kod</label>
        <input type="password" wire:model="servicePricePin"
               wire:keydown.enter="saveServicePrice"
               style="width:100%;border:1.5px solid {{ $servicePricePinError ? '#ef4444' : '#e2e8f0' }};border-radius:8px;padding:10px 14px;font-size:18px;letter-spacing:6px;text-align:center;outline:none;margin-bottom:8px;box-sizing:border-box"
               placeholder="····" maxlength="4">
        @if($servicePricePinError)
        <div style="font-size:12px;color:#ef4444;margin-bottom:10px">❌ Noto'g'ri PIN kod</div>
        @endif
        <div style="display:flex;gap:8px;margin-top:12px">
            <button wire:click="closeServicePrice"
                    style="flex:1;padding:10px;border-radius:8px;border:1px solid #e5e7eb;background:#f9fafb;color:#374151;font-size:13px;cursor:pointer">
                Bekor
            </button>
            <button wire:click="saveServicePrice"
                    style="flex:1;padding:10px;border-radius:8px;border:none;background:#4338ca;color:#fff;font-size:13px;font-weight:600;cursor:pointer">
                Saqlash
            </button>
        </div>
    </div>
</div>
@endif

@livewire('project-edit-modal')

{{-- KV.M AREA MODAL --}}
@if($showAreaModal)
@php
    $ak       = $areaServiceKey;
    $aSrv     = $services[$ak] ?? [];
    $aRate    = 0;
    $aTierLbl = '';
    if (!empty($aSrv['selected_tiers']) && isset($priceTiers[$ak])) {
        foreach ($aSrv['selected_tiers'] as $subKey => $tierId) {
            $td = collect($priceTiers[$ak][$subKey] ?? [])->firstWhere('id', $tierId);
            if ($td) { $aRate += $td['price']; $aTierLbl = $td['label']; break; }
        }
    }
    $aPreview = (float)$areaValue > 0 ? (int)round($aRate * (float)$areaValue) : 0;
@endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1200;display:flex;align-items:center;justify-content:center;padding:16px"
     wire:click.self="closeAreaModal">
<div style="background:#fff;border-radius:16px;width:100%;max-width:420px;max-height:90vh;overflow-y:auto;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>

    {{-- Header --}}
    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:4px">
        <div style="display:flex;align-items:center;gap:8px">
            <svg width="20" height="20" fill="none" stroke="#2563eb" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2a2 2 0 0 1 1.93 1.47l.3 1.11a7 7 0 0 1 .87.51l1.09-.4a2 2 0 0 1 2.29.9l1 1.72a2 2 0 0 1-.34 2.39l-.81.72a7 7 0 0 1 0 1l.81.72a2 2 0 0 1 .34 2.39l-1 1.72a2 2 0 0 1-2.29.9l-1.09-.4a7 7 0 0 1-.87.51l-.3 1.11A2 2 0 0 1 12 22a2 2 0 0 1-1.93-1.47l-.3-1.11a7 7 0 0 1-.87-.51l-1.09.4a2 2 0 0 1-2.29-.9l-1-1.72a2 2 0 0 1 .34-2.39l.81-.72a7 7 0 0 1 0-1l-.81-.72a2 2 0 0 1-.34-2.39l1-1.72a2 2 0 0 1 2.29-.9l1.09.4a7 7 0 0 1 .87-.51l.3-1.11A2 2 0 0 1 12 2z"/><circle cx="12" cy="12" r="3"/></svg>
            <span style="font-size:15px;font-weight:700;color:#111827">Maydon kiritish</span>
        </div>
        <button wire:click="closeAreaModal" style="background:none;border:none;cursor:pointer;color:#6b7280;font-size:20px;padding:0;line-height:1">✕</button>
    </div>
    <p style="font-size:12px;color:#6b7280;margin-bottom:16px">
        {{ $aSrv['label'] ?? '' }}{{ $aTierLbl ? ' — ' . $aTierLbl : '' }}
    </p>

    {{-- Rate info --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:13px;font-weight:500;color:#1d4ed8">Narx (1 m² uchun):</span>
        <span style="font-size:13px;font-weight:700;color:#1d4ed8">{{ number_format($aRate, 0, '.', ' ') }} so'm</span>
    </div>

    {{-- Area input --}}
    <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;display:block">Kvadrat metr (m²)</label>
        <div style="position:relative">
            <input wire:model.live="areaValue"
                   class="kb-input"
                   style="padding-right:50px;font-size:16px"
                   type="number"
                   min="0.1"
                   step="0.1"
                   placeholder="Masalan: 120"
                   autofocus>
            <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:600;color:#2563eb">m²</span>
        </div>
        <p style="font-size:11px;color:#9ca3af;margin-top:4px">Umumiy narx = narx × m²</p>
    </div>

    {{-- Live preview --}}
    @if((float)$areaValue > 0 && $aRate > 0)
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:12px 14px;margin-bottom:16px">
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px">
            <span style="color:#166534;font-weight:500">Umumiy narx:</span>
            <span style="color:#16a34a;font-weight:700;font-size:15px">{{ number_format($aPreview, 0, '.', ' ') }} so'm</span>
        </div>
        <div style="font-size:11px;color:#6b7280;margin-top:4px;text-align:right">
            {{ number_format($aRate, 0, '.', ' ') }} × {{ (float)$areaValue }} m²
        </div>
    </div>
    @endif

    {{-- Buttons --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <button wire:click="closeAreaModal"
                style="padding:12px;border-radius:10px;border:1.5px solid #e5e7eb;background:#fff;font-size:14px;font-weight:600;color:#374151;cursor:pointer">
            Bekor qilish
        </button>
        <button wire:click="saveArea"
                style="padding:12px;border-radius:10px;border:none;background:#16a34a;color:#fff;font-size:14px;font-weight:600;cursor:pointer">
            Saqlash
        </button>
    </div>
</div>
</div>
@endif

{{-- CHEGIRMA MODAL (inside overlay, above kb-modal) --}}
@if($showDiscountModal)
@php
    $dp = $this->discountPreview;
    $dKey = $discountServiceKey;
    $dPrice = (float)($services[$dKey]['price'] ?? 0);
@endphp
<div style="position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:1200;display:flex;align-items:center;justify-content:center;padding:16px"
     wire:click.self="closeDiscountModal">
<div style="background:#fff;border-radius:16px;width:100%;max-width:440px;max-height:90vh;overflow-y:auto;padding:24px;box-shadow:0 25px 80px rgba(0,0,0,.3)" wire:click.stop>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px">
        <div style="display:flex;align-items:center;gap:8px">
            <svg width="20" height="20" fill="none" stroke="#3b82f6" stroke-width="2" viewBox="0 0 24 24"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01M12 10h.01M8 10h.01M12 14h.01M8 14h.01M12 18h.01M8 18h.01"/></svg>
            <span style="font-size:15px;font-weight:700;color:#111827">Chegirma qo'llash</span>
        </div>
        <button wire:click="closeDiscountModal" style="background:none;border:none;cursor:pointer;color:#6b7280;font-size:20px;padding:0;line-height:1">✕</button>
    </div>
    <p style="font-size:12px;color:#6b7280;margin-bottom:16px">{{ $services[$dKey]['label'] ?? '' }} xizmati uchun chegirma belgilang</p>

    {{-- Xizmat narxi --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:center">
        <span style="font-size:13px;font-weight:500;color:#1d4ed8">Xizmat narxi:</span>
        <span style="font-size:13px;font-weight:700;color:#1d4ed8">{{ number_format($dPrice, 0, '.', ' ') }} so'm</span>
    </div>

    {{-- Chegirma turi --}}
    <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:500;color:#374151;margin-bottom:8px;display:block">Chegirma turi</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
            <button wire:click="$set('discountType','percent')"
                    style="padding:12px;border-radius:10px;border:2px solid {{ $discountType==='percent' ? '#3b82f6' : '#e5e7eb' }};background:{{ $discountType==='percent' ? '#eff6ff' : '#fff' }};cursor:pointer;color:{{ $discountType==='percent' ? '#1d4ed8' : '#374151' }}">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:block;margin:0 auto 4px"><line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                <span style="font-size:12px;font-weight:500">Foizda</span>
            </button>
            <button wire:click="$set('discountType','fixed')"
                    style="padding:12px;border-radius:10px;border:2px solid {{ $discountType==='fixed' ? '#3b82f6' : '#e5e7eb' }};background:{{ $discountType==='fixed' ? '#eff6ff' : '#fff' }};cursor:pointer;color:{{ $discountType==='fixed' ? '#1d4ed8' : '#374151' }}">
                <span style="font-size:18px;font-weight:700;display:block;margin-bottom:4px">so'm</span>
                <span style="font-size:12px;font-weight:500">Summada</span>
            </button>
        </div>
    </div>

    {{-- Chegirma qiymati --}}
    <div style="margin-bottom:14px">
        <label style="font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;display:block">Chegirma qiymati</label>
        <div style="position:relative">
            <input wire:model.live="discountValue"
                   class="kb-input"
                   style="padding-right:50px"
                   type="number"
                   min="0"
                   max="{{ $discountType==='percent' ? 100 : $dPrice }}"
                   placeholder="{{ $discountType==='percent' ? '0-100' : '0' }}">
            <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:13px;font-weight:500;color:#6b7280">
                {{ $discountType==='percent' ? '%' : "so'm" }}
            </span>
        </div>
    </div>

    {{-- Preview rows --}}
    @if((float)$discountValue > 0)
    <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:10px 14px;margin-bottom:8px;display:flex;justify-content:space-between">
        <span style="font-size:13px;font-weight:500;color:#c2410c">Chegirma miqdori:</span>
        <span style="font-size:13px;font-weight:700;color:#c2410c">-{{ number_format($dp['amount'], 0, '.', ' ') }} so'm</span>
    </div>
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 14px;margin-bottom:16px;display:flex;justify-content:space-between">
        <span style="font-size:13px;font-weight:500;color:#166534">Chegirmadan so'ng:</span>
        <span style="font-size:13px;font-weight:700;color:#16a34a">{{ number_format($dp['final'], 0, '.', ' ') }} so'm</span>
    </div>
    @else
    <div style="margin-bottom:16px"></div>
    @endif

    {{-- Buttons --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
        <button wire:click="closeDiscountModal"
                style="padding:10px;border:1px solid #e5e7eb;border-radius:8px;background:#fff;font-size:13px;font-weight:500;color:#374151;cursor:pointer">
            Bekor qilish
        </button>
        <button wire:click="applyDiscount"
                style="padding:10px;border:none;border-radius:8px;background:#2563eb;color:#fff;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            Saqlash
        </button>
    </div>
</div>
</div>
@endif

<div id="kb-notify-box" class="kb-notify" style="display:none"></div>

<style>@keyframes spin{to{transform:rotate(360deg)}}</style>

<script>
var kbMap = null;
var kbMarker = null;
var kbWireId = '{{ $this->getId() }}';

function kbSetCoords(lat, lng) {
    var setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
    var latEl = document.getElementById('kb-lat-input');
    var lngEl = document.getElementById('kb-lng-input');
    if (latEl) { setter.call(latEl, lat.toFixed(6)); latEl.dispatchEvent(new Event('input',{bubbles:true})); }
    if (lngEl) { setter.call(lngEl, lng.toFixed(6)); lngEl.dispatchEvent(new Event('input',{bubbles:true})); }
    // Birlashgan ko'rinadigan inputni yangilash
    var comb = document.getElementById('kb-coords-combined');
    if (comb) { setter.call(comb, lat.toFixed(6) + ', ' + lng.toFixed(6)); }
}

var _coordTimer = null;
function kbOnCombinedCoord(val) {
    clearTimeout(_coordTimer);
    _coordTimer = setTimeout(function() {
        var parts = val.split(',');
        if (parts.length !== 2) return;
        var lat = parseFloat(parts[0].trim());
        var lng = parseFloat(parts[1].trim());
        if (isNaN(lat) || isNaN(lng)) return;
        if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        var setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        var latEl = document.getElementById('kb-lat-input');
        var lngEl = document.getElementById('kb-lng-input');
        if (latEl) { setter.call(latEl, lat.toFixed(6)); latEl.dispatchEvent(new Event('input',{bubbles:true})); }
        if (lngEl) { setter.call(lngEl, lng.toFixed(6)); lngEl.dispatchEvent(new Event('input',{bubbles:true})); }
        if (!kbMap) return;
        var coords = [lat, lng];
        kbMap.setCenter(coords, 17);
        if (kbMarker) { kbMarker.geometry.setCoordinates(coords); }
        else { kbMarker = new ymaps.Placemark(coords, {}, {preset:'islands#blueDotIcon'}); kbMap.geoObjects.add(kbMarker); }
    }, 600);
}

function kbCopyCoords(btn) {
    var comb = document.getElementById('kb-coords-combined');
    if (!comb || !comb.value) return;
    navigator.clipboard.writeText(comb.value).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg> Nusxalandi!';
        btn.style.color = '#16a34a';
        setTimeout(function(){ btn.innerHTML = orig; btn.style.color = '#6b7280'; }, 2000);
    });
}

function kbSetAddress(addr) {
    if (window.Livewire) {
        try {
            var comp = window.Livewire.find(kbWireId);
            if (comp) { comp.call('setAddress', addr); }
        } catch(e) {
            try {
                var all = window.Livewire.all ? window.Livewire.all() : [];
                for (var i = 0; i < all.length; i++) {
                    try { if (all[i].get('showModal') === true) { all[i].call('setAddress', addr); break; } } catch(e2) {}
                }
            } catch(e3) {}
        }
    }
    var inp = document.getElementById('kb-address-input');
    if (inp) { inp.value = addr; inp.dispatchEvent(new Event('input', { bubbles: true })); }
}

function kbSearchOnMap() {
    var q = document.getElementById('kb-map-search').value.trim();
    if (!q || !kbMap) return;
    var btn = document.querySelector('#kb-map-search + button');
    if (btn) btn.textContent = '...';
    ymaps.geocode(q + ', O\'zbekiston', { results: 1 }).then(function(res) {
        if (btn) btn.textContent = 'Qidirish';
        var obj = res.geoObjects.get(0);
        if (!obj) { alert('Manzil topilmadi. Aniqroq yozing.'); return; }
        kbMap.setCenter(obj.geometry.getCoordinates(), 17);
    }).catch(function() {
        if (btn) btn.textContent = 'Qidirish';
        alert('Qidirishda xatolik');
    });
}

function kbParseYandexCoords(url) {
    var lat = null, lng = null;
    // whatshere[point]=lng,lat
    var m = url.match(/whatshere(?:%5B|\[)point(?:%5D|\])=([0-9.\-]+)%2C([0-9.\-]+)/i)
           || url.match(/whatshere\[point\]=([0-9.\-]+),([0-9.\-]+)/i);
    if (m) { lng = parseFloat(m[1]); lat = parseFloat(m[2]); }
    // ll=lng,lat
    if (!lat) {
        m = url.match(/[?&]ll=([0-9.\-]+)%2C([0-9.\-]+)/i) || url.match(/[?&]ll=([0-9.\-]+),([0-9.\-]+)/i);
        if (m) { lng = parseFloat(m[1]); lat = parseFloat(m[2]); }
    }
    return (lat && lng && !isNaN(lat) && !isNaN(lng)) ? [lat, lng] : null;
}

function kbLoadYandexUrl() {
    var input = document.getElementById('kb-yandex-url');
    if (!input) return;
    var url = input.value.trim();
    if (!url) return;
    var coords = kbParseYandexCoords(url);
    if (!coords) { alert("URL dan koordinata topilmadi. Yandex Maps havolasini tekshiring."); return; }
    input.value = '';
    if (!kbMap) {
        loadAndInitMap();
        setTimeout(function(){ kbLocatePlaceAt(coords[0], coords[1]); }, 1200);
    } else {
        kbLocatePlaceAt(coords[0], coords[1]);
    }
}

function kbLocateMe() {
    if (!navigator.geolocation) { alert("Brauzer geolokatsiyani qo'llab-quvvatlamaydi"); return; }
    var btn = document.getElementById('kb-locate-btn');
    if (btn) { btn.disabled = true; btn.style.opacity = '0.6'; }

    navigator.geolocation.getCurrentPosition(function(pos) {
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
        var lat = pos.coords.latitude, lng = pos.coords.longitude;
        var coords = [lat, lng];

        if (!kbMap) { loadAndInitMap(); setTimeout(function(){ kbLocatePlaceAt(lat, lng); }, 1200); return; }
        kbLocatePlaceAt(lat, lng);
    }, function(err) {
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
        var msg = err.code === 1 ? "Ruxsat berilmadi — brauzer sozlamalarini tekshiring"
                : err.code === 2 ? "Joylashuv aniqlanmadi"
                : "Vaqt tugadi";
        alert(msg);
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
}

function kbLocatePlaceAt(lat, lng) {
    var coords = [lat, lng];
    kbMap.setCenter(coords, 17);
    if (kbMarker) { kbMarker.geometry.setCoordinates(coords); }
    else { kbMarker = new ymaps.Placemark(coords, {}, { preset: 'islands#blueDotIcon' }); kbMap.geoObjects.add(kbMarker); }
    kbSetCoords(lat, lng);

    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&accept-language=uz&addressdetails=1')
        .then(function(r) { return r.json(); })
        .then(function(d) {
            var a = d.address || {}, parts = [];
            if (a.road && a.house_number) parts.push(a.road + ' ' + a.house_number);
            else if (a.road) parts.push(a.road);
            else if (a.neighbourhood || a.suburb) parts.push(a.neighbourhood || a.suburb);
            var city = a.city || a.town || a.village || '';
            if (city) parts.push(city);
            if (a.state) parts.push(a.state);
            var addr = parts.length ? parts.join(', ') : (d.display_name || (lat.toFixed(6)+', '+lng.toFixed(6)));
            kbSetAddress(addr);
            var box = document.getElementById('selected-location-box');
            if (box) {
                document.getElementById('selected-location-text').textContent = addr;
                document.getElementById('selected-location-coords').textContent = 'Koordinatalar: '+lat.toFixed(6)+', '+lng.toFixed(6);
                box.style.display = 'block';
            }
        })
        .catch(function() { kbSetAddress(lat.toFixed(6)+', '+lng.toFixed(6)); });
}

function kbSetMapType(type) {
    if (!kbMap) return;
    var types = { 'hybrid': 'yandex#hybrid', 'map': 'yandex#map', 'sat': 'yandex#satellite' };
    kbMap.setType(types[type] || 'yandex#hybrid');
    document.querySelectorAll('.kb-maptype').forEach(function(b) { b.classList.remove('active'); });
    var ab = document.getElementById('kb-btn-' + type);
    if (ab) ab.classList.add('active');
}

function initKbMap() {
    var container = document.getElementById('modal-map');
    if (!container) return;
    if (kbMap) return;

    kbMap = new ymaps.Map('modal-map', {
        center: [40.857500, 68.929654],
        zoom: 12,
        controls: ['zoomControl']
    });
    kbMap.setType('yandex#hybrid');

    kbMap.events.add('click', function(e) {
        if (kbMap.getZoom() < 16) {
            kbMap.setCenter(e.get('coords'), 17);
            var hint = document.getElementById('kb-map-hint');
            if (hint) { hint.style.display = 'block'; setTimeout(function(){ hint.style.display = 'none'; }, 2500); }
            return;
        }
        var coords = e.get('coords');
        var lat = coords[0], lng = coords[1];

        if (kbMarker) {
            kbMarker.geometry.setCoordinates(coords);
        } else {
            kbMarker = new ymaps.Placemark(coords, {}, { preset: 'islands#blueDotIcon' });
            kbMap.geoObjects.add(kbMarker);
        }

        kbSetCoords(lat, lng);
        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json&accept-language=uz&addressdetails=1')
            .then(function(r) { return r.json(); })
            .then(function(d) {
                var a = d.address || {};
                var parts = [];
                if (a.road && a.house_number) parts.push(a.road + ' ' + a.house_number);
                else if (a.road) parts.push(a.road);
                else if (a.pedestrian) parts.push(a.pedestrian);
                else if (a.neighbourhood || a.suburb) parts.push(a.neighbourhood || a.suburb);
                var city = a.city || a.town || a.village || a.hamlet;
                if (city) parts.push(city);
                if (a.state) parts.push(a.state);
                var addr = parts.length ? parts.join(', ') : (d.display_name || (lat.toFixed(6) + ', ' + lng.toFixed(6)));
                kbSetAddress(addr);
                var box = document.getElementById('selected-location-box');
                if (box) {
                    document.getElementById('selected-location-text').textContent = addr;
                    document.getElementById('selected-location-coords').textContent =
                        'Koordinatalar: ' + lat.toFixed(6) + ', ' + lng.toFixed(6);
                    box.style.display = 'block';
                }
            })
            .catch(function() {
                var addr = lat.toFixed(6) + ', ' + lng.toFixed(6);
                kbSetAddress(addr);
            });
    });
}

function loadAndInitMap() {
    if (typeof ymaps !== 'undefined') {
        ymaps.ready(initKbMap);
        return;
    }
    if (!document.getElementById('yandex-maps-api-kb')) {
        var s = document.createElement('script');
        s.id = 'yandex-maps-api-kb';
        s.src = 'https://api-maps.yandex.ru/2.1/?lang=uz_UZ';
        s.onload = function() { ymaps.ready(initKbMap); };
        document.head.appendChild(s);
    } else {
        var t = setInterval(function() {
            if (typeof ymaps !== 'undefined') { clearInterval(t); ymaps.ready(initKbMap); }
        }, 200);
    }
}

document.addEventListener('livewire:initialized', function () {
    Livewire.on('modal-opened', function () {
        if (kbMap) { try { kbMap.destroy(); } catch(e) {} kbMap = null; }
        kbMarker = null;
        var box = document.getElementById('selected-location-box');
        if (box) box.style.display = 'none';
        setTimeout(loadAndInitMap, 250);

    });

    Livewire.on('notify', function (data) {
        var d = Array.isArray(data) ? data[0] : data;
        var box = document.getElementById('kb-notify-box');
        if (!box) return;
        box.textContent = d.message || '';
        box.style.background = d.type === 'success' ? '#16a34a' : '#dc2626';
        box.style.display = 'block';
        setTimeout(function() { box.style.display = 'none'; }, 3500);
    });

    Livewire.on('project-created', function () {
        setTimeout(function() { window.location.reload(); }, 2000);
    });
});

// ── Kanban Drag & Drop ────────────────────────────────────────────────
var kbDragId = null;
window._kbDragged = false;

function kbDragStart(e, id) {
    kbDragId = id;
    window._kbDragged = false;
    e.dataTransfer.effectAllowed = 'move';
    e.currentTarget.classList.add('dragging');
}

function kbDragEnd(e) {
    e.currentTarget.classList.remove('dragging');
    setTimeout(function() { window._kbDragged = false; }, 50);
}

function kbDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    e.currentTarget.classList.add('drag-over');
}

function kbDragLeave(e) {
    if (!e.currentTarget.contains(e.relatedTarget)) {
        e.currentTarget.classList.remove('drag-over');
    }
}

function kbDrop(e, status) {
    e.preventDefault();
    e.currentTarget.classList.remove('drag-over');
    if (!kbDragId) return;
    window._kbDragged = true;
    var id = kbDragId;
    kbDragId = null;
    var lwEl = document.querySelector('[wire\\:id]');
    if (lwEl) {
        Livewire.find(lwEl.getAttribute('wire:id')).moveProject(id, status);
    }
}

</script>

@if(!auth()->user()?->isHisobchi() && !auth()->user()?->isBajaruvchi())
<button class="kb-fab" wire:click="openModal" title="Yangi loyiha">+</button>
@endif

<!-- Mobil navigatsiya tugmalari -->
<div id="kb-nav-btns" style="display:none;position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:499;gap:10px;align-items:center;">
    <button onclick="kanbanNav(-1)" style="background:transparent;color:transparent;border:none;width:60px;height:60px;cursor:pointer;"></button>
    <span id="kb-nav-label" style="display:none;"></span>
    <button onclick="kanbanNav(1)" style="background:transparent;color:transparent;border:none;width:60px;height:60px;cursor:pointer;"></button>
</div>

<script>
(function() {
    if (window.innerWidth > 640) return;

    const wrap = document.getElementById('kanban-wrap');
    const tabBar = document.getElementById('kb-tab-bar');
    if (!wrap) return;

    // Tab bar ko'rsat
    if (tabBar) tabBar.style.display = 'block';

    function getColWidth() {
        return (wrap.querySelector('.kanban-col')?.offsetWidth || 300) + 12;
    }

    // Tab bosish
    window.kanbanScrollTo = function(colId) {
        const col = document.getElementById(colId);
        if (!col) return;
        const cols = Array.from(wrap.querySelectorAll('.kanban-col'));
        const idx = cols.indexOf(col);
        wrap.scrollTo({ left: idx * getColWidth(), behavior: 'smooth' });
        updateActiveTabs(idx);
    };

    window.kanbanNav = function(dir) {
        const colW = getColWidth();
        const idx = Math.round(wrap.scrollLeft / colW);
        const newIdx = Math.max(0, idx + dir);
        wrap.scrollTo({ left: newIdx * colW, behavior: 'smooth' });
        setTimeout(() => updateActiveTabs(newIdx), 350);
    };

    function updateActiveTabs(idx) {
        const tabs = tabBar?.querySelectorAll('button');
        tabs?.forEach((t, i) => {
            t.style.background = i === idx ? '#2563eb' : '#f9fafb';
            t.style.color = i === idx ? '#fff' : '#374151';
            t.style.borderColor = i === idx ? '#2563eb' : '#e5e7eb';
        });
        // Aktiv tabni ko'rinadigan joyga scroll
        if (tabs?.[idx]) tabs[idx].scrollIntoView({ inline: 'center', behavior: 'smooth' });
    }

    wrap.addEventListener('scroll', function() {
        const idx = Math.round(wrap.scrollLeft / getColWidth());
        updateActiveTabs(idx);
    }, { passive: true });

    // Swipe — tab linklar orqali navigate qiladi
    const tabLinks = Array.from(tabBar?.querySelectorAll('a') || []);
    let tx = 0, ty = 0;
    const swipeZone = wrap;
    swipeZone.addEventListener('touchstart', e => { tx = e.touches[0].clientX; ty = e.touches[0].clientY; }, { passive: true });
    swipeZone.addEventListener('touchend', e => {
        const dx = e.changedTouches[0].clientX - tx;
        const dy = e.changedTouches[0].clientY - ty;
        if (Math.abs(dx) < 60 || Math.abs(dy) > Math.abs(dx)) return;
        const activeIdx = tabLinks.findIndex(a => a.style.background.includes('2563eb') || a.style.background === '#2563eb' || a.href.includes(window.location.search));
        const curr = activeIdx >= 0 ? activeIdx : tabLinks.findIndex(a => a.href === window.location.href);
        const next = dx < 0 ? curr + 1 : curr - 1;
        if (next >= 0 && next < tabLinks.length) {
            window.location.href = tabLinks[next].href;
        }
    }, { passive: true });

    setTimeout(() => updateActiveTabs(0), 300);
})();
</script>

</x-filament-panels::page>
