<?php

namespace App\Services;

use App\Repositories\CanaClipsRepository;
use App\Repositories\Criteria\Common\FilterColumn;
use App\Repositories\Criteria\Users\AllUsersSelect;
use App\Repositories\Criteria\Users\AttributeSearch;
use App\Repositories\Criteria\Users\FilterByCount;
use App\Repositories\Criteria\Users\JoinUserProfile;
use App\Repositories\Criteria\Users\KeywordSearch;
use App\Repositories\Criteria\Users\OrderBy;
use App\Repositories\Criteria\Users\OrderByCreated;
use App\Repositories\Criteria\Users\RoleSearch;
use App\Repositories\InterestRepository;
use App\Repositories\PaymentRecordRepository;
use App\Repositories\PresentationDeckRepository;
use App\Repositories\UserInterestRepository;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
use App\Repositories\WebinarRepository;
use App\Repositories\UserShortTrackerRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Mail;
use Join;

class UserService
{

    private $userRepository;
    private $userProfileRepository;
    private $interstRepository;
    private $userInterstRepository;
    private $webinarRepository;
    private $canaClipsRepository;
    private $presentationDeckRepository;
    private $paymentRecordRepository;
    private $userShortTrackerRepository;

    /**
     * UserService constructor.
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository, UserProfileRepository $userProfileRepository,
                                InterestRepository $interstRepository, CanaClipsRepository $canaClipsRepository,
                                UserInterestRepository $userInterstRepository, WebinarRepository $webinarRepository,
                                PresentationDeckRepository $presentationDeckRepository,
                                PaymentRecordRepository $paymentRecordRepository,
                                UserShortTrackerRepository $userShortTrackerRepository

    )
    {
        $this->userRepository = $userRepository;
        $this->userProfileRepository = $userProfileRepository;
        $this->interstRepository = $interstRepository;
        $this->userInterstRepository = $userInterstRepository;
        $this->webinarRepository = $webinarRepository;
        $this->canaClipsRepository = $canaClipsRepository;
        $this->presentationDeckRepository = $presentationDeckRepository;
        $this->paymentRecordRepository = $paymentRecordRepository;
        $this->userShortTrackerRepository = $userShortTrackerRepository;
    }


    public function allUsers($request)
    {
        $perPage = (!empty($request['perPage'])) ? ($request['perPage']) : env('PAGINATE_PER_PAGE', 15);

        $sort = ($request->sort) ? $request->sort : 'asc';
        $sortColumn = ($request->sortType) ? $request->sortType : 'users.id';

        if ( $request->perPage && $request->perPage >= 10 ) {
            $perPage = $request->perPage;
        }

        if ( $request->filterRole ) {
            $this->userRepository->pushCriteria ( new RoleSearch( $request->filterRole ) );
        }

        if ( $request->queryString ) {
            $this->userRepository->pushCriteria ( new KeywordSearch( $request->queryString ) );
        }

        if ( $request->filterSubscription ) {
                $this->userRepository->pushCriteria ( new AttributeSearch( 'subscription_level', $request->filterSubscription ) );
        }

        if ( $request->filterRenewalYear ) {
            $this->userRepository->pushCriteria ( new FilterColumn( 'renewal_year', $request->filterRenewalYear ) );
        }
        if ( $request->filterRenewalYearMonth ) {
            $this->userRepository->pushCriteria ( new FilterColumn( 'renewal_year_month', $request->filterRenewalYearMonth ) );
        }

        if ( $request->filterYear ) {
            $this->userRepository->pushCriteria ( new FilterColumn( 'create_year', $request->filterYear ) );
        }
        if ( $request->filterYearMonth ) {
            $this->userRepository->pushCriteria ( new FilterColumn( 'create_year_month', $request->filterYearMonth ) );
        }


        // check filters paid, expired, inTrial, disabled
        if ( (boolean)$request->inTrial || $request->paid || !empty( $request->expired ) || $request->disabled ) {

            $this->userRepository->pushCriteria ( new FilterByCount( $request ) );
        }

        $this->userRepository->pushCriteria ( new OrderBy( $sort, $sortColumn ) );
        $this->userRepository->pushCriteria ( new AllUsersSelect() );
        $this->userRepository->pushCriteria ( new JoinUserProfile() );


        $users = $this->userRepository->paginate ( $perPage );

        $userData = collect ( $users );

        $data = $userData[ 'data' ];
        $userData->pull ( 'data' );
        $userData->put ( 'users', $data );
        return $userData;
    }

    /*
    public function allUsers($request)
    {
        $perPage = (!empty($request['perPage'])) ? ($request['perPage']) : env('PAGINATE_PER_PAGE', 15);

        $sort = ($request->sort) ? $request->sort : 'asc';
        $sortColumn = ($request->sortType) ? $request->sortType : 'users.id';

        if ( $request->perPage && $request->perPage >= 10 ) {
            $perPage = $request->perPage;
        }

        if ( $request->filterRole ) {
            if ( $request->filterRole == 1 && !empty( $request->filterSubscription ) ) {

            } else {
                $this->userRepository->pushCriteria ( new RoleSearch( $request->filterRole ) );
            }
        }

        if ( $request->queryString ) {
            $this->userRepository->pushCriteria ( new KeywordSearch( $request->queryString ) );
        }

        if ( $request->filterSubscription ) {
            if ( $request->filterRole == 2 ) {
                $this->userRepository->pushCriteria ( new AttributeSearch( 'subscription_level', $request->filterSubscription ) );
            }
        }

        if ( $request->filterRenewalYear ) {
            $this->userRepository->pushCriteria ( new FilterColumn( 'renewal_year', $request->filterRenewalYear ) );
        }
        if ( $request->filterRenewalYearMonth ) {
            $this->userRepository->pushCriteria ( new FilterColumn( 'renewal_year_month', $request->filterRenewalYearMonth ) );
        }

        // check filters paid, expired, inTrial, disabled
        if ( (boolean)$request->inTrial || $request->paid || !empty( $request->expired ) || $request->disabled ) {

            $this->userRepository->pushCriteria ( new FilterByCount( $request ) );
        }

        $this->userRepository->pushCriteria ( new OrderBy( $sort, $sortColumn ) );
        $this->userRepository->pushCriteria ( new AllUsersSelect() );
        $this->userRepository->pushCriteria ( new JoinUserProfile() );


        $users = $this->userRepository->paginate ( $perPage );

        $userData = collect ( $users );

        $data = $userData[ 'data' ];
        $userData->pull ( 'data' );
        $userData->put ( 'users', $data );
        return $userData;
    }
    */

    public function getBasicInfoByEmail($email)
    {
        return $this->userRepository->basicInfoByEmail ( $email );
    }

    public function createUser($user_array)
    {
        return $this->userRepository->saveUser ( $user_array );
    }

    public function createUserProfile($user_profile_array)
    {
        return $this->userProfileRepository->saveUserProfile ( $user_profile_array );
    }

    public function updateUserProfile($user_profile_array, $user_id)
    {
        return $this->userProfileRepository->updateUserProfile ( $user_profile_array, $user_id );
    }

    public function updateUser($user_array, $user_id, $role = null,$paid_subscripton_end = null,$paid_subscripton_start = null)
    {

        return $this->userRepository->updateUser ( $user_array, $user_id, $role,$paid_subscripton_end,$paid_subscripton_start );
    }

    public function getUpdatePassword($email, $password)
    {
        return $this->userRepository->updatePassword ( $email, $password );
    }

    public function getBasicInfoByUserId($id)
    {
        //print_r( $this->userRepository->basicInfoById ( $id )); die();
        return    $this->userRepository->basicInfoById ( $id );
    }

    public function countUsersByProperty($attribute = null, $value = null)
    {
        return $this->userRepository->countUsersByProperty ( $attribute, $value );
    }

    public function countUsersDisableByProperty($attribute = null, $value = null)
    {
        return $this->userRepository->countUsersDisableByProperty ( $attribute, $value );
    }

    public function getAllInterestInfo()
    {

        return $this->interstRepository->showInterestInfo ();

    }

    public function AddInterestUser($data)
    {

        return $this->userInterstRepository->addInterest ( $data );

    }

    public function updateAcceptTou($data)
    {
        $userId = Auth::user ()->id;
        $accepted_tou = (!empty( $data[ 'accept_tou' ] )) ? ($data[ 'accept_tou' ]) : 'n';
        return $this->userRepository->UpdateAcceptTou ( $userId, $accepted_tou );

    }

    public function updateSignInCount($data)
    {
        $userId = Auth::user ()->id;
        $decline = (!empty( $data[ 'decline' ] )) ? ($data[ 'decline' ]) : '';
        return $this->userRepository->UpdateSignInCount ( $userId, $decline );

    }


    public function updateUserPersonalInfo($data, $userId)
    {
        return $this->userRepository->updatePersonalInfo ( $data, $userId );

    }

    public function getPersonalInfo($userId)
    {

        return $this->userRepository->personalInfo ( $userId );

    }

    public function viewInterestUser($userId)
    {
        return $this->userInterstRepository->viewUserInterst ( $userId );

    }

    public function viewSubscriptionUserDetail($userId)
    {
        return $this->userRepository->viewUserSubscription ( $userId );

    }

    public function changeSubscriptionPalnUser($userId, $request)
    {

        $plan = (!empty( $request[ 'plan' ] )) ? ($request[ 'plan' ]) : null;
        $reason = (!empty( $request[ 'reason' ] )) ? ($request[ 'reason' ]) : null;
        return $this->userRepository->changeUserSubscriptionPlan ( $userId, $plan, $reason );

    }

    public function checkSubscriptionChange($userId, $request)
    {
        return $this->userRepository->checkUserIsCancel ( $userId );

    }

    public function webinars($request)
    {
        return $this->webinarRepository->allWebinarInfo ( $request );
    }


    public function cannaClips($request)
    {
        return $this->canaClipsRepository->allCannaClipsInfo ( $request );
    }

    public function presentationDeck($request)
    {
        return $this->presentationDeckRepository->allPersnationDeckInfo ( $request );
    }

    public function paymentRecordInfo($request)
    {
        return $this->paymentRecordRepository->allPaymentRecordInfo ( $request );
    }

    public function UserChangePassword($request)
    {

        $userId = Auth::user ()->id;
        $new_password = (!empty( $request->password )) ? ($request->password) : null;
        return $this->userRepository->updateUserPassword ( $userId, $new_password );
    }

    public function userFeedBackMail($request)
    {
        $name = (!empty( $request->name )) ? ($request->name) : null;
        $email = (!empty( $request->email )) ? ($request->email) : null;
        $description = (!empty( $request->description )) ? ($request->description) : null;
        $image = (!empty( $request->image )) ? ($request->image) : null;
        $fileName = '';
        $to = Config::get ( 'custom_config.FEEDBACKTOEMAIL' );
        $data = ['from' => Config::get ( 'custom_config.from_email' ), 'system' => Config::get ( 'custom_config.system_email_send' )];
        $subject = Lang::get ( 'email_subjects.Contact_us' );
        try {
            Mail::send ( 'emails.UserFeedBack', array('name' => $name, 'email' => $email, 'description' => $description),
                function ($message) use ($email, $name, $subject, $data, $description, $image,$to) {
                    $message->from ( $email, $data[ 'system' ] );
                    $message->to ( $to, $name )->subject ( $subject );
                    if ( !empty( $image ) ) {
                        $message->attach ( $image->getRealPath (), ['as' => 'attachment', $image->getClientOriginalExtension (), 'mime' => $image->getMimeType ()] );
                    }
                } );
        } catch (\Swift_TransportException $ex) {
            return false;
        }
        if ( count ( Mail::failures () ) > 0 ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Count users by disable, intrail paid, expired , other
     * @return int
     */
    public function countUsersByFilters($requests, $type)
    {
        return $this->userRepository->countUsersByFilters ( $requests, $type );
    }

    public function updateUserRoles($userRoles, $userId)
    {
        return $this->userRepository->updateUserRolesByUserRoleIds ( $userRoles, $userId );
    }

    public function getShortTrackerList()
    {
        return $this->userShortTrackerRepository->getShortTrackerList();
    }


}