/**
 * @param {HTMLFormElement} signUpForm
 * @returns {boolean}
 */
function validateSignUp(signUpForm) {
    const name = signUpForm.elements["name"].value;
    const username = signUpForm.elements["username"].value;
    const password = signUpForm.elements["password"].value;
    const passwordConfirmation = signUpForm.elements["passwordConfirmation"].value;

    if (name === ""){
        alert("Name is empty");
        return false;
    }

    if (username === ""){
        alert("Username is empty");
        return false;
    }

    if (password === ""){
        alert("Password is empty");
        return false;
    }

    if (passwordConfirmation === ""){
        alert("Please confirm your password");
        return false;
    }
    
    if (password !== passwordConfirmation) {
        alert("Password and password confirmation do not match");
        return false;
    }

    return true;
}

function bindSignUpForm() {
    const signUpForm = document.getElementById('sign-up-form');
    if (!signUpForm) {
        return;
    }

    signUpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (validateSignUp(signUpForm)) {
            const formData = getFilteredFormData(signUpForm);
            await signUp(formData);
        }
    });
}

bindSignUpForm();