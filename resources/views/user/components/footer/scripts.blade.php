<script>
document.addEventListener('DOMContentLoaded', function () {
    const newsletterForm = document.getElementById('newsletterForm');

    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(newsletterForm);

            fetch(newsletterForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const message = data.message || 'Subscribed successfully!';
                if (typeof toastr !== 'undefined') {
                    toastr.success(message);
                } else {
                    alert(message);
                }
                newsletterForm.reset();
            })
            .catch(error => {
                let message = 'Subscription failed. Please try again!';
                if (error?.response?.data?.message) {
                    message = error.response.data.message;
                }
                if (typeof toastr !== 'undefined') {
                    toastr.error(message);
                } else {
                    alert(message);
                }
            });
        });
    }
});
</script>
