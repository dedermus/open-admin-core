(function() {
    'use strict';

    // Password visibility toggle для поля нового пароля
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

    // Password visibility toggle для поля подтверждения пароля
    const confirmPasswordToggle = document.querySelector('.password-toggle-confirm');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    const confirmToggleIcon = document.getElementById('confirmPasswordToggleIcon');

    if (confirmPasswordToggle && confirmPasswordInput) {
        confirmPasswordToggle.addEventListener('click', toggleConfirmPasswordVisibility);
        confirmPasswordToggle.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                toggleConfirmPasswordVisibility();
            }
        });

        function toggleConfirmPasswordVisibility() {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);

            if (type === 'text') {
                confirmToggleIcon.classList.remove('icon-eye-slash');
                confirmToggleIcon.classList.add('icon-eye');
                confirmPasswordToggle.setAttribute('aria-pressed', 'true');
                confirmPasswordToggle.setAttribute('aria-label', 'Hide password');
            } else {
                confirmToggleIcon.classList.remove('icon-eye');
                confirmToggleIcon.classList.add('icon-eye-slash');
                confirmPasswordToggle.setAttribute('aria-pressed', 'false');
                confirmPasswordToggle.setAttribute('aria-label', 'Show password');
            }

            confirmPasswordInput.focus();
        }
    }

    // Основная логика формы
    const form = document.getElementById('password-reset-form');

    if (form) {
        let isSubmitting = false;
        let recoveryTimeout = null;
        let throttleTimeout = null; // Таймаут для throttle
        const submitButton = form.querySelector('button[type="submit"]');

        // Функция восстановления кнопки
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

        // Функция блокировки кнопки со спиннером
        function disableButtonWithSpinner() {
            if (submitButton && !submitButton.disabled) {
                if (!submitButton.hasAttribute('data-original-html')) {
                    submitButton.setAttribute('data-original-html', submitButton.innerHTML);
                }

                submitButton.disabled = true;
                const buttonText = submitButton.textContent.trim();
                submitButton.innerHTML = `
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    ${submitButton.dataset.loadingText || buttonText}
                `;
                submitButton.classList.add('opacity-75');
            }
        }

        // Функция блокировки кнопки на определенное время (throttle)
        function throttleButton(seconds) {
            if (!submitButton) return;

            const originalHtml = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                Подождите ${seconds} сек...
            `;

            if (throttleTimeout) {
                clearTimeout(throttleTimeout);
            }

            throttleTimeout = setTimeout(() => {
                submitButton.disabled = false;
                submitButton.innerHTML = originalHtml;
                throttleTimeout = null;
            }, seconds * 1000);
        }

        // Функция показа уведомления
        function showAlert(message, type = 'danger') {
            // Удаляем старые алерты
            const oldAlerts = document.querySelectorAll('.alert-auto-dismiss');
            oldAlerts.forEach(alert => alert.remove());

            // Создаем новый алерт
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-auto-dismiss`;
            alertDiv.innerHTML = `
                <i class="icon-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${message}
            `;
            alertDiv.style.marginBottom = '1rem';

            // Вставляем перед формой
            const formParent = form.parentNode;
            formParent.insertBefore(alertDiv, form);

            // Автоматически скрываем через 5 секунд
            setTimeout(() => {
                alertDiv.style.transition = 'opacity 0.5s';
                alertDiv.style.opacity = '0';
                setTimeout(() => alertDiv.remove(), 500);
            }, 5000);
        }

        // Функция очистки ошибок валидации
        function clearValidationErrors() {
            const invalidFields = form.querySelectorAll('.is-invalid');
            invalidFields.forEach(field => field.classList.remove('is-invalid'));

            const errorMessages = form.querySelectorAll('.invalid-feedback');
            errorMessages.forEach(error => error.remove());
        }

        // Функция показа ошибок валидации
        function showValidationErrors(errors) {
            clearValidationErrors();

            for (const [field, messages] of Object.entries(errors)) {
                const input = form.querySelector(`[name="${field}"]`);
                if (input) {
                    input.classList.add('is-invalid');

                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'invalid-feedback d-block';
                    errorDiv.innerHTML = messages.join('<br>');

                    const parent = input.closest('.mb-3') || input.parentElement;
                    parent.appendChild(errorDiv);
                }
            }
        }

        // Обработчик отправки формы
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Проверяем валидность формы
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return;
            }

            if (isSubmitting) {
                return;
            }

            // Очищаем старые ошибки
            clearValidationErrors();

            isSubmitting = true;
            disableButtonWithSpinner();

            // Таймаут на случай проблем с сетью (30 секунд)
            recoveryTimeout = setTimeout(function() {
                if (isSubmitting && submitButton && submitButton.disabled) {
                    restoreButton();
                    showAlert(submitButton.dataset.timeoutMessage || 'Превышено время ожидания ответа от сервера. Пожалуйста, проверьте соединение и попробуйте снова.', 'warning');
                }
            }, 30000);

            // Собираем данные формы
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    // Успешный сброс пароля
                    if (recoveryTimeout) clearTimeout(recoveryTimeout);

                    showAlert(data.message || 'Пароль успешно изменен! Сейчас вы будете перенаправлены на страницу входа.', 'success');

                    // Перенаправляем на страницу входа через 2 секунды
                    setTimeout(() => {
                        window.location.href = data.redirect || '/admin/auth/login';
                    }, 2000);
                } else {
                    // Ошибка от сервера
                    if (recoveryTimeout) clearTimeout(recoveryTimeout);

                    if (data.errors) {
                        showValidationErrors(data.errors);
                    }

                    // Проверяем на throttle ошибку
                    if (data.message && (data.message.includes('throttled') || data.message.includes('много попыток'))) {
                        // Блокируем кнопку на 60 секунд
                        throttleButton(60);
                        showAlert('Слишком много попыток. Пожалуйста, подождите 1 минуту перед повторной попыткой.', 'warning');
                    } else {
                        showAlert(data.message || 'Произошла ошибка. Пожалуйста, попробуйте позже.', 'danger');
                    }

                    restoreButton();
                }
            } catch (error) {
                console.error('Ошибка:', error);
                if (recoveryTimeout) clearTimeout(recoveryTimeout);
                showAlert('Произошла ошибка сети. Пожалуйста, проверьте соединение и попробуйте позже.', 'danger');
                restoreButton();
            }
        });

        // Восстанавливаем форму при ошибке загрузки страницы
        if (document.querySelector('.is-invalid')) {
            restoreButton();
        }
    }
})();
