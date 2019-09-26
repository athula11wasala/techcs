<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PosLocations extends Model
{

    protected $connection = "mysql_external_intake";


    protected $table = 'pos_locations';


}
