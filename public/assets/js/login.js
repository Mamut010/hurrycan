/**
 * @param {HTMLFormElement} loginForm
 * @returns {boolean}
 */
function validateLogin(loginForm) {
    const username = loginForm.elements['username'].value;
    const password = loginForm.elements['password'].value;

    if (username === ""){
        alert("Username is empty");
        return false;
    }

    if (password === ""){
        alert("Password is empty");
        return false;
    }

    return true;
}

function bindLoginForm() {
    const loginForm = document.getElementById('login-form');
    if (!loginForm) {
        return;
    }

    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (validateLogin(loginForm)) {
            const formData = getFilteredFormData(loginForm);
            await login(formData);
        }
    });
}

bindLoginForm();