<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()->with('categories')->withSum('orderItems as sold', 'qty')
            ->when($request->filled('q'), function (Builder $q) use ($request) {
                $t = trim((string) $request->input('q'));
                $q->where(fn (Builder $w) => $w->where('name', 'like', "%{$t}%")->orWhere('design_no', 'like', "%{$t}%"));
            })
            ->when($request->filled('category'), fn (Builder $q) => $q->whereHas('categories', fn (Builder $c) => $c->where('categories.id', (int) $request->input('category'))))
            ->when($request->input('status') === 'active', fn (Builder $q) => $q->where('is_active', true))
            ->when($request->input('status') === 'hidden', fn (Builder $q) => $q->where('is_active', false))
            ->when($request->input('status') === 'signature', fn (Builder $q) => $q->where('is_signature', true))
            ->when($request->input('status') === 'out', fn (Builder $q) => $q->where('in_stock', false))
            ->when($request->input('status') === 'noprice', fn (Builder $q) => $q->whereNull('price'))
            ->ordered()->paginate(20)->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $next = (int) Product::query()->get(['design_no'])->map(fn ($p) => (int) preg_replace('/\D/', '', $p->design_no))->max() + 1;

        return view('admin.products.form', [
            'product' => new Product([
                'design_no' => str_pad((string) $next, 2, '0', STR_PAD_LEFT),
                'size_type' => 'alpha', 'is_active' => true, 'in_stock' => true, 'sort_order' => 0,
            ]),
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $product = new Product();
        $this->save($request, $product);

        return redirect()->route('admin.products.index')->with('ok', "Product “{$product->name}” created.");
    }

    public function edit(Product $product): View
    {
        $product->load('categories');

        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::query()->ordered()->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->save($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('ok', 'Product saved.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update(['is_active' => ! $product->is_active]);

        return back()->with('ok', $product->is_active ? "“{$product->name}” is now visible in the shop." : "“{$product->name}” is now hidden from the shop.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        $name = $product->name;
        \App\Support\Audit::log('product.deleted', ['design_no' => $product->design_no, 'name' => $name]);
        $this->deleteUpload($product->image);
        $product->delete();

        return redirect()->route('admin.products.index')->with('ok', "Product “{$name}” deleted.");
    }

    private function save(Request $request, Product $product): void
    {
        $data = $request->validate([
            'design_no' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/', Rule::unique('products', 'design_no')->ignore($product->id)],
            'name' => ['required', 'string', 'max:160'],
            'description' => ['nullable', 'string', 'max:3000'],
            'size_type' => ['required', Rule::in(array_keys(config('store.sizes')))],
            'colours' => ['nullable', 'array'],
            'colours.*' => ['string', Rule::in(array_keys(config('store.colours')))],
            'price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'price_large' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'compare_price' => ['nullable', 'integer', 'min:0', 'max:10000000'],
            'tag' => ['nullable', 'string', 'max:30'],
            'sort_order' => ['nullable', 'integer', 'min:-9999', 'max:9999'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['integer', 'exists:categories,id'],
            // Checked against the real file contents, not just the file name
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'mimetypes:image/jpeg,image/png,image/webp',
                'max:'.config('security.upload_max_kb', 5120),
                'dimensions:max_width='.config('security.upload_max_pixels', 6000).',max_height='.config('security.upload_max_pixels', 6000)],
        ], [
            'design_no.regex' => 'Use letters, numbers or dashes only.',
            'image.mimes' => 'Upload a JPG, PNG or WEBP image.',
            'image.max' => 'The image must be 5 MB or smaller.',
            'image.image' => 'That file is not a valid image.',
            'image.mimetypes' => 'Upload a JPG, PNG or WEBP image.',
            'image.dimensions' => 'The image is too large. Please use a photo under 6000 × 6000 pixels.',
        ]);

        $product->fill([
            'design_no' => strtoupper($data['design_no']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'size_type' => $data['size_type'],
            'colours' => array_values($data['colours'] ?? []) ?: null,
            'price' => $data['price'] ?? null,
            'price_large' => $data['size_type'] === 'waist' ? ($data['price_large'] ?? null) : null,
            'compare_price' => $data['compare_price'] ?? null,
            'price_from' => $request->boolean('price_from'),
            'tag' => $data['tag'] ?? null,
            'is_signature' => $request->boolean('is_signature'),
            'is_active' => $request->boolean('is_active'),
            'in_stock' => $request->boolean('in_stock'),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ]);
        $product->slug = $this->uniqueSlug($product);

        if ($request->boolean('remove_image')) {
            $this->deleteUpload($product->image);
            $product->image = null;
        }
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $this->deleteUpload($product->image);
            $product->image = $this->storeUpload($request->file('image'), $product->slug);
        }

        $product->save();
        $product->categories()->sync($data['categories'] ?? []);
    }

    private function uniqueSlug(Product $product): string
    {
        $base = Str::slug($product->name.'-'.$product->design_no) ?: 'product';
        $slug = $base;
        $i = 2;
        while (Product::query()->where('slug', $slug)->whereKeyNot($product->id ?? 0)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Uploads go straight into public/uploads so no storage symlink is needed on cPanel. */
    private function storeUpload(UploadedFile $file, string $slug): string
    {
        $dir = public_path('uploads/products');
        File::ensureDirectoryExists($dir);
        // Extension comes from the detected file type, never from the uploaded file name
        $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$file->getMimeType()] ?? null;
        if (! $ext) {
            throw \Illuminate\Validation\ValidationException::withMessages(['image' => 'Upload a JPG, PNG or WEBP image.']);
        }
        $name = Str::limit(Str::slug($slug), 60, '').'-'.Str::lower(Str::random(10)).'.'.$ext;
        $file->move($dir, $name);

        return 'uploads/products/'.$name;
    }

    private function deleteUpload(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/') && ! str_contains($path, '..')) {
            File::delete(public_path($path));
        }
    }
}
