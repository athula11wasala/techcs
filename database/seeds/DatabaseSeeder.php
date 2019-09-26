<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {


        $this->call(PermisssionsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(ChartsSeeder::class);
        $this->call(interestSeeder::class);
        $this->call(DataSetSeeder::class);
        $this->call(updateCannabisBenchmarksUsSeeder::class);
        $this->call(PermisssionsSeeder::class);
        $this->call(RolesSeeder::class);
        $this->call(PermissionRoleTableSeeder::class);
        $this->call(RolesUserTableSeeder::class);
        $this->call(BundleTableSeeder::class);
        $this->call(SubscriptioinsTableSeeder::class);
        $this->call(BundleTableSeeder::class);
        $this->call(UpdateDataSetsTableSeeder::class);
        $this->call(UpdatQaulifiyConditonableSeeder::class);
        $this->call(BundleTableSeeder::class);
        $this->call(AssignPermisssionToMangerTableSeeder::class);
        $this->call(AssignRequetAnaylistPermissionTableSeeder::class);

    }
}








