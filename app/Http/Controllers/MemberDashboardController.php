<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DocumentSubmission;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MemberDashboardController extends Controller
{
    /**
     * Ringkasan untuk anggota: status pengajuan dokumen yang pernah dikirim.
     *
     * Anggota sebelumnya tidak punya tempat untuk melihat pengajuannya
     * sendiri, sehingga tidak tahu apakah berkasnya masih ditinjau,
     * perlu revisi, atau sudah diterima.
     */
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $submissions = DocumentSubmission::query()
            ->where('user_id', $user->id)
            ->with('submittable')
            ->orderByDesc('created_at')
            ->get();

        $counts = [
            'total' => $submissions->count(),
            'pending' => $submissions->where('status', DocumentSubmission::STATUS_PENDING)->count(),
            'revision' => $submissions->where('status', DocumentSubmission::STATUS_REVISION)->count(),
            'approved' => $submissions->where('status', DocumentSubmission::STATUS_APPROVED)->count(),
            'rejected' => $submissions->where('status', DocumentSubmission::STATUS_REJECTED)->count(),
        ];

        return Inertia::render('member-dashboard/index', [
            'counts' => $counts,
            'submissions' => $submissions
                ->take(10)
                ->map(fn (DocumentSubmission $submission): array => [
                    'id' => $submission->id,
                    'title' => $submission->title,
                    'type' => $submission->type,
                    'type_label' => $submission->typeLabel(),
                    'status' => $submission->status,
                    'status_label' => $submission->statusLabel(),
                    'status_color' => $submission->statusColor(),
                    'revision_notes' => $submission->revision_notes,
                    'created_at' => $submission->created_at?->toIso8601String(),
                    'reviewed_at' => $submission->reviewed_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        ]);
    }
}
