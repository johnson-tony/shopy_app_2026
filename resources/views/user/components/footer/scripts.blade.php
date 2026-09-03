<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---------------------------------------------------------
    // 1. Mobile Footer Accordion Toggle
    // ---------------------------------------------------------
    const accordionColumns = document.querySelectorAll('.footer-column[data-accordion]');

    accordionColumns.forEach(function (col) {
        const title = col.querySelector('h4');
        if (!title) return;

        title.addEventListener('click', function () {
            // Only toggle on mobile screens (< 768px)
            if (window.innerWidth <= 768) {
                const isOpen = col.classList.contains('open');

                // Optionally close other accordions
                accordionColumns.forEach(c => c.classList.remove('open'));

                if (!isOpen) {
                    col.classList.add('open');
                }
            }
        });
    });

    // ---------------------------------------------------------
    // 2. Newsletter AJAX Subscription Handler
    // ---------------------------------------------------------
    const newsletterForm = document.getElementById('newsletterForm');

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const submitBtn = newsletterForm.querySelector('button[type="submit"]');
            const originalText = submitBtn ? submitBtn.textContent : 'Subscribe';

            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Subscribing...';
            }

            const formData = new FormData(newsletterForm);

            fetch(newsletterForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => Promise.reject(err));
                }
                return response.json();
            })
            .then(data => {
                const message = data.message || 'Subscribed successfully! Welcome aboard.';
                if (typeof toastr !== 'undefined') {
                    toastr.success(message);
                } else {
                    alert(message);
                }
                newsletterForm.reset();
            })
            .catch(error => {
                let message = 'Subscription failed. Please check your email and try again.';
                if (error?.errors?.email?.[0]) {
                    message = error.errors.email[0];
                } else if (error?.message) {
                    message = error.message;
                }

                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            })
            .finally(() => {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            });
        });
    }
});
</script>
