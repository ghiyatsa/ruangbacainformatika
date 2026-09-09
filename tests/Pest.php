<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');
uses(TestCase::class)->in('Unit');

/*
|--------------------------------------------------------------------------
| Safety Guard
|--------------------------------------------------------------------------
|
| A cached config file (from `php artisan config:cache`) freezes the values
| that were present when it was generated, which silently overrides every
| `<env>` entry in phpunit.xml. When that happens the suite connects to the
| real local MySQL database instead of the in-memory SQLite instance and
| `RefreshDatabase` wipes development data. Fail loudly instead of
| corrupting anyone's database.
|
*/

beforeAll(function (): void {
    if (app()->configurationIsCached()) {
        throw new RuntimeException(
            'Configuration is cached. Run `php artisan config:clear` before testing, '
            .'otherwise phpunit.xml environment values are ignored and the suite '
            .'may connect to your local database.'
        );
    }
});

beforeEach(function (): void {
    if (config('database.default') !== 'sqlite') {
        throw new RuntimeException(
            'Tests must run against SQLite. Got database connection '
            .'"'.config('database.default').'". Check that phpunit.xml env values '
            .'are applied and no configuration cache is present.'
        );
    }
});

/**
 * Helper autentikasi user untuk pengujian Feature.
 */
function asUser(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->create());
}

function asMember(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->member()->create());
}

function asStaff(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->staff()->create());
}

function asAdmin(?User $user = null): TestCase
{
    return test()->actingAs($user ?? User::factory()->admin()->create());
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/
