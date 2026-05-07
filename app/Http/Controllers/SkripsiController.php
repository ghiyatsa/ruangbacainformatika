<?php

namespace App\Http\Controllers;

use App\Http\Resources\SkripsiResource;
use App\Models\Skripsi;
use Inertia\Inertia;
use Inertia\Response;

class SkripsiController extends Controller
{
    public function show(Skripsi $skripsi): Response
    {
        return Inertia::render('skripsi/show', [
            'skripsi' => new SkripsiResource($skripsi),
        ]);
    }
}
