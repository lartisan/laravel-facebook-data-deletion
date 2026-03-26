<?php

namespace Lartisan\FacebookDataDeletion\Services;

use Illuminate\Support\Str;

class ConfirmationCodeGenerator
{
    public function generate(string $modelClass): string
    {
        do {
            $confirmationCode = Str::upper(Str::random(32));
        } while ($modelClass::query()->where('confirmation_code', $confirmationCode)->exists());

        return $confirmationCode;
    }
}
