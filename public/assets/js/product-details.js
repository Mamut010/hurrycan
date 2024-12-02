function getQuantity() {
    return Number.parseInt(document.getElementById('product-quantity').innerText);
}

function getProductName() {
    return document.getElementById('product-name').innerText;
}

function renderQuantity(quantity) {
    document.getElementById('product-quantity').innerText = quantity;
}

function getQuantityInCartFromCartData(cart, productId) {
    let quantityInCart = 0;
    for (const cartProduct of cart.cartProducts) {
        if (cartProduct.productId === productId) {
            quantityInCart = cartProduct.quantity;
            break;
        }
    }
    return quantityInCart;
}

async function fetchQuantityInCart(productId) {
    const fetchCartUrl = SERVER_URL + '/carts/user-cart/json';
    try {
        let response = await sendRequest(fetchCartUrl, { method: 'GET' });
        if (!response.ok) {
            if (response.status === UNAUTHORIZED_STATUS_CODE) {
                alert('Please login to continue');
                await redirectToLogin();
                return undefined;
            }
            else if (response.status === FORBIDDEN_STATUS_CODE) {
                alert('You don’t have permission to perform this action. Contact administrator for more information.')
                return undefined;
            }
            else if (response.status === NOT_FOUND_STATUS_CODE) {
                const createCartUrl = SERVER_URL + '/carts/user-cart';
                response = await sendRequest(createCartUrl, { method: 'POST' });
            }

            if (!response.ok) {
                console.error('Fetch cart failed:', response.statusText);
                alert('An error occurred while trying to get your cart. Please try again later.');
                return undefined;
            }

            response = await sendRequest(fetchCartUrl, { method: 'GET' });
        }

        const cart = await response.json();
        return getQuantityInCartFromCartData(cart, productId);
    }
    catch (error) {
        console.error(`Error fetching cart for product ${productId}`);
        alert('An error occurred while trying to get your cart. Please try again later.');
        return undefined;
    }
}

async function addToCart() {
    const productId = Number.parseInt(document.getElementById('product-id').value);
    const fetchQuantityResult = await fetchQuantityInCart(productId);
    if (typeof fetchQuantityResult !== 'number') {
        return false;
    }
    
    const addToCartUrl = SERVER_URL + '/carts/user-cart';
    const quantity = getQuantity();

    try {
        const response = await sendRequest(addToCartUrl, { 
            method: 'PATCH',
            body: JSON.stringify({
                updateds: [
                    {
                        productId,
                        quantity: fetchQuantityResult + quantity
                    }
                ]
            })
        });
        if (!response.ok) {
            console.error('Fetch cart failed:', response.statusText);
            alert('An error occurred while trying to get your cart. Please try again later.');
            return false;
        }
        else {
            const productName = getProductName();
            alert(`Added ${quantity} ${productName} to your cart successfully`);
            return true;
        }
    }
    catch (error) {
        console.error(`Error fetching cart for product ${productId}`);
        alert('An error occurred while trying to get your cart. Please try again later.');
        return false;
    }
}

document.getElementById('minus-quantity-button').addEventListener('click', () => {
    const quantity = getQuantity();
    const newQuantity = Math.max(1, quantity - 1);
    if (newQuantity !== quantity) {
        renderQuantity(newQuantity);
    }
});

document.getElementById('plus-quantity-button').addEventListener('click', () => {
    const quantity = getQuantity();
    const newQuantity = quantity + 1;
    renderQuantity(newQuantity);
});

document.getElementById('add-to-cart-button').addEventListener('click', addToCart);
document.getElementById('buy-now-button').addEventListener('click', async () => {
    if (await addToCart()) {
        const cartUrl = SERVER_URL + '/carts/user-cart';
        await navigateToUrl(cartUrl);
    }
});

document.querySelectorAll('#gallery img').forEach(img => {
    const displayImgElement = document.getElementById('display-img');
    img.addEventListener('click', () => {
        displayImgElement.src = img.src;
    });
});