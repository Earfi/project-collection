@extends('layout', ['title' => __('ui.add_collection')])

@section('content')
    @php
        $createActiveTab = ($errors->has('import_file') || request('tab') === 'import') ? 'import' : 'single';
    @endphp
    <style>
        .create-tabs{display:flex;gap:4px;border-bottom:1px solid var(--line);margin:14px 0 18px;padding:0}
        .create-tab{background:transparent;border:none;border-bottom:2px solid transparent;color:var(--gray);font:inherit;font-weight:600;font-size:15px;padding:10px 14px 12px;margin-bottom:-1px;cursor:pointer;border-radius:10px 10px 0 0}
        .create-tab:hover{background:var(--hover-soft);color:var(--ink)}
        .create-tab[aria-selected="true"]{color:var(--ink);border-bottom-color:var(--orange)}
        .create-tab-panel-title{font-size:1.1rem;margin:0 0 8px;font-weight:700}
        .import-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-bottom:14px}
        .import-dropzone-wrap{position:relative;border:2px dashed var(--line);border-radius:14px;background:var(--white);min-height:200px;padding:28px 20px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;cursor:pointer;transition:border-color .15s ease,background .15s ease,box-shadow .15s ease}
        .import-dropzone-wrap:hover{border-color:color-mix(in srgb,var(--gray) 55%,var(--line));background:var(--hover-soft)}
        .import-dropzone-wrap.import-dropzone--active{border-color:#22c55e;box-shadow:0 0 0 3px rgba(34,197,94,.15);background:color-mix(in srgb,var(--white) 92%,#22c55e)}
        [data-theme="dark"] .import-dropzone-wrap.import-dropzone--active{background:color-mix(in srgb,var(--white) 96%,#22c55e)}
        .import-dropzone-icon{margin-bottom:14px;display:block}
        .import-dropzone-title{font-size:1.05rem;font-weight:700;color:var(--ink);margin:0 0 6px;line-height:1.35}
        .import-dropzone-hint{font-size:14px;color:var(--gray);margin:0}
        .import-file-name{font-size:13px;color:var(--gray);margin:12px 0 0;max-width:100%;word-break:break-all}
        .import-file-name:not(:empty){color:var(--ink);font-weight:600}
        .import-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
    </style>
    <div class="card">
        <h1>{{ __('ui.add_collection') }}</h1>
        <div
            class="create-tabs"
            id="createTabs"
            role="tablist"
            aria-label="{{ __('ui.add_collection') }}"
        >
            <button type="button" class="create-tab" role="tab" id="tab-single" data-tab="single" aria-controls="panel-single" aria-selected="{{ $createActiveTab === 'single' ? 'true' : 'false' }}">{{ __('ui.add_tab_single') }}</button>
            <button type="button" class="create-tab" role="tab" id="tab-import" data-tab="import" aria-controls="panel-import" aria-selected="{{ $createActiveTab === 'import' ? 'true' : 'false' }}">{{ __('ui.add_tab_bulk') }}</button>
        </div>

        <div id="panel-single" class="create-tab-panel" role="tabpanel" aria-labelledby="tab-single" data-tab-panel="single" @if($createActiveTab !== 'single') hidden @endif>
        <p class="muted">{{ __('ui.type_hint') }}</p>
        <form method="POST" action="{{ route('items.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div>
                    <label>{{ __('ui.type') }} *</label>
                    <div class="combo">
                        <input class="input" id="typeValue" name="type" value="{{ old('type') }}" placeholder="Type" autocomplete="off" required>
                        <div id="typeMenu" class="combo-menu" style="display:none;">
                            @foreach($types as $typeOption)
                                <div class="combo-item" data-value="{{ $typeOption }}">{{ $typeOption }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label>{{ __('ui.scale') }}</label>
                    <div class="combo">
                        <input class="input" id="scaleValue" name="scale" value="{{ old('scale') }}" autocomplete="off">
                        <div id="scaleMenu" class="combo-menu" style="display:none;">
                            @foreach($allScales as $scaleOption)
                                <div class="combo-item" data-value="{{ $scaleOption }}">{{ $scaleOption }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div>
                    <label>{{ __('ui.maker') }}</label>
                    <div class="combo">
                        <input class="input" id="makerValue" name="maker" value="{{ old('maker') }}" autocomplete="off">
                        <div id="makerMenu" class="combo-menu" style="display:none;">
                            @foreach($allMakers as $makerOption)
                                <div class="combo-item" data-value="{{ $makerOption }}">{{ $makerOption }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div>
                    <label>{{ __('ui.subject_brand') }}</label>
                    <div class="combo">
                        <input class="input" id="subjectBrandValue" name="subject_brand" value="{{ old('subject_brand') }}" autocomplete="off">
                        <div id="subjectBrandMenu" class="combo-menu" style="display:none;">
                            @foreach($allSubjectBrands as $brandOption)
                                <div class="combo-item" data-value="{{ $brandOption }}">{{ $brandOption }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <label>{{ __('ui.name') }} *</label>
            <input class="input" name="name" required>

            <label>{{ __('ui.description') }}</label>
            <textarea class="input" name="description"></textarea>

            <div class="row">
                <div>
                    <label>{{ __('ui.price') }}</label>
                    <input class="input" name="price" type="number" step="0.01" min="0">
                </div>
                <div>
                    <label>{{ __('ui.qty') }}</label>
                    <input class="input" name="qty" type="number" min="0" value="{{ old('qty', 1) }}">
                </div>
            </div>
            <div class="row">
                <div>
                    <label>{{ __('ui.collected_date') }}</label>
                    <input class="input" name="collected_at" type="date">
                </div>
                <div>
                    <label>{{ __('ui.image_will_watermark') }}</label>
                    <input class="input" name="image" type="file" accept="image/*">
                </div>
            </div>
            <label>{{ __('ui.image_focus') }}</label>
            <div class="row" style="align-items:end;">
                <div>
                    <label for="imageFocusX">{{ __('ui.image_focus_x') }}: <span id="imageFocusXValue">50</span>%</label>
                    <input class="input" id="imageFocusX" name="image_focus_x" type="range" min="0" max="100" value="{{ old('image_focus_x', 50) }}">
                </div>
                <div>
                    <label for="imageFocusY">{{ __('ui.image_focus_y') }}: <span id="imageFocusYValue">50</span>%</label>
                    <input class="input" id="imageFocusY" name="image_focus_y" type="range" min="0" max="100" value="{{ old('image_focus_y', 50) }}">
                </div>
            </div>
            <div class="focus-preview-box">
                <div class="muted" style="margin-bottom:6px;">{{ __('ui.image_focus_preview') }}</div>
                <div id="focusPreviewWrap" class="focus-preview-wrap" style="display:none;">
                    <img id="imageFocusPreview" class="focus-preview-image" alt="Preview" style="display:none;">
                    <span class="item-photo-wm">{{ config('collection.watermark_text', 'EARF PICHAYA') }}</span>
                </div>
                <div id="imageFocusEmpty" class="muted focus-preview-placeholder">{{ __('ui.no_image') }}</div>
                <div class="muted" style="margin-top:6px;">{{ __('ui.image_focus_preview_hint') }}</div>
            </div>
            <div class="field-actions">
                <button class="btn orange" type="submit">{{ __('ui.save') }}</button>
                <a class="btn secondary" href="{{ route('items.index') }}">{{ __('ui.cancel') }}</a>
            </div>
        </form>
        </div>

        <div id="panel-import" class="create-tab-panel" role="tabpanel" aria-labelledby="tab-import" data-tab-panel="import" @if($createActiveTab !== 'import') hidden @endif>
        <h2 class="create-tab-panel-title">{{ __('ui.excel_import_title') }}</h2>
        <p class="muted" style="margin-bottom:1rem;">{{ __('ui.excel_import_help') }}</p>
        @error('import_file')
            <p class="muted" style="color:#dc2626;margin-bottom:0.75rem;">{{ $message }}</p>
        @enderror
        <div class="import-toolbar">
            <a class="btn secondary" href="{{ route('items.import.template') }}">{{ __('ui.excel_template_download') }}</a>
        </div>
        <form method="POST" action="{{ route('items.import') }}" enctype="multipart/form-data" id="importExcelForm">
            @csrf
            <input
                type="file"
                name="import_file"
                id="importFileInput"
                class="import-sr-only"
                accept=".xlsx,.xls,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel"
                required
            >
            <div
                class="import-dropzone-wrap"
                id="importDropzone"
                role="button"
                tabindex="0"
                aria-label="{{ __('ui.import_dropzone_title') }}. {{ __('ui.import_dropzone_hint') }}"
            >
                <svg class="import-dropzone-icon" width="56" height="56" viewBox="0 0 56 56" aria-hidden="true">
                    <rect x="6" y="4" width="36" height="46" rx="5" fill="#22c55e"/>
                    <rect x="10" y="9" width="28" height="36" rx="2" fill="#ffffff"/>
                    <line x1="18" y1="9" x2="18" y2="45" stroke="#e5e7eb" stroke-width="1"/>
                    <line x1="26" y1="9" x2="26" y2="45" stroke="#e5e7eb" stroke-width="1"/>
                    <line x1="34" y1="9" x2="34" y2="45" stroke="#e5e7eb" stroke-width="1"/>
                    <line x1="10" y1="19" x2="38" y2="19" stroke="#e5e7eb" stroke-width="1"/>
                    <line x1="10" y1="27" x2="38" y2="27" stroke="#e5e7eb" stroke-width="1"/>
                    <line x1="10" y1="35" x2="38" y2="35" stroke="#e5e7eb" stroke-width="1"/>
                    <circle cx="44" cy="40" r="11" fill="#22c55e"/>
                    <path d="M44 35.5v9M39.5 40h9" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round"/>
                </svg>
                <p class="import-dropzone-title">{{ __('ui.import_dropzone_title') }}</p>
                <p class="import-dropzone-hint">{{ __('ui.import_dropzone_hint') }}</p>
                <p class="import-file-name" id="importFileLabel" aria-live="polite"></p>
            </div>
            <div class="field-actions" style="margin-top:1rem;">
                <button class="btn orange" type="submit">{{ __('ui.excel_import_submit') }}</button>
                <a class="btn secondary" href="{{ route('items.index') }}">{{ __('ui.cancel') }}</a>
            </div>
        </form>
        </div>
    </div>
    <div
        id="comboData"
        data-type-metadata='@json($typeMetadata)'
        data-all-scales='@json($allScales->values())'
        data-all-makers='@json($allMakers->values())'
        data-all-subject-brands='@json($allSubjectBrands->values())'
        data-types='@json($types->values())'
        hidden
    ></div>
    <script>
        (function () {
            const comboData = document.getElementById('comboData');
            if (!comboData) return;
            const typeMetadataRaw = JSON.parse(comboData.dataset.typeMetadata || '{}');
            const typeMetadata = Object.fromEntries(
                Object.entries(typeMetadataRaw).map(([key, value]) => [String(key).toLowerCase(), value])
            );
            const allScaleOptions = JSON.parse(comboData.dataset.allScales || '[]');
            const allMakerOptions = JSON.parse(comboData.dataset.allMakers || '[]');
            const allSubjectBrandOptions = JSON.parse(comboData.dataset.allSubjectBrands || '[]');
            const typeOptions = JSON.parse(comboData.dataset.types || '[]');

            const uniqueSorted = (values) => Array.from(new Set((values || []).filter(Boolean))).sort((a, b) => String(a).localeCompare(String(b)));
            const typeInput = document.getElementById('typeValue');
            if (!typeInput) return;

            const getTypeScopedOptions = (field) => {
                const key = typeInput.value.trim().toLowerCase();
                const scoped = typeMetadata[key]?.[field];
                if (Array.isArray(scoped) && scoped.length > 0) return uniqueSorted(scoped);
                if (field === 'scales') return uniqueSorted(allScaleOptions);
                if (field === 'makers') return uniqueSorted(allMakerOptions);
                return uniqueSorted(allSubjectBrandOptions);
            };

            const setupCombo = (inputId, menuId, optionsProvider, onPick) => {
                const input = document.getElementById(inputId);
                const menu = document.getElementById(menuId);
                if (!input || !menu) return { refreshMenu: () => {} };

                const refreshMenu = () => {
                    const q = input.value.trim().toLowerCase();
                    const options = optionsProvider();
                    menu.innerHTML = '';
                    let count = 0;
                    options.forEach((option) => {
                        const text = String(option);
                        if (q !== '' && !text.toLowerCase().includes(q)) return;
                        const row = document.createElement('div');
                        row.className = 'combo-item';
                        row.dataset.value = text;
                        row.textContent = text;
                        menu.appendChild(row);
                        count++;
                    });
                    menu.style.display = count > 0 ? 'block' : 'none';
                };

                input.addEventListener('focus', refreshMenu);
                input.addEventListener('input', refreshMenu);
                menu.addEventListener('mousedown', (e) => {
                    if (e.button !== 0) return;
                    if (e.target.closest('.combo-item')) {
                        e.preventDefault();
                    }
                });
                menu.addEventListener('click', (e) => {
                    const row = e.target.closest('.combo-item');
                    if (!row) return;
                    input.value = row.dataset.value || '';
                    menu.style.display = 'none';
                    if (onPick) onPick();
                });

                return { refreshMenu };
            };

            const scaleCombo = setupCombo('scaleValue', 'scaleMenu', () => getTypeScopedOptions('scales'));
            const makerCombo = setupCombo('makerValue', 'makerMenu', () => getTypeScopedOptions('makers'));
            const subjectBrandCombo = setupCombo('subjectBrandValue', 'subjectBrandMenu', () => getTypeScopedOptions('subjectBrands'));
            const typeCombo = setupCombo('typeValue', 'typeMenu', () => typeOptions, () => {
                scaleCombo.refreshMenu();
                makerCombo.refreshMenu();
                subjectBrandCombo.refreshMenu();
            });

            typeInput.addEventListener('input', () => {
                typeCombo.refreshMenu();
                scaleCombo.refreshMenu();
                makerCombo.refreshMenu();
                subjectBrandCombo.refreshMenu();
            });

            document.addEventListener(
                'click',
                (e) => {
                    const t = e.target;
                    const menuHit = t.closest('.combo-menu');
                    if (menuHit) {
                        document.querySelectorAll('.combo-menu').forEach((menu) => {
                            if (menu !== menuHit) menu.style.display = 'none';
                        });
                        return;
                    }
                    const combo = t.closest('.combo');
                    document.querySelectorAll('.combo-menu').forEach((menuEl) => {
                        const owner = menuEl.closest('.combo');
                        if (owner !== combo) menuEl.style.display = 'none';
                    });
                },
                true
            );

            const focusX = document.getElementById('imageFocusX');
            const focusY = document.getElementById('imageFocusY');
            const focusXValue = document.getElementById('imageFocusXValue');
            const focusYValue = document.getElementById('imageFocusYValue');
            const imageInput = document.querySelector('input[name="image"]');
            const preview = document.getElementById('imageFocusPreview');
            const previewEmpty = document.getElementById('imageFocusEmpty');
            const previewWrap = document.getElementById('focusPreviewWrap');
            let objectUrl = '';
            const syncFocus = () => {
                if (focusX && focusXValue) focusXValue.textContent = focusX.value;
                if (focusY && focusYValue) focusYValue.textContent = focusY.value;
                if (preview && focusX && focusY) preview.style.objectPosition = `${focusX.value}% ${focusY.value}%`;
            };
            const syncPreview = () => {
                const file = imageInput?.files?.[0];
                if (!preview || !previewEmpty) return;
                if (objectUrl) URL.revokeObjectURL(objectUrl);
                if (!file) {
                    objectUrl = '';
                    preview.removeAttribute('src');
                    preview.style.display = 'none';
                    if (previewWrap) previewWrap.style.display = 'none';
                    previewEmpty.style.display = 'flex';
                    return;
                }
                objectUrl = URL.createObjectURL(file);
                preview.src = objectUrl;
                preview.style.display = 'block';
                if (previewWrap) previewWrap.style.display = 'block';
                previewEmpty.style.display = 'none';
                syncFocus();
            };
            if (focusX) focusX.addEventListener('input', syncFocus);
            if (focusY) focusY.addEventListener('input', syncFocus);
            if (imageInput) imageInput.addEventListener('change', syncPreview);
            syncFocus();
            syncPreview();
        })();
        (function () {
            const tabBar = document.getElementById('createTabs');
            if (!tabBar) return;
            const buttons = tabBar.querySelectorAll('.create-tab[data-tab]');
            const panels = document.querySelectorAll('.create-tab-panel[data-tab-panel]');
            const setTab = (name) => {
                buttons.forEach((btn) => {
                    const on = btn.getAttribute('data-tab') === name;
                    btn.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                panels.forEach((p) => {
                    p.hidden = p.getAttribute('data-tab-panel') !== name;
                });
                try {
                    const url = new URL(window.location.href);
                    if (name === 'import') {
                        url.searchParams.set('tab', 'import');
                    } else {
                        url.searchParams.delete('tab');
                    }
                    const q = url.searchParams.toString();
                    window.history.replaceState({}, '', url.pathname + (q ? '?' + q : '') + url.hash);
                } catch (e) { /* ignore */ }
            };
            buttons.forEach((btn) => {
                btn.addEventListener('click', () => setTab(btn.getAttribute('data-tab') || 'single'));
            });
        })();
        (function () {
            const input = document.getElementById('importFileInput');
            const zone = document.getElementById('importDropzone');
            const label = document.getElementById('importFileLabel');
            const invalidTypeMsg = @json(__('ui.import_file_type_error'));
            if (!input || !zone) return;

            const allowed = /\.(xlsx|xls)$/i;
            const syncLabel = () => {
                const f = input.files && input.files[0];
                if (label) label.textContent = f ? f.name : '';
            };
            const setFileFromList = (fileList) => {
                const file = fileList && fileList[0];
                if (!file) return;
                if (!allowed.test(file.name)) {
                    input.setCustomValidity(invalidTypeMsg);
                    input.reportValidity();
                    input.setCustomValidity('');
                    return;
                }
                const dt = new DataTransfer();
                dt.items.add(file);
                input.files = dt.files;
                input.setCustomValidity('');
                syncLabel();
            };

            zone.addEventListener('click', () => input.click());
            zone.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    input.click();
                }
            });
            input.addEventListener('change', syncLabel);

            ['dragenter', 'dragover'].forEach((ev) => {
                zone.addEventListener(ev, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    zone.classList.add('import-dropzone--active');
                });
            });
            zone.addEventListener('dragleave', (e) => {
                if (!zone.contains(e.relatedTarget)) {
                    zone.classList.remove('import-dropzone--active');
                }
            });
            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                zone.classList.remove('import-dropzone--active');
                setFileFromList(e.dataTransfer && e.dataTransfer.files);
            });

            syncLabel();
        })();
    </script>
@endsection
