<?php

use Illuminate\Database\Seeder;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("roles")->delete();

        $owner = new Role();
        $owner->name = "ADMINISTRATOR";
        $owner->display_name = "Product Admin";
        $owner->description = "The Admin is the  given project";
        $owner->save ();

        $owner = new Role();
        $owner->name = "EQUIO";
        $owner->display_name = "Equio";
        $owner->description = "The Equio is the given project";
        $owner->save ();

        $owner = new Role();
        $owner->name = "MANAGER";
        $owner->display_name = "Manager";
        $owner->description = "The Manager is the given project";
        $owner->save ();

        $owner = new Role();
        $owner->name = "EDITOR";
        $owner->display_name = "Editor";
        $owner->description = "The Editor is the given project";
        $owner->save ();

        $owner = new Role();
        $owner->name = "REPORTER";
        $owner->display_name = "Reporter";
        $owner->description = "The Reporter is the given project";
        $owner->save ();



        $this->updateRoleId();

    }

    public function updateRoleId()
    {
        $adminstator =  Role::where("name","ADMINISTRATOR")->first();
        $adminstator->id =  1;
        $adminstator->save();


        $equio  =  Role::where("name","EQUIO")->first();
        $equio->id =  2;
        $equio->save();

        $manger  =  Role::where("name","MANAGER")->first();
        $manger->id =  3;
        $manger->save();

        $editor  =  Role::where("name","EDITOR")->first();
        $editor->id =  4;
        $editor->save();

        $reporter  =  Role::where("name","REPORTER")->first();
        $reporter->id =  5;
        $reporter->save();

    }

}
