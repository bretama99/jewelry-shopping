{{-- File: resources/views/admin/metal-categories/create.blade.php --}}
@extends('layouts.admin')

@section('title', 'Create Metal Category')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Create Metal Category</h1>
            <p class="mb-0 text-muted">Add a new precious metal with live pricing integration</p>
        </div>
        <a href="{{ route('admin.metal-categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Metal Categories
        </a>
    </div>

    <form method="POST" action="{{ route('admin.metal-categories.store') }}" enctype="multipart/form-data" id="metalCategoryForm">
        @csrf
        
        <div class="row">
            <!-- Left Column - Basic Information -->
            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <!-- Metal Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Metal Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" value="{{ old('name') }}"
                                   placeholder="e.g., Gold, Silver, Platinum, Palladium" required>
                            <div class="form-text">The display name for this precious metal</div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Metal Symbol -->
                        <div class="mb-3">
                            <label for="symbol" class="form-label">Metal Symbol</label>
                            <select class="form-select @error('symbol') is-invalid @enderror"
                                    id="symbol" name="symbol">
                                <option value="">Select Metal Symbol (Optional)</option>
                                <option value="XAU" {{ old('symbol') == 'XAU' ? 'selected' : '' }}>
                                    XAU (Gold) - The most popular precious metal
                                </option>
                                <option value="XAG" {{ old('symbol') == 'XAG' ? 'selected' : '' }}>
                                    XAG (Silver) - Affordable luxury metal
                                </option>
                                <option value="XPT" {{ old('symbol') == 'XPT' ? 'selected' : '' }}>
                                    XPT (Platinum) - Premium white metal
                                </option>
                                <option value="XPD" {{ old('symbol') == 'XPD' ? 'selected' : '' }}>
                                    XPD (Palladium) - Modern precious metal
                                </option>
                            </select>
                            <div class="form-text">
                                <strong>Optional:</strong> Select a symbol if you want to use live API pricing. Leave blank for manual pricing only.
                            </div>
                            @error('symbol')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- URL Slug -->

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="4"
                                      placeholder="Brief description of the metal and its properties...">{{ old('description') }}</textarea>
                            <div class="form-text">Optional description for customers and SEO</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Status & Configuration</h6>
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

                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Metal Image</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="image" class="form-label">Metal Image</label>
                            <input type="file" class="form-control @error('image') is-invalid @enderror"
                                   id="image" name="image" accept="image/*">
                            <div class="form-text">
                                Supported formats: JPG, PNG, GIF. Max size: 2MB. Recommended: 400x300px
                                <br><small class="text-muted">Optional: Image representing this metal (ingots, jewelry samples, etc.)</small>
                            </div>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="imagePreview" style="display: none;">
                            <label class="form-label">Image Preview</label>
                            <div>
                                <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Compatible Jewelry Types</h6>
                    </div>
                    <div class="card-body">
                        @if($subcategories->count() > 0)
                            <div class="row">
                                @foreach($subcategories as $subcategory)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                   name="subcategories[]" value="{{ $subcategory->id }}"
                                                   id="subcategory_{{ $subcategory->id }}"
                                                   {{ in_array($subcategory->id, old('subcategories', [])) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="subcategory_{{ $subcategory->id }}">
                                                {{ $subcategory->name }}
                                                <div class="small text-muted">
                                                    Labor: ${{ number_format($subcategory->default_labor_cost ?? 15, 2) }}/g |
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="form-text">Select which jewelry types can be made with this metal</div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-exclamation-triangle text-warning fa-2x mb-2"></i>
                                <p class="text-muted mb-2">No jewelry subcategories found.</p>
                                <a href="{{ route('admin.subcategories.create') }}" class="btn btn-sm btn-outline-primary">
                                    Create Subcategory First
                                </a>
                            </div>
                        @endif
                        @error('subcategories')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">Ready to create this metal category?</h6>
                                <small class="text-muted">All information can be edited later if needed</small>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-secondary" onclick="saveDraft()">
                                    <i class="fas fa-file-alt me-2"></i>Save as Draft
                                </button>
                                <a href="{{ route('admin.metal-categories.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Create Metal Category
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
    const nameInput = document.getElementById('name');

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
}

function saveDraft() {
    // Set status to inactive and submit
    document.getElementById('is_active').value = '0';
    document.getElementById('metalCategoryForm').submit();
}
</script>

@endsection