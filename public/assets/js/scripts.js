const SERVER_URL = 'http://localhost:9000';
const AUTH_URL = SERVER_URL + '/auth';
const REFRESH_URL = AUTH_URL + '/token';
const LOGIN_URL = AUTH_URL + '/login';
const SIGN_UP_URL = AUTH_URL + '/sign-up';
const LOGOUT_URL = AUTH_URL + '/logout';

const CSRF_TOKEN_KEY = 'csrf-token';
const USER_KEY = 'current-user';
const ACCESS_TOKEN_CLAIMS = 'access-claims';
const LOGIN_INTERCEPTED_URL = 'intended-url';

const X_XSRF_TOKEN_HEADER = 'X-XSRF-Token';

const BAD_REQUEST_STATUS_CODE = 400;
const UNAUTHORIZED_STATUS_CODE = 401;
const FORBIDDEN_STATUS_CODE = 403;
const NOT_FOUND_STATUS_CODE = 404;
const CONFLICT_STATUS_CODE = 409;

/////////////////////// UTILS SECTION ///////////////////////

/**
 * Round a number to the nearest multiple of the specified value.
 * @param {Number} number The number to be rounded
 * @param {Number} multiple The specified multiple. Defaulted to 1.
 * @returns {Number} The result of rounding the number
 */
function round(num, multiple = 1) { 
    if(num > 0) {
        return Math.ceil(num / multiple) * multiple;
    }
    else if(num < 0) {
        return Math.floor(num / multiple) * multiple;
    }
    else {
        return multiple;
    }
}

/**
 * Check if a value is a pure object (not an array or null).
 * @param {any} value The value to check
 * @returns {boolean} Whether the value is a pure object
 */
function isPureObject(value) {
    return typeof value === 'object' && !Array.isArray(value) && value !== null;
}

/**
 * Return the first item in the array or the default value if the array is empty.
 * @param {Array<any>} arr The array of items
 * @param {any} defaultValue The default value
 * @returns {any} Either the first item in the array or the default value if the array is empty
 */
function firstOrDefault(arr, defaultValue) {
    return arr.length > 0 ? arr[0] : defaultValue;
}

/**
 * Create a FormData containing entries from a form element satisfying a specified filter.
 * @param {HTMLFormElement} form The form element
 * @param {(value: FormDataEntryValue, key: string) => boolean} filter 
 * The filter for the entries. Defaulted to exclude empty or whitespace-only values.
 * @returns {FormData} FormData containing only entries satisfying the filter
 */
function getFilteredFormData(form, filter = undefined) {
    filter ??= (v) => v.trim() !== '';

    const formData = new FormData(form);
    const filteredFormData = new FormData();

    for (const [key, value] of formData.entries()) {
        if (filter(value, key)) {
            filteredFormData.append(key, value);
        }
    }
    
    return filteredFormData;
}

/**
 * @param {Response} response 
 * @returns {boolean}
 */
function isHtmlResponse(response) {
    const contentType = response.headers.get("content-type");
    return contentType && contentType.indexOf("text/html") !== -1;
}

/////////////////////// PAGE FUNCTIONALITY SECTION ///////////////////////

function hamburger() {
    const menu = document.getElementById("menu-links");
    const logo = document.getElementById("ffc-logo");
    if (menu.style.display === "block") {
        menu.style.display = "none";
        logo.style.display = "block";
    } 
    else{
        menu.style.display = "block";
        logo.style.display = "none";
    }
}

function confirmCart(){
    alert("Your order have been confirmed");
}

function openNav() {
    document.getElementById("menu-links").style.width = "50%";
}
  
function closeNav() {
    document.getElementById("menu-links").style.width = "0";
}

function increaseQuantity(){
    const element = document.getElementById('quantity-number');
    const value = element.innerText;
    element.innerText = value + 1;
}

function decreaseQuantity(){
    const element = document.getElementById('quantity-number');
    const value = element.innerText;
    element.innerText = value > 1 ? value - 1 : value;
}

/////////////////////// LOCAL STORAGE SECTION ///////////////////////

function getCsrfToken() {
    return localStorage.getItem(CSRF_TOKEN_KEY);
}

function storeCsrfToken(token) {
    localStorage.setItem(CSRF_TOKEN_KEY, token);
}

/**
 * @returns {?string}
 */
function getUserName() {
    const storedUser = localStorage.getItem(USER_KEY);
    return storedUser ? JSON.parse(storedUser).name : null;
}

function storeCurrentUser(user) {
    localStorage.setItem(USER_KEY, JSON.stringify(user));
}

function storeAccessTokenClaims(claims) {
    localStorage.setItem(ACCESS_TOKEN_CLAIMS, JSON.stringify(claims));
}

function extractLoginInterceptedUrl() {
    const url = localStorage.getItem(LOGIN_INTERCEPTED_URL);
    if (url !== null) {
        localStorage.removeItem(LOGIN_INTERCEPTED_URL);
    }
    return url;
}

function storeIntendedUrl(url) {
    localStorage.setItem(LOGIN_INTERCEPTED_URL, url);
}

function removeCredentials() {
    localStorage.removeItem(CSRF_TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    localStorage.removeItem(ACCESS_TOKEN_CLAIMS);
}

function isAccessTokenExpired(clearOnExpired = true) {
    const storedAccessTokenClaims = localStorage.getItem(ACCESS_TOKEN_CLAIMS);
    if (!storedAccessTokenClaims) {
        return true; // Assume that no token means already expired
    }

    const claims = JSON.parse(storedAccessTokenClaims);
    const expMs = claims.exp * 1000;
    const currentTimeMs = Date.now();

    const isExpired = currentTimeMs >= expMs;
    if (isExpired && clearOnExpired) {
        // Automatic clear on expired credentials
        removeCredentials();
    }
    return isExpired;
}

/////////////////////// FETCHING SECTION ///////////////////////

/**
 * Send a fetch request with the specified input and options. If the fetch failed due to token expiration,
 * refresh the token and try fetching again.
 * This method automatically attaches csrf token to the sent request when needed.
 * @param {RequestInfo | URL} input
 * @param {RequestInit} options
 * @returns {Promise<Response>}
 */
async function sendRequest(input, options = undefined) {
    options ??= {};
    const finalOptions = { ...options, credentials: 'include', headers: { ...options.headers }};

    const isNotGetRequest = finalOptions.method !== 'GET';
    const csrfToken = getCsrfToken();
    if (csrfToken && isNotGetRequest) {
        finalOptions.headers[X_XSRF_TOKEN_HEADER] = csrfToken;
    }

    const response = await fetch(input, finalOptions);
    if (response.status === FORBIDDEN_STATUS_CODE && isNotGetRequest) {
        alert('You don’t have permission to perform this action.');
    }
    else if (response.status >= 500) {
        console.error(`Server error: ${response.status}`);
        alert('A server error occurred. Please try again later.');
    }

    const shouldRefresh = response.status === UNAUTHORIZED_STATUS_CODE && isAccessTokenExpired();
    if (!shouldRefresh) {
        return response;
    }

    const newCsrfToken = await tryRefresh();
    if (!newCsrfToken) {
        return response;
    }

    if (isNotGetRequest) {
        finalOptions.headers[X_XSRF_TOKEN_HEADER] = newCsrfToken;
    }
    return await fetch(input, finalOptions);
}

async function tryRefresh() {
    const refreshResponse = await fetch(REFRESH_URL, {
        method: 'POST',
        credentials: 'include'
    });

    if (!refreshResponse.ok) {
        return undefined;
    }

    const refreshData = await refreshResponse.json();
    /**
     * @type {string}
     */
    const csrfToken = refreshData.csrf;
    const user = refreshData.user;
    const accessTokenClaims = refreshData.claims;

    storeCsrfToken(csrfToken);
    storeCurrentUser(user);
    storeAccessTokenClaims(accessTokenClaims);

    return csrfToken;
}

/////////////////////// DYNAMIC PAGE BUILDING SECTION ///////////////////////

/**
 * @param {string} htmlContent 
 */
function loadPage(htmlContent) {
    return new Promise((resolve, reject) => {
        try {
            document.open();
            document.write(htmlContent);
            document.close();
            
            initUiState();
            resolve();
        }
        catch (error) {
            reject(error);
        }
    });
}

/**
 * @param {HTMLTemplateElement} template
 */
function handleScripts(template) {
    const newScripts = Array.from(template.querySelectorAll('script[src]'));
    const existingScripts = Array.from(document.querySelectorAll('script[src]'));

    const existingScriptSrcs = new Set();
    existingScripts.forEach(script => existingScriptSrcs.add(script.src));
    //const existingScriptSrcs = existingScripts.map(script => script.src);
    
    newScripts.forEach(script => {
        if (!existingScriptSrcs.has(script.src)) {
            // Custom handling logic for new scripts
            const newScript = document.createElement('script');
            newScript.src = script.src;
            newScript.defer = script.defer ?? false;
            newScript.async = script.async ?? false;
            document.head.appendChild(newScript);
        }
    });

    // Optionally handle inline scripts
    const inlineScripts = Array.from(template.querySelectorAll('script:not([src])'));
    inlineScripts.forEach(inlineScript => {
        executeInlineScript(inlineScript);
    });
}

function executeInlineScript(inlineScript) {
    const newScript = document.createElement('script');
    newScript.textContent = inlineScript.textContent;
    document.body.appendChild(newScript);
}

/////////////////////// NAVIGATION SECTION ///////////////////////

/**
 * @param {string} url
 * @returns {string}
 */
function buildCacheBustingUrl(url) {
    // Separate the URL components: base, query string, and hash
    const [baseUrlWithQuery, hash] = url.split('#'); // Splits into URL and hash segment
    const [baseUrl, queryString] = baseUrlWithQuery.split('?'); // Splits into base and query string

    // Build the new URL with cache-busting
    const timestampParam = `_=${new Date().getTime()}`;
    const newQueryString = queryString 
        ? `${queryString}&${timestampParam}` 
        : `${timestampParam}`;
    const newHashSegment = hash ? `#${hash}` : '';

    return `${baseUrl}?${newQueryString}${newHashSegment}`;
}

/**
 * @param {string} url
 * @param {boolean} saveScrollPosition
 * @returns {Promise<void>}
 */
async function navigateToUrl(url, saveScrollPosition = false) {
    const cacheBustingUrl = buildCacheBustingUrl(url);
    const scrollPosition = saveScrollPosition ? window.scrollY : 0;

    try {
        const response = await sendRequest(cacheBustingUrl, {
            method: 'GET',
            headers: {
                'Content-Type': 'text/html'
            }
        });

        if (!response.ok) {
            if (url !== LOGIN_URL && response.status === UNAUTHORIZED_STATUS_CODE) {
                alert('Please login to continue');
                await redirectToLogin(url);
                return;
            }
            else if (!isHtmlResponse(response))  {
                throw new Error(`Navigation failed with status ${response.status}`);
            }
        }
    
        const htmlContent = await response.text();

        await loadPage(htmlContent);
    
        history.pushState(null, '', url);
        window.scrollTo(0, scrollPosition);
    }
    catch (error) {
        console.error(`Error navigating to URL '${url}': `, error);
    }
}

/**
 * @param {string} intendedUrl 
 */
async function redirectToLogin(intendedUrl = undefined) {
    intendedUrl ??= location.href;
    storeIntendedUrl(intendedUrl);
    await navigateToUrl(LOGIN_URL);
}

async function reload(saveScrollPosition = true) {
    const currentUrl = location.href;
    await navigateToUrl(currentUrl, saveScrollPosition);
}

/////////////////////// QUERY STRING SECTION ///////////////////////

function dataToQs(key, value) {
	if (typeof value === 'undefined') {
  	    return '';
    }
    else {
  	    value = typeof value === 'object' ? JSON.stringify(value) : value;
        return `${key}=` + value;
    }
}

function makeQs(data) {
    const qsParts = [];
    for (const key in data) {
        const value = data[key];
        const dataQs = dataToQs(key, value);
        qsParts.push(dataQs);
    }
    const qs = qsParts.filter((item) => item !== '').join('&');
    return encodeURI(qs);
}

function addDataAsQs(url, data) {
    const uriQs = makeQs(data);
    return url + (uriQs !== '' ? '?' + uriQs : '');
}

/////////////////////// PRODUCTS FILTER SECTION ///////////////////////

function getShopsFilter() {
    const shopCheckboxes = document.querySelectorAll('#shop-filter-container input[type="checkbox"]');
    if (shopCheckboxes.length === 0) {
        return undefined;
    }

    /**
     * @type {string[]}
     */
    const shops = [];
    for (const shopCheckbox of shopCheckboxes) {
        if (shopCheckbox.checked) {
            const shopName = shopCheckbox.value;
            shops.push(shopName);
        }
    }
    return shops;
}

function getPricesFilter() {
    const priceCheckboxes = document.querySelectorAll('#price-filter-container input[type="checkbox"]');
    if (priceCheckboxes.length === 0) {
        return undefined;
    }

    const prices = [];
    for (const priceCheckbox of priceCheckboxes) {
        if (!priceCheckbox.checked) {
            continue;
        }

        /**
         * @type {{value: string, value2?: string, lt?: boolean, gt?: boolean}}
         */
        const priceFilter = {
            value: priceCheckbox.value
        };

        if (typeof priceCheckbox.dataset.value !== 'undefined') {
            priceFilter.value2 = priceCheckbox.dataset.value;
        }
        else if(typeof priceCheckbox.dataset.operator !== 'undefined') {
            priceFilter[priceCheckbox.dataset.operator] = true;
        }

        prices.push(priceFilter);
    }
    return prices;
}

function getRatingFilter() {
    const ratingInputs = document.querySelectorAll('#rating-filter-container input');
    if (ratingInputs.length === 0) {
        return undefined;
    }

    /**
     * @type {{value: string, lt?: boolean, gt?: boolean}}
     */
    const ratingFilter = {};

    for (const inputElement of ratingInputs) {
        if (inputElement.type === 'range') {
            ratingFilter.value = inputElement.value;
        }
        else if (inputElement.type === 'radio' && inputElement.checked) {
            ratingFilter[inputElement.value] = true;
        }
    }
    return ratingFilter?.lt || ratingFilter?.gt ? ratingFilter : undefined;
}

/**
 * @param {HTMLInputElement} inputSearchBarElement
 */
function getProductsQueryData(inputSearchBarElement) {
    const keyword = inputSearchBarElement.value;

    const shops = getShopsFilter();
    if (typeof shops === 'undefined') {
        return {keyword};
    }

    const prices = getPricesFilter();
    const rating = getRatingFilter();

    /**
     * @type {Record<string,any>}
     */
    const filter = {};
    if (shops.length > 0) {
        filter.shops = shops;
    }
    if (typeof prices !== 'undefined' && prices.length > 0) {
        filter.prices = prices;
    }
    if (typeof rating !== 'undefined') {
        filter.rating =  rating;
    }

    if (Object.keys(filter).length > 0) {
        return {keyword, filter};
    }
    else {
        return {keyword};
    }
}

/**
 * @param {HTMLInputElement} inputSearchBarElement
 * @returns {() => Promise<void>}
 */
function createSearchProducts(inputSearchBarElement) {
    return async () => {
        const data = getProductsQueryData(inputSearchBarElement);
        const productsPageUrl = SERVER_URL + '/products';
        const url = addDataAsQs(productsPageUrl, data);
        await navigateToUrl(url);
    }
}

/////////////////////// AUTH SECTION ///////////////////////

async function login(body) {
    try {
        const response = await sendRequest(LOGIN_URL, { method: 'POST', body });

        if (response.status === BAD_REQUEST_STATUS_CODE || response.status === CONFLICT_STATUS_CODE) {
            const error = await response.json();
            const errors = isPureObject(error) ? Object.values(error) : [error];
            const errorMsg = firstOrDefault(errors, 'Invalid data. Please check your input.');
            alert(errorMsg);
            return;
        }
        else if (!response.ok) {
            console.error('Login failed:', response.statusText);
            alert('An error occurred while logging in. Please try again later.');
            return;
        }

        const responseData =  await response.json();
        /**
         * @type {string}
         */
        const csrfToken = responseData.csrf;
        const user = responseData.user;
        const accessTokenClaims = responseData.claims;

        storeCsrfToken(csrfToken);
        storeCurrentUser(user);
        storeAccessTokenClaims(accessTokenClaims);

        const nextUrl = extractLoginInterceptedUrl() ?? SERVER_URL;
        navigateToUrl(nextUrl);
    }
    catch (error) {
        console.error('Error during login:', error);
        alert('An error occurred while logging in. Please try again later.');
    }
}

async function signUp(body) {
    try {
        const response = await sendRequest(SIGN_UP_URL, { method: 'POST', body });

        if (response.status === BAD_REQUEST_STATUS_CODE || response.status === CONFLICT_STATUS_CODE) {
            const error = await response.json();
            debugger;
            const errors = isPureObject(error) ? Object.values(error) : [error];
            const errorMsg = firstOrDefault(errors, 'Invalid data. Please check your input.');
            alert(errorMsg);
            return;
        }
        else if (!response.ok) {
            console.error('Sign-up failed:', response.statusText);
            alert('An error occurred while signing up. Please try again later.');
            return;
        }

        alert('You have signed up successfully. Navigating to login page...');
        navigateToUrl(LOGIN_URL);
    }
    catch (error) {
        console.error('Error during sign-up:', error);
        alert('An error occurred while signing up. Please try again later.');
    }
}

async function logout() {
    try {
        const response = await sendRequest(LOGOUT_URL, { method: 'DELETE' });

        if (response.status === UNAUTHORIZED_STATUS_CODE) {
            await redirectToLogin();
            return;
        }
        else if(!response.ok) {
            console.error('Logout failed:', response.statusText);
            alert('An error occurred while logging out. Please try again later.');
            return;
        }

        alert('You have successfully logged out');
        removeCredentials();
        await reload();
    }
    catch (error) {
        console.error('Error during logout:', error);
        alert('An error occurred while logging out. Please try again later.');
    }
}

/**
 * @param {HTMLElement} parent
 * @param {string} name
 */
function populateLoggedInAuthMenu(parent, name) {
    const nameSpanElement = document.createElement('span');
    nameSpanElement.innerText = name;
    nameSpanElement.classList.add('current-user');

    const logoutDivElement = document.createElement('div');
    logoutDivElement.innerText = 'Logout';
    logoutDivElement.style.cursor = 'pointer';
    logoutDivElement.addEventListener('click', async () => { await logout(); });

    parent.replaceChildren(nameSpanElement, ' | ', logoutDivElement);
}

/////////////////////// BINDING & POPULATION ///////////////////////

/**
 * Intercept all anchor tags navigation behavior with custom navigation logic
 */
function bindAnchorNavigation() {
    document.addEventListener('click', e => {
        const target = e.target;

        if (target.tagName.toLowerCase() === 'a' && target.href) {
            const currentUrl = location.href;
            const targetUrl = target.href;

            // If the target URL is different from the current URL and on the same origin
            if (targetUrl !== currentUrl && targetUrl.startsWith(location.origin)) {
                e.preventDefault();  // Prevent default navigation
                navigateToUrl(targetUrl);  // Custom navigation
            }
        }
    });
}

/**
 * Bind search bar and search button with intended query functionality
 */
function bindSearchProductsFunctionality() {
    const productSearchBarElement = document.getElementById('product-search-bar');
    if (!productSearchBarElement) {
        return;
    }

    const productSearchButtonElement = document.getElementById('product-search-button');
    const searchProducts = createSearchProducts(productSearchBarElement);

    productSearchButtonElement.addEventListener('click', async () => await searchProducts());
    productSearchBarElement.addEventListener('keydown', async (e) => {
        if (e.key === 'Enter') {
            await searchProducts();
        }
    });
}

/**
 * Bind all elements with data-url attribute with associated onclick events
 */
function bindDataUrlNavigation() {
    document.querySelectorAll('[data-url]').forEach(element => {
        element.addEventListener('click', async () => {
            const url = element.dataset.url;
            await navigateToUrl(url);
        });
        // Change cursor to pointer to signal clickability
        element.style.cursor = 'pointer';
    });
}

/**
 * Bind user back/forward navigation with custom navigation behavior to load the correct content
 */
function bindPageHistoryNavigation() {
    window.addEventListener('popstate', async () => {
        const currentUrl = location.href;  // This will give the current URL in the history
        await navigateToUrl(currentUrl, false);
    });
}

/**
 * Populate auth-menu element with current logged in user if exists
 */
function populateAuthMenu() {
    const authMenuElement = document.getElementById('auth-menu');
    if (authMenuElement) {
        const name = getUserName();
        if (name) {
            populateLoggedInAuthMenu(authMenuElement, name);
        }
    }
}

function initBinding() {
    bindAnchorNavigation();
    bindDataUrlNavigation();
    bindSearchProductsFunctionality();
    bindPageHistoryNavigation();
}

function initPopulation() {
    populateAuthMenu();
}

function initUiState() {
    initBinding();
    initPopulation();
}

initUiState();