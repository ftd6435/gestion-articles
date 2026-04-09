/**
 * Toggle password visibility
 */
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('toggleIcon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Small UX enhancement
 */
document.addEventListener('DOMContentLoaded', () => {
    const inputs = document.querySelectorAll('.form-control');

    inputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('is-focused');
        });

        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('is-focused');
        });
    });

    document.querySelectorAll('form.auth-form').forEach(form => {
        form.addEventListener('submit', () => {
            const submitButton = form.querySelector('button[type="submit"][data-loading-text]');
            if (!submitButton) {
                return;
            }

            submitButton.disabled = true;
            submitButton.textContent = submitButton.getAttribute('data-loading-text') || 'Connexion...';
        });
    });
});

