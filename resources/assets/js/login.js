(function() {
    'use strict';

    // Password visibility toggle
    const passwordToggle = document.querySelector('.password-toggle');
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('passwordToggleIcon');

    if (passwordToggle && passwordInput) {
        passwordToggle.addEventListener('click', togglePasswordVisibility);
        passwordToggle.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                togglePasswordVisibility();
            }
        });

        function togglePasswordVisibility() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);

            if (type === 'text') {
                toggleIcon.classList.remove('icon-eye-slash');
                toggleIcon.classList.add('icon-eye');
                passwordToggle.setAttribute('aria-pressed', 'true');
                passwordToggle.setAttribute('aria-label', 'Hide password');
            } else {
                toggleIcon.classList.remove('icon-eye');
                toggleIcon.classList.add('icon-eye-slash');
                passwordToggle.setAttribute('aria-pressed', 'false');
                passwordToggle.setAttribute('aria-label', 'Show password');
            }

            passwordInput.focus();
        }
    }

    // Защита от двойной отправки и индикатор загрузки
    const form = document.getElementById('login-form');

    if (form) {
        let isSubmitting = false;
        let recoveryTimeout = null;
        const submitButton = form.querySelector('button[type="submit"]');

        // Оригинальный HTML сохраняется из атрибута data-original-html
        // который устанавливается в шаблоне

        function restoreButton() {
            if (submitButton && submitButton.disabled) {
                submitButton.disabled = false;
                const originalHtml = submitButton.getAttribute('data-original-html');
                if (originalHtml) {
                    submitButton.innerHTML = originalHtml;
                }
                submitButton.classList.remove('opacity-75');
                isSubmitting = false;

                if (recoveryTimeout) {
                    clearTimeout(recoveryTimeout);
                    recoveryTimeout = null;
                }
            }
        }

        function disableButtonWithSpinner() {
            if (submitButton && !submitButton.disabled) {
                if (!submitButton.hasAttribute('data-original-html')) {
                    submitButton.setAttribute('data-original-html', submitButton.innerHTML);
                }

                submitButton.disabled = true;
                const buttonText = submitButton.textContent.trim();
                submitButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    ${buttonText}
                `;
                submitButton.classList.add('opacity-75');
            }
        }

        form.addEventListener('submit', function(e) {
            if (isSubmitting) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            isSubmitting = true;
            disableButtonWithSpinner();

            recoveryTimeout = setTimeout(function() {
                if (isSubmitting && submitButton && submitButton.disabled) {
                    restoreButton();

                    const alert = document.createElement('div');
                    alert.className = 'alert alert-warning alert-dismissible fade show mt-3';
                    alert.role = 'alert';
                    alert.innerHTML = `
                        <i class="icon-exclamation-triangle me-2"></i>
                        Превышено время ожидания ответа от сервера. Пожалуйста, проверьте соединение и попробуйте снова.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    form.prepend(alert);
                    setTimeout(() => alert.remove(), 5000);
                }
            }, 30000);

            return true;
        });

        function checkForValidationErrors() {
            const hasErrors = document.querySelector('.alert-danger, .invalid-feedback, .is-invalid');
            if (hasErrors && isSubmitting) {
                restoreButton();
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkForValidationErrors);
        } else {
            checkForValidationErrors();
        }

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'attributes') {
                    const hasErrors = document.querySelector('.alert-danger, .invalid-feedback, .is-invalid');
                    if (hasErrors && isSubmitting) {
                        restoreButton();
                        observer.disconnect();
                    }
                }
            });
        });

        if (isSubmitting) {
            observer.observe(document.body, {
                childList: true,
                subtree: true,
                attributes: true,
                attributeFilter: ['class']
            });
        }

        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(function(input) {
            input.addEventListener('focus', function() {
                if (isSubmitting && submitButton && submitButton.disabled) {
                    const hasErrors = document.querySelector('.alert-danger, .invalid-feedback, .is-invalid');
                    if (hasErrors) {
                        restoreButton();
                    }
                }
            });
        });

        window.addEventListener('beforeunload', function() {
            if (recoveryTimeout) {
                clearTimeout(recoveryTimeout);
            }
        });
    }
})();
