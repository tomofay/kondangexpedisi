<?php

namespace App\Http\Controllers;

use App\Models\LandingPageContent;
use Illuminate\Http\Request;

class LandingPageContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = min(max((int) $request->integer('per_page', 20), 5), 100);
        $sortBy = in_array($request->input('sort_by'), ['id', 'section', 'sort_order', 'title', 'created_at'], true)
            ? $request->input('sort_by')
            : 'sort_order';
        $sortDir = $request->input('sort_dir') === 'asc' ? 'asc' : 'desc';

        $query = LandingPageContent::query();

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('section')) {
            $query->where('section', $request->input('section'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOL));
        }

        return response()->json(
            $query->orderBy($sortBy, $sortDir)->paginate($perPage)->appends($request->query())
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'section' => ['required', 'in:hero,feature,testimonial,faq,cta,contact,statistic'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        $content = LandingPageContent::query()->create($validated);

        return response()->json(['message' => 'Landing page content created.', 'data' => $content], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(LandingPageContent $landingPageContent)
    {
        return response()->json($landingPageContent);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LandingPageContent $landingPageContent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LandingPageContent $landingPageContent)
    {
        $validated = $request->validate([
            'section' => ['sometimes', 'in:hero,feature,testimonial,faq,cta,contact,statistic'],
            'title' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'cta_label' => ['nullable', 'string', 'max:255'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ]);

        $landingPageContent->update($validated);

        return response()->json(['message' => 'Landing page content updated.', 'data' => $landingPageContent->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LandingPageContent $landingPageContent)
    {
        $landingPageContent->delete();

        return response()->json(['message' => 'Landing page content deleted.']);
    }
}
