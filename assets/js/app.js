document.addEventListener('DOMContentLoaded', function () {

    /* ---------------- Flash messages (existing, unchanged) ---------------- */

    const flashMessages = document.querySelectorAll('.alert, .flash-message');

    flashMessages.forEach(function (message) {
        setTimeout(function () {
            message.classList.add('is-fading');
            setTimeout(function () {
                message.remove();
            }, 300);
        }, 4000);
    });

    /* ---------------- Auth form validation (existing, unchanged) ---------------- */

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

    /* ---------------- Delete confirmation (upgraded to a themed modal) ---------------- */
    /* Falls back to window.confirm automatically if the modal markup
       fails to build for any reason, so delete protection never breaks. */

    const deleteActions = document.querySelectorAll('.btn-delete, form[action*="delete.php"]');

    deleteActions.forEach(function (action) {
        const trigger = action.tagName === 'FORM' ? action : action.closest('form');

        if (!trigger) {
            return;
        }

        trigger.addEventListener('submit', function (event) {
            if (trigger.dataset.confirmed === 'true') {
                return; // user already confirmed via the modal, let it submit
            }

            event.preventDefault();
            confirmDelete(function (confirmed) {
                if (confirmed) {
                    trigger.dataset.confirmed = 'true';
                    trigger.submit();
                }
            });
        });
    });

    function confirmDelete(callback) {
        try {
            openConfirmModal({
                title: 'Delete this post?',
                message: 'This action cannot be undone. The post will be permanently removed.',
                confirmLabel: 'Delete',
                cancelLabel: 'Keep it',
                onConfirm: function () { callback(true); },
                onCancel: function () { callback(false); }
            });
        } catch (err) {
            // Safety net: never let a UI bug block the delete flow entirely.
            callback(window.confirm('Are you sure you want to delete this blog post? This action cannot be undone.'));
        }
    }

    function openConfirmModal(options) {
        const overlay = document.createElement('div');
        overlay.className = 'confirm-overlay';

        overlay.innerHTML =
            '<div class="confirm-modal" role="alertdialog" aria-modal="true" aria-labelledby="confirm-title">' +
                '<h2 id="confirm-title">' + options.title + '</h2>' +
                '<p>' + options.message + '</p>' +
                '<div class="confirm-actions">' +
                    '<button type="button" class="confirm-cancel">' + options.cancelLabel + '</button>' +
                    '<button type="button" class="confirm-delete">' + options.confirmLabel + '</button>' +
                '</div>' +
            '</div>';

        document.body.appendChild(overlay);
        document.body.classList.add('modal-open');

        // Trigger the entrance transition on the next frame.
        requestAnimationFrame(function () {
            overlay.classList.add('is-visible');
        });

        const cancelBtn = overlay.querySelector('.confirm-cancel');
        const confirmBtn = overlay.querySelector('.confirm-delete');

        function close(confirmed) {
            overlay.classList.remove('is-visible');
            document.body.classList.remove('modal-open');
            setTimeout(function () {
                overlay.remove();
            }, 200);

            if (confirmed) {
                options.onConfirm();
            } else {
                options.onCancel();
            }
        }

        cancelBtn.addEventListener('click', function () { close(false); });
        confirmBtn.addEventListener('click', function () { close(true); });
        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) {
                close(false);
            }
        });

        document.addEventListener('keydown', function escHandler(event) {
            if (event.key === 'Escape') {
                document.removeEventListener('keydown', escHandler);
                close(false);
            }
        });

        confirmBtn.focus();
    }

    /* ---------------- Markdown live preview (existing, unchanged) ---------------- */

    const markdownTextarea = document.getElementById('content');
    const markdownPreview = document.getElementById('preview');

    if (markdownTextarea && markdownPreview && typeof marked !== 'undefined') {
        const updatePreview = function () {
            markdownPreview.innerHTML = marked.parse(markdownTextarea.value || '');
        };

        markdownTextarea.addEventListener('input', updatePreview);
        updatePreview();
    }

    /* ---------------- Post card staggered reveal on scroll ---------------- */

    const revealCards = document.querySelectorAll('.post-card');

    if (revealCards.length && 'IntersectionObserver' in window) {
        revealCards.forEach(function (card, index) {
            card.classList.add('reveal-on-scroll');
            card.style.transitionDelay = Math.min(index * 60, 360) + 'ms';
        });

        const revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealCards.forEach(function (card) {
            revealObserver.observe(card);
        });
    } else {
        revealCards.forEach(function (card) {
            card.classList.add('is-revealed');
        });
    }

    /* ---------------- Reading progress bar (post detail view only) ---------------- */

    const postDetail = document.querySelector('.post-detail');

    if (postDetail) {
        const progressBar = document.createElement('div');
        progressBar.className = 'reading-progress';
        progressBar.innerHTML = '<div class="reading-progress-fill"></div>';
        document.body.prepend(progressBar);

        const fill = progressBar.querySelector('.reading-progress-fill');

        function updateProgress() {
            const rect = postDetail.getBoundingClientRect();
            const articleHeight = postDetail.offsetHeight - window.innerHeight;
            const scrolled = -rect.top;
            const percent = articleHeight > 0
                ? Math.min(100, Math.max(0, (scrolled / articleHeight) * 100))
                : 0;

            fill.style.width = percent + '%';
        }

        window.addEventListener('scroll', updateProgress, { passive: true });
        window.addEventListener('resize', updateProgress);
        updateProgress();
    }

    /* ---------------- Estimated reading time badge (post detail view only) ---------------- */

    const postContent = document.getElementById('post-content');
    const postMeta = document.querySelector('.post-detail .post-meta');

    if (postContent && postMeta) {
        // Give marked.js a moment to finish rendering the markdown first.
        setTimeout(function () {
            const wordCount = postContent.textContent.trim().split(/\s+/).filter(Boolean).length;
            const minutes = Math.max(1, Math.round(wordCount / 200));

            const badge = document.createElement('span');
            badge.className = 'reading-time';
            badge.textContent = minutes + ' min read';
            postMeta.appendChild(badge);
        }, 150);
    }

});