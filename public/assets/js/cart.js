function getProductName() {
    return document.getElementById('product-name').innerText;
}

async function removeProductFromCart(productId) {
    const removeProductFromCartUrl = SERVER_URL + '/carts/user-cart';
    try {
        const response = await sendRequest(removeProductFromCartUrl, { 
            method: 'PATCH',
            body: JSON.stringify({
                deleteds: productId
            })
        });

        if (!response.ok) {
            if (response.status === UNAUTHORIZED_STATUS_CODE) {
                await redirectToLogin();
            }
            else {
                const productName = getProductName();
                console.error('Remove product from cart failed:', response.statusText);
                alert(
                    `An error occurred while trying to remove ${productName} from your cart.`
                    + 'Please try again later.'
                );
            }
            return;
        }
        
        const productName = getProductName();
        alert(`${productName} has been removed from your cart successfully`);
        await reload(true);
    }
    catch (error) {
        console.error(`Error removing product ${productId} from cart`);
        alert('An error occurred while trying to remove products from your cart. Please try again later.');
    }
}

async function deleteCart() {
    const deleteCartUrl = SERVER_URL + '/carts/user-cart';
    try {
        const response = await sendRequest(deleteCartUrl, { method: 'DELETE' });
        if (!response.ok) {
            if (response.status === UNAUTHORIZED_STATUS_CODE) {
                await redirectToLogin();
            }
            else {
                console.error('Delete cart failed:', response.statusText);
                alert('An error occurred while trying to delete your cart. Please try again later.');
            }
            return;
        }
        
        alert('All products have been removed from your cart successfully');
        await reload();
    }
    catch (error) {
        console.error(`Error deleting cart`);
        alert('An error occurred while trying to delete your cart. Please try again later.');
    }
}

document.getElementById('delete-cart-button').addEventListener('click', async () => {
    if (confirm('Are you sure to delete your cart?')) {
        await deleteCart();
    }
});

document.querySelectorAll('button[data-id]').forEach(button => {
    const productId = button.dataset.id;
    button.addEventListener('click', async () => await removeProductFromCart(productId));
});