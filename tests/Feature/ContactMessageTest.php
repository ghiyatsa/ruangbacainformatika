<?php

use App\Models\ContactMessage;
use Illuminate\Cache\RateLimiter as CacheRateLimiter;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ThrottleRequests;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\from;
use function Pest\Laravel\withoutMiddleware;

beforeEach(function () {
    withoutMiddleware(PreventRequestForgery::class);
});

it('visitors can submit the contact form', function () {
    from(route('contact'))
        ->post(route('contact.store'), [
            'name' => 'Pengunjung Demo',
            'email' => 'pengunjung@example.com',
            'phone' => '081234567890',
            'subject' => 'Pertanyaan layanan koleksi',
            'message' => 'Saya ingin menanyakan proses pembaruan data akun pada layanan ruang baca.',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHas('inertia.flash_data.toast.message', 'Pesan Anda berhasil dikirim. Tim pengelola perpustakaan akan segera menindaklanjuti.');

    assertDatabaseHas(ContactMessage::class, [
        'name' => 'Pengunjung Demo',
        'email' => 'pengunjung@example.com',
        'subject' => 'Pertanyaan layanan koleksi',
        'status' => ContactMessage::STATUS_NEW,
    ]);
});

it('contact form validates required fields', function () {
    from(route('contact'))
        ->post(route('contact.store'), [
            'name' => '',
            'email' => 'email-tidak-valid',
            'subject' => 'Hai',
            'message' => 'Terlalu pendek',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors([
            'name',
            'email',
            'subject',
            'message',
        ]);
});

it('contact form rejects malformed contact details and unclear text', function () {
    from(route('contact'))
        ->post(route('contact.store'), [
            'name' => '@@@@',
            'email' => 'pengunjung@example.com',
            'phone' => '12345',
            'subject' => '.... ....',
            'message' => '........ ........ ........',
        ])
        ->assertRedirect(route('contact'))
        ->assertSessionHasErrors([
            'name',
            'phone',
            'subject',
            'message',
        ]);
});

it('contact form is rate limited after repeated submissions', function () {
    $route = app('router')->getRoutes()->getByName('contact.store');

    expect($route->gatherMiddleware())->toContain('throttle:contact-messages');

    $middleware = new ThrottleRequests(app(CacheRateLimiter::class));
    $next = fn () => response('ok');
    $makeRequest = function () use ($route): Request {
        $request = Request::create(route('contact.store'), 'POST', server: [
            'REMOTE_ADDR' => '127.0.0.1',
        ]);
        $request->setRouteResolver(fn () => $route);

        return $request;
    };

    foreach (range(1, 5) as $attempt) {
        $response = $middleware->handle($makeRequest(), $next, 'contact-messages');

        expect($response->getStatusCode())->toBe(200);
    }

    try {
        $middleware->handle($makeRequest(), $next, 'contact-messages');

        $this->fail('Expected the contact rate limiter to stop the sixth submission.');
    } catch (HttpResponseException $exception) {
        $response = $exception->getResponse();

        expect($response->getStatusCode())->toBe(429);
        expect($response->getData(true))->toMatchArray([
            'message' => 'Terlalu banyak percobaan mengirim pesan. Coba lagi sebentar.',
        ]);
    }
});
