<x-app-layout>
    <x-slot name="header">
        Create New Menu Product
    </x-slot>

    <div class="max-w-3xl bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Product Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" id="productNameInput" oninput="autoGenerateSku()" value="{{ old('name') }}" required placeholder="e.g. Iced Spanish Latte" class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">SKU / Code *</label>
                    <div class="flex items-center space-x-1.5">
                        <input type="text" name="sku" id="productSkuInput" oninput="this.dataset.manualEdited='true'" value="{{ old('sku') }}" required placeholder="e.g. COF-SPN-001" class="flex-1 text-xs px-3 py-2 border border-slate-300 rounded-xl font-mono uppercase">
                        <button type="button" onclick="autoGenerateSku(true)" class="px-2.5 py-2 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 text-xs font-bold rounded-xl transition flex items-center space-x-1" title="Generate SKU based on category">
                            <span>⚡</span>
                            <span>Auto</span>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Category *</label>
                    <select name="category_id" id="productCategorySelect" onchange="autoGenerateSku()" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Base Price ($) *</label>
                    <input type="number" step="0.01" name="base_price" value="{{ old('base_price', '3.50') }}" required class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl font-bold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="2" placeholder="Brief beverage or food tasting notes..." class="w-full text-xs px-3 py-2 border border-slate-300 rounded-xl">{{ old('description') }}</textarea>
            </div>

            <!-- Realistic Product Photo Section -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs uppercase tracking-wider">Product Photo</h4>
                        <p class="text-[11px] text-slate-500">Upload a photo, enter an image URL, or choose from realistic drink & pastry photos.</p>
                    </div>
                    <button type="button" onclick="openPhotoPickerModal()" class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-lg text-xs shadow-xs transition flex items-center space-x-1">
                        <span>🔍</span>
                        <span>Browse Photo Library</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
                    <!-- Image Preview -->
                    <div class="flex flex-col items-center justify-center p-3 border-2 border-dashed border-slate-300 rounded-xl bg-white text-center h-32 relative overflow-hidden group">
                        <img id="imagePreview" src="https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=600&q=80" alt="Preview" class="w-full h-full object-cover rounded-lg">
                        <span class="absolute bottom-1 bg-slate-900/80 text-white text-[9px] px-2 py-0.5 rounded font-mono">Photo Preview</span>
                    </div>

                    <!-- Upload and URL inputs -->
                    <div class="md:col-span-2 space-y-2">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Option A: Upload Image File (PNG, JPG, WEBP)</label>
                            <input type="file" name="image" id="imageFileInput" accept="image/*" onchange="previewSelectedFile(this)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-slate-600 mb-1">Option B: Photo URL (Unsplash, CDN, or Web)</label>
                            <input type="url" name="image_url" id="imageUrlInput" placeholder="https://images.unsplash.com/..." oninput="previewUrlInput(this.value)" class="w-full text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center space-x-2">
                <input type="checkbox" name="is_available" value="1" id="is_available" checked class="rounded text-amber-500 focus:ring-amber-400">
                <label for="is_available" class="text-xs font-semibold text-slate-700">Available for ordering immediately in POS</label>
            </div>

            <!-- Dynamic Variants Section (Strictly Optional) -->
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h4 class="font-bold text-slate-900 text-xs">Size & Temperature Variants (Optional)</h4>
                        <p class="text-[11px] text-slate-500">Add multiple sizes or temperature options if applicable (e.g. Hot, Iced, Large).</p>
                    </div>
                    <button type="button" onclick="addVariantRow()" class="text-xs font-bold text-amber-600 hover:text-amber-700 bg-white border border-amber-300 px-3 py-1.5 rounded-lg shadow-2xs transition">+ Add Variant</button>
                </div>

                <div id="variantsContainer" class="space-y-2">
                    <p id="noVariantsNotice" class="text-xs text-slate-400 italic py-1">No variants added (single standard product). Click "+ Add Variant" if this product has sizes or temperatures.</p>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <a href="{{ route('products.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Cancel</a>
                <button type="submit" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-bold rounded-xl text-xs shadow-sm">Save Product</button>
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
        function autoGenerateSku(force = false) {
            const skuInput = document.getElementById('productSkuInput');
            if (!force && skuInput.dataset.manualEdited === 'true' && skuInput.value.trim() !== '') {
                return;
            }
            const catSelect = document.getElementById('productCategorySelect');
            const catText = (catSelect.options[catSelect.selectedIndex]?.text || '').toLowerCase();
            let prefix = 'PRD';
            if (catText.includes('coffee') || catText.includes('espresso')) prefix = 'COF';
            else if (catText.includes('tea') || catText.includes('matcha')) prefix = 'TEA';
            else if (catText.includes('cold') || catText.includes('brew') || catText.includes('refresher')) prefix = 'REF';
            else if (catText.includes('pastr') || catText.includes('baker') || catText.includes('food')) prefix = 'BAK';
            else if (catText.includes('signat')) prefix = 'SIG';
            else if (catText.length >= 3) prefix = catText.substring(0, 3).toUpperCase();

            const name = document.getElementById('productNameInput').value.trim();
            let namePart = 'ITM';
            if (name.length >= 2) {
                const words = name.split(/\s+/);
                if (words.length >= 2) {
                    namePart = (words[0].substring(0, 2) + words[1].substring(0, 1)).toUpperCase();
                } else {
                    namePart = name.substring(0, Math.min(3, name.length)).toUpperCase();
                }
            }
            const randNum = Math.floor(100 + Math.random() * 900);
            skuInput.value = `${prefix}-${namePart}-${randNum}`;
        }

        let variantIndex = 1;
        function addVariantRow() {
            const notice = document.getElementById('noVariantsNotice');
            if (notice) notice.classList.add('hidden');

            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'variant-row flex items-center space-x-2';
            row.innerHTML = `
                <input type="text" name="variants[${variantIndex}][name]" placeholder="Variant Name (e.g. Hot, Iced, Large)" class="flex-2 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="number" step="0.01" name="variants[${variantIndex}][price_modifier]" placeholder="Price Delta ($0.00)" value="0.00" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <input type="text" name="variants[${variantIndex}][sku]" placeholder="SKU suffix" class="flex-1 text-xs px-3 py-1.5 border border-slate-300 rounded-lg">
                <button type="button" onclick="removeVariantRow(this)" class="text-rose-500 hover:text-rose-700 text-xs px-2 font-bold" title="Remove variant">&times;</button>
            `;
            container.appendChild(row);
            variantIndex++;
        }

        function removeVariantRow(btn) {
            btn.closest('.variant-row').remove();
            const container = document.getElementById('variantsContainer');
            if (container.querySelectorAll('.variant-row').length === 0) {
                const notice = document.getElementById('noVariantsNotice');
                if (notice) notice.classList.remove('hidden');
            }
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
