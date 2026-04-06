(function() {
    'use strict';

    const form = document.getElementById('password-reset-form');

    if (form) {
        let isSubmitting = false;
        let recoveryTimeout = null;
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
                    ${buttonText}
                `;
                submitButton.classList.add('opacity-75');
            }
        }

        // Обработчик отправки формы
        form.addEventListener('submit', function(e) {
            // Проверяем валидность формы
            if (!form.checkValidity()) {
                e.stopPropagation();
                form.classList.add('was-validated');
                return false;
            }

            if (isSubmitting) {
                e.preventDefault();
                e.stopPropagation();
                return false;
            }

            isSubmitting = true;
            disableButtonWithSpinner();

            // Таймаут на случай проблем с сетью (30 секунд)
            recoveryTimeout = setTimeout(function() {
                if (isSubmitting && submitButton && submitButton.disabled) {
                    restoreButton();

                    // Создаем алерт об ошибке
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-warning alert-dismissible fade show mt-3';
                    alert.role = 'alert';
                    alert.innerHTML = `
                        <i class="icon-exclamation-triangle me-2"></i>
                        ${submitButton.dataset.timeoutMessage || 'Превышено время ожидания ответа от сервера. Пожалуйста, проверьте соединение и попробуйте снова.'}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    form.prepend(alert);

                    // Автоматическое скрытие через 5 секунд
                    setTimeout(() => {
                        if (alert && alert.remove) alert.remove();
                    }, 5000);
                }
            }, 30000);

            return true;
        });

        // Функция проверки наличия ошибок валидации после ответа сервера
        function checkForValidationErrors() {
            const hasErrors = document.querySelector('.alert-danger, .invalid-feedback, .is-invalid');
            if (hasErrors && isSubmitting) {
                restoreButton();
            }
        }

        // Проверяем ошибки при загрузке страницы
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', checkForValidationErrors);
        } else {
            checkForValidationErrors();
        }

        // Наблюдатель за появлением ошибок на странице
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

        // Восстанавливаем кнопку при фокусе на поле ввода (если есть ошибки)
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

        // Очищаем таймаут при уходе со страницы
        window.addEventListener('beforeunload', function() {
            if (recoveryTimeout) {
                clearTimeout(recoveryTimeout);
            }
        });
    }
})();
