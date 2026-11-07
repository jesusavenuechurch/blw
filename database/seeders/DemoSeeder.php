<?php

namespace Database\Seeders;

use App\Models\CheckIn;
use App\Models\Partner;
use App\Support\Options;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Fake partners and check-ins for previewing the workspace locally.
 * Run with: php artisan db:seed --class=DemoSeeder  (never on production)
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $first = ['Grace', 'Emmanuel', 'Chiamaka', 'Tunde', 'Ngozi', 'David', 'Esther', 'Samuel', 'Blessing', 'Daniel', 'Ruth', 'Joseph', 'Favour', 'Michael', 'Deborah', 'Victor', 'Joy', 'Peter', 'Mercy', 'Femi', 'Kemi', 'Chinedu', 'Abigail', 'Tobi', 'Sarah', 'Paul', 'Funmi', 'Kingsley', 'Lydia', 'Moses'];
        $last = ['Okafor', 'Adeyemi', 'Mensah', 'Okonkwo', 'Bello', 'Eze', 'Adebayo', 'Nwosu', 'Johnson', 'Owusu', 'Ibrahim', 'Achebe', 'Balogun', 'Asante', 'Obi', 'Williams', 'Dlamini', 'Nkosi', 'Okoro', 'Ade'];
        $churches = ['CE Ikeja', 'CE Lekki', 'CE Abuja Central', 'CE Accra', 'CE London Zone 1', 'CE Johannesburg', 'CE Port Harcourt', 'CE Toronto', 'CE Houston', 'CE Nairobi'];
        $zones = ['Lagos Zone 1', 'Lagos Zone 5', 'Abuja Zone', 'Ghana Zone A', 'UK Zone 1', 'SA Zone 2', 'PH Zone 3', 'Canada Zone', 'USA Region 2', 'Kenya Zone'];
        $tierWeights = ['ruby' => 30, 'silver' => 22, 'gold' => 18, 'diamond' => 10, 'as_one_man' => 12, 'top_individual' => 8];

        for ($i = 0; $i < 96; $i++) {
            $name = $first[array_rand($first)].' '.$last[array_rand($last)];
            $tier = $this->weighted($tierWeights);
            $roll = mt_rand(1, 100);
            $registered = $roll > 35;
            $sent = $registered || $roll > 15;
            $failed = ! $sent && $roll > 8;
            $spouse = $registered && mt_rand(1, 100) > 60;
            $ippc = $registered && mt_rand(1, 100) > 25;
            $lounge = $ippc && mt_rand(1, 100) > 35;
            $c = array_rand($churches);
            $created = Carbon::now()->subDays(mt_rand(3, 20))->subMinutes(mt_rand(0, 1440));

            $partner = Partner::create([
                'title' => Options::TITLES[array_rand(Options::TITLES)],
                'designation' => Options::DESIGNATIONS[array_rand(Options::DESIGNATIONS)],
                'full_name' => $name,
                'email' => Str::slug($name, '.').'.'.$i.'@example.com',
                'phone' => '+23480'.mt_rand(10000000, 99999999),
                'kc_handle' => $registered ? Str::slug($name, '').mt_rand(1, 99) : null,
                'region' => 'Region '.mt_rand(1, 6),
                'zone' => $zones[$c],
                'group' => 'Group '.chr(65 + mt_rand(0, 5)),
                'church' => $churches[$c],
                'tier' => $tier,
                'ror_copies_sponsored' => mt_rand(1, 40) * 250,
                'will_attend_ippc' => $ippc,
                'will_be_at_exhibition' => $lounge,
                'delivery_method' => $lounge ? null : 'Deliver to Bro. '.$first[array_rand($first)].' at the zonal office, +234 801 000 0000',
                'coming_with_spouse' => $spouse,
                'spouse_title' => $spouse ? 'Sister' : null,
                'spouse_name' => $spouse ? $first[array_rand($first)].' '.explode(' ', $name)[1] : null,
                'is_registered' => $registered,
                'token_used_at' => $registered ? $created->copy()->addHours(mt_rand(2, 72)) : null,
                'email_sent' => $sent,
                'email_failed' => $failed,
                'email_pending' => ! $sent && ! $failed,
                'email_response' => $failed ? 'Expected response code 250 but got code "550", with message "550 5.1.1 Mailbox does not exist"' : ($sent ? 'Sent successfully on attempt 1' : null),
            ]);
            $partner->timestamps = false;
            $partner->forceFill(['created_at' => $created, 'updated_at' => $partner->token_used_at ?? $created])->saveQuietly();

            if ($registered && mt_rand(1, 100) > 45) {
                foreach (array_unique([mt_rand(0, 5), mt_rand(0, 5)]) as $daysAgo) {
                    $at = Carbon::today()->subDays($daysAgo)->setTime(mt_rand(9, 20), mt_rand(0, 59));
                    CheckIn::create([
                        'partner_id' => $partner->id,
                        'checked_in_at' => $at->isFuture() ? Carbon::now()->subMinutes(mt_rand(1, 300)) : $at,
                    ]);
                }
            }
        }
    }

    private function weighted(array $weights): string
    {
        $r = mt_rand(1, array_sum($weights));
        foreach ($weights as $k => $w) {
            if (($r -= $w) <= 0) {
                return $k;
            }
        }

        return array_key_first($weights);
    }
}
