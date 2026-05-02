<?php

namespace App\Http\Controllers;

use App\Services\SimilarityApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SimilarityController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('similarity');
    }

    public function check(Request $request, SimilarityApiService $api): JsonResponse
    {
        $validated = $request->validate([
            'judul' => 'required|string|min:5',
        ]);

        $hasil = $api->checkSimilarity($validated['judul']);

        return response()->json($hasil);
    }
}
