<?php

namespace App\Http\Controllers\Management;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\PromotionRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\AuditService;

class PromotionController extends Controller
{
    public function index()
    {
        $promotions = Promotion::latest()->paginate(15);
        return view('promotions.index', compact('promotions'));
    }

    public function create()
    {
        $products = Product::where('is_available', true)->get();
        $categories = Category::where('is_active', true)->get();

        return view('promotions.create', compact('products', 'categories'));
    }

    public function store(PromotionRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $promotion = Promotion::create($data);
        AuditService::log('promotion_created', $promotion);

        return redirect()->route('promotions.index')->with('success', "Promotion '{$promotion->name}' created.");
    }

    public function edit(Promotion $promotion)
    {
        $products = Product::where('is_available', true)->get();
        $categories = Category::where('is_active', true)->get();

        return view('promotions.edit', compact('promotion', 'products', 'categories'));
    }

    public function update(PromotionRequest $request, Promotion $promotion)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', false);

        $promotion->update($data);
        AuditService::log('promotion_updated', $promotion);

        return redirect()->route('promotions.index')->with('success', "Promotion '{$promotion->name}' updated.");
    }

    public function destroy(Promotion $promotion)
    {
        $name = $promotion->name;
        $promotion->delete();
        AuditService::log('promotion_deleted', null, ['name' => $name]);

        return redirect()->route('promotions.index')->with('success', "Promotion '{$name}' deleted.");
    }
}
