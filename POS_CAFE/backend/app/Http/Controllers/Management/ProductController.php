<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\ProductVariant;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['category', 'variants', 'addons']);

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        $products = $query->latest()->paginate(15);
        $categories = Category::where('is_active', true)->get();

        return view('menu.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('menu.products.create', compact('categories'));
    }

    public function store(ProductRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(4);
        $data['is_available'] = $request->boolean('is_available', true);

        $product = Product::create($data);

        // Store Variants
        if (!empty($data['variants'])) {
            foreach ($data['variants'] as $vData) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'name' => $vData['name'],
                    'price_modifier' => (float) $vData['price_modifier'],
                    'sku' => $vData['sku'] ?? ($product->sku . '-' . strtoupper(substr($vData['name'], 0, 3))),
                    'is_active' => true,
                ]);
            }
        }

        // Store Addons
        if (!empty($data['addons'])) {
            foreach ($data['addons'] as $aData) {
                ProductAddon::create([
                    'product_id' => $product->id,
                    'name' => $aData['name'],
                    'price' => (float) $aData['price'],
                    'is_active' => true,
                ]);
            }
        }

        AuditService::log('product_created', $product, null, ['name' => $product->name, 'sku' => $product->sku]);

        return redirect()->route('products.index')->with('success', "Product '{$product->name}' created successfully.");
    }

    public function edit(Product $product)
    {
        $product->load(['variants', 'addons', 'recipes.items.ingredient']);
        $categories = Category::where('is_active', true)->get();

        return view('menu.products.edit', compact('product', 'categories'));
    }

    public function update(ProductRequest $request, Product $product)
    {
        $data = $request->validated();
        $data['is_available'] = $request->boolean('is_available', false);

        $old = $product->toArray();
        $product->update($data);

        // Sync variants
        if ($request->has('variants')) {
            $product->variants()->delete();
            foreach ($request->input('variants') as $vData) {
                if (!empty($vData['name'])) {
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'name' => $vData['name'],
                        'price_modifier' => (float) $vData['price_modifier'],
                        'sku' => $vData['sku'] ?? null,
                        'is_active' => true,
                    ]);
                }
            }
        }

        // Sync addons
        if ($request->has('addons')) {
            $product->addons()->delete();
            foreach ($request->input('addons') as $aData) {
                if (!empty($aData['name'])) {
                    ProductAddon::create([
                        'product_id' => $product->id,
                        'name' => $aData['name'],
                        'price' => (float) $aData['price'],
                        'is_active' => true,
                    ]);
                }
            }
        }

        AuditService::log('product_updated', $product, $old, $product->toArray());

        return redirect()->route('products.index')->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function toggleAvailability(Product $product)
    {
        $product->is_available = !$product->is_available;
        $product->save();

        $status = $product->is_available ? 'available' : 'unavailable';
        AuditService::log('product_availability_toggled', $product, null, ['is_available' => $product->is_available]);

        return back()->with('success', "Product '{$product->name}' is now marked as {$status}.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        AuditService::log('product_deleted', null, ['name' => $name]);

        return redirect()->route('products.index')->with('success', "Product '{$name}' removed.");
    }
}
