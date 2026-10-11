/**
 * Products Catalog Client-side Script
 */

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('productSearchInput');
    const categoryFilter = document.getElementById('categoryFilter');
    const statusFilter = document.getElementById('statusFilter');
    const productsTable = document.getElementById('productsTable');

    if (productsTable && (searchInput || categoryFilter || statusFilter)) {
        const rows = productsTable.querySelectorAll('tbody tr');

        function filterProducts() {
            const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            const selectedCategory = categoryFilter ? categoryFilter.value : '';
            const selectedStatus = statusFilter ? statusFilter.value : '';

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowCategory = row.getAttribute('data-category') || '';
                const rowStatus = row.getAttribute('data-status') || '';

                const matchesQuery = !query || text.includes(query);
                const matchesCategory = !selectedCategory || rowCategory === selectedCategory;
                const matchesStatus = !selectedStatus || rowStatus === selectedStatus;

                if (matchesQuery && matchesCategory && matchesStatus) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterProducts);
        if (categoryFilter) categoryFilter.addEventListener('change', filterProducts);
        if (statusFilter) statusFilter.addEventListener('change', filterProducts);
    }
});

/**
 * Auto-generate a random unique SKU for new products
 */
function generateSKU() {
    const skuInput = document.getElementById('sku');
    if (skuInput) {
        const prefix = 'SKU';
        const randomNum = Math.floor(100000 + Math.random() * 900000);
        skuInput.value = `${prefix}-${randomNum}`;
    }
}
