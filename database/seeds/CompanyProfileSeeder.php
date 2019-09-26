<?php

use Illuminate\Database\Seeder;

class CompanyProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('company_profiles')->insert([
            'name' => 'testcompany',
            'country' => 'srilanka',
            'profile_cover' => 'storage/company-profile/profile-cover/1/1111.jpg',
            'profile_document' => 'storage/company-profile/profile-document/1/11111.pdf',
            'created_by' => 1018,
            'created_at' => '2018-02-26 18:30:00',
            'updated_at' => '2018-02-26 18:31:00'
        ],

            [
                'name' => 'abcComapny',
                'country' => 'india',
                'profile_cover' => 'storage/company-profile/profile-cover/2/1111.jpg',
                'profile_document' => 'storage/company-profile/profile-document/2/11111.pdf',
                'created_by' => 1018,
                'created_at' => '2018-02-26 18:30:00',
                'updated_at' => '2018-02-26 18:31:00'
            ]
            );
    }
}
