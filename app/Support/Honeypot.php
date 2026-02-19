<?php

namespace App\Support;

class Honeypot
{
    public const FIELD_ONE = 'middle_name';

    public const FIELD_TWO = 'referral_code';

    /**
     * @return array<string, array<int, string>>
     */
    public static function rules(): array
    {
        return [
            self::FIELD_ONE => ['nullable', 'string', 'max:0'],
            self::FIELD_TWO => ['nullable', 'string', 'max:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            self::FIELD_ONE.'.max' => 'Unable to process request.',
            self::FIELD_TWO.'.max' => 'Unable to process request.',
        ];
    }
}
