@if (session('success'))
    <div class="mb-6 rounded-xl bg-emerald-950/40 border border-emerald-500/30 p-4 text-sm text-emerald-300 flex items-center gap-3">
        <i class="fa-solid fa-circle-check text-emerald-400"></i>
        <div class="font-medium">{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="mb-6 rounded-xl bg-rose-950/40 border border-rose-500/30 p-4 text-sm text-rose-300 flex items-center gap-3">
        <i class="fa-solid fa-circle-exclamation text-rose-400"></i>
        <div class="font-medium">{{ session('error') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="mb-6 rounded-xl bg-rose-950/40 border border-rose-500/30 p-4 text-sm text-rose-300">
        <div class="font-semibold flex items-center gap-2 mb-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-400"></i> Delivery Partner Notice:
        </div>
        <ul class="list-disc list-inside space-y-1 text-rose-400 text-xs">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif