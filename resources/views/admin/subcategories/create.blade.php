{{-- File: resources/views/admin/subcategories/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Subcategory')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create New Subcategory</h1>
            <p class="mb-0 text-muted">Add a new jewelry type to your catalog</p>
        </div>
        <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Subcategories
        </a>
    </div>

    <form method="POST" action="{{ route('admin.subcategories.store') }}" enctype="multipart/form-data" id="subcategoryForm">
        @csrf
        
        <div class="row">
            <!-- Left Column - Basic Information -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <!-- Subcategory Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Subcategory Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
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
                                      placeholder="Brief description of this jewelry type...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Default Labor Cost -->
                        <div class="mb-3">
                            <label for="default_labor_cost" class="form-label">Default Labor Cost (AUD per gram) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('default_labor_cost') is-invalid @enderror"
                                   id="default_labor_cost" name="default_labor_cost"
                                   value="{{ old('default_labor_cost', 15) }}"
                                   step="0.01" min="0" max="999.99" required>
                            <div class="form-text">Cost of crafting per gram of metal</div>
                            @error('default_labor_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Status & Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Status & Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select @error('is_active') is-invalid @enderror"
                                        id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>
                                        Active (Available for products)
                                    </option>
                                    <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>
                                        Inactive (Hidden from products)
                                    </option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                                       min="0" max="9999">
                                <div class="form-text">Lower numbers appear first</div>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Metals & Image -->
            <div class="col-lg-6">
                <!-- Available Metals -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Available Metals</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Compatible Metals <span class="text-danger">*</span></label>
                            <div class="row">
                                @foreach($metalCategories as $metal)
                                    <div class="col-md-12 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="metal_categories[]" value="{{ $metal->id }}"
                                                   id="metal_{{ $metal->id }}"
                                                   {{ in_array($metal->id, old('metal_categories', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="metal_{{ $metal->id }}">
                                                <span class="badge bg-secondary me-2">{{ $metal->symbol }}</span>
                                                {{ $metal->name }}
                                                <div class="small text-muted">Compatible with this jewelry type</div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select which metals are available for this jewelry type. Choose at least one metal.</div>
                            @error('metal_categories')
                                <div class="text-danger small">{{ $message }}</div>
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
                        <div class="mb-3">
                            <label for="image" class="form-label">Category Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">Supported formats: JPG, PNG, GIF. Max size: 2MB. Recommended: 400x300px</div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="imagePreview" class="mb-3" style="display: none;">
                            <label class="form-label">Image Preview</label>
                            <div>
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Ready to create this subcategory?</h6>
                                <small class="text-muted">All information can be edited later if needed</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                    <i class="fas fa-file-alt me-2"></i>Save as Draft
                                </button>
                                <a href="{{ route('admin.subcategories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Subcategory
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
});

function initializeForm() {
    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const previewContainer = document.getElementById('imagePreview');
        const previewImg = document.getElementById('previewImg');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewContainer.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewContainer.style.display = 'none';
        }
    });

    // Form validation
    document.getElementById('subcategoryForm').addEventListener('submit', function(e) {
        const metalCategories = document.querySelectorAll('input[name="metal_categories[]"]:checked');
        if (metalCategories.length === 0) {
            e.preventDefault();
            alert('Please select at least one compatible metal category.');
            return false;
        }
    });
}

function saveDraft() {
    // Set status to inactive and submit
    document.getElementById('is_active').value = '0';
    document.getElementById('subcategoryForm').submit();
}
</script>

@endsection