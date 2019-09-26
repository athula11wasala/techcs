<?php


namespace App\Equio;

use App\Models\Country;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Config;
use App\Models\User;
use App\Repositories\SubscriptionRepository;
use App\Models\DataSet;
use Illuminate\Support\Facades\DB;
use App\Services\WooCommerceService;
use Illuminate\Support\Facades\Auth;
use Mockery\Exception;
use Automattic\WooCommerce\Client;
use Automattic\WooCommerce\HttpClient\HttpClientException;
use App\Models\RoleUser;

/**
 * Helper Class for Equio
 * @package App\Equio
 */
class Helper
{

    private $routeName;
    private $subscriptions;


    /**
     * Filter array values by given array keys
     * @param $data
     * @param $keys
     * @return array
     */
    public static function filterArrayValuesByKeys($data, $keys)
    {
        $newData = array();
        foreach ( $data as $key => $value ) {
            if ( in_array ( $key, $keys ) ) {
                $newData[ $key ] = $value;
            }
        }
        return $newData;
    }

    /**
     * Generate random string with given length
     * @param int $length
     * @return string
     */
    public static function randomString($length = 8)
    {
        return str_random ( $length );
    }

    public static function arrayUnset($request, $keys)
    {
        foreach ( $keys as $value ) {
            if ( array_key_exists ( $value, $request ) ) {
                unset( $request[ $value ] );
            }
        }
        return $request;
    }

    public static function stringReplace($value, $searchArray = ["$", ",", "-"])
    {
        $string = str_replace ( $searchArray, '', $value );
        return $string;
    }

    public static function arrayPushElement(&$data, $value = null)
    {
        if ( !empty( $value ) ) {
            array_push ( $data, $value );
        }
    }

    public static function stringChange($value)
    {
        $string = str_replace ( '_', ' ', ucfirst ( $value ) );;
        return $string;
    }

    public static function getYouTubeThumbnil($url)
    {
        $value = explode ( "/", $url );
        if ( stripos ( $url, "youtube.com" ) !== false ) {
            $link = str_replace ( "watch?v=", "", $value[ 3 ] );
            $videoId = $link;
            if ( stripos ( $link, "&" ) !== false ) {
                $videoId = strstr ( $link, '&', true );
            }
            $thumnailImg = "https://img.youtube.com/vi/$videoId/hqdefault.jpg";
            return $thumnailImg;
        }
        if ( stripos ( $url, "youtu.be" ) !== false ) {
            $videoId = $value[ 3 ];
            $thumnailImg = "https://img.youtube.com/vi/$videoId/hqdefault.jpg";
            return $thumnailImg;
        }
        if ( stripos ( $url, "ted.com" ) !== false ) {
            return self::getTedThumbnil ( $url );
        }
        if ( stripos ( $url, "vimeo.com" ) !== false ) {
            return self::getViemoThumbnil ( $url );
        }

    }


    public static function getViemoThumbnil($url = null)
    {
        $image = "";
        $value = explode ( "/", $url );
        $videoId = !empty( end ( $value ) ) ? end ( $value ) : '';
        $apiData = unserialize ( file_get_contents ( "http://vimeo.com/api/v2/video/$videoId.php" ) );
        if ( !empty( $apiData[ 0 ] ) ) {
            $videoInfo = $apiData[ 0 ];
            if ( isset( $videoInfo[ 'thumbnail_medium' ] ) ) {
                $image = $videoInfo[ 'thumbnail_medium' ];
            }
        }
        return $image;
    }

    public static function getTedThumbnil($url = null)
    {
        $image = "";
        $source = $url;
        $tedJson = json_decode ( file_get_contents ( 'http://www.ted.com/talks/oembed.json?url=' . urlencode ( $source ) ), TRUE );
        if ( !empty( $tedJson ) ) {
            if ( isset( $tedJson[ 'thumbnail_url' ] ) ) {
                $image = $tedJson[ 'thumbnail_url' ];
            }
        }
        return $image;
    }

    public static function UrlExtesnionTCallThmbnil($url)
    {
        $ext = pathinfo ( $url, PATHINFO_EXTENSION );
        if ( empty( $ext ) && !empty( $url ) ) {
            if ( strpos ( $url, 'youtube' ) > 0 ) {
                $extension = 'youtube';
                return Helper::getYouTubeThumbnil ( $url );
            } elseif ( strpos ( $url, 'vimeo' ) > 0 ) {
                return Helper::getViemoThumbnil ( $url );
            } elseif ( strpos ( $url, 'pdf' ) > 0 ) {
                return null;
            } else {
                if ( stripos ( $url, "ted.com" ) !== false ) {
                    return Helper::getTedThumbnil ( $url );
                } else if ( stripos ( $url, "youtu.be" ) !== false ) {
                    return Helper::getYouTubeThumbnil ( $url );
                } else {
                    return null;
                }
            }
        }
        return null;
    }


    public static function paginate($resultData, $perPage = 15, $sortColumn = 'id', $sortType = '')
    {
        $currentPage = LengthAwarePaginator::resolveCurrentPage ();
        $temp_collection = new Collection( $resultData );
        if ( empty( $sortType ) ) {

            $sorted = $temp_collection->sortBy ( $sortColumn, true );
        } else {

            $sorted = $temp_collection->sortDesc ( $sortColumn, true );
        }

        $resultData = $sorted->values ()->all ();
        $collection = new Collection( $resultData );

        $currentPageSearchResults = $collection->slice ( ($currentPage - 1) * $perPage, $perPage )->all ();
        $paginatedSearchResults = new LengthAwarePaginator(
            $currentPageSearchResults,
            count ( $collection ),
            $perPage
        );
        $page = "";

        return new LengthAwarePaginator(
            $currentPageSearchResults,
            count ( $collection ),//total count
            $perPage,//items per page
            $page,//current page
            ['path' => Paginator::resolveCurrentPath ()]
        );
    }

    public static function getYouTubeId($url)
    {
        $value = explode ( "/", $url );
        $videoId = !empty( end ( $value ) ) ? end ( $value ) : '';
        return $videoId;
    }


    /* public static function getViemoeId($url)
     {
         $vimeo = $url;
         return (int)substr ( parse_url ( $vimeo, PHP_URL_PATH ), 1 );

     }
   */

    public static function UrlExtesnionType($url)
    {
        $ext = pathinfo ( $url, PATHINFO_EXTENSION );
        if ( empty( $ext ) ) {
            if ( strpos ( $url, 'youtube' ) > 0 ) {
                return 'youtube';
            } elseif ( strpos ( $url, 'vimeo' ) > 0 ) {
                return 'vimeo';
            } elseif ( strpos ( $url, 'pdf' ) > 0 ) {
                return 'pdf';
            } else {
                return 'unknown';
            }
        } else {
            return $ext;
        }
    }


    public static function userPositionInfo()
    {
        $data = Config::get ( 'custom_config.USER_POSITION' );
        $result = [];
        foreach ( $data as $key => $value ) {
            $result[] = ['id' => $key, 'name' => $value];
        }
        return $result;
    }

    public static function industryInfo()
    {
        $data = Config::get ( 'custom_config.INDUSTRY_ROLE' );
        $result = [];
        foreach ( $data as $key => $value ) {
            $result[] = ['id' => $key, 'name' => $value];
        }
        return $result;
    }

    public static function CompanyHeaderInfo()
    {
        $data = Config::get ( 'custom_config.COMPANY_NEWS_INFORMATION' );
        $result = [];
        foreach ( $data as $key => $value ) {
            $result[] = ['id' => $key, 'name' => $value];
        }
        return $result;
    }


    public function callbackWooDigital(&$page_row)
    {
        $objwoocommerce = new WooCommerceService();
        $dbWoodIdArr = DB::table ( 'reports' )->pluck ( "woo_id" );
        if ( empty( $dbWoodIdArr ) ) {
            $dbWoodIdArr = [];
        }
        $objectWooetails = $objwoocommerce->getSpecificWooData ( 'products',
            ['status' => 'publish', 'type' => 'simple', 'per_page' => '100', 'page' => $page_row],
            ['id' => '', 'name' => '', 'status' => '', 'type' => '']
        )->whereNotIn ( 'id', $dbWoodIdArr );

        return $objectWooetails;

    }

    public function callbackWooHardCopy(&$page_row)
    {
        $objwoocommerce = new WooCommerceService();
        $dbWoodIdArr = DB::table ( 'report_Hard_copy_detail' )->pluck ( "woo_id" );
        if ( empty( $dbWoodIdArr ) ) {
            $dbWoodIdArr = [];
        }
        $objectWooetails = $objwoocommerce->getSpecificWooData ( 'products',
            ['status' => 'publish', 'type' => 'simple', 'per_page' => '100', 'page' => $page_row],
            ['id' => '', 'name' => '', 'status' => '', 'type' => '']
        )->whereNotIn ( 'id', $dbWoodIdArr );

        return $objectWooetails;

    }


    public function wooComerceWodIdInfo($call_func)
    {
        $page_row = [1, 2];
        $objectWoodata[] = array_map ( array($this, $call_func), $page_row );
        $result = [];
        foreach ( $page_row as $count ) {

            if ( isset( $objectWoodata[ $count - 1 ] ) ) {

                foreach ( $objectWoodata[ $count - 1 ] as $row ) {

                    foreach ( $row as $value ) {

                        $result[] = ['id' => $value[ 'id' ], 'name' => $value[ 'name' ]];
                    }


                }

            }

        }


        return $result;
    }



    public function getWooCommerceRetiveId($selectId = 0)
    {
        $objwoocommerce = new WooCommerceService();
        $objectSelectWoodata = $objwoocommerce->retrieveWooData ( 'products/' . $selectId, [] );
        if ( !empty( $objectSelectWoodata ) ) {
            if ( !empty( $objectSelectWoodata->name ) ) {
                return $objectSelectWoodata->name;
            }
            return null;
        }
        return null;
    }

    public static function getWooCommerceRetivePrice($selectId = 0)
    {
        $objwoocommerce = new WooCommerceService();
        $objectSelectWoodata = $objwoocommerce->retrieveWooData ( 'products/' . $selectId, [] );
        if ( !empty( $objectSelectWoodata ) ) {
            if ( isset( $objectSelectWoodata->regular_price ) ) {
                return (floatval ( $objectSelectWoodata->regular_price ));
            }
            return '';
        }
        return null;
    }



    public static function reportCategoryInfo()
    {
        $data = Config::get ( 'custom_config.REPORT_CATEGORY' );
        $result = [];
        foreach ( $data as $key => $value ) {
            $result[] = ['id' => $key, 'name' => $value];
        }
        return $result;

    }

    public static function reportSegmentInfo()
    {
        $data = Config::get ( 'custom_config.REPORT_SEGMENT' );
        $result = [];
        foreach ( $data as $key => $value ) {
            $result[] = ['id' => $key, 'name' => $value,];
        }
        return $result;
    }

    public static function numberFormatShort($n, $precision = 2)
    {
        if ( $n < 900 ) {
            // 0 - 900
            $n_format = number_format ( $n, $precision );
            $suffix = '';
        } else if ( $n < 900000 ) {
            // 0.9k-850k
            $n_format = number_format ( $n / 1000, $precision );
            $suffix = 'K';
        } else if ( $n < 900000000 ) {
            // 0.9m-850m
            $n_format = number_format ( $n / 1000000, $precision );
            $suffix = 'M';
        } else if ( $n < 900000000000 ) {
            // 0.9b-850b
            $n_format = number_format ( $n / 1000000000, $precision );
            $suffix = 'B';
        } else {
            // 0.9t+
            $n_format = number_format ( $n / 1000000000000, $precision );
            $suffix = 'T';
        }
        // Remove unecessary zeroes after decimal. "1.0" -> "1"; "1.00" -> "1"
        // Intentionally does not affect partials, eg "1.50" -> "1.50"
        if ( $precision > 0 ) {
            $dotzero = '.' . str_repeat ( '0', $precision );
            $n_format = str_replace ( $dotzero, '', $n_format );
        }
        return $n_format . $suffix;
    }

    public static function dateFormatter($date, $dateFormat)
    {
        return Carbon::createFromFormat ( $dateFormat, $date );
    }

    public static function checkAdminstratorRole($userId)
    {
        $user = User::find ( $userId );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();

        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->name );
        }
        if ( in_array ( 'ADMINISTRATOR', $allRoleIds ) ) {
            return true;
        } else {
            return false;
        }

    }

    /*
      * @param $userof user id
      * return ststus of user's trail period
      */
    public function checkTrailPeriodUserSearch($user, $routeName = null, $subscriptions = null)
    {
        $this->routeName = $routeName;
        $this->subscriptions = $subscriptions;
        $user = User::find ( $user );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();

        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_ADMINISTRATOR" ), $allRoleIds ) ) {
            return array(
                'status' => true,
                'message' => 'Can Access',
            );
        }

        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
            // check subscription
            $isSubscribed = $this->checkSubscriptions ( $user->id );
            if ( !$isSubscribed ) {
                return array(
                    'status' => false,
                    'status_code' => Config::get ( "custom_config.ERROR.PERMISSION_DENIED" ),
                    'message' => 'No permission'
                );
            }

            if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
                \Log::info ( "==== " . __FUNCTION__ . "user does not purchased any reports" );
                // Check user subscription date
                $subscription = $this->userSubscription (
                    $user->paid_subscription_start,
                    $user->paid_subscription_end
                );
                if ( !$subscription ) {
                    return array(
                        'status' => false,
                        'message' => 'Your subscription has expired',
                        'status_code' => Config::get ( "custom_config.ERROR.SUBSCRIPTION_EXPIRED" )
                    );
                    //  return response(["message" => "Your subscription has expired.",'status' => false], 401);
                } else {
                    return array(
                        'status' => true,
                        'message' => 'Can Access',
                        //'status_code'=>
                    );
                }
            } else {
                return array(
                    'status' => true,
                    'message' => 'Can Access'
                );

            }
        }

        return array(
            'status' => true,
            'message' => 'Can Access'
        );

    }

    public function checkTrailPeriodUser($user, $routeName = null, $subscriptions = null)
    {
        $this->routeName = $routeName;
        $this->subscriptions = $subscriptions;
        $user = User::find ( $user );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();

        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }

     if($this->checkAuthnicatedRole($allRoleIds) == true){
            return array(
                'status' => true,
                'message' => 'Can Access',
            );

        }
        /*
      if ( in_array ( Config::get ( "custom_config.ROLE_ADMINISTRATOR" ), $allRoleIds ) ) {

          return array(
              'status' => true,
              'message' => 'Can Access',
          );
      }
      */

      if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
          // check subscription

          $isSubscribed = $this->checkSubscriptions ( $user->id );
          if ( !$isSubscribed ) {
              return array(
                  'status' => false,
                  'status_code' => Config::get ( "custom_config.ERROR.PERMISSION_DENIED" ),
                  'message' => 'No permission'
              );
          }
          if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
              if ( $user->reports_purchased == "n" ) {
                  \Log::info ( "==== " . __FUNCTION__ . "user does not purchased any reports" );
                  // Check user subscription date
                  $subscription = $this->userSubscription (
                      $user->paid_subscription_start,
                      $user->paid_subscription_end
                  );
                  if ( !$subscription ) {
                      return array(
                          'status' => false,
                          'message' => 'Your subscription has expired',
                          'status_code' => Config::get ( "custom_config.ERROR.SUBSCRIPTION_EXPIRED" )
                      );
                      //  return response(["message" => "Your subscription has expired.",'status' => false], 401);
                  } else {
                      return array(
                          'status' => true,
                          'message' => 'Can Access',
                          //'status_code'=>
                      );
                  }
              } else {
                  return array(
                      'status' => true,
                      'message' => 'Can Access'
                  );
              }
          } else {
              return array(
                  'status' => true,
                  'message' => 'Can Access'
              );
          }
      } else {

          return array(
              'status' => true,
              'message' => 'Can Access',
          );
      }
  }

  /*
   * return boolean true if have there is not current route in route table
   * @param $user object
   */
    public function checkSubscriptions($user)
    {
        $routElements = explode ( '/', $this->routeName );
        $routName = $routElements[ 2 ];

        if ( count ( $routElements ) > 3 ) {
            $routName .= "/";
            $routName .= $routElements[ 3 ];
        }
        if ( count ( $routElements ) > 4 ) {
            $routName .= "/";
            $routName .= $routElements[ 4 ];
        }

        $routName = explode ( '?', $routName )[ 0 ];
        if ( empty( $this->subscriptions ) ) {

            $isSubscribe = $this->subscriptionsByuser ( $user, $routName );
        } else {
            $isSubscribe = $this->subscriptions->subscriptionsByuser ( $user, $routName );
        }

        if ( $isSubscribe == 0 ) {
            return true;
        } else {
            return false;
        }
    }

    /*
    * @param $startDate start date of user table
    * @param $endDate end date of user table
    * return boolean true if login pass else false
    */
    public function userSubscription($startDate, $endDate)
    {
        $today = date ( 'Y-m-d' );
        if ( !empty( $startDate ) ) {
            $startDate = date ( 'Y-m-d', strtotime ( $startDate ) );
            if ( !empty( $endDate ) ) {
                $lastDate = date ( 'Y-m-d', strtotime ( $endDate ) );
            } else {
                // one year to current date
                $startDateStr = strtotime ( $startDate );
                $new_date = strtotime ( '+ 1 year', $startDateStr );
                $lastDate = date ( 'Y-m-d', $new_date );
            }
            // check if current date between start date and end date
            if ( ($today >= $startDate) && ($today <= $lastDate) ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /*
    * @param $childTblWithDbConn table with dbconnection
    * @param $dataSetId dataset of user table
    * return datasetId
    */
    public function getDataSetId($childTblWithDbConn, $dataSetId)
    {
        $result = DB::select (
            DB::raw ( "SELECT tab1.id FROM data_sets tab1 left OUTER JOIN $childTblWithDbConn tab2
                         ON tab1.id = tab2.`dataset_id` where tab2.dataset_id !=0 and tab2.dataset_id !='' 
                         and tab2.latest =1 and tab1.data_set =$dataSetId order by tab1.to desc limit 1
                                  " )
        );
        if ( !empty( $result ) ) {

            if ( isset( $result[ 0 ]->id ) ) {
                return $result[ 0 ]->id;
            } else {
                return false;
            }
        } else {
            return false;
        }


    }

    public static function getState($user)
    {
        $result = State::select ( '*' )->where ( 'code', '=', $user->state_id )->orWhere ( 'name', '=', $user->state_id )->first ();
        if ( !empty( $result ) ) {
            $user->state_id = $result->id;
            return $user;
        }
        return $user;
    }

    public static function getCountry($user)
    {
        $result = Country::select ( '*' )->where ( 'sortname', '=', $user->country_id )->orWhere ( 'name', '=', $user->country_id )->first ();
        if ( !empty( $result ) ) {
            $user->country_id = $result->id;
        }
        $state = State::select ( '*' )->where ( 'code', '=', $user->state_id )->orWhere ( 'name', '=', $user->state_id )->first ();
        if ( !empty( $state ) ) {
            $user->state_id = $state->id;
        }
        return $user;
    }


    public function getlocationFileName($destinationPath, $name)
    {
        if ( !empty( glob ( $destinationPath . "$name.*" ) ) ) {
            if ( isset( glob ( $destinationPath . "$name.*" )[ 0 ] ) ) {
                $pathWithFile = glob ( $destinationPath . "$name.*" )[ 0 ];
                $file = explode ( "/", $pathWithFile );
                $result = end ( $file );

                return $result;
            } else {
                return null;
            }
        }
    }

    public static function customErrorMsg($errorMsg)
    {
        $objErrors = (array)$errorMsg;
        $errorArr = $objErrors[ key ( $objErrors ) ];
        $validateArr = [];
        $validateMessge = [];
        if ( !empty( $errorArr ) ) {
            foreach ( $errorArr as $rows ) {
                $validateArr[] = $rows;
            }
            if ( !empty( $validateArr ) ) {
                foreach ( $validateArr as $row ) {
                    $validateMessge[] = $row[ 0 ];
                }
            }
        }
        return $validateMessge;
    }

    public static function getYouTubeURL($url)
    {
        $value = explode ( "/", $url );
        if ( stripos ( $url, "youtu.be" ) !== false ) {
            return 'www.youtube.com/embed/' . $value[ 3 ];
        }
        if ( stripos ( $url, "youtube.com" ) !== false ) {
            $link = str_replace ( "watch?v=", "", $value[ 3 ] );

            if ( stripos ( $link, "&" ) !== false ) {
                $link = strstr ( $link, '&', true );
            }
            return 'www.youtube.com/embed/' . $link;
        }

        if ( stripos ( $url, "ted.com" ) !== false ) {
            return $url;
        }
        if ( stripos ( $url, "vimeo.com" ) !== false ) {
            return $url;
        }
        return null;
    }

    public static function checkEnterprisePdfAccess()
    {

        $user = User::find ( Auth::user ()->id );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {

            $subscriptionLevel = Auth::user ()->subscription_level;

            if ( $subscriptionLevel == "enterprise" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium_plus" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium" ) {
                return false;
            }
            if ( $subscriptionLevel == "essential" ) {

                return false;
            }
        }
        return true;
    }

    public static function checkChartSearchAccess()
    {

        $user = User::find ( Auth::user ()->id );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {

            $subscriptionLevel = Auth::user ()->subscription_level;

            if ( $subscriptionLevel == "enterprise" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium_plus" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium" ) {
                return false;
            }
            if ( $subscriptionLevel == "essential" ) {

                return false;
            }
        }
        return true;
    }

    public static function checkWebniarSearchAccess()
    {

        $user = User::find ( Auth::user ()->id );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {

            $subscriptionLevel = Auth::user ()->subscription_level;

            if ( $subscriptionLevel == "enterprise" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium_plus" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium" ) {
                return true;
            }
            if ( $subscriptionLevel == "essential" ) {
                return true;
            }
        }
        return true;
    }

    public static function checkEssentialUser()
    {
        $user = User::find ( Auth::user ()->id );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
            $subscriptionLevel = Auth::user ()->subscription_level;
            if ( $subscriptionLevel == "essential" ) {
                return true;
            }
        }
        return false;
    }


    public static function checkReportSearchAccess()
    {
        $user = User::find ( Auth::user ()->id );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
            $subscriptionLevel = Auth::user ()->subscription_level;
            if ( $subscriptionLevel == "enterprise" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium_plus" ) {
                return true;
            }
            if ( $subscriptionLevel == "premium" ) {
                return true;
            }
            if ( $subscriptionLevel == "essential" ) {
                return false;
            }
        }
        return true;
    }


    public static function checkEssentailLogAccess()
    {
        $user = User::find ( Auth::user ()->id );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        if ( in_array ( Config::get ( "custom_config.ROLE_EQUIO" ), $allRoleIds ) ) {
            $subscriptionLevel = Auth::user ()->subscription_level;
            if ( $subscriptionLevel == "enterprise" ) {
                return false;
            }
            if ( $subscriptionLevel == "premium_plus" ) {
                return false;
            }
            if ( $subscriptionLevel == "premium" ) {
                return false;
            }
            if ( $subscriptionLevel == "essential" ) {
                return true;
            }
        }
        return false;
    }


    public function getUserPurchaserdReport()
    {

        $user = User::find ( Auth::user ()->id );
        $email = $user->email;

        $this->woocommerce = new Client(
            Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
            ['wp_api' => true, 'version' => 'wc/v1',]
        );

        try {
            $purchasedItem = collect ();
            $results = $this->woocommerce->get (
                'orders',
                [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'completed'
                ]
            );
            foreach ( $results as $result ) {
                foreach ( $result->line_items as $lineItem ) {
                    $purchasedItem->push ( $lineItem->product_id );
                }
            }
            $results = $this->woocommerce->get (
                'orders',
                [
                    'search' => $email,
                    'per_page' => 50,
                    'status' => 'processing'
                ]
            );
            foreach ( $results as $result ) {
                foreach ( $result->line_items as $lineItem ) {
                    $purchasedItem->push ( $lineItem->product_id );
                }
            }
            return $this->getPurchasedReportId ( $purchasedItem );

        } catch (HttpClientException $e) {
            throw new EquioException( $e->getMessage () );
        } catch (\Exception $e) {
            throw new EquioException( $e->getMessage () );
        }
    }

    public function getPurchasedReportId($request)
    {
        $result = [];
        $resultObj = DB::table ( "reports" )->select ( 'reports.id' )
            // ->where('reports.available', "=", 1)
            ->whereIn ( 'reports.woo_id', $request )
            ->groupBy ( 'reports.name' )->orderBy ( 'id', 'DESC' )->get ();

        if ( !empty( $resultObj ) ) {
            foreach ( $resultObj as $arr ) {
                $result [] = $arr->id;
            }
        }
        return $result;
    }

    public function subscriptionsByuser($user, $route)
    {
        $result = DB::table ( "subscription_route" )->select ( 'subscription_route.id' )
            ->join ( 'subscription', 'subscription.id', 'subscription_route.subscription_id' )
            ->join ( 'route', 'route.id', 'subscription_route.route_id' )
            ->join ( 'users', 'users.subscription_level', 'subscription.code' )
            ->where ( 'users.id', '=', $user )
            ->where ( 'route.routes', '=', $route )
            ->get ()->count ();

        return $result;
    }

    public function implodeWithCharacters($arr, $charcter = ", ")
    {

        $name = [];
        foreach ( $arr as $rows ) {
            $name[] = ['name' => $rows->name, 'qty' => $rows->quantity];
        }
        return $name;
    }

    public function getPaymentStatusAttribute($value)
    {
        $retrun_value = "";
        switch ($value) {
            case "wc-pending":
                $retrun_value = "Pending";
                break;
            case "wc-processing":
                $retrun_value = "Processing";
                break;
            case "wc-paid":
                $retrun_value = "Paid";
                break;
            case "wc-completed":
                $retrun_value = "Completed";
                break;
            case "wc-refunded":
                $retrun_value = "Refunded";
                break;
            case "wc-onhold":
                $retrun_value = "On Hold";
                break;
            default:
                $retrun_value = $value;
        }

        return $retrun_value;

    }

    /**
     * get Woocommerce Purchase Subcription
     * @return bool
     */
    public function wooComerceSubsCriptionPurchseHistory()
    {
        $this->woocommerce = new Client(
            Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
            ['wp_api' => true, 'version' => 'wc/v1',]
        );

        try {
            $results = $this->woocommerce->get (
                'subscriptions',
                [
                    'search' => Auth::user ()->email,
                    'per_page' => 50,
                    'status' => 'active',
                    'order'>'desc'
                ]
            );
            return $results;

        } catch (HttpClientException $e) {
            return false;
        } catch
        (\Exception $e) {
            return false;
        }

    }

    public function wooComercePurchseHistoryInfo()
    {
        $this->woocommerce = new Client(
            Config::get ( 'custom_config.WOOCOMMERCE_API_URL' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_KEY' ),
            Config::get ( 'custom_config.WOOCOMMERCE_API_SECRET' ),
            ['wp_api' => true, 'version' => 'wc/v1',]
        );

        $results = $this->woocommerce->get (
            'orders',
            [
                'search' => Auth::user ()->email,
                'per_page' => 50,
                //'status' => 'completed'
            ]
        );
        $arrResults = [];
        foreach ( $results as $rows ) {

            $shipping_address = $rows->shipping->first_name . " " . $rows->shipping->last_name . ", " . $rows->shipping->address_1 . ", " . $rows->shipping->address_2 . "\n " .
                $rows->shipping->city . "," . $rows->shipping->state . "," . $rows->shipping->postcode;

            $full_name = $rows->billing->first_name . " " . $rows->billing->last_name;

            $arrResults[] = ['id' => $rows->id, 'email' => Auth::user ()->email, 'name' => $full_name, 'item_name' => $this->implodeWithCharacters ( $rows->line_items ), 'order_id' => $rows->id, 'payment_status' => $this->getPaymentStatusAttribute ( $rows->status ),
                'shipping_address' => $shipping_address,
                'payment_date' => date ( "m-d-Y h:m:s", strtotime ( $rows->date_created ) ),
                'payment_amount' => $rows->total, 'billing' => $rows->billing, 'shipping' => $rows->shipping];

        }

        $subscription =  $this->wooComerceSubsCriptionPurchseHistory();
        if(!empty($subscription)&&  count($subscription)>0){

            foreach ( $subscription as $rows ) {

                $shipping_address = $rows->shipping->first_name . " " . $rows->shipping->last_name . ", " . $rows->shipping->address_1 . ", " . $rows->shipping->address_2 . "\n " .
                    $rows->shipping->city . "," . $rows->shipping->state . "," . $rows->shipping->postcode;

                $full_name = $rows->billing->first_name . " " . $rows->billing->last_name;

                $arrResults[] = ['id' =>$rows->id, 'email' => Auth::user ()->email, 'name' => $full_name, 'item_name' => $this->implodeWithCharacters ( $rows->line_items ), 'order_id' => $rows->id, 'payment_status' => $this->getPaymentStatusAttribute ( $rows->status ),
                    'shipping_address' => $shipping_address,
                    'payment_date' => date ( "m-d-Y h:m:s", strtotime ( $rows->date_created ) ),
                    'payment_amount' => $rows->total, 'billing' => $rows->billing, 'shipping' => $rows->shipping];

            }

        }

        return $arrResults;
    }

    public static function userRolesAndPermissions($user_id)
    {
        $all = RoleUser::where ( 'user_id', $user_id )
            ->leftJoin ( 'roles', 'roles.id', 'role_user.role_id' )
            ->select ( "roles.name" )
            ->get ();
        return $all;
    }

    public static function removeArrayFields($request, $keys)
    {
        foreach ( $keys as $value ) {
            unset( $request[ $value ] );
        }
        return $request;
    }

    public function checkAuthnicatedRole($allRoleIds){

        if ( in_array ( Config::get ( "custom_config.ROLE_ADMINISTRATOR" ), $allRoleIds ) ) {
            return true;
        }


        if ( in_array ( Config::get ( "custom_config.ROLE_REPORTER" ), $allRoleIds ) ) {
            return true;
        }


        if ( in_array ( Config::get ( "custom_config.ROLE_EDITOR" ), $allRoleIds ) ) {
            return true;
        }

        if ( in_array ( Config::get ( "custom_config.ROLE_MANAGER" ), $allRoleIds ) ) {
            return true;
        }
        return false;

    }

    public  function getUserAssingRoleNum($userId){

        $user = User::find ( $userId );
        $allRoleIds = [];
        $allUserAssignRole = $user->roles ()->get ();
        foreach ( $allUserAssignRole as $rows ) {
            array_push ( $allRoleIds, $rows->id );
        }
        return $allRoleIds;

    }

    public static function stringContain($txt){

        $retString = "";

        if( strpos($txt, 'Premium' ) == true )
        {

            $retString =  "premium";
        }


        if( strpos($txt, 'Premium Plus' ) == true )
        {

            $retString =  "premium_plus";
        }
        if( strpos($txt, 'Essential' ) == true )
        {

            $retString =  "essential";
        }

        if( strpos($txt, 'Enterprise' ) == true )
        {

            $retString = "enterprise";
        }
        return $retString;

    }

    public static function  getUserByStripeId($stripeId){

        $objUser =  User::where("stripe_id", $stripeId)->select("id","email")->first();
        if (!empty($objUser)) {

            return $objUser;
        }
        return false;
    }

    public static function  getUserByEmail($email){

        $objUser =  User::where("email", $email)->select("id","email")->first();
        if (!empty($objUser)) {

            return $objUser;
        }
        return false;
    }


    public static function  getModuleSucriptionPlanId($userId){

        $objDbDetaials =   DB::table("module_subscription_trackers")
            ->where("user_id", $userId)
            ->select("plan_id", "subscription_id")->first();

        return $objDbDetaials;
    }

    public static function  getLatestSubcriptionActivity($userId){

        $objDbDetaials =   DB::table("shortposition_activity_log")
            ->where("user_id", $userId)
            ->select("action" ,"id")->orderBy("id","desc")->first();

        return $objDbDetaials;
    }


}
