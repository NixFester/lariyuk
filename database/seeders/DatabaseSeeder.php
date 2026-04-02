<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventHighlight;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PaymentMethodSeeder::class);

        Admin::create([
            'name'     => 'Admin LariYuk',
            'email'    => 'admin@lariyuk.id',
            'password' => bcrypt('admin'),
        ]);

        $eventsData = [
            [
                'title'       => 'Jakarta Marathon 2025',
                'location'    => 'Jakarta',
                'venue'       => 'Monas, Jakarta Pusat',
                'date'        => '2025-11-17',
                'time'        => '05:00 WIB',
                'description' => 'Jakarta Marathon kembali hadir dengan rute terbaik melewati landmark ikonik Jakarta. Event maraton terbesar di Indonesia.',
                'slots'       => 5000,
                'registered'  => 1250,
                'has_medal'   => true,
                'is_weekend'  => true,
                'is_virtual'  => false,
                'is_beginner' => false,
                'early_bird_until' => Carbon::now()->addDays(30),
                'image'       => null,
                'categories'  => [
                    ['name' => '5K',            'normal_price' => 150000],
                    ['name' => '10K',           'normal_price' => 200000],
                    ['name' => 'Half Marathon', 'normal_price' => 500000],
                    ['name' => 'Full Marathon', 'normal_price' => 750000],
                ],
                'highlights'  => ['Medali finisher eksklusif','Kaos event official','E-certificate','Pos hydration lengkap'],
            ],
            [
                'title'       => 'Bandung Running Festival',
                'location'    => 'Bandung',
                'venue'       => 'Gedung Sate, Bandung',
                'date'        => '2025-12-01',
                'time'        => '06:00 WIB',
                'description' => 'Nikmati suasana sejuk Bandung sambil berlari di rute yang menikmati pemandangan kota.',
                'slots'       => 3000,
                'registered'  => 800,
                'has_medal'   => true,
                'is_weekend'  => true,
                'is_virtual'  => false,
                'is_beginner' => true,
                'early_bird_until' => Carbon::now()->addDays(15),
                'image'       => null,
                'categories'  => [
                    ['name' => '5K',  'normal_price' => 150000],
                    ['name' => '10K', 'normal_price' => 200000],
                ],
                'highlights'  => ['Medali finisher','Snack lokal','Live music','Photo booth'],
            ],
            [
                'title'       => 'Semarang Night Run',
                'location'    => 'Semarang',
                'venue'       => 'Lawang Sewu, Semarang',
                'date'        => '2026-01-25',
                'time'        => '19:00 WIB',
                'description' => 'Pengalaman lari malam yang unik dengan pemandangan kota Semarang yang menawan.',
                'slots'       => 2500,
                'registered'  => 400,
                'has_medal'   => true,
                'is_weekend'  => true,
                'is_virtual'  => false,
                'is_beginner' => true,
                'early_bird_until' => null,
                'image'       => null,
                'categories'  => [
                    ['name' => '5K',  'normal_price' => 150000],
                    ['name' => '10K', 'normal_price' => 200000],
                ],
                'highlights'  => ['LED medali','Neon kit','Light snacks','DJ performance'],
            ],
        ];

        foreach ($eventsData as $data) {
            $categories = $data['categories'];
            $highlights = $data['highlights'];
            unset($data['categories'], $data['highlights']);
            $data['slug']      = \Str::slug($data['title']);
            $data['is_active'] = true;
            $event = Event::create($data);

            foreach ($categories as $cat) {
                EventCategory::create([
                    'event_id'         => $event->id,
                    'name'             => $cat['name'],
                    'normal_price'     => $cat['normal_price'],
                    'early_bird_price' => $event->early_bird_until
                        ? (int) round($cat['normal_price'] * 0.90) : null,
                ]);
            }
            foreach ($highlights as $h) {
                EventHighlight::create(['event_id' => $event->id, 'highlight' => $h]);
            }
        }
    }
}
