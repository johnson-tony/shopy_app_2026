<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "preventDuplicates": false,
                "onclick": null,
                "showDuration": "300",
                "hideDuration": "1000",
                "timeOut": "5000",
                "extendedTimeOut": "1500",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            @if (session('success'))
                toastr.success("{!! addslashes(session('success')) !!}", "Success");
            @endif

            @if (session('error'))
                toastr.error("{!! addslashes(session('error')) !!}", "Error");
            @endif

            @if (session('info'))
                toastr.info("{!! addslashes(session('info')) !!}", "Info");
            @endif

            @if (session('warning'))
                toastr.warning("{!! addslashes(session('warning')) !!}", "Warning");
            @endif
        }
    });
</script>
<style>
    #toast-container {
        z-index: 999999 !important;
    }
    #toast-container > div {
        opacity: 0.97 !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.15) !important;
        font-family: inherit !important;
    }
</style>
