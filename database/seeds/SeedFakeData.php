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

        $users = factory(\App\User::class, 1)->create([
            'name' => env('DEMOUSER', 'Demo'),
            'email' => env('DEMOEMAIL', 'demouser@example.com'),
            'password' => bcrypt(env('DEMOPASS', 'password'))
        ])->merge(factory(\App\User::class, 9)->create());

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

        collect([
            ['name' => 'Netflix Premium'],
            ['name' => 'Amazon Music'],
            ['name' => 'Apple Music'],
            ['name' => 'Spotify Family'],
            ['name' => 'Custom'],
        ])->each(function($item) use($users, $renewalFrequencies){
            $factory = factory(Category::class)->create($item);

            collect(array_fill(0, 5, ''))->each(function() use($factory, $users, $renewalFrequencies){

                $renewalFrequency = $renewalFrequencies->random(1)->pluck('id')->first();
                $owner = $users->random(1)->pluck('id')->first();
                factory(Sharing::class)->create([
                    'name' => $factory->name,
                    'price' => $factory->price,
                    'renewal_frequency_id' => $renewalFrequency,
                    'category_id' => $factory->id,
                    'owner_id' => $owner
                ])->each(function($sharing) use($users){
                    $userIds = $users->random(2)->pluck('id')->mapWithKeys(function($item){
                        return [$item => ['status' => rand(SharingStatus::Pending, SharingStatus::Joined)]];
                    });
                    $sharing->users()->sync($userIds);
                });
            });

        });
    }
}
