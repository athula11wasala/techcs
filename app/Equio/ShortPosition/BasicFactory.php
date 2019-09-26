<?php
namespace App\Equio\ShortPosition;

use App\Equio\ShortPosition\AbstractShortPositionFactory;
use App\Equio\ShortPosition\BasicPlan;

class BasicFactory extends AbstractShortPositionFactory 
{

    public function getPlan() 
    {
        return new BasicPlan();
    }

}