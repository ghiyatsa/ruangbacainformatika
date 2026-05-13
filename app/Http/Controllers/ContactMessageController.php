<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ContactMessageController extends Controller
{
    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::query()->create([
            ...$request->validated(),
            'status' => ContactMessage::STATUS_NEW,
        ]);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Pesan Anda berhasil dikirim. Admin akan segera meninjau kontak ini.',
        ]);

        return back(status: 303);
    }
}
