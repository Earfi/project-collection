@extends('layout', ['title' => __('ui.edit_collection')])

@section('content')
    <div class="card">
        <h1>{{ __('ui.edit_collection') }}</h1>
        <p class="muted">{{ __('ui.upload_later_hint') }}</p>
        <form method="POST" action="{{ route('items.update', $item) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div>
                    <label>{{ __('ui.type') }} *</label>
                    <div class="combo">
                        <input class="input" id="typeValue" name="type" value="{{ old('type', $item->type) }}" autocomplete="off" required>
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
                        <input class="input" id="scaleValue" name="scale" value="{{ old('scale', $item->scale) }}" autocomplete="off">
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
                        <input class="input" id="makerValue" name="maker" value="{{ old('maker', $item->maker) }}" autocomplete="off">
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
                        <input class="input" id="subjectBrandValue" name="subject_brand" value="{{ old('subject_brand', $item->subject_brand) }}" autocomplete="off">
                        <div id="subjectBrandMenu" class="combo-menu" style="display:none;">
                            @foreach($allSubjectBrands as $brandOption)
                                <div class="combo-item" data-value="{{ $brandOption }}">{{ $brandOption }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <label>{{ __('ui.name') }} *</label>
            <input class="input" name="name" value="{{ old('name', $item->name) }}" required>

            <label>{{ __('ui.description') }}</label>
            <textarea class="input" name="description">{{ old('description', $item->description) }}</textarea>

            <div class="row">
                <div>
                    <label>{{ __('ui.price') }}</label>
                    <input class="input" name="price" type="number" step="0.01" min="0" value="{{ old('price', $item->price) }}">
                </div>
                <div>
                    <label>{{ __('ui.qty') }}</label>
                    <input class="input" name="qty" type="number" min="0" value="{{ old('qty', $item->qty ?? 1) }}">
                </div>
            </div>
            <div class="row">
                <div>
                    <label>{{ __('ui.collected_date') }}</label>
                    <input class="input" name="collected_at" type="date" value="{{ old('collected_at', $item->collected_at) }}">
                </div>
                <div>
                    <label>{{ __('ui.image_will_watermark') }}</label>
                    <input class="input" name="image" type="file" accept="image/*">
                </div>
            </div>
            <label>{{ __('ui.image_focus') }}</label>
            <div class="row" style="align-items:end;">
                <div>
                    <label for="imageFocusX">{{ __('ui.image_focus_x') }}: <span id="imageFocusXValue">{{ old('image_focus_x', $item->image_focus_x ?? 50) }}</span>%</label>
                    <input class="input" id="imageFocusX" name="image_focus_x" type="range" min="0" max="100" value="{{ old('image_focus_x', $item->image_focus_x ?? 50) }}">
                </div>
                <div>
                    <label for="imageFocusY">{{ __('ui.image_focus_y') }}: <span id="imageFocusYValue">{{ old('image_focus_y', $item->image_focus_y ?? 50) }}</span>%</label>
                    <input class="input" id="imageFocusY" name="image_focus_y" type="range" min="0" max="100" value="{{ old('image_focus_y', $item->image_focus_y ?? 50) }}">
                </div>
            </div>
            <div class="focus-preview-box">
                <div class="muted" style="margin-bottom:6px;">{{ __('ui.image_focus_preview') }}</div>
                <div id="focusPreviewWrap" class="focus-preview-wrap" @if(!$item->image_path) style="display:none;" @endif>
                    <img
                        id="imageFocusPreview"
                        class="focus-preview-image"
                        @if($item->image_path) src="{{ asset('storage/'.$item->image_path) }}" @endif
                        alt="{{ $item->name }}"
                        data-existing-src="{{ $item->image_path ? asset('storage/'.$item->image_path) : '' }}"
                        @if(!$item->image_path) style="display:none;" @endif
                    >
                    <span class="item-photo-wm">{{ config('collection.watermark_text', 'EARF PICHAYA') }}</span>
                </div>
                <div id="imageFocusEmpty" class="muted focus-preview-placeholder" @if($item->image_path) style="display:none;" @endif>{{ __('ui.no_image') }}</div>
                <div class="muted" style="margin-top:6px;">{{ __('ui.image_focus_preview_hint') }}</div>
            </div>
            <div class="field-actions">
                <button class="btn orange" type="submit">{{ __('ui.update') }}</button>
                <a class="btn secondary" href="{{ route('items.index') }}">{{ __('ui.back') }}</a>
            </div>
        </form>
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
            const existingSrc = preview?.dataset.existingSrc || '';
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
                    if (existingSrc) {
                        if (previewWrap) previewWrap.style.display = 'block';
                        preview.src = existingSrc;
                        preview.style.display = 'block';
                        previewEmpty.style.display = 'none';
                    } else {
                        preview.removeAttribute('src');
                        preview.style.display = 'none';
                        if (previewWrap) previewWrap.style.display = 'none';
                        previewEmpty.style.display = 'flex';
                    }
                    syncFocus();
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
    </script>
@endsection
