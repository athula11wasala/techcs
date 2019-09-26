<?php
namespace App\Equio\ShortPosition;

use App\Equio\ShortPosition\AbstractShortPositionFactory;
use App\Equio\ShortPosition\SinglePlan;

class AdditionalFactory extends AbstractShortPositionFactory 
{

    public function getPlan() 
    {
        return new SinglePlan();
    }

}