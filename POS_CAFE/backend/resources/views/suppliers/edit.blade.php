<x-app-layout>
    <x-slot name="header">
        Edit Supplier: {{ $supplier->name }}
    </x-slot>

    <div class="max-w-2xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('suppliers.update', $supplier->id) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Company / Supplier Name *</label>
                <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Contact Person</label>
                    <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $supplier->email) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Physical Address</label>
                    <input type="text" name="address" value="{{ old('address', $supplier->address) }}" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>
            </div>

            <div class="flex items-center space-x-2 pt-2">
                <input type="checkbox" name="is_active" value="1" id="is_active" {{ $supplier->is_active ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_active" class="text-xs font-semibold text-slate-700">Active supplier</label>
            </div>

            <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                <a href="{{ route('suppliers.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Update Supplier</button>
            </div>
        </form>
    </div>
</x-app-layout>
