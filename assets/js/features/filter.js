class FilterManager {
    constructor(options) {
        this.endpoint = options.endpoint;
        this.resultsArea = document.getElementById(options.resultsId);
        this.countElement = document.getElementById(options.countId);
        this.loadingClass = options.loadingClass || 'loading';
        this.errorClass = options.errorClass || 'error';
        this.filters = {};
        
        // Pagination properties
        this.currentPage = 1;
        this.hasMore = true;
        this.isLoading = false;
        this.perPage = 12;
        
        // Load more button - FIX THIS PART
        this.loadMoreBtn = document.getElementById('loadMoreBtn');
        this.loadMoreContainer = document.getElementById('loadMoreContainer');
        
        this.init();
        this.bindLoadMore();
    }
    
    init() {
        this.bindEvents();
        this.updateResults();
    }
    
    bindEvents() {
        document.querySelectorAll(".filter-input").forEach((input) => {
            input.addEventListener("change", () => this.resetAndLoad());
        });
        
        document.getElementById("sort-select")?.addEventListener("change", () => this.resetAndLoad());
        document.getElementById("apply-price")?.addEventListener("click", () => this.resetAndLoad());
        document.addEventListener('dropdown-change', () => this.resetAndLoad());
    }
    
    bindLoadMore() {
        if (this.loadMoreBtn) {
            this.loadMoreBtn.addEventListener('click', () => this.loadMore());
        }
    }
    
    bindInfiniteScroll() {
        window.addEventListener('scroll', () => {
            if (this.isLoading || !this.hasMore) return;
            
            const scrollPosition = window.innerHeight + window.scrollY;
            const pageHeight = document.documentElement.scrollHeight;
            
            // Load more when user scrolls near bottom (200px before)
            if (pageHeight - scrollPosition < 200) {
                this.loadMore();
            }
        });
    }
    
    resetAndLoad() {
        this.currentPage = 1;
        this.hasMore = true;
        if (this.loadMoreContainer) {
            this.loadMoreContainer.style.display = 'block';
        }
        this.updateResults();
    }
    
    collectFilters() {
        const filters = {
            category: this.getSelectedValues("category_filter"),
            min_price: document.getElementById("min-price")?.value,
            max_price: document.getElementById("max-price")?.value,
            in_stock: document.getElementById("in-stock")?.checked,
            brand: this.getSelectedValues("brand_filter"),
            sort: document.getElementById("sort-select")?.value,
            species: this.getDropdownValue('species'),
            search: document.getElementById('search')?.value,
            gender: this.getDropdownValue('gender')
        };
        
        // Remove empty values
        Object.keys(filters).forEach(key => {
            if (filters[key] === undefined || filters[key] === '' || 
                (Array.isArray(filters[key]) && filters[key].length === 0)) {
                delete filters[key];
            }
        });
        
        return filters;
    }
    
    getSelectedValues(name) {
        const checkboxes = document.querySelectorAll(`input[name="${name}"]:checked`);
        return Array.from(checkboxes).map((cb) => cb.value);
    }
    
    getDropdownValue(id) {
        const selected = document.querySelector(`#${id}Dropdown .selected`);
        const value = selected ? selected.dataset.value : 'all';
        return value !== 'all' ? value : null;
    }
    
    async updateResults() {
        this.currentPage = 1;
        this.isLoading = true;
        const filters = this.collectFilters();
        
        if (this.resultsArea) {
            this.resultsArea.innerHTML = `<div class="${this.loadingClass}">Loading...</div>`;
        }
        
        try {
            // Use load_more.php with page 1 for initial load
            const response = await fetch(`${BASE_URL}backend/api/load_more.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    page: 1,
                    per_page: this.perPage,
                    type: this.endpoint === 'filter_pets' ? 'pets' : 'products',
                    ...filters
                }),
            });
            
            const data = await response.json();
            
            if (this.resultsArea) {
                this.resultsArea.innerHTML = data.html || '<div class="no-results">No items found.</div>';
            }
            
            if (this.countElement) {
                this.countElement.textContent = data.total + ' items found';
            }
            
            this.hasMore = data.hasMore;
            if (!this.hasMore && this.loadMoreContainer) {
                this.loadMoreContainer.style.display = 'none';
            }
            
        } catch (error) {
            console.error("Error:", error);
            if (this.resultsArea) {
                this.resultsArea.innerHTML = `<div class="${this.errorClass}">Error loading results. Please try again.</div>`;
            }
        } finally {
            this.isLoading = false;
        }
    }
    
    async loadMore() {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        this.currentPage++;
        const filters = this.collectFilters();
        
        if (this.loadMoreBtn) {
            this.loadMoreBtn.textContent = 'Loading...';
            this.loadMoreBtn.disabled = true;
        }
        
        try {
            const response = await fetch(`${BASE_URL}backend/api/load_more.php`, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    page: this.currentPage,
                    per_page: this.perPage,
                    type: this.endpoint === 'filter_pets' ? 'pets' : 'products',
                    ...filters
                }),
            });
            
            const data = await response.json();
            
            if (data.success && data.html) {
                this.resultsArea.insertAdjacentHTML('beforeend', data.html);
                this.hasMore = data.hasMore;
                
                if (!this.hasMore && this.loadMoreContainer) {
                    this.loadMoreContainer.style.display = 'none';
                }
                
                if (this.countElement) {
                    this.countElement.textContent = data.total + ' items found';
                }
            }
            
        } catch (error) {
            console.error("Error loading more:", error);
            this.currentPage--; // Revert page on error
        } finally {
            this.isLoading = false;
            if (this.loadMoreBtn) {
                this.loadMoreBtn.textContent = 'Load More';
                this.loadMoreBtn.disabled = false;
            }
        }
    }
}

// Make sure the FilterManager is initialized for pets
document.addEventListener('DOMContentLoaded', function() {
    // Check if we're on the pets page
    if (document.getElementById('pets-results-area')) {
        console.log('Initializing pets filter');
        window.filterManager = new FilterManager({
            endpoint: 'filter_pets',
            resultsId: 'pets-results-area',
            countId: 'pets-count',
            loadingClass: 'loading',
            errorClass: 'error'
        });
    }
    
    // Check if we're on the products page
    if (document.getElementById('product-results-area')) {
        console.log('Initializing products filter');
        window.filterManager = new FilterManager({
            endpoint: 'filter_products',
            resultsId: 'product-results-area',
            countId: 'product-count',
            loadingClass: 'loading',
            errorClass: 'error'
        });
    }
});