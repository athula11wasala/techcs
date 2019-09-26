<?php
/**
 * This class is used for building objects for short position subscriptions
 * 
 * @category ShortPosition
 * @package  App\Equio\ShortPosition
 * @author   Ishara <ishara@ceylonsolutions.com>
 */
namespace App\Equio\ShortPosition;

use App\Equio\ShortPosition\AdditionalFactory;
use App\Equio\ShortPosition\BasicFactory;
use Illuminate\Support\Facades\Config;

/**
 * Class AbstractShortPositionFactory
 * 
 * @category ShortPosition
 * @package  App\Equio\ShortPosition
 * @author   Ishara <ishara@ceylonsolutions.com>
 */
abstract class AbstractShortPositionFactory
{
    /**
     * Get Factory 
     * 
     * @return Object $factory
     */
    public static function getFactory($planId = '') 
    {

        switch ($planId) {

        case Config::get('custom_config.ADDITIONAL_SINGLE_COMPANY'):

            return new AdditionalFactory();

        default:

            return new BasicFactory();

        }

        throw new Exception('Bad Sshort position config');

    }

    /**
     * Get Plan 
     * 
     * @return ''
     */
    abstract public function getPlan();

}