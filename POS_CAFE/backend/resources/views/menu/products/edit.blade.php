<x-app-layout>
    <x-slot name="header">
        Edit Product: {{ $product->name }}
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('products.update', $product->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Code *</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                    <select name="category_id" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Base Price ($) *</label>
                    <input type="number" step="0.01" name="base_price" value="{{ old('base_price', $product->base_price) }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('description', $product->description) }}</textarea>
            </div>

            <!-- Realistic Product Photo Section -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Product Photo</h4>
                        <p class="text-[11px] text-slate-500">Update photo by uploading a file, entering a URL, or selecting from real drink photos.</p>
                    </div>
                    <button type="button" onclick="openPhotoPickerModal()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-xs shadow-xs transition flex items-center space-x-1">
                        <span>🔍</span>
                        <span>Browse Photo Library</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <!-- Image Preview -->
                    <div class="flex flex-col items-center justify-center p-3 border-2 border-dashed border-slate-300 rounded-xl bg-white text-center h-32 relative overflow-hidden group">
                        <img id="imagePreview" src="{{ $product->image_path ?: 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80' }}" alt="Preview" class="w-full h-full object-cover rounded-lg">
                        <span class="absolute bottom-1 bg-slate-900/80 text-white text-[9px] px-2 py-0.5 rounded font-mono">Current Photo</span>
                    </div>

                    <!-- Upload and URL inputs -->
                    <div class="md:col-span-2 space-y-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Option A: Upload New Photo File</label>
                            <input type="file" name="image" id="imageFileInput" accept="image/*" onchange="previewSelectedFile(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Option B: Image URL</label>
                            <input type="url" name="image_url" id="imageUrlInput" value="{{ $product->image_path }}" placeholder="https://images.unsplash.com/..." oninput="previewUrlInput(this.value)" class="w-full text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_available" value="1" id="is_available" {{ $product->is_available ? 'checked' : '' }} class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_available" class="text-xs font-semibold text-slate-700">Available for ordering</label>
            </div>

            <!-- Variants List -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Variants & Sizes</h4>
                        <p class="text-[11px] text-slate-500">Configured variants and price deltas</p>
                    </div>
                    <button type="button" onclick="addVariantRow()" class="text-xs font-bold text-amber-600 hover:text-amber-700">+ Add Variant</button>
                </div>

                <div id="variantsContainer" class="space-y-2">
                    @forelse($product->variants as $index => $variant)
                        <div class="flex items-center space-x-2">
                            <input type="text" name="variants[{{ $index }}][name]" value="{{ $variant->name }}" placeholder="Variant Name" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="number" step="0.01" name="variants[{{ $index }}][price_modifier]" value="{{ $variant->price_modifier }}" placeholder="Price Delta" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="text" name="variants[{{ $index }}][sku]" value="{{ $variant->sku }}" placeholder="SKU" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
                        </div>
                    @empty
                        <div class="flex items-center space-x-2">
                            <input type="text" name="variants[0][name]" placeholder="Variant Name" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="number" step="0.01" name="variants[0][price_modifier]" value="0.00" placeholder="Price Delta" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                            <input type="text" name="variants[0][sku]" placeholder="SKU" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Update Product</button>
            </div>
        </form>
    </div>

    <!-- Photo Library Modal -->
    <div id="photoLibraryModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl flex flex-col max-h-[85vh]">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Select Professional Product Photo</h3>
                    <p class="text-xs text-slate-500">Click any drink or pastry photo to apply it to this product.</p>
                </div>
                <button onclick="closePhotoPickerModal()" class="text-slate-400 hover:text-slate-600 text-xl font-bold">&times;</button>
            </div>

            <div class="py-3 flex items-center space-x-2">
                <input type="text" id="photoSearchQuery" placeholder="Search coffee, tea, latte, croissant, muffin..." oninput="filterPhotoGallery()" class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-xl">
                <button type="button" onclick="loadPhotosFromApi('all')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">All</button>
                <button type="button" onclick="loadPhotosFromApi('coffee')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Coffee</button>
                <button type="button" onclick="loadPhotosFromApi('tea')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Tea</button>
                <button type="button" onclick="loadPhotosFromApi('bakery')" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl">Bakery</button>
            </div>

            <div id="photoGridContainer" class="flex-1 overflow-y-auto grid grid-cols-2 sm:grid-cols-3 gap-3 p-1">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <script>
        let variantIndex = {{ $product->variants->count() ?: 1 }};
        function addVariantRow() {
            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'flex items-center space-x-2';
            row.innerHTML = `
                <input type="text" name="variants[${variantIndex}][name]" placeholder="Variant Name (e.g. Large)" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="number" step="0.01" name="variants[${variantIndex}][price_modifier]" placeholder="Price Delta ($0.80)" value="0.50" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="text" name="variants[${variantIndex}][sku]" placeholder="SKU" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 text-xs px-2 font-bold">&times;</button>
            `;
            container.appendChild(row);
            variantIndex++;
        }

        function previewSelectedFile(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function previewUrlInput(url) {
            if (url && url.trim().length > 5) {
                document.getElementById('imagePreview').src = url.trim();
            }
        }

        let allLoadedPhotos = [];

        async function openPhotoPickerModal() {
            document.getElementById('photoLibraryModal').classList.remove('hidden');
            if (allLoadedPhotos.length === 0) {
                await loadPhotosFromApi('all');
            }
        }

        function closePhotoPickerModal() {
            document.getElementById('photoLibraryModal').classList.add('hidden');
        }

        async function loadPhotosFromApi(category = 'all') {
            try {
                const res = await fetch(`{{ route('photos.search') }}?q=${category}`);
                const data = await res.json();
                allLoadedPhotos = data.photos || [];
                renderPhotos(allLoadedPhotos);
            } catch (e) {
                console.error("Failed to load photos", e);
            }
        }

        function renderPhotos(photos) {
            const container = document.getElementById('photoGridContainer');
            container.innerHTML = '';
            photos.forEach(photo => {
                const card = document.createElement('div');
                card.className = 'group relative rounded-xl overflow-hidden border border-slate-200 hover:border-amber-500 cursor-pointer shadow-xs transition hover:shadow-md';
                card.onclick = () => selectPhotoFromPicker(photo.url);
                card.innerHTML = `
                    <div class="h-28 w-full bg-slate-100 overflow-hidden">
                        <img src="${photo.url}" alt="${photo.title}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                    </div>
                    <div class="p-2 bg-white">
                        <span class="text-[11px] font-bold text-slate-800 line-clamp-1">${photo.title}</span>
                        <span class="text-[9px] uppercase font-semibold text-amber-600">${photo.category}</span>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function filterPhotoGallery() {
            const query = document.getElementById('photoSearchQuery').value.toLowerCase().trim();
            if (!query) {
                renderPhotos(allLoadedPhotos);
                return;
            }
            const filtered = allLoadedPhotos.filter(p => p.title.toLowerCase().includes(query) || p.category.toLowerCase().includes(query));
            renderPhotos(filtered);
        }

        function selectPhotoFromPicker(url) {
            document.getElementById('imageUrlInput').value = url;
            previewUrlInput(url);
            closePhotoPickerModal();
        }
    </script>
</x-app-layout>
