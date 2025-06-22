{{-- File: resources/views/admin/subcategories/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Subcategory')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Edit Subcategory</h1>
            <p class="mb-0 text-muted">Update subcategory: <strong>{{ $subcategory->name }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn btn-outline-info">
                <i class="fas fa-eye me-2"></i>View Details
            </a>
            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Subcategories
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.subcategories.update', $subcategory) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
            <!-- Main Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <!-- Subcategory Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name', $subcategory->name) }}"
                                   placeholder="e.g., Rings, Necklaces, Bracelets" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4"
                                      placeholder="Brief description of this jewelry type...">{{ old('description', $subcategory->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Available Metals -->
                        <div class="mb-3">
                            <label class="form-label">Available Metals <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach($metalCategories as $metal)
                                    <div class="col-md-6 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="metal_categories[]" value="{{ $metal->id }}"
                                                   id="metal_{{ $metal->id }}"
                                                   {{ in_array($metal->id, old('metal_categories', $subcategory->metalCategories->pluck('id')->toArray())) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="metal_{{ $metal->id }}">
                                                <span class="badge bg-secondary me-2">{{ $metal->symbol }}</span>
                                                {{ $metal->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select which metals are available for this jewelry type</div>
                            @error('metal_categories')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing Configuration -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Default Pricing Configuration</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <!-- Default Labor Cost -->
                            <div class="col-md-6">
                                <label for="default_labor_cost" class="form-label">Default Labor Cost (AUD per gram) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('default_labor_cost') is-invalid @enderror"
                                       id="default_labor_cost" name="default_labor_cost"
                                       value="{{ old('default_labor_cost', $subcategory->default_labor_cost) }}"
                                       step="0.01" min="0" max="999.99" required>
                                <div class="form-text">Default labor cost for products in this category</div>
                                @error('default_labor_cost')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Status & Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Status & Settings</h6>
                    </div>
                    <div class="card-body">
                        <!-- Status -->
                        <div class="mb-3">
                            <label for="is_active" class="form-label">Status</label>
                            <select class="form-select @error('is_active') is-invalid @enderror"
                                    id="is_active" name="is_active">
                                <option value="1" {{ old('is_active', $subcategory->is_active) == 1 ? 'selected' : '' }}>
                                    Active (Available for products)
                                </option>
                                <option value="0" {{ old('is_active', $subcategory->is_active) == 0 ? 'selected' : '' }}>
                                    Inactive (Hidden from products)
                                </option>
                            </select>
                            @error('is_active')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                   id="sort_order" name="sort_order"
                                   value="{{ old('sort_order', $subcategory->sort_order) }}"
                                   min="0" max="9999">
                            <div class="form-text">Lower numbers appear first in listings</div>
                            @error('sort_order')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                <!-- Image Upload -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Category Image</h6>
                    </div>
                    <div class="card-body">
                        <!-- Current Image -->
                        @if($subcategory->image_url)
                            <div class="mb-3">
                                <label class="form-label">Current Image</label>
                                <div class="d-flex align-items-center gap-3">
                                    <div class="position-relative">
                                        <img src="{{ $subcategory->image_url }}" alt="{{ $subcategory->name }}"
                                             class="img-thumbnail" style="max-width: 200px;">
                                        <div class="form-check position-absolute top-0 end-0 m-1">
                                            <input type="checkbox" class="form-check-input"
                                                   name="remove_image" id="remove_image" value="1">
                                            <label class="form-check-label text-danger" for="remove_image">
                                                Remove
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Upload New Image -->
                        <div class="mb-3">
                            <label for="image" class="form-label">{{ $subcategory->image_url ? 'Replace Image' : 'Upload Image' }}</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 2MB. Recommended: 400x300px</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="mt-3" style="display: none;">
                            <label class="form-label">New Image Preview</label>
                            <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                </div>
                <!-- Action Buttons -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Subcategory
                            </button>
                            <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-generate slug from name
    const nameInput = document.getElementById('name');
    const slugInput = document.getElementById('slug');

    nameInput.addEventListener('input', function() {
        if (!slugInput.value || slugInput.dataset.generated === 'true') {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
            slugInput.value = slug;
            slugInput.dataset.generated = 'true';
        }
    });

    slugInput.addEventListener('input', function() {
        this.dataset.generated = 'false';
    });

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').style.display = 'block';
            };
            reader.readAsDataURL(e.target.files[0]);
        }
    });

    // Update pricing preview
    updatePricingPreview();
    document.getElementById('default_labor_cost').addEventListener('input', updatePricingPreview);
});

</script>
@endsection
