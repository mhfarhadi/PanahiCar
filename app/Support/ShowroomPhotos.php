<?php

namespace App\Support;

class ShowroomPhotos
{
    public const URLS = [
        'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1542362567-b07e54382151?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1617531653332-bd46c24f2068?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1583121274602-3e2820c69888?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1541899481282-d31b1887bfb5?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1489824904134-891ab6453941?auto=format&fit=crop&w=1400&q=80',
        'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1400&q=80',
    ];

    public static function url(int $index): string
    {
        $photos = self::URLS;

        return $photos[abs($index) % count($photos)];
    }
}
