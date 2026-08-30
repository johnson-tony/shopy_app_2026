<div class="flex items-center justify-between gap-4 px-4 sm:px-6 py-2.5 text-sm shadow-md" style="background:#2962ff;">
    <p class="text-white flex items-center gap-2 font-medium">
        <i class="fas fa-shield-halved"></i>
        <span>
            You are browsing the storefront as a customer.
        </span>
    </p>
    <form id="endImpersonationForm" method="POST" action="{{ route('stop.impersonation') }}">
        @csrf
        <button type="button" id="endImpersonationBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white text-indigo-700 font-semibold text-xs hover:bg-slate-100 transition cursor-pointer shadow-sm">
            <i class="fas fa-arrow-left"></i>
            End &amp; Close Tab
        </button>
    </form>
</div>

<script>
    document.getElementById('endImpersonationBtn')?.addEventListener('click', function(e) {
        e.preventDefault();
        const form = document.getElementById('endImpersonationForm');
        const token = form.querySelector('input[name="_token"]')?.value;

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            keepalive: true
        }).then(() => {
            window.close();
            setTimeout(() => {
                window.location.href = "{{ route('admin.users.index') }}";
            }, 250);
        }).catch(() => {
            window.close();
            setTimeout(() => {
                form.submit();
            }, 250);
        });
    });
</script>
