<?php

use App\Models\Loan;
use App\Notifications\Auth\VerifyEmailOtpNotification;
use App\Notifications\LoanReceiptNotification;

it('uses the default queue for email notifications', function () {
    $verificationNotification = new VerifyEmailOtpNotification;
    $loanReceiptNotification = new LoanReceiptNotification(Loan::factory()->make());

    expect($verificationNotification->queue)->toBeNull()
        ->and($loanReceiptNotification->queue)->toBeNull();
});
