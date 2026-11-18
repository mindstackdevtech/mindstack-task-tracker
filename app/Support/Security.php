<?php

namespace App\Support;

use Illuminate\Validation\Rules\Password;

class Security
{
    /** Plain-language version of the rule below, shown under password fields. */
    public const PASSWORD_HINT = 'At least 8 characters, with a capital letter, a small letter and a number.';

    /**
     * The one password rule for sign-up, password reset and password change.
     */
    public static function passwordRule(): Password
    {
        return Password::min(8)->letters()->mixedCase()->numbers();
    }

    /**
     * Makes a value safe to put in a CSV file. Spreadsheet programs run cells
     * that start with = + - or @ as formulas, so those get a leading quote.
     */
    public static function csvCell(mixed $value): string
    {
        $text = (string) ($value ?? '');

        return preg_match('/^[=+\-@\t\r]/', $text) ? "'".$text : $text;
    }
}
