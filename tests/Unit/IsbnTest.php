<?php

use App\Support\Isbn;

it('normalizes isbn separators and uppercases an isbn-10 check digit', function () {
    expect(Isbn::normalize(' 0-8044-2957-x '))->toBe('080442957X');
});

it('accepts valid isbn-10 and isbn-13 values', function () {
    expect(Isbn::isValid('0-8044-2957-X'))->toBeTrue()
        ->and(Isbn::isValid('978-0-306-40615-7'))->toBeTrue();
});

it('accepts local 8 digit isbn values', function () {
    expect(Isbn::isValid('1234-5678'))->toBeTrue()
        ->and(Isbn::normalize(' 1234-5678 '))->toBe('12345678');
});

it('rejects incomplete or invalid isbn values', function () {
    expect(Isbn::isValid('123456789'))->toBeFalse()
        ->and(Isbn::isValid('0804429579'))->toBeFalse()
        ->and(Isbn::isValid('9780306406158'))->toBeFalse();
});
