<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'first_name', 'middle_name', 'last_name', 'phone_number', 'image_url', 'public_profile_url',
        'position', 'industry', 'headline', 'summary', 'specialities', 'url', 'relationship_status', 'title',
        'current_location', 'about_me', 'occupation', 'street_address1', 'street_address2', 'state', 'state_id', 'address',
        'country', 'city', 'zip', 'type_of_business'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [

    ];

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
