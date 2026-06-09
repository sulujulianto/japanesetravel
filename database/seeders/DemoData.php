<?php

namespace Database\Seeders;

class DemoData
{
    public static function users(): array
    {
        return [
            [
                'username' => 'kei.watanabe',
                'email' => 'kei@japantravel.com',
            ],
            [
                'username' => 'maya.kuroda',
                'email' => 'maya@japantravel.com',
            ],
            [
                'username' => 'riko.suzuki',
                'email' => 'riko@japantravel.com',
            ],
            [
                'username' => 'haru.nakamura',
                'email' => 'haru@japantravel.com',
            ],
            [
                'username' => 'yuna.tanaka',
                'email' => 'yuna@japantravel.com',
            ],
            [
                'username' => 'sora.mizuno',
                'email' => 'sora@japantravel.com',
            ],
        ];
    }

    public static function places(): array
    {
        return [
            [
                'slug' => 'senso-ji-temple',
                'name_id' => 'Kuil Senso-ji',
                'name_en' => 'Senso-ji Temple',
                'description_id' => 'Kuil bersejarah di Asakusa dengan gerbang Kaminarimon dan deretan toko di Nakamise-dori.',
                'description_en' => 'A historic Asakusa temple known for Kaminarimon Gate and the shops along Nakamise-dori.',
                'address' => '2 Chome-3-1 Asakusa, Taito City, Tokyo',
                'facilities' => 'Area ibadah, pusat informasi, toko oleh-oleh, toilet umum',
                'open_days' => 'Setiap hari',
                'open_hours' => '06:00 - 17:00',
            ],
            [
                'slug' => 'shibuya-crossing',
                'name_id' => 'Shibuya Crossing',
                'name_en' => 'Shibuya Crossing',
                'description_id' => 'Persimpangan ikonik di pusat Shibuya yang dikelilingi pusat belanja, kafe, dan sudut pandang kota.',
                'description_en' => 'An iconic central Shibuya crossing surrounded by shopping, cafes, and city viewpoints.',
                'address' => 'Shibuya Station Hachiko Exit, Shibuya City, Tokyo',
                'facilities' => 'Akses stasiun, pusat informasi, area belanja, kafe',
                'open_days' => 'Setiap hari',
                'open_hours' => '24 jam',
            ],
            [
                'slug' => 'fushimi-inari-taisha',
                'name_id' => 'Fushimi Inari Taisha',
                'name_en' => 'Fushimi Inari Taisha',
                'description_id' => 'Kompleks kuil di Kyoto dengan jalur ribuan gerbang torii yang mendaki lereng Gunung Inari.',
                'description_en' => 'A Kyoto shrine complex with thousands of torii gates forming trails up Mount Inari.',
                'address' => '68 Fukakusa Yabunouchicho, Fushimi Ward, Kyoto',
                'facilities' => 'Jalur pejalan kaki, area ibadah, toilet umum, toko kecil',
                'open_days' => 'Setiap hari',
                'open_hours' => '24 jam',
            ],
            [
                'slug' => 'arashiyama-bamboo-grove',
                'name_id' => 'Hutan Bambu Arashiyama',
                'name_en' => 'Arashiyama Bamboo Grove',
                'description_id' => 'Jalur bambu yang teduh di Arashiyama, dekat dengan kawasan kuil, taman, dan tepi Sungai Katsura.',
                'description_en' => 'A shaded bamboo path in Arashiyama near temples, gardens, and the Katsura River.',
                'address' => 'Sagaogurayama Tabuchiyamacho, Ukyo Ward, Kyoto',
                'facilities' => 'Jalur pejalan kaki, akses stasiun, toilet umum di area sekitar',
                'open_days' => 'Setiap hari',
                'open_hours' => '24 jam',
            ],
            [
                'slug' => 'dotonbori',
                'name_id' => 'Dotonbori',
                'name_en' => 'Dotonbori',
                'description_id' => 'Kawasan tepi kanal Osaka yang dikenal dengan papan cahaya, restoran, dan jajanan jalanan.',
                'description_en' => 'An Osaka canal district known for illuminated signs, restaurants, and street food.',
                'address' => 'Dotonbori, Chuo Ward, Osaka',
                'facilities' => 'Area kuliner, pusat belanja, jalur tepi kanal, akses stasiun',
                'open_days' => 'Setiap hari',
                'open_hours' => 'Jam operasional berbeda menurut tempat',
            ],
            [
                'slug' => 'nara-park',
                'name_id' => 'Taman Nara',
                'name_en' => 'Nara Park',
                'description_id' => 'Taman luas di Nara yang menghubungkan ruang hijau, museum, dan beberapa kuil bersejarah.',
                'description_en' => 'A broad Nara park connecting green spaces, museums, and several historic temples.',
                'address' => 'Nara Park, Nara, Nara Prefecture',
                'facilities' => 'Jalur pejalan kaki, pusat informasi, toilet umum, area istirahat',
                'open_days' => 'Setiap hari',
                'open_hours' => '24 jam',
            ],
            [
                'slug' => 'lake-kawaguchi',
                'name_id' => 'Danau Kawaguchi',
                'name_en' => 'Lake Kawaguchi',
                'description_id' => 'Danau di kawasan Fuji Five Lakes dengan jalur tepi air dan pemandangan Gunung Fuji saat cuaca cerah.',
                'description_en' => 'A Fuji Five Lakes destination with waterfront paths and Mount Fuji views in clear weather.',
                'address' => 'Fujikawaguchiko, Minamitsuru District, Yamanashi',
                'facilities' => 'Jalur tepi danau, pusat informasi, penyewaan sepeda, area istirahat',
                'open_days' => 'Setiap hari',
                'open_hours' => 'Area publik 24 jam',
            ],
            [
                'slug' => 'hiroshima-peace-memorial-park',
                'name_id' => 'Taman Memorial Perdamaian Hiroshima',
                'name_en' => 'Hiroshima Peace Memorial Park',
                'description_id' => 'Ruang publik untuk refleksi sejarah, dengan monumen dan museum di pusat kota Hiroshima.',
                'description_en' => 'A public space for historical reflection, with memorials and a museum in central Hiroshima.',
                'address' => '1 Nakajimacho, Naka Ward, Hiroshima',
                'facilities' => 'Museum, pusat informasi, toilet umum, area istirahat',
                'open_days' => 'Taman buka setiap hari',
                'open_hours' => 'Taman 24 jam; museum memiliki jadwal terpisah',
            ],
            [
                'slug' => 'itsukushima-shrine',
                'name_id' => 'Kuil Itsukushima',
                'name_en' => 'Itsukushima Shrine',
                'description_id' => 'Kompleks kuil di Miyajima yang dikenal dengan gerbang torii di laut dan lanskap pesisirnya.',
                'description_en' => 'A Miyajima shrine complex known for its offshore torii gate and coastal setting.',
                'address' => '1-1 Miyajimacho, Hatsukaichi, Hiroshima',
                'facilities' => 'Kompleks kuil, akses feri, pusat informasi, toilet umum',
                'open_days' => 'Setiap hari',
                'open_hours' => '06:30 - 18:00',
            ],
            [
                'slug' => 'shirakawa-go',
                'name_id' => 'Shirakawa-go',
                'name_en' => 'Shirakawa-go',
                'description_id' => 'Desa pegunungan di Gifu dengan rumah tradisional beratap gassho-zukuri dan jalur observasi.',
                'description_en' => 'A mountain village in Gifu with traditional gassho-zukuri houses and an observation trail.',
                'address' => 'Ogimachi, Shirakawa, Ono District, Gifu',
                'facilities' => 'Pusat informasi, museum lokal, area parkir, toilet umum',
                'open_days' => 'Setiap hari',
                'open_hours' => 'Area desa terbuka; fasilitas memiliki jadwal masing-masing',
            ],
        ];
    }

    public static function souvenirs(): array
    {
        return [
            [
                'name_id' => 'Tokyo Banana',
                'name_en' => 'Tokyo Banana',
                'description_id' => 'Kue bolu lembut berisi krim pisang dalam kemasan praktis untuk oleh-oleh.',
                'description_en' => 'Soft sponge cakes with banana cream in a travel-friendly gift box.',
                'price' => 185000,
                'stock' => 36,
            ],
            [
                'name_id' => 'Shiroi Koibito',
                'name_en' => 'Shiroi Koibito',
                'description_id' => 'Biskuit tipis dengan lapisan cokelat putih, dikenal sebagai oleh-oleh dari Hokkaido.',
                'description_en' => 'Thin biscuits layered with white chocolate, widely associated with Hokkaido.',
                'price' => 210000,
                'stock' => 30,
            ],
            [
                'name_id' => 'Camilan Matcha',
                'name_en' => 'Matcha Snacks',
                'description_id' => 'Pilihan biskuit rasa matcha dengan karakter teh yang ringan dan sedikit pahit.',
                'description_en' => 'Matcha-flavored biscuits with a light tea aroma and gently bitter finish.',
                'price' => 95000,
                'stock' => 42,
            ],
            [
                'name_id' => 'KitKat Rasa Regional',
                'name_en' => 'Regional KitKat',
                'description_id' => 'Cokelat wafer dengan rasa musiman atau regional yang berganti mengikuti ketersediaan.',
                'description_en' => 'Wafer chocolate in seasonal or regional flavors, subject to availability.',
                'price' => 135000,
                'stock' => 28,
            ],
            [
                'name_id' => 'Yatsuhashi Kyoto',
                'name_en' => 'Kyoto Yatsuhashi',
                'description_id' => 'Penganan Kyoto berbahan beras dengan aroma kayu manis dan isian kacang merah.',
                'description_en' => 'A Kyoto rice confection with cinnamon notes and sweet red bean filling.',
                'price' => 120000,
                'stock' => 24,
            ],
            [
                'name_id' => 'Senbei',
                'name_en' => 'Senbei Rice Crackers',
                'description_id' => 'Kerupuk beras renyah dengan rasa gurih yang cocok sebagai camilan perjalanan.',
                'description_en' => 'Crisp, savory rice crackers suited to sharing or travel snacking.',
                'price' => 85000,
                'stock' => 48,
            ],
            [
                'name_id' => 'Tenugui Motif Jepang',
                'name_en' => 'Japanese Pattern Tenugui',
                'description_id' => 'Kain katun serbaguna dengan motif Jepang untuk dekorasi, pembungkus, atau penggunaan harian.',
                'description_en' => 'A versatile cotton cloth with Japanese motifs for display, wrapping, or daily use.',
                'price' => 145000,
                'stock' => 22,
            ],
            [
                'name_id' => 'Boneka Daruma',
                'name_en' => 'Daruma Doll',
                'description_id' => 'Dekorasi daruma berukuran kecil yang dapat menjadi pengingat tujuan atau harapan pribadi.',
                'description_en' => 'A small daruma decoration traditionally used as a reminder of a personal goal or wish.',
                'price' => 175000,
                'stock' => 18,
            ],
            [
                'name_id' => 'Maneki-neko',
                'name_en' => 'Maneki-neko',
                'description_id' => 'Figur kucing dekoratif berukuran ringkas untuk meja atau rak.',
                'description_en' => 'A compact decorative cat figure for a desk or shelf.',
                'price' => 160000,
                'stock' => 20,
            ],
            [
                'name_id' => 'Teh Hijau Jepang',
                'name_en' => 'Japanese Green Tea',
                'description_id' => 'Daun teh hijau dengan rasa bersih untuk diseduh hangat di rumah.',
                'description_en' => 'Green tea leaves with a clean flavor for brewing at home.',
                'price' => 125000,
                'stock' => 34,
            ],
        ];
    }

    public static function regions(): array
    {
        return [
            ['id' => 'Tokyo', 'en' => 'Tokyo'],
            ['id' => 'Kyoto', 'en' => 'Kyoto'],
            ['id' => 'Osaka', 'en' => 'Osaka'],
            ['id' => 'Nara', 'en' => 'Nara'],
            ['id' => 'Yamanashi', 'en' => 'Yamanashi'],
            ['id' => 'Hiroshima', 'en' => 'Hiroshima'],
            ['id' => 'Gifu', 'en' => 'Gifu'],
        ];
    }

    public static function souvenirTypes(): array
    {
        return array_map(
            static fn (array $souvenir): array => [
                'name_id' => $souvenir['name_id'],
                'name_en' => $souvenir['name_en'],
            ],
            self::souvenirs(),
        );
    }

    public static function reviewTemplates(): array
    {
        return [
            [
                'place_slug' => 'senso-ji-temple',
                'user_index' => 0,
                'rating' => 5,
                'comment' => 'Datang pagi membuat area kuil lebih nyaman untuk dijelajahi.',
                'days_ago' => 18,
            ],
            [
                'place_slug' => 'senso-ji-temple',
                'user_index' => 1,
                'rating' => 4,
                'comment' => 'Akses dari stasiun mudah dan banyak pilihan camilan di sekitar Nakamise.',
                'days_ago' => 44,
            ],
            [
                'place_slug' => 'shibuya-crossing',
                'user_index' => 2,
                'rating' => 4,
                'comment' => 'Ramai pada sore hari, tetapi suasana kotanya menarik untuk diamati.',
                'days_ago' => 12,
            ],
            [
                'place_slug' => 'fushimi-inari-taisha',
                'user_index' => 3,
                'rating' => 5,
                'comment' => 'Jalurnya cukup panjang; sepatu yang nyaman sangat membantu.',
                'days_ago' => 29,
            ],
            [
                'place_slug' => 'fushimi-inari-taisha',
                'user_index' => 4,
                'rating' => 4,
                'comment' => 'Bagian atas lebih tenang dibanding area gerbang utama.',
                'days_ago' => 67,
            ],
            [
                'place_slug' => 'arashiyama-bamboo-grove',
                'user_index' => 5,
                'rating' => 4,
                'comment' => 'Jalurnya singkat, jadi sebaiknya sekalian menjelajahi kawasan Arashiyama.',
                'days_ago' => 23,
            ],
            [
                'place_slug' => 'dotonbori',
                'user_index' => 0,
                'rating' => 4,
                'comment' => 'Pilihan makanan banyak dan area kanal terasa hidup pada malam hari.',
                'days_ago' => 36,
            ],
            [
                'place_slug' => 'dotonbori',
                'user_index' => 3,
                'rating' => 3,
                'comment' => 'Sangat padat saat akhir pekan, tetapi tetap mudah dijangkau.',
                'days_ago' => 81,
            ],
            [
                'place_slug' => 'nara-park',
                'user_index' => 1,
                'rating' => 5,
                'comment' => 'Area tamannya luas dan nyaman untuk berjalan santai.',
                'days_ago' => 9,
            ],
            [
                'place_slug' => 'lake-kawaguchi',
                'user_index' => 2,
                'rating' => 5,
                'comment' => 'Pemandangan Fuji terlihat jelas saat cuaca cerah di pagi hari.',
                'days_ago' => 52,
            ],
            [
                'place_slug' => 'lake-kawaguchi',
                'user_index' => 5,
                'rating' => 4,
                'comment' => 'Jalur tepi danau cocok untuk berjalan pelan dan menikmati suasana.',
                'days_ago' => 74,
            ],
            [
                'place_slug' => 'hiroshima-peace-memorial-park',
                'user_index' => 4,
                'rating' => 5,
                'comment' => 'Tempat yang tenang untuk memahami sejarah dan merenung.',
                'days_ago' => 31,
            ],
            [
                'place_slug' => 'itsukushima-shrine',
                'user_index' => 0,
                'rating' => 4,
                'comment' => 'Perubahan pasang surut memberi suasana yang berbeda sepanjang hari.',
                'days_ago' => 58,
            ],
            [
                'place_slug' => 'itsukushima-shrine',
                'user_index' => 2,
                'rating' => 5,
                'comment' => 'Perjalanan feri singkat dan kawasan Miyajima mudah dijelajahi dengan berjalan kaki.',
                'days_ago' => 96,
            ],
            [
                'place_slug' => 'shirakawa-go',
                'user_index' => 3,
                'rating' => 4,
                'comment' => 'Pemandangan dari titik observasi memberi gambaran utuh kawasan desa.',
                'days_ago' => 41,
            ],
        ];
    }
}
