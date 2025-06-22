// public/js/admin/categories.js
class CategoryManager {
    constructor() {
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.setupDataTable();
        this.setupImagePreview();
        this.setupSlugGeneration();
    }

    setupEventListeners() {
        // Status toggles
        document.addEventListener('change', '.status-toggle', this.handleStatusToggle);

        // Bulk actions
        document.addEventListener('click', '.bulk-action-btn', this.handleBulkAction);

        // Search with debounce
        document.addEventListener('input', '#search', this.debounce(this.handleSearch, 300));
    }

    // ... Additional methods
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new CategoryManager();
});
