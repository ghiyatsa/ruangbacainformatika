<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The Setting model still has 'value' => 'encrypted' cast at this point,
        // so reading via the model automatically decrypts each value.
        // We then write back the plain text via DB facade (bypasses the cast).
        Setting::all()->each(function (Setting $setting): void {
            try {
                // $setting->value is already decrypted by the cast
                $plainValue = $setting->value;

                // Re-save as plain text, but encrypt similarity_api_secret
                if ($setting->key === 'similarity_api_secret' && filled($plainValue)) {
                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(['value' => encrypt($plainValue)]);
                } else {
                    DB::table('settings')
                        ->where('id', $setting->id)
                        ->update(['value' => $plainValue]);
                }
            } catch (Exception) {
                // Value was not encrypted (already plain), skip
            }
        });
    }

    public function down(): void
    {
        // Re-encrypt all values (rollback: encrypt everything again)
        Setting::all()->each(function (Setting $setting): void {
            DB::table('settings')
                ->where('id', $setting->id)
                ->update(['value' => encrypt($setting->value)]);
        });
    }
};
