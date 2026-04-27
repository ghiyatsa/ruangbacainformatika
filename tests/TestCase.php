<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Fortify\Features;

/**
 * @mixin BaseTestCase
 *
 * @method self actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, string $driver = null)
 * @method self get(string $uri, array $headers = [])
 * @method self post(string $uri, array $data = [], array $headers = [])
 * @method self put(string $uri, array $data = [], array $headers = [])
 * @method self patch(string $uri, array $data = [], array $headers = [])
 * @method self delete(string $uri, array $data = [], array $headers = [])
 */
abstract class TestCase extends BaseTestCase
{
    protected function skipUnlessFortifyHas(string $feature, ?string $message = null): void
    {
        if (! Features::enabled($feature)) {
            $this->markTestSkipped($message ?? "Fortify feature [{$feature}] is not enabled.");
        }
    }
}
