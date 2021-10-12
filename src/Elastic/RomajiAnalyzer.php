<?php

namespace App\Elastic;

use App\Document\Manager\SongManager;

class RomajiAnalyzer
{
    const KATAKANA_MAPPING = [
        'ァ' => 'a',
        'ア' => 'a',
        'ィ' => 'i',
        'イ' => 'i',
        'ゥ' => 'u',
        'ウ' => 'u',
        'ェ' => 'e',
        'エ' => 'e',
        'ォ' => 'o',
        'オ' => 'o',
        'カ' => 'Ka',
        'ガ' => 'Ga',
        'キ' => 'Ki',
        'ギ' => 'Gi',
        'ク' => 'Ku',
        'グ' => 'Gu',
        'ケ' => 'Ke',
        'ゲ' => 'Ge',
        'コ' => 'Ko',
        'ゴ' => 'Go',
        'サ' => 'Sa',
        'ザ' => 'Za',
        'シ' => 'Si',
        'ジ' => 'Zi',
        'ス' => 'Su',
        'ズ' => 'Zu',
        'セ' => 'Se',
        'ゼ' => 'Ze',
        'ソ' => 'So',
        'ゾ' => 'Zo',
        'タ' => 'Ta',
        'ダ' => 'Da',
        'チ' => 'Ti',
        'ヂ' => 'Di',
        'ッ' => 'Tu',
        'ツ' => 'Tu',
        'ヅ' => 'Du',
        'テ' => 'Te',
        'デ' => 'De',
        'ト' => 'To',
        'ド' => 'Do',
        'ナ' => 'Na',
        'ニ' => 'Ni',
        'ヌ' => 'Nu',
        'ネ' => 'Ne',
        'ノ' => 'No',
        'ハ' => 'Ha',
        'バ' => 'Ba',
        'パ' => 'Pa',
        'ヒ' => 'Hi',
        'ビ' => 'Bi',
        'ピ' => 'Pi',
        'フ' => 'Hu',
        'ブ' => 'Bu',
        'プ' => 'Pu',
        'ヘ' => 'He',
        'ベ' => 'Be',
        'ペ' => 'Pe',
        'ホ' => 'Ho',
        'ボ' => 'Bo',
        'ポ' => 'Po',
        'マ' => 'Ma',
        'ミ' => 'Mi',
        'ム' => 'Mu',
        'メ' => 'Me',
        'モ' => 'Mo',
        'ャ' => 'Ya',
        'ヤ' => 'Ya',
        'ュ' => 'Yu',
        'ユ' => 'Yu',
        'ョ' => 'Yo',
        'ヨ' => 'Yo',
        'ラ' => 'Ra',
        'リ' => 'Ri',
        'ル' => 'Ru',
        'レ' => 'Re',
        'ロ' => 'Ro',
        'ヮ' => 'Wa',
        'ワ' => 'Wa',
        'ヰ' => 'Wi',
        'ヱ' => 'We',
        'ヲ' => 'Wo',
        'ン' => 'n',
        'ヴ' => 'Vu',
        'ヵ' => 'Ka',
        'ヶ' => 'Ke',
    ];

    const HIRAGANA_MAPPING = [
        'ー' => '',
        'ぁ' => 'a',
        'あ' => 'a',
        'ぃ' => 'i',
        'い' => 'i',
        'ぅ' => 'u',
        'う' => 'u',
        'ぇ' => 'e',
        'え' => 'e',
        'ぉ' => 'o',
        'お' => 'o',
        'か' => 'Ka',
        'が' => 'Ga',
        'き' => 'Ki',
        'ぎ' => 'Gi',
        'く' => 'Ku',
        'ぐ' => 'Gu',
        'け' => 'Ke',
        'げ' => 'Ge',
        'こ' => 'Ko',
        'ご' => 'Go',
        'さ' => 'Sa',
        'ざ' => 'Za',
        'し' => 'Si',
        'じ' => 'Zi',
        'す' => 'Su',
        'ず' => 'Zu',
        'せ' => 'Se',
        'ぜ' => 'Ze',
        'そ' => 'So',
        'ぞ' => 'Zo',
        'た' => 'Ta',
        'だ' => 'Da',
        'ち' => 'Ti',
        'ぢ' => 'Di',
        'っ' => 'Tu',
        'つ' => 'Tu',
        'づ' => 'Du',
        'て' => 'Te',
        'で' => 'De',
        'と' => 'To',
        'ど' => 'Do',
        'な' => 'Na',
        'に' => 'Ni',
        'ぬ' => 'Nu',
        'ね' => 'Ne',
        'の' => 'No',
        'は' => 'Ha',
        'ば' => 'Ba',
        'ぱ' => 'Pa',
        'ひ' => 'Hi',
        'び' => 'Bi',
        'ぴ' => 'Pi',
        'ふ' => 'Hu',
        'ぶ' => 'Bu',
        'ぷ' => 'Pu',
        'へ' => 'He',
        'べ' => 'Be',
        'ぺ' => 'Pe',
        'ほ' => 'Ho',
        'ぼ' => 'Bo',
        'ぽ' => 'Po',
        'ま' => 'Ma',
        'み' => 'Mi',
        'む' => 'Mu',
        'め' => 'Me',
        'も' => 'Mo',
        'ゃ' => 'Ya',
        'や' => 'Ya',
        'ゅ' => 'Yu',
        'ゆ' => 'Yu',
        'ょ' => 'Yo',
        'よ' => 'Yo',
        'ら' => 'Ra',
        'り' => 'Ri',
        'る' => 'Ru',
        'れ' => 'Re',
        'ろ' => 'Ro',
        'ゎ' => 'Wa',
        'わ' => 'Wa',
        'ゐ' => 'Wi',
        'ゑ' => 'We',
        'を' => 'Wo',
        'ん' => 'n',
    ];

    /**
     * @var SongManager
     */
    private $songManager;

    public function __construct(SongManager $songManager)
    {
        $this->songManager = $songManager;
    }

    public function analyze(?string $value)
    {
        $tokens = $this->songManager->getElastic()->indices()->analyze([
            'index' => 'songs',
            'body' => [
                'analyzer' => 'romaji_analyzer',
                'text' => $value,
            ],
        ])['tokens'] ?? [];

        $replacements = [];
        foreach ($tokens as $token) {
            // Do not replace values that are in the value.
            if (strpos($value, $token['token']) !== false) {
                continue;
            }
            $length = $token['end_offset'] - $token['start_offset'];
            $replacements[mb_substr($value, $token['start_offset'], $length)] = ' ' . ucfirst($token['token']) . ' ';
        }

        $basicFiltered = trim(preg_replace('/\s\s+/', ' ', str_replace(array_keys($replacements), array_values($replacements), $value)));

        // Replace remaining hiragana and katakana symbols
        $katakanaFiltered = str_replace(array_keys(self::KATAKANA_MAPPING), array_values(self::KATAKANA_MAPPING), $basicFiltered);
        $hiraganaFiltered = str_replace(array_keys(self::HIRAGANA_MAPPING), array_values(self::HIRAGANA_MAPPING), $katakanaFiltered);

        return $hiraganaFiltered;
    }
}