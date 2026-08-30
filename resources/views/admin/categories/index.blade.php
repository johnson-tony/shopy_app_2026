@extends('admin.layouts.admin')

@section('title', 'Category Management')

@section('content')
<div class="space-y-6">
    <!-- Header Banner -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 text-xs font-semibold text-indigo-400 mb-2">
                <span class="w-2 h-2 rounded-full bg-indigo-400"></span>
                <span>Storefront Catalog</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">Category Management</h1>
            <p class="text-slate-400 text-sm mt-1">
                Manage product hierarchies, subcategories, banner visuals, and featured placements.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.categories.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm shadow-md shadow-indigo-600/30 transition cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Add Category</span>
            </a>
        </div>
    </div>

    <!-- Metrics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Categories</p>
            <p class="text-2xl font-black text-white mt-1.5">{{ $stats['total'] }}</p>
        </div>
        <!-- Root Categories -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Root / Parent</p>
            <p class="text-2xl font-black text-indigo-400 mt-1.5">{{ $stats['root'] }}</p>
        </div>
        <!-- Subcategories -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Subcategories</p>
            <p class="text-2xl font-black text-cyan-400 mt-1.5">{{ $stats['sub'] }}</p>
        </div>
        <!-- Active -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Active</p>
            <p class="text-2xl font-black text-emerald-400 mt-1.5">{{ $stats['active'] }}</p>
        </div>
        <!-- Featured -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-sm">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Featured</p>
            <p class="text-2xl font-black text-amber-400 mt-1.5">{{ $stats['featured'] }}</p>
        </div>
    </div>

    <!-- Filters & Search Form -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-xl">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-center">
            <!-- Search Keyword -->
            <div class="relative lg:col-span-2">
                <input type="text" name="search" value="{{ $search }}" placeholder="Search by name, slug or description..."
                    class="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                <svg class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Parent Filter -->
            <div>
                <select name="parent" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Hierarchies</option>
                    <option value="root" {{ $parentFilter === 'root' ? 'selected' : '' }}>Root Categories Only</option>
                    <option value="sub" {{ $parentFilter === 'sub' ? 'selected' : '' }}>Subcategories Only</option>
                    <optgroup label="Under Specific Parent">
                        @foreach ($rootCategories as $root)
                            <option value="{{ $root->id }}" {{ (string) $parentFilter === (string) $root->id ? 'selected' : '' }}>
                                {{ $root->name }}
                            </option>
                        @endforeach
                    </optgroup>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition">
                    <option value="">All Statuses</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                </select>
            </div>

            <!-- Actions (Filter & Reset) -->
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold transition cursor-pointer">
                    Apply
                </button>
                @if ($search !== '' || $parentFilter !== null || $statusFilter !== null || $featuredFilter !== null)
                    <a href="{{ route('admin.categories.index') }}" class="px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm font-medium transition" title="Clear Filters">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Categories Table -->
    <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-3.5 px-4">Category</th>
                        <th class="py-3.5 px-4">Hierarchy</th>
                        <th class="py-3.5 px-4">Order</th>
                        <th class="py-3.5 px-4">Featured</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 text-slate-300">
                    @forelse ($categories as $category)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Category Image & Info -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    @if ($category->image_url)
                                        <img src="{{ $category->image_url }}" alt="{{ $category->name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-700 shrink-0 bg-slate-800">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                            @if ($category->icon)
                                                <i class="{{ $category->icon }} text-base text-indigo-400"></i>
                                            @else
                                                <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                                </svg>
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-bold text-white text-sm">{{ $category->name }}</p>
                                        <p class="text-[11px] text-slate-500 font-mono">{{ $category->slug }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Hierarchy / Parent -->
                            <td class="py-3.5 px-4">
                                @if ($category->parent)
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-medium bg-slate-800 text-slate-300 border border-slate-700">
                                        <span class="text-indigo-400">{{ $category->parent->name }}</span>
                                        <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        <span>Subcategory</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-indigo-950/60 text-indigo-300 border border-indigo-500/30">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                        Root Category
                                    </span>
                                @endif
                            </td>

                            <!-- Sort Order -->
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 font-mono text-xs">
                                    {{ $category->sort_order }}
                                </span>
                            </td>

                            <!-- Featured Toggle -->
                            <td class="py-3.5 px-4">
                                <form method="POST" action="{{ route('admin.categories.toggleFeatured', $category) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $category->is_featured ? 'bg-amber-950/70 text-amber-300 border border-amber-500/40 hover:bg-amber-900/60' : 'bg-slate-800/80 text-slate-500 border border-slate-700 hover:text-slate-300' }}">
                                        <svg class="w-3 h-3 {{ $category->is_featured ? 'text-amber-400 fill-amber-400' : 'text-slate-500' }}" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        {{ $category->is_featured ? 'Featured' : 'Standard' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Active Status Toggle -->
                            <td class="py-3.5 px-4">
                                <form method="POST" action="{{ route('admin.categories.toggleStatus', $category) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold transition cursor-pointer {{ $category->status ? 'bg-emerald-950/70 text-emerald-300 border border-emerald-500/40 hover:bg-emerald-900/60' : 'bg-rose-950/70 text-rose-300 border border-rose-500/40 hover:bg-rose-900/60' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $category->status ? 'bg-emerald-400' : 'bg-rose-400' }}"></span>
                                        {{ $category->status ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-indigo-600 text-slate-300 hover:text-white transition" title="Edit Category">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>

                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Are you sure you want to delete category \'{{ $category->name }}\'? Any subcategories will become root categories.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-600 text-slate-300 hover:text-white transition cursor-pointer" title="Delete Category">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-12 h-12 rounded-2xl bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                        </svg>
                                    </div>
                                    <p class="text-slate-300 font-semibold text-sm">No categories found</p>
                                    <p class="text-slate-500 text-xs mt-1">Get started by creating your first storefront category.</p>
                                    <a href="{{ route('admin.categories.create') }}" class="mt-4 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs transition">
                                        + Add Category
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if ($categories->hasPages())
            <div class="pt-4 border-t border-slate-800">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
