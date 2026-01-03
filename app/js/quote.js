document.addEventListener('DOMContentLoaded', function() {
    // This script requires productsData to be defined in a <script> tag in the HTML before this script is loaded.

    const equipmentList = document.getElementById('equipment_list');
    const template = document.getElementById('item-template');

    if (!equipmentList || !template) {
        console.error("Required elements (equipment_list or item-template) not found.");
        return;
    }

    // Initialize TomSelect
    const tomSelect = new TomSelect('#product-selector', {
        valueField: 'id',
        labelField: 'name',
        searchField: 'name',
        options: productsData, // This variable must be provided in the HTML
        create: false,
        onChange: function(value) {
            if (!value) return;

            const product = productsData.find(p => p.id == value);
            if (product) {
                addItem(product);
            }
            this.clear(); // Clear the selector after adding
        }
    });

    function addItem(product, quantity = 1) {
        const clone = template.content.cloneNode(true);
        const item = clone.querySelector('.equipment-item');

        item.querySelector('.product-id').value = product.id;
        item.querySelector('.product-description').value = product.name;
        item.querySelector('.price').value = product.price;
        item.querySelector('.quantity').value = quantity;

        equipmentList.appendChild(item);
        updateItemTotal(item);
    }

    equipmentList.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.equipment-item').remove();
        }
    });

    equipmentList.addEventListener('input', function(e) {
        if (e.target.classList.contains('quantity') || e.target.classList.contains('price')) {
            const item = e.target.closest('.equipment-item');
            updateItemTotal(item);
        }
    });

    function updateItemTotal(item) {
        const quantity = parseFloat(item.querySelector('.quantity').value) || 0;
        const price = parseFloat(item.querySelector('.price').value) || 0;
        const total = quantity * price;
        item.querySelector('.item-total').value = total.toFixed(2) + ' ج.م';
    }

    // If existingItemsData is provided (for edit page), populate the list
    if (typeof existingItemsData !== 'undefined' && Array.isArray(existingItemsData)) {
        existingItemsData.forEach(item => {
            const product = {
                id: item.product_id,
                name: item.description,
                price: item.price
            };
            addItem(product, item.quantity);
        });
    }
});
