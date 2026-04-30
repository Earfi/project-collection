<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ItemExcelImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->string('type')->toString();
        $scale = $request->string('scale')->toString();
        $maker = $request->string('maker')->toString();
        $subjectBrand = $request->string('subject_brand')->toString();

        $items = Item::when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($scale !== '', fn ($query) => $query->where('scale', $scale))
            ->when($maker !== '', fn ($query) => $query->where('maker', $maker))
            ->when($subjectBrand !== '', fn ($query) => $query->where('subject_brand', $subjectBrand))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $types = Item::select('type')->distinct()->orderBy('type')->pluck('type');
        $scales = Item::whereNotNull('scale')->select('scale')->distinct()->orderBy('scale')->pluck('scale');
        $makers = Item::whereNotNull('maker')->select('maker')->distinct()->orderBy('maker')->pluck('maker');
        $subjectBrands = Item::whereNotNull('subject_brand')->select('subject_brand')->distinct()->orderBy('subject_brand')->pluck('subject_brand');

        return view('items.index', compact(
            'items',
            'types',
            'scales',
            'makers',
            'subjectBrands',
            'type',
            'scale',
            'maker',
            'subjectBrand'
        ));
    }

    public function create(): View
    {
        $types = Item::select('type')->distinct()->orderBy('type')->pluck('type');
        $allScales = Item::whereNotNull('scale')->where('scale', '!=', '')->select('scale')->distinct()->orderBy('scale')->pluck('scale');
        $allMakers = Item::whereNotNull('maker')->where('maker', '!=', '')->select('maker')->distinct()->orderBy('maker')->pluck('maker');
        $allSubjectBrands = Item::whereNotNull('subject_brand')->where('subject_brand', '!=', '')->select('subject_brand')->distinct()->orderBy('subject_brand')->pluck('subject_brand');
        $typeMetadata = $this->buildTypeMetadata();

        return view('items.create', compact('types', 'allScales', 'allMakers', 'allSubjectBrands', 'typeMetadata'));
    }

    public function edit(Item $item): View
    {
        $types = Item::select('type')->distinct()->orderBy('type')->pluck('type');
        $allScales = Item::whereNotNull('scale')->where('scale', '!=', '')->select('scale')->distinct()->orderBy('scale')->pluck('scale');
        $allMakers = Item::whereNotNull('maker')->where('maker', '!=', '')->select('maker')->distinct()->orderBy('maker')->pluck('maker');
        $allSubjectBrands = Item::whereNotNull('subject_brand')->where('subject_brand', '!=', '')->select('subject_brand')->distinct()->orderBy('subject_brand')->pluck('subject_brand');
        $typeMetadata = $this->buildTypeMetadata();

        return view('items.edit', compact('item', 'types', 'allScales', 'allMakers', 'allSubjectBrands', 'typeMetadata'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateItem($request);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $watermarkedImage = $this->createWatermarkedImage($request->file('image')->getRealPath());
            $filename = 'items/'.uniqid('item_', true).'.jpg';
            Storage::disk('public')->put($filename, $watermarkedImage);
            $imagePath = $filename;
        }

        Item::create([
            'type' => $validated['type'],
            'scale' => $validated['scale'] ?? null,
            'maker' => $validated['maker'] ?? null,
            'subject_brand' => $validated['subject_brand'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? 0,
            'qty' => $validated['qty'] ?? 1,
            'collected_at' => $validated['collected_at'] ?? null,
            'image_path' => $imagePath,
            'image_focus_x' => $validated['image_focus_x'] ?? 50,
            'image_focus_y' => $validated['image_focus_y'] ?? 50,
        ]);

        return redirect()->route('items.index')->with('status', 'เพิ่มของสะสมเรียบร้อยแล้ว');
    }

    public function importTemplate(): BinaryFileResponse
    {
        $tmp = tempnam(sys_get_temp_dir(), 'item_tpl_');
        if ($tmp === false) {
            abort(500);
        }

        (new ItemExcelImporter)->writeTemplateToPath($tmp);

        return response()->download($tmp, 'item-import-template.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function importExcel(Request $request): RedirectResponse
    {
        $importTab = ['tab' => 'import'];

        $validator = Validator::make($request->all(), [
            'import_file' => ['required', 'file', 'mimes:xlsx,xls', 'max:30720'],
        ]);
        if ($validator->fails()) {
            return redirect()->route('items.create', $importTab)->withErrors($validator);
        }

        try {
            $parsed = (new ItemExcelImporter)->parse($request->file('import_file'));
        } catch (\Throwable $e) {
            return redirect()
                ->route('items.create', $importTab)
                ->withErrors(['import_file' => $e->getMessage()]);
        }

        if ($parsed['items'] === []) {
            $hint = $parsed['messages'][0] ?? __('ui.excel_import_empty');

            return redirect()
                ->route('items.create', $importTab)
                ->withErrors(['import_file' => $hint]);
        }

        $created = 0;
        DB::transaction(function () use ($parsed, &$created): void {
            foreach ($parsed['items'] as $row) {
                $fields = $row['fields'];
                $imagePath = null;
                $bytes = $row['image_bytes'];
                if ($bytes !== null && $bytes !== '') {
                    $tmp = tempnam(sys_get_temp_dir(), 'item_img_');
                    if ($tmp !== false) {
                        try {
                            file_put_contents($tmp, $bytes);
                            $watermarked = $this->createWatermarkedImage($tmp);
                            $filename = 'items/'.uniqid('item_', true).'.jpg';
                            Storage::disk('public')->put($filename, $watermarked);
                            $imagePath = $filename;
                        } finally {
                            @unlink($tmp);
                        }
                    }
                }

                Item::create([
                    'type' => $fields['type'],
                    'scale' => $fields['scale'],
                    'maker' => $fields['maker'],
                    'subject_brand' => $fields['subject_brand'],
                    'name' => $fields['name'],
                    'description' => $fields['description'],
                    'price' => $fields['price'],
                    'qty' => $fields['qty'],
                    'collected_at' => $fields['collected_at'],
                    'image_path' => $imagePath,
                    'image_focus_x' => $fields['image_focus_x'],
                    'image_focus_y' => $fields['image_focus_y'],
                ]);
                $created++;
            }
        });

        $status = __('ui.import_success', ['count' => $created]);
        if ($parsed['messages'] !== []) {
            $status .= ' — '.implode(' ', array_slice($parsed['messages'], 0, 5));
            if (count($parsed['messages']) > 5) {
                $status .= ' …';
            }
        }

        return redirect()
            ->route('items.index')
            ->with('status', $status);
    }

    public function update(Request $request, Item $item): RedirectResponse
    {
        $validated = $this->validateItem($request);

        $imagePath = $item->image_path;
        if ($request->hasFile('image')) {
            $watermarkedImage = $this->createWatermarkedImage($request->file('image')->getRealPath());
            $filename = 'items/'.uniqid('item_', true).'.jpg';
            Storage::disk('public')->put($filename, $watermarkedImage);
            $imagePath = $filename;
        }

        $item->update([
            'type' => $validated['type'],
            'scale' => $validated['scale'] ?? null,
            'maker' => $validated['maker'] ?? null,
            'subject_brand' => $validated['subject_brand'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price' => $validated['price'] ?? 0,
            'qty' => $validated['qty'] ?? 1,
            'collected_at' => $validated['collected_at'] ?? null,
            'image_path' => $imagePath,
            'image_focus_x' => $validated['image_focus_x'] ?? 50,
            'image_focus_y' => $validated['image_focus_y'] ?? 50,
        ]);

        return redirect()->route('items.index')->with('status', 'อัปเดตรายการเรียบร้อยแล้ว');
    }

    public function destroy(Item $item): RedirectResponse
    {
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }

        $item->delete();

        return redirect()->route('items.index')->with('status', 'ลบรายการเรียบร้อยแล้ว');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer', 'exists:items,id'],
        ])->validate();

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        $deleted = 0;
        DB::transaction(function () use ($ids, &$deleted): void {
            $items = Item::whereIn('id', $ids)->get();
            foreach ($items as $item) {
                if ($item->image_path) {
                    Storage::disk('public')->delete($item->image_path);
                }
            }
            $deleted = Item::whereIn('id', $ids)->delete();
        });

        return redirect()
            ->route('items.index')
            ->with('status', __('ui.bulk_deleted', ['count' => $deleted]));
    }

    private function validateItem(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'scale' => ['nullable', 'string', 'max:50'],
            'maker' => ['nullable', 'string', 'max:100'],
            'subject_brand' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'qty' => ['nullable', 'integer', 'min:0'],
            'collected_at' => ['nullable', 'date'],
            'image' => ['nullable', 'image', 'max:5120'],
            'image_focus_x' => ['nullable', 'integer', 'between:0,100'],
            'image_focus_y' => ['nullable', 'integer', 'between:0,100'],
        ]);
    }

    private function createWatermarkedImage(string $filePath): string
    {
        $content = file_get_contents($filePath);
        $image = imagecreatefromstring($content ?: '');

        if (! $image) {
            return (string) $content;
        }

        $text = (string) (config('collection.watermark_text') ?: 'EARF PICHAYA');
        $textColor = imagecolorallocate($image, 255, 255, 255);
        $shadowColor = imagecolorallocate($image, 0, 0, 0);

        $font = 5;
        $x = max(10, imagesx($image) - (imagefontwidth($font) * strlen($text)) - 20);
        $y = max(10, imagesy($image) - imagefontheight($font) - 20);

        imagestring($image, $font, $x + 1, $y + 1, $text, $shadowColor);
        imagestring($image, $font, $x, $y, $text, $textColor);

        ob_start();
        imagejpeg($image, null, 85);
        $binary = ob_get_clean() ?: '';

        imagedestroy($image);

        return $binary;
    }

    private function buildTypeMetadata(): array
    {
        return Item::select('type', 'scale', 'maker', 'subject_brand')
            ->get()
            ->groupBy(fn ($row) => trim((string) $row->type))
            ->mapWithKeys(function ($rows, $type) {
                if ($type === '') {
                    return [];
                }

                return [$type => [
                    'scales' => $rows->pluck('scale')->filter(fn ($value) => filled($value))->unique()->sort()->values(),
                    'makers' => $rows->pluck('maker')->filter(fn ($value) => filled($value))->unique()->sort()->values(),
                    'subjectBrands' => $rows->pluck('subject_brand')->filter(fn ($value) => filled($value))->unique()->sort()->values(),
                ]];
            })
            ->all();
    }
}
