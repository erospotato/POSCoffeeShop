<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\SupplierRequest;
use App\Models\Supplier;
use App\Services\AuditService;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::withCount('purchaseOrders')->latest()->paginate(15);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(SupplierRequest $request)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        $supplier = Supplier::create($data);
        AuditService::log('supplier_created', $supplier);

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$supplier->name}' added.");
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier)
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', false);

        $supplier->update($data);
        AuditService::log('supplier_updated', $supplier);

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$supplier->name}' updated.");
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchaseOrders()->exists()) {
            return back()->withErrors(['supplier_error' => 'Cannot delete supplier with active purchase order history.']);
        }

        $name = $supplier->name;
        $supplier->delete();
        AuditService::log('supplier_deleted', null, ['name' => $name]);

        return redirect()->route('suppliers.index')->with('success', "Supplier '{$name}' deleted.");
    }
}
