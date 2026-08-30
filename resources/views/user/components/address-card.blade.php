@props(['address'])

<div class="bg-white rounded-2xl border {{ $address->is_default ? 'border-indigo-500 ring-2 ring-indigo-100 shadow-sm' : 'border-slate-200 shadow-xs' }} flex flex-col justify-between overflow-hidden transition hover:shadow-md">
    <div class="p-6 space-y-4">
        <!-- Top Header: Address Type Badge & Default Badge -->
        <div class="flex items-center justify-between gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wider {{ $address->is_default ? 'bg-indigo-50 text-indigo-700 border border-indigo-100' : 'bg-slate-100 text-slate-700' }}">
                @if(strtolower($address->address_type ?? '') === 'home')
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                @elseif(strtolower($address->address_type ?? '') === 'work')
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                @else
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                    </svg>
                @endif
                <span>{{ $address->address_type ?: 'Address' }}</span>
            </span>

            @if($address->is_default)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>DEFAULT</span>
                </span>
            @endif
        </div>

        <!-- Address Body Details -->
        <div class="space-y-1.5 text-sm">
            <h4 class="font-bold text-slate-900 text-base">{{ $address->full_name }}</h4>
            
            @if($address->phone)
                <p class="text-slate-600 flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                    </svg>
                    <span>{{ $address->phone }}</span>
                </p>
            @endif

            <div class="pt-2 text-slate-700 leading-relaxed">
                <p>{{ $address->address_line1 }}</p>
                @if($address->address_line2)
                    <p>{{ $address->address_line2 }}</p>
                @endif
                @if($address->landmark)
                    <p class="text-xs text-slate-500 italic">Landmark: {{ $address->landmark }}</p>
                @endif
                <p>{{ $address->city }}, {{ $address->state }} - <span class="font-semibold">{{ $address->postal_code }}</span></p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">{{ $address->country }}</p>
            </div>
        </div>
    </div>

    <!-- Actions Footer -->
    <div class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
        <div>
            @if(!$address->is_default)
                <form method="POST" action="{{ route('user.addresses.default', $address) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="font-semibold text-indigo-600 hover:text-indigo-800 transition">
                        Set as Default
                    </button>
                </form>
            @else
                <span class="text-emerald-700 font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Default Delivery Address
                </span>
            @endif
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('user.addresses.edit', $address) }}" class="font-semibold text-slate-700 hover:text-indigo-600 transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                <span>Edit</span>
            </a>

            <span class="text-slate-300">|</span>

            <form method="POST" action="{{ route('user.addresses.destroy', $address) }}" onsubmit="return confirm('Are you sure you want to delete this address?');" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="font-semibold text-rose-600 hover:text-rose-800 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>Delete</span>
                </button>
            </form>
        </div>
    </div>
</div>
