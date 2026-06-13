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

it('accepts isbn entries with a recognized format even when checksum digits do not match', function () {
    expect(Isbn::hasAcceptedFormat('9786028599000'))->toBeTrue()
        ->and(Isbn::isValid('9786028599000'))->toBeFalse()
        ->and(Isbn::hasAcceptedFormat('0804429579'))->toBeTrue()
        ->and(Isbn::isValid('0804429579'))->toBeFalse();
});

it('rejects incomplete or invalid isbn values', function () {
    expect(Isbn::hasAcceptedFormat('123456789'))->toBeFalse()
        ->and(Isbn::hasAcceptedFormat('ABCDEFGHIJKLM'))->toBeFalse()
        ->and(Isbn::isValid('9780306406158'))->toBeFalse();
});
