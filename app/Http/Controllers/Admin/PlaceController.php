<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Support\AdminPagination;
use App\Support\AdminShell;
use App\Support\CacheKeys;
use App\Support\Format;
use App\Support\Media;
use App\Support\PlaceSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Inertia\Inertia;
use Inertia\Response;

class PlaceController extends Controller
{
    // 1. TAMPILKAN DAFTAR DATA
    public function index(): Response
    {
        $places = Place::latest()->paginate(10);

        return Inertia::render('Admin/Places/Index', [
            'copy' => [
                ...AdminShell::copy(),
                ...$this->copy(),
            ],
            'places' => [
                'data' => $places->getCollection()
                    ->map(fn (Place $place): array => $this->serializePlace($place))
                    ->values()
                    ->all(),
                'pagination' => AdminPagination::serialize($places, __('Menampilkan :from–:to dari :total destinasi', [
                    'from' => Format::number($places->firstItem() ?? 0),
                    'to' => Format::number($places->lastItem() ?? 0),
                    'total' => Format::number($places->total()),
                ])),
            ],
            'routes' => [
                ...AdminShell::routes(),
                'createPlace' => route('admin.places.create', absolute: false),
            ],
        ]);
    }

    // 2. TAMPILKAN FORM TAMBAH
    public function create(): View
    {
        return view('admin.places.create', [
            'scheduleOptions' => PlaceSchedule::options(),
            'scheduleValues' => PlaceSchedule::formValues(null),
        ]);
    }

    // 3. SIMPAN DATA BARU
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_id' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'description_id' => 'nullable|string|required_with:description_en',
            'description_en' => 'nullable|string|required_with:description_id',
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
                Rule::dimensions()
                    ->maxWidth((int) config('media.max_width', 6000))
                    ->maxHeight((int) config('media.max_height', 6000)),
            ],
            'address' => 'nullable|string|max:255',
            'facilities' => 'nullable|string',
            ...PlaceSchedule::rules(),
        ]);

        $schedule = PlaceSchedule::fromValidated($validated);
        $scheduleAttributes = $schedule === null ? [
            'open_days' => null,
            'open_hours' => null,
            'opening_hours' => null,
        ] : PlaceSchedule::attributes($schedule);

        $description = null;
        if (! empty($validated['description_id']) || ! empty($validated['description_en'])) {
            $description = [
                'id' => $validated['description_id'],
                'en' => $validated['description_en'],
            ];
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = Media::storeUploadedImage($request->file('image'), 'uploads/places');
        }

        Place::create([
            'name' => [
                'id' => $validated['name_id'],
                'en' => $validated['name_en'],
            ],
            'slug' => Str::slug($validated['name_en']).'-'.Str::random(5),
            'description' => $description,
            'image' => $imagePath,
            'address' => $validated['address'],
            'facilities' => $validated['facilities'],
            ...$scheduleAttributes,
            'created_by' => Auth::guard('admin')->id(),
        ]);

        CacheKeys::bump(CacheKeys::PLACES_VERSION);

        return redirect()->route('admin.places.index')->with('success', __('Destinasi berhasil ditambahkan.'));
    }

    // 4. TAMPILKAN FORM EDIT (BARU)
    public function edit(Place $place): View
    {
        $scheduleValues = PlaceSchedule::formValues($place->opening_hours);
        $hasStructuredSchedule = $scheduleValues['open_day_start'] !== '';

        return view('admin.places.edit', [
            'hasSchedule' => $hasStructuredSchedule || filled($place->open_days) || filled($place->open_hours),
            'legacySchedule' => $hasStructuredSchedule ? null : collect([$place->open_days, $place->open_hours])
                ->filter(fn (mixed $value): bool => filled($value))
                ->implode(' · '),
            'place' => $place,
            'scheduleOptions' => PlaceSchedule::options(),
            'scheduleValues' => $scheduleValues,
        ]);
    }

    // 5. UPDATE DATA (BARU)
    public function update(Request $request, Place $place): RedirectResponse
    {
        $validated = $request->validate([
            'name_id' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'description_id' => 'nullable|string|required_with:description_en',
            'description_en' => 'nullable|string|required_with:description_id',
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048',
                Rule::dimensions()
                    ->maxWidth((int) config('media.max_width', 6000))
                    ->maxHeight((int) config('media.max_height', 6000)),
            ],
            'address' => 'nullable|string|max:255',
            'facilities' => 'nullable|string',
            ...PlaceSchedule::rules(),
        ]);

        $schedule = PlaceSchedule::fromValidated($validated);
        $scheduleAttributes = match (true) {
            $request->boolean('clear_schedule') => [
                'open_days' => null,
                'open_hours' => null,
                'opening_hours' => null,
            ],
            $schedule !== null => PlaceSchedule::attributes($schedule),
            default => [],
        };

        // Cek jika ada gambar baru diupload
        if ($request->hasFile('image')) {
            $place->image = Media::replaceUploadedImage($request->file('image'), $place->image, 'uploads/places');
        }

        // Update slug jika nama berubah
        if ($place->getTranslation('name', 'en') !== $validated['name_en']) {
            $place->slug = Str::slug($validated['name_en']).'-'.Str::random(5);
        }

        $description = null;
        if (! empty($validated['description_id']) || ! empty($validated['description_en'])) {
            $description = [
                'id' => $validated['description_id'],
                'en' => $validated['description_en'],
            ];
        }

        $place->update([
            'name' => [
                'id' => $validated['name_id'],
                'en' => $validated['name_en'],
            ],
            'description' => $description,
            'image' => $place->image,
            'address' => $validated['address'],
            'facilities' => $validated['facilities'],
            ...$scheduleAttributes,
        ]);

        CacheKeys::bump(CacheKeys::PLACES_VERSION);

        return redirect()->route('admin.places.index')->with('success', __('Destinasi berhasil diperbarui.'));
    }

    // 6. HAPUS DATA (BARU)
    public function destroy(Place $place): RedirectResponse
    {
        // Hapus gambar dari storage agar tidak menumpuk sampah file
        Media::delete($place->image);

        $place->delete();

        CacheKeys::bump(CacheKeys::PLACES_VERSION);

        return redirect()->route('admin.places.index')->with('success', __('Destinasi berhasil dihapus.'));
    }

    /** @return array<string, mixed> */
    private function serializePlace(Place $place): array
    {
        $name = trim($place->getTranslation('name', app()->getLocale()));
        $address = trim((string) $place->getAttribute('address'));

        return [
            'address' => $address !== '' ? $address : __('Alamat belum diisi.'),
            'deleteConfirmation' => __('Yakin ingin menghapus :name? Data tidak bisa dikembalikan.', [
                'name' => $name,
            ]),
            'deleteUrl' => route('admin.places.destroy', $place, absolute: false),
            'editUrl' => route('admin.places.edit', $place, absolute: false),
            'id' => (int) $place->getKey(),
            'imageUrl' => $place->getImageUrlAttribute() ?? asset('demo/place-placeholder.svg'),
            'name' => $name,
            'reference' => '#'.$place->getKey(),
        ];
    }

    /** @return array<string, string> */
    private function copy(): array
    {
        return [
            'actions' => __('Aksi'),
            'add' => __('Tambah Destinasi'),
            'address' => __('Alamat'),
            'delete' => __('Hapus'),
            'deleting' => __('Menghapus'),
            'description' => __('Kelola informasi destinasi, detail kunjungan, dan media yang tampil di katalog publik.'),
            'edit' => __('Edit'),
            'emptyDescription' => __('Tambahkan destinasi pertama untuk mulai mengisi katalog.'),
            'emptyTitle' => __('Belum ada data destinasi wisata.'),
            'eyebrow' => __('Master Data'),
            'image' => __('Gambar'),
            'name' => __('Nama Destinasi'),
            'next' => __('Berikutnya'),
            'previous' => __('Sebelumnya'),
            'resultsDescription' => __('Menampilkan destinasi terbaru lebih dahulu.'),
            'resultsTitle' => __('Daftar Destinasi'),
            'title' => __('Kelola Destinasi Wisata'),
        ];
    }
}
