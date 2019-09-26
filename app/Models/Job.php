<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Job extends Model
{

    protected $connection = "mysql_external_intake";


    protected $table = 'jobs';


}
