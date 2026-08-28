<?php

namespace App\Http\Controllers;

use App\Models\CatalogBookmark;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CatalogBookmarkController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $records = $request->user()
            ->catalogBookmarks()
            ->orderBy('id')
            ->get()
            ->map(fn (CatalogBookmark $bookmark): array => $bookmark->payload)
            ->values()
            ->all();

        return response()->json(['bookmarks' => $records]);
    }

    public function replace(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bookmarks' => ['present', 'array', 'max:200'],
            'bookmarks.*.catalogType' => [
                'required',
                'string',
                Rule::in(['book', 'skripsi', 'thesis', 'internship_report', 'post']),
            ],
            'bookmarks.*.id' => ['required', 'integer', 'min:1'],
            'bookmarks.*.href' => ['required', 'string', 'max:2048'],
            'bookmarks.*.title' => ['required', 'string', 'max:500'],
            'bookmarks.*.subtitle' => ['nullable', 'string', 'max:500'],
            'bookmarks.*.meta' => ['nullable', 'string', 'max:200'],
            'bookmarks.*.year' => ['nullable', 'integer'],
            'bookmarks.*.coverImageUrl' => ['nullable', 'string', 'max:2048'],
            'bookmarks.*.kindLabel' => ['required', 'string', 'max:50'],
            'bookmarks.*.statusLabel' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        $records = collect($validated['bookmarks'])
            ->map(fn (array $record): array => [
                'catalogType' => (string) $record['catalogType'],
                'id' => (int) $record['id'],
                'href' => (string) $record['href'],
                'title' => (string) $record['title'],
                'subtitle' => $record['subtitle'] ?? null,
                'meta' => $record['meta'] ?? null,
                'year' => isset($record['year']) ? (int) $record['year'] : null,
                'coverImageUrl' => $record['coverImageUrl'] ?? null,
                'kindLabel' => (string) $record['kindLabel'],
                'statusLabel' => $record['statusLabel'] ?? null,
            ])
            ->unique(fn (array $record): string => "{$record['catalogType']}:{$record['id']}")
            ->values();

        DB::transaction(function () use ($user, $records): void {
            $user->catalogBookmarks()->delete();

            foreach ($records as $record) {
                $user->catalogBookmarks()->create([
                    'record_key' => "{$record['catalogType']}:{$record['id']}",
                    'payload' => $record,
                ]);
            }
        });

        return response()->json(['bookmarks' => $records->all()]);
    }
}
