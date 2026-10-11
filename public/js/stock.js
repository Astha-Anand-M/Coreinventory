/**
 * Stock In & Stock Out Dynamic Interactions
 */

document.addEventListener('DOMContentLoaded', () => {
    const productSelect = document.getElementById('stockProductSelect');
    const stockInfoCard = document.getElementById('stockInfoCard');
    const currentStockDisplay = document.getElementById('currentStockDisplay');
    const quantityInput = document.getElementById('quantity');
    const stockForm = document.getElementById('stockForm');
    const isStockOut = document.getElementById('isStockOut') !== null;

    if (productSelect && currentStockDisplay) {
        productSelect.addEventListener('change', () => {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const stock = selectedOption.getAttribute('data-stock');
            const unit = selectedOption.getAttribute('data-unit') || 'pcs';

            if (stock !== null && selectedOption.value !== '') {
                currentStockDisplay.textContent = `${stock} ${unit}`;
                if (stockInfoCard) stockInfoCard.style.display = 'block';

                if (isStockOut && quantityInput) {
                    quantityInput.max = stock;
                }
            } else {
                currentStockDisplay.textContent = '-';
                if (stockInfoCard) stockInfoCard.style.display = 'none';
            }
        });
    }

    if (stockForm && isStockOut) {
        stockForm.addEventListener('submit', (e) => {
            const selectedOption = productSelect.options[productSelect.selectedIndex];
            const availableStock = parseInt(selectedOption.getAttribute('data-stock') || '0', 10);
            const qtyToDeduct = parseInt(quantityInput.value || '0', 10);

            if (qtyToDeduct > availableStock) {
                e.preventDefault();
                alert(`Error: You cannot deduct ${qtyToDeduct} items. Only ${availableStock} items are currently in stock.`);
                return false;
            }
        });
    }
});
