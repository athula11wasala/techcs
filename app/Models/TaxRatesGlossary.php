<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRatesGlossary extends Model
{
    protected $connection = "mysql_external_intake";


    protected $table = 'tax_rates_glossary';
}
