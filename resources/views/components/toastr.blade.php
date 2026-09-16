<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof toastr !== 'undefined') {
            toastr.options = {
                "closeButton": true,
                "debug": false,
                "newestOnTop": true,
                "progressBar": false,
                "positionClass": "toast-bottom-center",
                "preventDuplicates": true,
                "onclick": null,
                "showDuration": "200",
                "hideDuration": "200",
                "timeOut": "3000",
                "extendedTimeOut": "1000",
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
        width: 100% !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 24px !important;
        padding: 0 16px !important;
        box-sizing: border-box !important;
        pointer-events: none;
    }

    #toast-container > div {
        width: auto !important;
        min-width: 0 !important;
        max-width: min(420px, calc(100vw - 32px)) !important;
        margin: 8px auto !important;
        padding: 10px 34px 10px 14px !important;
        opacity: 0.98 !important;
        border-radius: 10px !important;
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.16) !important;
        font-family: inherit !important;
        font-size: 13px !important;
        line-height: 1.35 !important;
        pointer-events: auto;
    }

    #toast-container .toast-title {
        font-size: 13px !important;
        font-weight: 600 !important;
        margin-bottom: 2px !important;
    }

    #toast-container .toast-message {
        font-size: 13px !important;
        font-weight: 400 !important;
    }

    #toast-container .toast-close-button {
        font-size: 18px !important;
        line-height: 18px !important;
        right: 9px !important;
        top: 8px !important;
    }
</style>
