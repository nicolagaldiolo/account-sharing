<?php

use App\Category;
use App\Chat;
use App\RenewalFrequency;
use App\Sharing;
use App\Enums\RenewalFrequencies;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Enums\SharingStatus;
class SeedFakeData extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // Creo gli utenti
        $users = factory(\App\User::class, 1)->create([
            'name' => env('DEMOUSER', 'Demo'),
            'email' => env('DEMOEMAIL', 'demouser@example.com'),
            'password' => bcrypt(env('DEMOPASS', 'password'))
        ])->merge(factory(\App\User::class, 9)->create());


        // Creo le frequenze di rinnovo
        $renewalFrequencies = collect([]);
        collect([
            [
                'value' => 1,
                'type' => RenewalFrequencies::Months
            ],
            [
                'value' => 1,
                'type' => RenewalFrequencies::Years
            ]
        ])->each(function($item) use ($renewalFrequencies){
            $renewalFrequencies->push(factory(RenewalFrequency::class)->create($item));
        });

        // Creo le categorie
        $categories = collect([]);
        collect([
            [
                'name' => 'Netflix Premium',
                'customizable' => false,
            ],
            [
                'name' => 'Amazon Music',
                'customizable' => false,
            ],
            [
                'name' => 'Apple Music',
                'customizable' => false,
            ],
            [
                'name' => 'Spotify Family',
                'customizable' => false,
            ],
            [
                'name' => 'Custom',
                'customizable' => true,
            ],
        ])->each(function($item) use($categories){
            $categories->push(factory(Category::class)->create($item));
        });


        // Creo le condivisioni
        $users->each(function($me) use($categories, $renewalFrequencies, $users){

            $categories->each(function($category) use($me, $renewalFrequencies, $users){

                $sharing = factory(Sharing::class)->create([
                    'name' => $category->name,
                    'price' => $category->price,
                    'renewal_frequency_id' => $renewalFrequencies->random(1)->pluck('id')->first(),
                    'category_id' => $category->id,
                ]);

                // Per ogni condivisione assegno degli utenti random e mi assicuro di togliere l'utente corrente
                $sharingUsers = $users->reject(function($value) use($me){
                    return $value->id === $me->id;
                })->random(4)->pluck('id')->mapWithKeys(function($item){
                    $sharingStatus = SharingStatus::getValues();
                    $status = rand(min($sharingStatus), max($sharingStatus));
                    return [$item => [
                            'status' => $status,
                            'credential_updated_at' => Carbon::now(),
                        ]
                    ];
                });

                // L'tente corrento lo aggiungo come joiner e owner
                $sharingUsers->put($me->id, [
                    'owner' => true,
                    'status' => SharingStatus::Joined,
                    'credential_updated_at' => Carbon::now(),
                ]);

                $sharing->users()->sync($sharingUsers);

                // Per ogni utente joiner creo 5 messaggi in chat
                $sharingUsers->filter(function ($value) {
                    return $value['status'] === SharingStatus::Joined;
                })->each(function($value, $key) use($sharing){
                    factory(Chat::class, 5)->create([
                        'sharing_id' => $sharing->id,
                        'user_id' => $key
                    ]);
                });

            });
        });

        // Per ogni condivisione in stato di join creo 2 pagamenti
        \App\SharingUser::whereStatus(SharingStatus::Joined)->get()->each(function($item){

            factory(\App\Renewal::class, 1)->create([
                'sharing_user_id' => $item->id,
                'status' => \App\Enums\RenewalStatus::Confirmed,
                'starts_at' => \Carbon\Carbon::now()->startOfMonth(),
                'expires_at' => \Carbon\Carbon::now()->endOfMonth()

            ]);
            factory(\App\Renewal::class, 1)->create([
                'sharing_user_id' => $item->id,
                'status' => \App\Enums\RenewalStatus::Pending,
                'starts_at' => \Carbon\Carbon::now()->addMonthNoOverflow()->startOfMonth(),
                'expires_at' => \Carbon\Carbon::now()->addMonthNoOverflow()->endOfMonth()
            ]);

        });

    }
}
