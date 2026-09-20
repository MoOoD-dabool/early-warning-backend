<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Wording-only change: replaces colloquial contractions in the Arabic
     * precaution texts ("بالمنزل" -> "في المنزل", ...) with formal Arabic.
     * Punctuation is untouched on purpose: the Flutter app splits each
     * paragraph into bullet points on the sentence-ending "." / "!".
     * Rows are matched by their existing content (not by key), so it works on
     * any database and does nothing when a text has no such wording.
     */
    private const REPLACEMENTS = [
        'بالمنزل' => 'في المنزل',
        'بالمبنى' => 'في المبنى',
        'بالحي' => 'في الحي',
        'بالداخل' => 'في الداخل',
        'بالخارج' => 'في الخارج',
        'وابلّغ' => 'وأبلغ',
    ];

    private const COLUMNS = [
        'instructions_before_ar',
        'instructions_during_ar',
        'instructions_after_ar',
    ];

    public function up(): void
    {
        foreach (DB::table('disaster_types')->get() as $row) {
            $changes = [];

            foreach (self::COLUMNS as $column) {
                $old = (string) $row->{$column};
                $new = strtr($old, self::REPLACEMENTS);

                if ($new !== $old) {
                    $changes[$column] = $new;
                }
            }

            if ($changes !== []) {
                DB::table('disaster_types')->where('id', $row->id)->update($changes);
            }
        }
    }

    /**
     * Wording fix only; nothing to restore.
     */
    public function down(): void
    {
    }
};
