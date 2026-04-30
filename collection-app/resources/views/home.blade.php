@extends('layout', ['title' => __('ui.home'), 'fullWidth' => true])

@section('content')
    @php
        $ownedPercent = $totalItems > 0 ? round(($ownedItems / $totalItems) * 100, 1) : 0;
        $wishPercent = $totalItems > 0 ? round(($wishItems / $totalItems) * 100, 1) : 0;
        $imagePercent = $totalItems > 0 ? round(($withImages / $totalItems) * 100, 1) : 0;
        $sparkPoints = [];
        $nSpark = count($sparkline);
        foreach ($sparkline as $i => $v) {
            $x = $nSpark <= 1 ? 50 : ($i / ($nSpark - 1)) * 100;
            $y = 100 - (($v / $sparkMax) * 72) - 14;
            $sparkPoints[] = round($x, 2).','.round($y, 2);
        }
        $sparkPoly = implode(' ', $sparkPoints);
        $areaBaseY = 86;
        $sparkArea = $sparkPoly ? $sparkPoly.' 100,'.$areaBaseY.' 0,'.$areaBaseY : '';
    @endphp
    <style>
        .dash{max-width:1280px;margin:0 auto;padding-bottom:32px}
        .dash-hero{
            border-radius:20px;padding:clamp(20px,4vw,36px);margin-bottom:20px;
            background:linear-gradient(135deg,color-mix(in srgb,var(--blue) 22%,var(--white)) 0%,color-mix(in srgb,#0d9488 14%,var(--white)) 45%,var(--white) 100%);
            border:1px solid var(--line);box-shadow:var(--card-shadow);
            display:flex;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;gap:20px;
        }
        [data-theme="dark"] .dash-hero{
            background:linear-gradient(135deg,color-mix(in srgb,var(--blue) 28%,var(--white)) 0%,color-mix(in srgb,#0f766e 18%,var(--white)) 50%,var(--white) 100%);
        }
        .dash-hero h1{margin:0 0 8px;font-size:clamp(1.5rem,3.5vw,2rem);letter-spacing:-.02em;line-height:1.15}
        .dash-hero-actions{display:flex;flex-wrap:wrap;gap:10px}
        .dash-kpi{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
        @media (max-width:900px){.dash-kpi{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width:480px){.dash-kpi{grid-template-columns:1fr}}
        .dash-kpi-card{
            border-radius:16px;padding:18px 16px;border:1px solid var(--line);background:var(--white);
            box-shadow:var(--card-shadow);position:relative;overflow:hidden;
        }
        .dash-kpi-card::before{
            content:'';position:absolute;top:0;left:0;right:0;height:3px;
            background:linear-gradient(90deg,var(--blue),var(--orange));opacity:.85;
        }
        .dash-kpi-card--accent::before{background:linear-gradient(90deg,#2563eb,#38bdf8)}
        .dash-kpi-card--mint::before{background:linear-gradient(90deg,#0d9488,#22c55e)}
        .dash-kpi-card--amber::before{background:linear-gradient(90deg,#ea580c,#fbbf24)}
        .dash-kpi-label{font-size:13px;font-weight:600;color:var(--gray);text-transform:uppercase;letter-spacing:.04em;margin-bottom:6px}
        .dash-kpi-value{font-size:clamp(1.5rem,3vw,1.85rem);font-weight:800;color:var(--ink);line-height:1.1}
        .dash-kpi-sub{font-size:13px;color:var(--gray);margin-top:8px}
        .dash-badge{display:inline-flex;align-items:center;padding:3px 8px;border-radius:999px;font-size:12px;font-weight:700;margin-top:8px}
        .dash-badge--up{background:color-mix(in srgb,#22c55e 18%,transparent);color:#15803d}
        .dash-badge--down{background:color-mix(in srgb,#ef4444 16%,transparent);color:#b91c1c}
        .dash-badge--new{background:color-mix(in srgb,var(--blue) 16%,transparent);color:var(--blue)}
        .dash-badge--flat{background:var(--hover-soft);color:var(--gray)}
        [data-theme="dark"] .dash-badge--up{color:#4ade80}
        [data-theme="dark"] .dash-badge--down{color:#f87171}
        .dash-row{display:grid;gap:14px;margin-bottom:18px}
        .dash-row--2{grid-template-columns:minmax(260px,320px) minmax(0,1fr)}
        @media (max-width:900px){.dash-row--2{grid-template-columns:1fr}}
        .dash-panel{border-radius:16px;padding:18px;border:1px solid var(--line);background:var(--white);box-shadow:var(--card-shadow)}
        .dash-panel h2{margin:0 0 14px;font-size:1.05rem;font-weight:700}
        .dash-donut-wrap{display:flex;flex-direction:column;align-items:center;gap:14px;padding:8px 0}
        .dash-donut{width:140px;height:140px;border-radius:50%;
            background:conic-gradient(var(--blue) 0 {{ $ownedPercent }}%, var(--orange) {{ $ownedPercent }}% 100%);
            display:flex;align-items:center;justify-content:center;box-shadow:inset 0 0 0 1px color-mix(in srgb,var(--line) 50%,transparent);
        }
        .dash-donut-inner{width:92px;height:92px;border-radius:50%;background:var(--white);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.1rem;box-shadow:var(--card-shadow)}
        .dash-legend{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;font-size:13px}
        .dash-bar-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(80px,120px) auto;align-items:center;gap:10px;padding:9px 0;border-bottom:1px dashed var(--line);font-size:14px}
        .dash-bar-row:last-child{border-bottom:none}
        .dash-bar-track{height:8px;border-radius:999px;background:var(--hover-soft);overflow:hidden}
        .dash-bar-fill{height:100%;border-radius:999px;background:linear-gradient(90deg,var(--blue),#6366f1);min-width:2px;transition:width .4s ease}
        .dash-chart-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:10px}
        .dash-spark{height:120px;width:100%}
        .dash-spark polyline{vector-effect:non-scaling-stroke}
        .dash-mini-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:18px}
        @media (max-width:700px){.dash-mini-grid{grid-template-columns:1fr}}
        .dash-rank{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid var(--line);font-size:14px}
        .dash-rank:last-child{border-bottom:none}
        .dash-rank-bar{flex:1;height:6px;border-radius:99px;background:var(--hover-soft);max-width:140px;overflow:hidden}
        .dash-rank-fill{height:100%;border-radius:99px;background:linear-gradient(90deg,#0d9488,var(--blue))}
        .dash-latest{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px}
        .dash-latest-card{border-radius:14px;padding:10px;border:1px solid var(--line);background:var(--white);transition:transform .15s ease,box-shadow .15s ease}
        .dash-latest-card:hover{transform:translateY(-2px);box-shadow:0 12px 28px rgba(33,87,213,.12)}
        .dash-latest-card .t{font-size:13px;font-weight:600;margin-top:8px;line-height:1.25;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .dash-latest-card .m{font-size:11px;color:var(--gray);margin-top:4px}
        .dash-stat-inline{display:flex;gap:16px;flex-wrap:wrap;margin-top:10px;font-size:13px;color:var(--gray)}
    </style>

    <div class="dash">
        <div class="dash-hero">
            <div>
                <h1>{{ __('ui.project_collection') }}</h1>
                <p class="muted" style="margin:0;font-size:15px;max-width:520px;">{{ __('ui.dashboard_subtitle') }}</p>
                <div class="dash-stat-inline">
                    <span>{{ __('ui.dashboard_total_qty') }}: <strong style="color:var(--ink)">{{ number_format($totalQty) }}</strong></span>
                    <span>{{ __('ui.dashboard_avg_price') }}: <strong style="color:var(--ink)">{{ number_format($avgPrice, 2) }}</strong></span>
                </div>
            </div>
            <div class="dash-hero-actions">
                <a class="btn" href="{{ route('items.index') }}">{{ __('ui.dashboard_view_collection') }}</a>
                @auth
                    <a class="btn orange" href="{{ route('items.create') }}">{{ __('ui.add_item') }}</a>
                @endauth
            </div>
        </div>

        <div class="dash-kpi">
            <div class="dash-kpi-card dash-kpi-card--accent">
                <div class="dash-kpi-label">{{ __('ui.total_items') }}</div>
                <div class="dash-kpi-value">{{ number_format($totalItems) }}</div>
                <div class="dash-kpi-sub">{{ __('ui.owned_items') }} · {{ number_format($ownedItems) }} &nbsp;|&nbsp; {{ __('ui.wishlist') }} · {{ number_format($wishItems) }}</div>
            </div>
            <div class="dash-kpi-card">
                <div class="dash-kpi-label">{{ __('ui.total_value') }}</div>
                <div class="dash-kpi-value">{{ number_format($totalValue, 2) }}</div>
                <div class="dash-kpi-sub">{{ __('ui.price') }} × {{ __('ui.qty') }} {{ __('ui.dashboard_sum_hint') }}</div>
            </div>
            <div class="dash-kpi-card dash-kpi-card--mint">
                <div class="dash-kpi-label">{{ __('ui.dashboard_photo_coverage') }}</div>
                <div class="dash-kpi-value">{{ $imagePercent }}%</div>
                <div class="dash-kpi-sub">{{ number_format($withImages) }} / {{ number_format($totalItems) }} {{ __('ui.dashboard_with_photo') }}</div>
            </div>
            <div class="dash-kpi-card dash-kpi-card--amber">
                <div class="dash-kpi-label">{{ __('ui.dashboard_added_week') }}</div>
                <div class="dash-kpi-value">{{ number_format($addedLast7) }}</div>
                <div class="dash-kpi-sub">{{ __('ui.dashboard_vs_prior_week') }}</div>
                @if($addedPrev7 > 0 && $weekTrendPercent !== null)
                    <span class="dash-badge {{ $weekTrendPercent >= 0 ? 'dash-badge--up' : 'dash-badge--down' }}">
                        {{ $weekTrendPercent >= 0 ? '↑' : '↓' }} {{ abs($weekTrendPercent) }}%
                    </span>
                @elseif($addedLast7 > 0 && $addedPrev7 === 0)
                    <span class="dash-badge dash-badge--new">{{ __('ui.dashboard_trend_new') }}</span>
                @else
                    <span class="dash-badge dash-badge--flat">{{ __('ui.dashboard_trend_flat') }}</span>
                @endif
            </div>
        </div>

        <div class="dash-row dash-row--2">
            <div class="dash-panel">
                <h2>{{ __('ui.ownership_ratio') }}</h2>
                <div class="dash-donut-wrap">
                    <div class="dash-donut">
                        <div class="dash-donut-inner">{{ $ownedPercent }}%</div>
                    </div>
                    <div class="dash-legend">
                        <span class="pill" style="border-color:var(--blue);color:var(--blue)">{{ __('ui.owned_items') }} {{ $ownedPercent }}%</span>
                        <span class="pill" style="border-color:var(--orange);color:var(--orange)">{{ __('ui.wishlist') }} {{ $wishPercent }}%</span>
                    </div>
                </div>
            </div>
            <div class="dash-panel">
                <h2>{{ __('ui.by_type') }}</h2>
                @forelse($byType as $row)
                    @php $pct = $typeMax > 0 ? ($row->count / $typeMax) * 100 : 0; @endphp
                    <div class="dash-bar-row">
                        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $row->type }}">{{ $row->type }}</span>
                        <div class="dash-bar-track"><div class="dash-bar-fill" style="width: {{ $pct }}%"></div></div>
                        <strong>{{ $row->count }}</strong>
                    </div>
                @empty
                    <p class="muted" style="margin:0;">{{ __('ui.no_data') }}</p>
                @endforelse
            </div>
        </div>

        <div class="dash-panel" style="margin-bottom:18px;">
            <div class="dash-chart-head">
                <div>
                    <h2 style="margin:0 0 4px;">{{ __('ui.dashboard_activity_title') }}</h2>
                    <p class="muted" style="margin:0;font-size:13px;">{{ __('ui.dashboard_activity_sub') }}</p>
                </div>
            </div>
            <svg class="dash-spark" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                <defs>
                    <linearGradient id="dashSparkGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#2563eb" stop-opacity="0.35"/>
                        <stop offset="100%" stop-color="#2563eb" stop-opacity="0"/>
                    </linearGradient>
                </defs>
                @if($sparkPoly)
                    <polygon points="{{ $sparkArea }}" fill="url(#dashSparkGrad)" stroke="none"/>
                    <polyline points="{{ $sparkPoly }}" fill="none" stroke="#2563eb" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
                @endif
            </svg>
        </div>

        <div class="dash-mini-grid">
            <div class="dash-panel">
                <h2>{{ __('ui.dashboard_top_makers') }}</h2>
                @forelse($topMakers as $row)
                    @php $w = $makerMax > 0 ? ($row->c / $makerMax) * 100 : 0; @endphp
                    <div class="dash-rank">
                        <span style="overflow:hidden;text-overflow:ellipsis;">{{ $row->maker }}</span>
                        <div class="dash-rank-bar"><div class="dash-rank-fill" style="width: {{ $w }}%"></div></div>
                        <strong>{{ $row->c }}</strong>
                    </div>
                @empty
                    <p class="muted" style="margin:0;">{{ __('ui.no_data') }}</p>
                @endforelse
            </div>
            <div class="dash-panel">
                <h2>{{ __('ui.dashboard_top_brands') }}</h2>
                @forelse($topBrands as $row)
                    @php $w = $brandMax > 0 ? ($row->c / $brandMax) * 100 : 0; @endphp
                    <div class="dash-rank">
                        <span style="overflow:hidden;text-overflow:ellipsis;">{{ $row->brand }}</span>
                        <div class="dash-rank-bar"><div class="dash-rank-fill" style="width: {{ $w }}%"></div></div>
                        <strong>{{ $row->c }}</strong>
                    </div>
                @empty
                    <p class="muted" style="margin:0;">{{ __('ui.no_data') }}</p>
                @endforelse
            </div>
        </div>

        <div class="dash-panel" style="margin-bottom:18px;">
            <h2>{{ __('ui.latest_added') }}</h2>
            <div class="dash-latest">
                @forelse($latestItems as $item)
                    <a href="{{ route('items.index') }}" class="dash-latest-card" style="text-decoration:none;color:inherit;">
                        @if($item->image_path)
                            <div class="item-photo">
                                <img src="{{ asset('storage/'.$item->image_path) }}" alt="{{ $item->name }}" style="object-position: {{ (int)($item->image_focus_x ?? 50) }}% {{ (int)($item->image_focus_y ?? 50) }}%;">
                                <span class="item-photo-wm">{{ config('collection.watermark_text', 'EARF PICHAYA') }}</span>
                            </div>
                        @else
                            <div class="muted" style="aspect-ratio:1;display:flex;align-items:center;justify-content:center;border:1px dashed var(--line);border-radius:10px;font-size:12px;">{{ __('ui.no_image') }}</div>
                        @endif
                        <div class="t">{{ $item->name }}</div>
                        <div class="m">{{ $item->type }} @if($item->scale) · {{ $item->scale }} @endif</div>
                    </a>
                @empty
                    <p class="muted" style="grid-column:1/-1;margin:0;">{{ __('ui.no_data') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
