function clearProductsFilters() {
    const checkableInputTypes = ['checkbox', 'radio'];
    const shopCheckboxes = document.querySelectorAll('#shop-filter-container input[type="checkbox"]');
    const priceCheckboxes = document.querySelectorAll('#price-filter-container input[type="checkbox"]');
    const ratingInputs = document.querySelectorAll('#rating-filter-container input');
    
    [...shopCheckboxes, ...priceCheckboxes, ...ratingInputs].forEach(inputElement => {
        const inputType = inputElement.type;
        if (inputType === 'range') {
            inputElement.value = round((inputElement.min + inputElement.max) / 2, inputElement.step);
        }
        else if (checkableInputTypes.includes(inputType)) {
            inputElement.checked = false;
        }
    });
}

document.getElementById('clear-filters-button')?.addEventListener('click', clearProductsFilters);