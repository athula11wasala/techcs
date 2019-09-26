<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientSaturation extends Model {
    protected $connection = "mysql_external_intake";
    protected $table = 'patient_saturation_projection_us';
}
