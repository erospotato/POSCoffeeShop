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

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'prod_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $data['image_path'] = '/uploads/products/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image_path'] = $request->input('image_url');
        }

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

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = 'prod_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/products'), $filename);
            $data['image_path'] = '/uploads/products/' . $filename;
        } elseif ($request->filled('image_url')) {
            $data['image_path'] = $request->input('image_url');
        }

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

    public function searchPhotos(Request $request)
    {
        $query = strtolower(trim($request->input('q', '')));

        $library = [
            // Coffee items
            ['title' => 'Espresso Shot', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1510591509098-f4fdc6d0ff04?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Caffè Cappuccino', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Vanilla Bean Latte', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1561047029-3000c68339ca?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Classic Americano', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Caramel Cloud Macchiato', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1485808191679-5f86510681a2?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Iced Cold Brew', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Mocha Delight', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1578314675249-a6910f80cc4e?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Flat White', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1577968897966-3d4325b36b61?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Iced Spanish Latte', 'category' => 'coffee', 'url' => 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=600&q=80'],

            // Teas & Refreshers
            ['title' => 'Ceremonial Matcha Latte', 'category' => 'tea', 'url' => 'https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Iced Peach Green Tea', 'category' => 'tea', 'url' => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Hibiscus Berry Infusion', 'category' => 'tea', 'url' => 'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Earl Grey Milk Tea', 'category' => 'tea', 'url' => 'https://images.unsplash.com/photo-1544787219-7f47ccb76574?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Lemon Iced Tea', 'category' => 'tea', 'url' => 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=600&q=80'],

            // Bakery & Pastries
            ['title' => 'Butter Flaky Croissant', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Wild Blueberry Muffin', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1607958996333-41aef7caefaa?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Pain au Chocolat', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1530610476181-d83430b64dcd?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Cinnamon Swirl Roll', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'New York Cheesecake', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Chocolate Fudge Brownie', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Sourdough Avocado Toast', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1525351484163-7529414344d8?auto=format&fit=crop&w=600&q=80'],
            ['title' => 'Almond Danish Pastry', 'category' => 'bakery', 'url' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=600&q=80'],
        ];

        if ($query !== '' && $query !== 'all') {
            $filtered = array_values(array_filter($library, function($item) use ($query) {
                return str_contains(strtolower($item['title']), $query)
                    || str_contains(strtolower($item['category']), $query);
            }));
            return response()->json(['photos' => !empty($filtered) ? $filtered : $library]);
        }

        return response()->json(['photos' => $library]);
    }
}
