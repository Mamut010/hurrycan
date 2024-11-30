async function checkoutCart() {
    const checkoutCarttUrl = SERVER_URL + '/carts/user-cart';
    try {
        const response = await sendRequest(checkoutCarttUrl, { method: 'DELETE' });
        if (!response.ok) {
            if (response.status === UNAUTHORIZED_STATUS_CODE) {
                await redirectToLogin();
            }
            else {
                console.error('Checkout cart failed:', response.statusText);
                alert('An error occurred while trying to checkout your cart. Please try again later.');
            }
            return;
        }
        
        confirmCart();
        await reload();
    }
    catch (error) {
        console.error(`Error checking out cart`);
        alert('An error occurred while trying to checkout your cart. Please try again later.');
    }
}

document.getElementById('confirm-cart-button').addEventListener('click', async () => {
    if (confirm('Are you sure to checkout your current cart?')) {
        await checkoutCart();
    }
})