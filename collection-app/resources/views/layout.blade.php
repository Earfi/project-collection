<!doctype html>
<html lang="{{ app()->getLocale() }}" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'My Collection' }}</title>
    <style>
        :root{
            --blue:#1877f2;
            --orange:#ff8a24;
            --white:#ffffff;
            --bg:#f0f2f5;
            --gray:#65676b;
            --line:#d9dee6;
            --ink:#1c1e21;
            --card-shadow:0 1px 3px rgba(0,0,0,.12);
            --on-primary:#ffffff;
            --hover-soft:#f2f3f5;
            --nav-bg:linear-gradient(90deg,#0f5132,#146c43);
            --nav-border:#0b3d27;
            --nav-text:#ffffff;
            --nav-link-hover-bg:rgba(255,255,255,.18);
            --nav-link-hover-border:rgba(255,255,255,.26);
        }
        [data-theme="dark"]{
            --white:#151b26;
            --bg:#0e131b;
            --gray:#a8b3c5;
            --line:#2a3548;
            --ink:#eef3ff;
            --card-shadow:0 10px 28px rgba(0,0,0,.35);
            --on-primary:#ffffff;
            --hover-soft:#1c2433;
            --nav-bg:#111827;
            --nav-border:#1f2937;
            --nav-text:#eef3ff;
            --nav-link-hover-bg:rgba(255,255,255,.1);
            --nav-link-hover-border:rgba(255,255,255,.14);
        }
        *{box-sizing:border-box}
        body{font-family:Inter,Segoe UI,Arial,sans-serif;margin:0;background:var(--bg);color:var(--ink)}
        .wrap{max-width:1200px;margin:0 auto;padding:clamp(8px,1.4vw,14px)}
        .wrap.full-width{max-width:none;width:100%}
        .nav{background:var(--nav-bg);border-bottom:1px solid var(--nav-border)}
        .nav-inner{display:flex;align-items:center;justify-content:space-between;gap:10px;min-height:52px}
        .brand{display:flex;align-items:center;gap:8px;color:var(--nav-text);font-weight:700;letter-spacing:.2px}
        .brand-dot{width:10px;height:10px;border-radius:3px;background:#22c55e;box-shadow:0 0 0 2px rgba(255,255,255,.2)}
        .nav-desktop-links,.nav-actions{display:flex;align-items:center;gap:8px}
        .nav .nav-desktop-links a,.nav .mobile-nav-links a{color:var(--nav-text);text-decoration:none;padding:8px 10px;border:1px solid transparent;border-radius:8px;font-weight:600;font-size:14px}
        .nav .nav-desktop-links a:hover,.nav .mobile-nav-links a:hover{border-color:var(--nav-link-hover-border);background:var(--nav-link-hover-bg)}
        .nav .nav-desktop-links a.nav-link--active,.nav .mobile-nav-links a.nav-link--active{
            border:2px solid rgba(255,255,255,.95);
            background:rgba(255,255,255,.2);
            box-shadow:0 0 0 1px rgba(0,0,0,.12),inset 0 1px 0 rgba(255,255,255,.35);
        }
        .nav .nav-desktop-links a.nav-link--active:hover,.nav .mobile-nav-links a.nav-link--active:hover{
            border-color:#fff;
            background:rgba(255,255,255,.28);
        }
        [data-theme="dark"] .nav .nav-desktop-links a.nav-link--active,[data-theme="dark"] .nav .mobile-nav-links a.nav-link--active{
            border-color:rgba(255,255,255,.88);
            background:rgba(255,255,255,.1);
            box-shadow:0 0 0 1px rgba(0,0,0,.35),inset 0 1px 0 rgba(255,255,255,.12);
        }
        [data-theme="dark"] .nav .nav-desktop-links a.nav-link--active:hover,[data-theme="dark"] .nav .mobile-nav-links a.nav-link--active:hover{
            background:rgba(255,255,255,.16);
        }
        .nav-pill{background:rgba(255,255,255,.16);border:1px solid rgba(255,255,255,.26);color:var(--nav-text);border-radius:8px;padding:7px 10px;font-size:13px;cursor:pointer}
        .nav-pill:hover{background:rgba(255,255,255,.26)}
        .nav-lang{position:relative}
        .nav-lang > summary.nav-pill{list-style:none}
        .nav-lang > summary::-webkit-details-marker{display:none}
        .nav-lang[open] > summary{background:rgba(255,255,255,.26);border-color:rgba(255,255,255,.4)}
        .nav-lang-menu{
            position:absolute;top:calc(100% + 8px);right:0;min-width:12.5rem;padding:8px;
            background:var(--white);color:var(--ink);border:1px solid var(--line);border-radius:12px;
            box-shadow:0 14px 40px rgba(0,0,0,.18),0 2px 8px rgba(0,0,0,.08);z-index:80;
        }
        .nav-lang-item{display:block;padding:10px 12px;border-radius:8px;text-decoration:none;font-size:14px;font-weight:600;color:var(--ink)}
        .nav-lang-item:hover{background:var(--hover-soft)}
        .nav-lang-item--active{background:color-mix(in srgb,var(--blue) 14%,var(--hover-soft));color:var(--blue)}
        .mobile-nav-actions .nav-lang{width:100%}
        .mobile-nav-actions .nav-lang-menu{left:0;right:0}
        .nav-toggle{display:none;background:transparent;border:1px solid rgba(255,255,255,.35);color:var(--nav-text);border-radius:8px;padding:7px 10px;cursor:pointer}
        .mobile-nav-panel{display:none;border-top:1px solid rgba(255,255,255,.22);padding-top:10px;padding-bottom:10px}
        .mobile-nav-panel.open{display:block}
        .mobile-nav-links{display:flex;flex-direction:column;gap:6px}
        .mobile-nav-actions{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
        .card{background:var(--white);border:1px solid var(--line);border-radius:14px;padding:18px;margin-bottom:16px;box-shadow:var(--card-shadow)}
        .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:16px}
        .list-grid{grid-template-columns:repeat(7,minmax(0,1fr));align-items:stretch;gap:10px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .btn{background:var(--blue);color:var(--on-primary);border:1px solid var(--blue);border-radius:10px;padding:8px 12px;cursor:pointer;text-decoration:none;display:inline-block}
        .btn:hover{filter:brightness(0.95)}
        .btn.secondary{background:var(--white);color:var(--ink);border-color:var(--line)}
        .btn.orange{background:var(--orange);border-color:var(--orange);color:var(--on-primary)}
        .btn.danger{background:#dc2626;border-color:#dc2626;color:#fff}
        .btn.danger:hover{filter:brightness(0.92)}
        .input,select,textarea{width:100%;padding:10px 12px;margin-top:6px;margin-bottom:14px;border:1px solid var(--line);border-radius:10px;background:var(--white);color:var(--ink)}
        select option{background:var(--white);color:var(--ink)}
        .muted{color:var(--gray);font-size:14px}
        .stat{font-size:28px;font-weight:700}
        .actions{display:flex;gap:10px;flex-wrap:wrap}
        .field-actions{display:flex;gap:10px;margin-top:8px}
        img{max-width:100%;border-radius:10px;display:block}
        .pill{display:inline-block;padding:4px 10px;border:1px solid var(--line);border-radius:999px;font-size:12px;color:var(--gray)}
        .item-card{transition:transform .15s ease,border-color .15s ease,box-shadow .15s ease;display:flex;flex-direction:column;height:100%;padding:9px;margin:0;cursor:pointer}
        .item-card:hover{transform:translateY(-2px);border-color:var(--blue);box-shadow:0 10px 26px rgba(33,87,213,.14);background:linear-gradient(180deg,var(--white),color-mix(in srgb, var(--white) 92%, var(--blue)))}
        .item-card:focus-within{outline:2px solid var(--orange);outline-offset:2px}
        .item-photo{position:relative;width:100%;aspect-ratio:1 / 1;border-radius:10px;overflow:hidden;background:var(--hover-soft)}
        .item-photo>img{width:100%;height:100%;object-fit:cover;display:block}
        .item-photo-wm{position:absolute;bottom:4px;right:5px;z-index:2;margin:0;padding:0;font-size:clamp(8px,1.35vw,11px);font-weight:700;letter-spacing:.02em;color:#fff;line-height:1.15;text-align:right;text-shadow:0 0 4px #000,0 1px 2px rgba(0,0,0,.9);pointer-events:none;max-width:calc(100% - 10px);overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .item-image-placeholder{width:100%;aspect-ratio:1 / 1;display:flex;align-items:center;justify-content:center;border:1px dashed var(--line);border-radius:10px}
        .item-title{margin:7px 0 3px;font-size:13px;line-height:1.25;display:-webkit-box;line-clamp:2;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.5em}
        .item-type{display:inline-flex;align-self:flex-start;width:fit-content;max-width:100%;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .item-maker{margin-bottom:6px;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:11px}
        .item-price{white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:12px}
        .focus-preview-box{width:min(260px,100%);margin:4px 0 14px}
        .focus-preview-wrap{position:relative;width:100%;aspect-ratio:1 / 1;border:1px solid var(--line);border-radius:10px;overflow:hidden;background:var(--hover-soft)}
        .focus-preview-wrap .focus-preview-image{width:100%;height:100%;object-fit:cover;display:block;border-radius:0;border:none}
        .focus-preview-placeholder{width:100%;aspect-ratio:1 / 1;display:flex;align-items:center;justify-content:center;border:1px dashed var(--line);border-radius:10px}
        .icon-btn{background:var(--white);color:var(--ink);border:1px solid var(--line);border-radius:10px;padding:9px 12px;cursor:pointer}
        .icon-btn:hover{border-color:var(--blue)}
        .hidden-loader{display:none}
        .combo{position:relative;z-index:0}
        .combo:focus-within{z-index:45}
        .combo > .input{
            padding-right:2.5rem;
            cursor:pointer;
            background-color:var(--white);
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2365676b' stroke-width='2.25' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat:no-repeat;
            background-position:right 12px center;
            background-size:16px;
        }
        [data-theme="dark"] .combo > .input{
            background-color:var(--white);
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a8b3c5' stroke-width='2.25' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        }
        .combo-menu{
            position:absolute;top:100%;left:0;right:0;margin-top:6px;
            background:var(--white);
            border:1px solid color-mix(in srgb,var(--ink) 14%,var(--line));
            border-radius:12px;padding:6px;max-height:220px;overflow:auto;
            z-index:50;
            box-shadow:0 14px 44px rgba(0,0,0,.16),0 4px 12px rgba(0,0,0,.1),0 0 0 1px rgba(0,0,0,.05);
        }
        [data-theme="dark"] .combo-menu{
            border-color:color-mix(in srgb,var(--white) 22%,var(--line));
            box-shadow:0 16px 48px rgba(0,0,0,.55),0 0 0 1px rgba(255,255,255,.06);
        }
        .combo-item{
            padding:11px 12px;border-radius:8px;cursor:pointer;font-size:14px;font-weight:500;
            line-height:1.35;color:var(--ink);border:1px solid transparent;
        }
        .combo-item + .combo-item{margin-top:2px}
        .combo-item:hover{
            background:color-mix(in srgb,var(--blue) 10%,var(--hover-soft));
            border-color:color-mix(in srgb,var(--blue) 22%,var(--line));
        }
        .combo-item:active{background:color-mix(in srgb,var(--blue) 16%,var(--hover-soft))}
        .action-btn{min-width:80px;height:34px;display:inline-flex;align-items:center;justify-content:center;padding:0 10px;font-size:14px}
        .item-actions .action-btn{width:100%;min-width:0}
        .modal-backdrop{position:fixed;inset:0;background:rgba(0,0,0,.45);display:none;align-items:center;justify-content:center;z-index:40;padding:16px}
        .modal-backdrop.open{display:flex}
        .modal-card{width:min(520px,95vw);background:var(--white);color:var(--ink);border:1px solid var(--line);border-radius:14px;box-shadow:var(--card-shadow);padding:16px}
        .modal-head{display:flex;justify-content:space-between;align-items:center;gap:12px}
        .modal-head h3{flex:1;line-height:1.25}
        .modal-grid{display:grid;grid-template-columns:130px 1fr;gap:8px 12px;margin-top:12px;font-size:14px}
        .list-layout{display:grid;grid-template-columns:1fr;gap:16px}
        .list-main{min-width:0}
        .filter-panel{background:var(--white);}
        .list-header{background:var(--white);}
        @media (min-width: 992px){
            .list-layout{grid-template-columns:260px minmax(0,1fr);align-items:start}
            .filter-panel{
                position:sticky;
                left:auto;
                top:96px;
                width:260px;
                border-radius:14px;
                z-index:2;
                box-shadow:var(--card-shadow);
            }
        }
        @media (max-width: 900px){
            .nav-desktop-links,.nav-actions{display:none}
            .nav-toggle{display:inline-block}
        }
        @media (max-width: 1400px){.list-grid{grid-template-columns:repeat(6,minmax(0,1fr))}}
        @media (max-width: 1200px){.list-grid{grid-template-columns:repeat(5,minmax(0,1fr))}}
        @media (max-width: 1100px){.list-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
        @media (max-width: 820px){.row{grid-template-columns:1fr}.list-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media (max-width: 480px){.list-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="nav">
        <div class="wrap">
            <div class="nav-inner">
                <div class="brand">
                    <span class="brand-dot"></span>
                    <span>Earf Pichaya</span>
                </div>
                <div class="nav-desktop-links">
                    <a href="{{ route('home') }}" @class(['nav-link--active' => request()->routeIs('home')])>{{ __('ui.home') }}</a>
                    <a href="{{ route('items.index') }}" @class(['nav-link--active' => request()->routeIs('items.index', 'items.edit')])>{{ __('ui.collections') }}</a>
                    @auth
                        <a href="{{ route('items.create') }}" @class(['nav-link--active' => request()->routeIs('items.create')])>{{ __('ui.add_item') }}</a>
                    @endauth
                </div>
                <div class="nav-actions">
                    <button id="themeToggle" class="nav-pill" type="button" data-light-label="{{ __('ui.light') }}" data-dark-label="{{ __('ui.dark') }}">{{ __('ui.dark') }}</button>
                    @include('partials.locale-switcher')
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-pill" type="submit">{{ __('ui.logout') }}</button>
                        </form>
                    @else
                        <a class="nav-pill" href="{{ route('login') }}">{{ __('ui.admin_login') }}</a>
                    @endauth
                </div>
                <button id="navToggle" class="nav-toggle" type="button" aria-label="Open menu">☰</button>
            </div>
            <div id="mobileNavPanel" class="mobile-nav-panel">
                <div class="mobile-nav-links">
                    <a href="{{ route('home') }}" @class(['nav-link--active' => request()->routeIs('home')])>{{ __('ui.home') }}</a>
                    <a href="{{ route('items.index') }}" @class(['nav-link--active' => request()->routeIs('items.index', 'items.edit')])>{{ __('ui.collections') }}</a>
                    @auth
                        <a href="{{ route('items.create') }}" @class(['nav-link--active' => request()->routeIs('items.create')])>{{ __('ui.add_item') }}</a>
                    @endauth
                </div>
                <div class="mobile-nav-actions">
                    <button id="themeToggleMobile" class="nav-pill" type="button">{{ __('ui.dark') }}</button>
                    @include('partials.locale-switcher')
                    @auth
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="nav-pill" type="submit">{{ __('ui.logout') }}</button>
                        </form>
                    @else
                        <a class="nav-pill" href="{{ route('login') }}">{{ __('ui.admin_login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
    <div class="wrap {{ !empty($fullWidth) ? 'full-width' : '' }}">
        @if(session('status'))
            <div class="card">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="card">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        @yield('content')
    </div>
    <script>
        (function () {
            const root = document.documentElement;
            const toggle = document.getElementById('themeToggle');
            const toggleMobile = document.getElementById('themeToggleMobile');
            const saved = localStorage.getItem('theme');
            const initial = (saved === 'dark' || saved === 'light') ? saved : 'light';

            const applyTheme = (theme) => {
                root.setAttribute('data-theme', theme);
                const lightLabel = (toggle?.dataset.lightLabel) || 'Light';
                const darkLabel = (toggle?.dataset.darkLabel) || 'Dark';
                if (toggle) toggle.textContent = theme === 'dark' ? lightLabel : darkLabel;
                if (toggleMobile) toggleMobile.textContent = theme === 'dark' ? lightLabel : darkLabel;
            };

            applyTheme(initial);

            const toggleTheme = () => {
                    const current = root.getAttribute('data-theme') || 'light';
                    const next = current === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('theme', next);
                    applyTheme(next);
            };
            if (toggle) toggle.addEventListener('click', toggleTheme);
            if (toggleMobile) toggleMobile.addEventListener('click', toggleTheme);

            const navToggle = document.getElementById('navToggle');
            const mobileNavPanel = document.getElementById('mobileNavPanel');
            if (navToggle && mobileNavPanel) {
                navToggle.addEventListener('click', () => {
                    mobileNavPanel.classList.toggle('open');
                });
            }
        })();
    </script>
</body>
</html>
