<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ModeRequest;
use App\Models\Mode;
use App\Services\CloudinaryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ModeController extends Controller
{
    protected CloudinaryService $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Display a listing of modes with search and status filtering.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $statusFilter = $request->input('status');

        $query = Mode::query();

        // Search Filter
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($statusFilter === 'active' || $statusFilter === '1') {
            $query->where('status', true);
        } elseif ($statusFilter === 'inactive' || $statusFilter === '0') {
            $query->where('status', false);
        }

        $modes = $query->orderBy('sort_order', 'asc')
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Mode::count(),
            'active' => Mode::where('status', true)->count(),
            'inactive' => Mode::where('status', false)->count(),
        ];

        return view('admin.modes.index', compact(
            'modes',
            'stats',
            'search',
            'statusFilter'
        ));
    }

    /**
     * Show the form for creating a new mode.
     */
    public function create(): View
    {
        return view('admin.modes.create');
    }

    /**
     * Store a newly created mode in database with Cloudinary image upload.
     */
    public function store(ModeRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Auto-generate unique slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Mode::generateUniqueSlug($data['name']);
        }

        // Handle Cloudinary Image Upload to dedicated 'mode' folder
        if ($request->hasFile('image')) {
            try {
                $data['image'] = $this->cloudinaryService->uploadModeImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Mode Image Upload Failed', ['error' => $e->getMessage()]);
                return back()
                    ->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        $mode = Mode::create($data);

        return redirect()->route('admin.modes.index')
            ->with('success', "Shopping mode '{$mode->name}' has been created successfully.");
    }

    /**
     * Show the form for editing the specified mode.
     */
    public function edit(Mode $mode): View
    {
        return view('admin.modes.edit', compact('mode'));
    }

    /**
     * Update the specified mode in database with Cloudinary image replacement.
     */
    public function update(ModeRequest $request, Mode $mode): RedirectResponse
    {
        $data = $request->validated();

        // Auto-generate unique slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = Mode::generateUniqueSlug($data['name'], $mode->id);
        }

        // Handle Cloudinary Image Upload to dedicated 'mode' folder
        if ($request->hasFile('image')) {
            try {
                if ($mode->image) {
                    $this->deleteModeImage($mode->image);
                }
                $data['image'] = $this->cloudinaryService->uploadModeImage($request->file('image'));
            } catch (Exception $e) {
                Log::error('Mode Image Upload Failed', ['error' => $e->getMessage()]);
                return back()
                    ->withErrors(['image' => 'Image upload to Cloudinary failed: ' . $e->getMessage()])
                    ->withInput();
            }
        }

        $mode->update($data);

        return redirect()->route('admin.modes.index')
            ->with('success', "Shopping mode '{$mode->name}' has been updated successfully.");
    }

    /**
     * Remove the specified mode from database.
     */
    public function destroy(Mode $mode): RedirectResponse
    {
        // Prevent accidental deletion if related records exist in future modules
        if ($mode->hasRelatedRecords()) {
            return back()->with('error', "Mode '{$mode->name}' cannot be deleted because it is assigned to existing catalog or order records.");
        }

        $name = $mode->name;

        // Delete associated image from Cloudinary or local storage
        if ($mode->image) {
            $this->deleteModeImage($mode->image);
        }

        $mode->delete();

        return redirect()->route('admin.modes.index')
            ->with('success', "Shopping mode '{$name}' has been deleted successfully.");
    }

    /**
     * Helper to safely remove mode image from Cloudinary or local storage.
     */
    protected function deleteModeImage(string $imagePath): void
    {
        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, 'mode')) {
            $this->cloudinaryService->deleteImage($imagePath);
        } elseif (Storage::disk('public')->exists($imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
    }

    /**
     * Toggle active status via 1-click switch.
     */
    public function toggleStatus(Mode $mode, Request $request): RedirectResponse|JsonResponse
    {
        $mode->status = !$mode->status;
        $mode->save();

        $statusText = $mode->status ? 'activated' : 'deactivated';
        $message = "Mode '{$mode->name}' has been {$statusText}.";

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $mode->status,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
