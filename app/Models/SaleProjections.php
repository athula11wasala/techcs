<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleProjections extends Model
{
    protected $connection = "mysql_external_intake";
  
    protected $table = "sale_projections";

    protected $primaryKey = 'id';

    public $timestamps = true;
}
