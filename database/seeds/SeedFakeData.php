<?php

use App\Category;
use App\RenewalFrequency;
use App\Sharing;
use App\Enums\RenewalFrequencies;
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
                $sharings = factory(Sharing::class)->create([
                    'name' => $category->name,
                    'price' => $category->price,
                    'renewal_frequency_id' => $renewalFrequencies->random(1)->pluck('id')->first(),
                    'category_id' => $category->id,
                    'owner_id' => $me->id
                ])->each(function($sharing) use($me, $users){

                    // Per ogni condivisione assegno degli utenti random come joiner e mi assicuro di togliere l'utente corrente
                    $usersWithoutMe = $users->reject(function($value) use($me){
                        return $value->id === $me->id;
                    })->random(5)->pluck('id')->mapWithKeys(function($item){
                        $sharingStatus = SharingStatus::getValues();
                        $status = rand(min($sharingStatus), max($sharingStatus));
                        return [$item => ['status' => $status]];
                    });

                    $sharing->users()->sync($usersWithoutMe);
                });

            });
        });

        // Per ogni condivisione in stato di join creo 2 pagamenti
        \App\SharingUser::whereStatus(SharingStatus::Joined)->get()->each(function($item){

            factory(\App\Renewal::class, 1)->create([
                'sharing_user_id' => $item->id,
                'status' => \App\Enums\RenewalStatus::Confirmed,
                'expire_on' => \Carbon\Carbon::now()->endOfMonth()
            ]);
            factory(\App\Renewal::class, 1)->create([
                'sharing_user_id' => $item->id,
                'status' => \App\Enums\RenewalStatus::Pending,
                'expire_on' => \Carbon\Carbon::now()->addMonth()->endOfMonth()
            ]);
        });

    }
}
