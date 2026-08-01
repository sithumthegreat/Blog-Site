document.addEventListener('DOMContentLoaded', function () {
    const flashMessages = document.querySelectorAll('.alert, .flash-message');

    flashMessages.forEach(function (message) {
        setTimeout(function () {
            message.classList.add('is-fading');
            setTimeout(function () {
                message.remove();
            }, 300);
        }, 4000);
    });

    const authForms = document.querySelectorAll('form[action*="login.php"], form[action*="register.php"]');

    authForms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const fields = form.querySelectorAll('input[required], textarea[required]');
            let hasError = false;

            fields.forEach(function (field) {
                const fieldName = field.name || field.id || 'field';
                const value = field.value.trim();
                const errorElement = field.parentElement ? field.parentElement.querySelector('.field-error') : null;

                if (!value) {
                    hasError = true;
                    field.classList.add('is-invalid');

                    if (errorElement) {
                        errorElement.textContent = 'This field is required.';
                    }
                } else {
                    field.classList.remove('is-invalid');

                    if (errorElement) {
                        errorElement.textContent = '';
                    }
                }

                if (field.type === 'email' && value) {
                    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailPattern.test(value)) {
                        hasError = true;
                        field.classList.add('is-invalid');

                        if (errorElement) {
                            errorElement.textContent = 'Please enter a valid email address.';
                        }
                    }
                }
            });

            if (hasError) {
                event.preventDefault();
            }
        });
    });

    const deleteActions = document.querySelectorAll('.btn-delete, form[action*="delete.php"]');

    deleteActions.forEach(function (action) {
        const trigger = action.tagName === 'FORM' ? action : action.closest('form');

        if (!trigger) {
            return;
        }

        trigger.addEventListener('submit', function (event) {
            const shouldDelete = window.confirm('Are you sure you want to delete this blog post? This action cannot be undone.');

            if (!shouldDelete) {
                event.preventDefault();
            }
        });
    });

    const markdownTextarea = document.getElementById('content');
    const markdownPreview = document.getElementById('preview');

    if (markdownTextarea && markdownPreview && typeof marked !== 'undefined') {
        const updatePreview = function () {
            markdownPreview.innerHTML = marked.parse(markdownTextarea.value || '');
        };

        markdownTextarea.addEventListener('input', updatePreview);
        updatePreview();
    }
});
