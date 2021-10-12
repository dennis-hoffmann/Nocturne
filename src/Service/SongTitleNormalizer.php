<?php

namespace App\Service;

class SongTitleNormalizer
{
    public static function normalize(string $title)
    {
        return trim(preg_replace(
            [
                '/\s\(?.*Remaster\)?/i',
                '/Album Version/i',
                '/Extended Version/',
                '/Extended/i',
                '/\s\(?Feat.?.*\)?$/i',
                '/\s\(.* Version\)?$/i',
                '/\s\(.* Edit\)$/i',
                '/\s\(.* Mix\)$/i',
            ],
            '',
            $title
        ));
    }
}