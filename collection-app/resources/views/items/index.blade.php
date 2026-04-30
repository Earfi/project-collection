@extends('layout', ['title' => __('ui.list_title'), 'fullWidth' => true])

@section('content')
    <style>
        .list-main-shell{position:relative;min-height:220px}
        .list-main-shell--pending .list-main-body{opacity:0;pointer-events:none}
        .list-main-body{transition:opacity .45s ease}
        .list-main-loader{position:absolute;inset:0;z-index:8;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;background:color-mix(in srgb,var(--bg) 92%,transparent);transition:opacity .4s ease,visibility .4s ease}
        .list-main-shell:not(.list-main-shell--pending) .list-main-loader{opacity:0;visibility:hidden;pointer-events:none}
        .list-main-spinner{width:40px;height:40px;border-radius:50%;border:3px solid var(--line);border-top-color:var(--orange);animation:list-spin .75s linear infinite}
        @keyframes list-spin{to{transform:rotate(360deg)}}
        .bulk-admin-bar{display:flex;flex-wrap:wrap;align-items:center;gap:12px 16px;padding:12px 14px;margin-bottom:12px}
        .bulk-admin-bar label{display:inline-flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;font-weight:600;color:var(--ink)}
        .bulk-admin-bar .bulk-count{flex:1;min-width:120px;font-size:14px;color:var(--gray)}
        .item-card{position:relative}
        .item-select-wrap{position:absolute;top:8px;left:8px;z-index:6;display:flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:color-mix(in srgb,var(--white) 92%,transparent);border:1px solid var(--line);box-shadow:var(--card-shadow);cursor:pointer}
        .item-select-wrap input{width:16px;height:16px;margin:0;cursor:pointer;accent-color:var(--orange)}
    </style>
    <div class="list-layout">
        <button id="filterToggle" class="btn secondary" type="button" style="margin-bottom:6px;width:max-content;">{{ __('ui.filter') }}</button>
        <aside id="filterPanel" class="card filter-panel">
            <h3 style="margin-top:0;">{{ __('ui.filter') }}</h3>
            <form method="GET" action="{{ route('items.index') }}">
                <label>{{ __('ui.type') }}</label>
                <select name="type">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($types as $typeOption)
                        <option value="{{ $typeOption }}" @selected($typeOption === $type)>{{ $typeOption }}</option>
                    @endforeach
                </select>

                <label>{{ __('ui.scale') }}</label>
                <select name="scale">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($scales as $scaleOption)
                        <option value="{{ $scaleOption }}" @selected($scaleOption === $scale)>{{ $scaleOption }}</option>
                    @endforeach
                </select>

                <label>{{ __('ui.maker') }}</label>
                <select name="maker">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($makers as $makerOption)
                        <option value="{{ $makerOption }}" @selected($makerOption === $maker)>{{ $makerOption }}</option>
                    @endforeach
                </select>

                <label>{{ __('ui.subject_brand') }}</label>
                <select name="subject_brand">
                    <option value="">{{ __('ui.all') }}</option>
                    @foreach($subjectBrands as $brandOption)
                        <option value="{{ $brandOption }}" @selected($brandOption === $subjectBrand)>{{ $brandOption }}</option>
                    @endforeach
                </select>

                <div class="field-actions">
                    <button class="btn" type="submit">{{ __('ui.filter') }}</button>
                    <a class="btn secondary" href="{{ route('items.index') }}">{{ __('ui.reset') }}</a>
                </div>
            </form>
        </aside>

        <section class="list-main">
            <div id="listMainShell" class="list-main-shell list-main-shell--pending" aria-busy="true">
                <div class="list-main-loader" aria-hidden="true">
                    <div class="list-main-spinner" role="presentation"></div>
                    <p class="muted" style="margin:0;font-size:14px;">{{ __('ui.list_loading') }}</p>
                </div>
                <div class="list-main-body">
            <div class="card list-header">
                <h1 style="margin:0;font-size:30px;line-height:1.2;">{{ __('ui.list_title') }}</h1>
                <p class="muted" style="margin:6px 0 0;">{{ $items->total() }} items</p>
            </div>

            @auth
                @if($items->total() > 0)
                    <div class="card bulk-admin-bar">
                        <label>
                            <input type="checkbox" id="bulkSelectAll" autocomplete="off">
                            <span>{{ __('ui.bulk_select_page') }}</span>
                        </label>
                        <span class="bulk-count" id="bulkSelectedLabel">{{ __('ui.bulk_selected', ['count' => 0]) }}</span>
                        <button type="submit" class="btn danger" id="bulkDeleteBtn" form="bulkDeleteForm" disabled>{{ __('ui.bulk_delete') }}</button>
                    </div>
                    <form id="bulkDeleteForm" method="POST" action="{{ route('items.bulk-destroy') }}" style="display:none;">
                        @csrf
                    </form>
                @endif
            @endauth

            <div id="listGrid" class="grid list-grid">
                @forelse($items as $item)
                    <div
                        class="card item-card"
                        data-item-id="{{ $item->id }}"
                        data-modal-name="{{ $item->name }}"
                        data-modal-type="{{ $item->type }}"
                        data-modal-scale="{{ $item->scale }}"
                        data-modal-maker="{{ $item->maker }}"
                        data-modal-brand="{{ $item->subject_brand }}"
                        data-modal-price="{{ number_format((float)$item->price, 2) }}"
                        data-modal-qty="{{ $item->qty }}"
                        data-modal-date="{{ $item->collected_at }}"
                    >
                        @if($item->image_path)
                            <div class="item-photo">
                                <img
                                    src="{{ asset('storage/'.$item->image_path) }}"
                                    alt="{{ $item->name }}"
                                    data-focus-x="{{ $item->image_focus_x ?? 50 }}"
                                    data-focus-y="{{ $item->image_focus_y ?? 50 }}"
                                >
                                <span class="item-photo-wm">{{ config('collection.watermark_text', 'EARF PICHAYA') }}</span>
                            </div>
                        @else
                            <div class="muted item-image-placeholder">{{ __('ui.no_image') }}</div>
                        @endif
                        <h3 class="item-title">{{ $item->name }}</h3>
                        <div class="pill item-type">{{ $item->type ?? '-' }}</div>
                        <div class="muted item-maker">{{ $item->maker ?? '-' }}</div>
                        <div class="item-price"><span class="muted">{{ __('ui.price') }}:</span> {{ number_format((float)$item->price, 2) }}</div>
                        @auth
                            <label class="item-select-wrap" title="{{ __('ui.bulk_select_page') }}">
                                <input type="checkbox" class="item-select-cb" value="{{ $item->id }}" autocomplete="off" aria-label="{{ __('ui.name') }}: {{ $item->name }}">
                            </label>
                            <div class="field-actions item-actions" style="margin-top:10px;display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                                <a class="btn secondary action-btn" href="{{ route('items.edit', $item) }}" onclick="event.stopPropagation();">{{ __('ui.edit') }}</a>
                                <form method="POST" action="{{ route('items.destroy', $item) }}" class="delete-form" data-confirm="{{ __('ui.confirm_delete') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn danger action-btn" type="submit" onclick="event.stopPropagation();">{{ __('ui.delete') }}</button>
                                </form>
                            </div>
                        @endauth
                    </div>
                @empty
                    <div class="card">{{ __('ui.no_data') }}</div>
                @endforelse
            </div>

            <div id="paginationState" data-next-url="{{ $items->nextPageUrl() ?? '' }}"></div>
            <div id="infiniteLoader" class="card muted {{ $items->hasMorePages() ? '' : 'hidden-loader' }}" style="text-align:center;">
                Loading more...
            </div>
                </div>
            </div>
        </section>
    </div>

    <div id="itemModal" class="modal-backdrop" aria-hidden="true">
        <div class="modal-card">
            <div class="modal-head">
                <h3 id="modalName" style="margin:0;"></h3>
                <button id="modalCloseBtn" class="btn secondary action-btn" type="button">Close</button>
            </div>
            <div class="modal-grid">
                <div class="muted">{{ __('ui.type') }}</div><div id="modalType">-</div>
                <div class="muted">{{ __('ui.scale') }}</div><div id="modalScale">-</div>
                <div class="muted">{{ __('ui.maker') }}</div><div id="modalMaker">-</div>
                <div class="muted">{{ __('ui.brand') }}</div><div id="modalBrand">-</div>
                <div class="muted">{{ __('ui.price') }}</div><div id="modalPrice">-</div>
                <div class="muted">{{ __('ui.qty') }}</div><div id="modalQty">-</div>
                <div class="muted">{{ __('ui.collected_date') }}</div><div id="modalDate">-</div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const shell = document.getElementById('listMainShell');
            const revealList = () => {
                if (!shell) return;
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => {
                        shell.classList.remove('list-main-shell--pending');
                        shell.setAttribute('aria-busy', 'false');
                    });
                });
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', revealList);
            } else {
                revealList();
            }

            const grid = document.getElementById('listGrid');
            const state = document.getElementById('paginationState');
            const loader = document.getElementById('infiniteLoader');
            if (!grid || !state || !loader) return;

            const bulkConfirmTpl = @json(__('ui.bulk_delete_confirm'));
            const bulkSelectedTpl = @json(__('ui.bulk_selected'));
            const bulkForm = document.getElementById('bulkDeleteForm');
            const bulkBtn = document.getElementById('bulkDeleteBtn');
            const bulkSelectAll = document.getElementById('bulkSelectAll');
            const bulkLabel = document.getElementById('bulkSelectedLabel');

            const syncBulkUi = () => {
                if (!bulkBtn || !bulkLabel) return;
                const boxes = grid.querySelectorAll('.item-select-cb');
                const checked = grid.querySelectorAll('.item-select-cb:checked');
                const n = checked.length;
                bulkLabel.textContent = bulkSelectedTpl.replace(':count', String(n));
                bulkBtn.disabled = n === 0;
                if (bulkSelectAll && boxes.length) {
                    bulkSelectAll.checked = n > 0 && n === boxes.length;
                    bulkSelectAll.indeterminate = n > 0 && n < boxes.length;
                }
            };

            if (bulkSelectAll) {
                bulkSelectAll.addEventListener('change', () => {
                    grid.querySelectorAll('.item-select-cb').forEach((cb) => {
                        cb.checked = bulkSelectAll.checked;
                    });
                    syncBulkUi();
                });
            }
            grid.addEventListener('change', (e) => {
                if (e.target && e.target.classList && e.target.classList.contains('item-select-cb')) {
                    syncBulkUi();
                }
            });

            if (bulkForm) {
                bulkForm.addEventListener('submit', (e) => {
                    const checked = grid.querySelectorAll('.item-select-cb:checked');
                    if (checked.length === 0) {
                        e.preventDefault();
                        return;
                    }
                    const msg = bulkConfirmTpl.replace(':count', String(checked.length));
                    if (!confirm(msg)) {
                        e.preventDefault();
                        return;
                    }
                    bulkForm.querySelectorAll('input[name="ids[]"]').forEach((n) => n.remove());
                    checked.forEach((cb) => {
                        const h = document.createElement('input');
                        h.type = 'hidden';
                        h.name = 'ids[]';
                        h.value = cb.value;
                        bulkForm.appendChild(h);
                    });
                });
            }

            let loading = false;

            const loadMore = async () => {
                const nextUrl = state.dataset.nextUrl;
                if (!nextUrl || loading) return;
                loading = true;
                loader.style.display = 'block';

                try {
                    const res = await fetch(nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newCards = doc.querySelectorAll('#listGrid .item-card');
                    newCards.forEach(card => grid.appendChild(card));
                    applyImageFocus(grid);
                    syncBulkUi();

                    const newState = doc.getElementById('paginationState');
                    state.dataset.nextUrl = newState ? (newState.dataset.nextUrl || '') : '';
                    if (!state.dataset.nextUrl) loader.style.display = 'none';
                } catch (e) {
                    loader.textContent = 'Load failed. Scroll again to retry.';
                } finally {
                    loading = false;
                }
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) loadMore();
                });
            }, { rootMargin: '200px' });

            observer.observe(loader);
            const applyImageFocus = (root) => {
                root.querySelectorAll('img[data-focus-x][data-focus-y]').forEach((img) => {
                    const x = Number(img.dataset.focusX || 50);
                    const y = Number(img.dataset.focusY || 50);
                    img.style.objectPosition = `${x}% ${y}%`;
                });
            };
            applyImageFocus(grid);

            document.addEventListener('submit', function (e) {
                const form = e.target;
                if (form instanceof HTMLFormElement && form.classList.contains('delete-form')) {
                    const msg = form.dataset.confirm || 'Delete item?';
                    if (!confirm(msg)) {
                        e.preventDefault();
                    }
                }
            });

            const modal = document.getElementById('itemModal');
            const modalCloseBtn = document.getElementById('modalCloseBtn');
            const fill = (id, value) => {
                const el = document.getElementById(id);
                if (el) el.textContent = value && String(value).trim() !== '' ? value : '-';
            };
            const openModalFromCard = (card) => {
                if (!modal) return;
                fill('modalName', card.dataset.modalName);
                fill('modalType', card.dataset.modalType);
                fill('modalScale', card.dataset.modalScale);
                fill('modalMaker', card.dataset.modalMaker);
                fill('modalBrand', card.dataset.modalBrand);
                fill('modalPrice', card.dataset.modalPrice);
                fill('modalQty', card.dataset.modalQty);
                fill('modalDate', card.dataset.modalDate);
                modal.classList.add('open');
                modal.setAttribute('aria-hidden', 'false');
            };
            const closeModal = () => {
                if (!modal) return;
                modal.classList.remove('open');
                modal.setAttribute('aria-hidden', 'true');
            };

            grid.addEventListener('click', (e) => {
                if (e.target.closest('.item-select-wrap')) return;
                const card = e.target.closest('.item-card');
                if (!card) return;
                openModalFromCard(card);
            });
            syncBulkUi();
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeModal();
                });
            }
            if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeModal);
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeModal();
            });

            const filterToggle = document.getElementById('filterToggle');
            const filterPanel = document.getElementById('filterPanel');
            if (filterToggle && filterPanel) {
                const syncFilterVisibility = () => {
                    if (window.innerWidth >= 992) {
                        filterPanel.style.display = 'block';
                        filterToggle.style.display = 'none';
                    } else {
                        filterToggle.style.display = 'inline-block';
                        if (!filterPanel.dataset.opened) {
                            filterPanel.style.display = 'none';
                        }
                    }
                };

                filterToggle.addEventListener('click', () => {
                    const hidden = filterPanel.style.display === 'none';
                    filterPanel.style.display = hidden ? 'block' : 'none';
                    filterPanel.dataset.opened = hidden ? '1' : '';
                });

                window.addEventListener('resize', syncFilterVisibility);
                syncFilterVisibility();
            }
        })();
    </script>
@endsection
