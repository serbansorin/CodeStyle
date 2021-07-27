<?php
namespace App\Models\Dome\Provider;

use App\Models\Dome\Storage\Document\StorageDocument;
use App\Repositories\Dome\Storage\StorageRepository;
use Illuminate\Database\Eloquent\Model;
use DB;

/**
 * Class Providers
 *
 * @package App\Models\Dome\Provider
 */
class Providers extends Model
{
    /**
     *
     * @var string
     */
    protected $table = 'providers';
    
    
    /**
     *
     * @var string
     */
    protected $primaryKey = 'uid';

    /**
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     *
     * @var array
     */
    public $accepted_values = [
        'name',
        'tagline',
        'url',
        'address',
        'email',
        'phone',
        'phone_country_code',
        'phone_country',
        'type_uid',
        'location_lat',
        'location_long',
        'location_slug',
        'location_country',
        'location_region',
        'location_city',
        'location_street_name',
        'location_street_number'
    ];

    /**
     *
     * @var array
     */
    public $photo_types = [
        'avatar',
        'cover'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uid',
        'name',
        'slug',
        'url',
        'address',
        'email',
        'phone',
        'phone_country',
        'type_uid',
        'location_lat',
        'location_long',
        'location_slug',
        'status',
        'status_updated_at',
        'status_ip',
        'active_provider',
        'image_cover_uid',
        'image_avatar_uid',
        'avatars_album_uid',
        'covers_album_uid',
        'timeline_album_uid',
        'total_votes',
        'total_shares',
        'total_photos',
        'total_albums',
        'total_views',
        'total_comments',
        'total_engagements',
        'total_messages',
        'total_notifications',
        'created_at',
        'created_ip',
        'updated_at',
        'updated_ip',
        'location_country',
        'location_region',
        'location_city',
        'location_street_name',
        'location_street_number'
    ];

    /**
     *
     * @var array
     */
    public $allowedWith = [
        'pets',
        'type'
    ];

    /**
     *
     * @var array
     */
    protected $appends = [
        'avatar_url',
        'cover_url',
        'pet_count'
    ];

    /**
     *
     * @return mixed
     */
    public function contacts()
    {
        return $this->hasMany('App\Models\Dome\Provider\ProviderDetailsContacts', 'provider_uid', 'uid');
    }

    /**
     *
     * @return mixed
     */
    public function details()
    {
        return $this->hasOne('App\Models\Dome\Provider\ProviderDetails', 'provider_uid', 'uid');
    }

    /**
     *
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany('App\Models\Dome\User\Profile\UserProfile', 'providers_acl', 'provider_uid', 'profile_uid');
    }

    /**
     *
     * @return mixed
     */
    public function pets() // TODO: Provider can no longer have pets assigned to it
    {
        return $this->belongsToMany('App\Models\Dome\Pet\Pet', 'pets_health_providers', 'provider_uid', 'pet_uid');
    }

    // public function petcount()
    // {
    // return \App\Models\Dome\Pet\Pet::where('entity_uid', $this->uid)->count();
    // }
    public function getPetCountAttribute()
    {
        return \App\Models\Dome\Pet\Pet::where('entity_uid', $this->uid)->count();
    }

    /**
     *
     * @param
     *            $value
     */
    public function setSlugAttribute($value)
    {
        if ($value == '') {
            $value = $this->name;
        }
        $this->attributes['slug'] = $this->generateSlug($value);
    }

    /**
     *
     * @return mixed
     */
    public function avatar()
    {
        return $this->hasOne('App\Models\Dome\Storage\Document\StorageDocument', 'uid', 'image_avatar_uid');
    }

    /**
     *
     * @return mixed
     */
    public function cover()
    {
        return $this->hasOne('App\Models\Dome\Storage\Document\StorageDocument', 'uid', 'image_cover_uid');
    }

    /**
     *
     * @param
     *            $name
     * @param int $i            
     * @return string
     */
    public function generateSlug($name, $i = 1)
    {
        // Generate the slug string
        $slug = str_slug($name, '.');
        
        // If this is not the original try
        if ($i != 1) {
            $slug = $slug . '.' . $i;
        }
        
        // Check if this slug is already used
        $check = DB::table('slugs')->where('slug', $slug);
        
        // Don't check on current record
        if (! empty($this->uid)) {
            $check = $check->where('uid', '<>', $this->uid);
        }
        
        // Execute the check DB query
        $check = $check->first();
        
        // If it's not used return it
        if (empty($check)) {
            
            return $slug;
        } else {
            
            // If it's used try again with a different index
            return $this->generateSlug($name, $i + 1);
        }
    }

    /**
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function type()
    {
        return $this->hasOne('App\Models\Dome\Provider\ProviderType', 'uid', 'type_uid');
    }

    /**
     *
     * @return mixed|null|string
     */
    public function getAvatarUrlAttribute()
    {
        if ($this->image_avatar_uid !== NULL) {
            
            // return all the modifications
            $manipulations = [
                'album-photo',
                'album-photo-square',
                'avatar',
                'avatar-medium',
                'avatar-xsmall'
            ];
            
            $urls = [
                'original' => StorageRepository::imageUrl('document', $this->image_avatar_uid, false, 'original')
            ];
            
            foreach ($manipulations as $manipulation) {
                
                $urls[$manipulation] = StorageRepository::imageUrl('document', $this->image_avatar_uid, false, $manipulation);
            }
            
            return $urls;
        }
        
        // DOCUMENT NOT FOUND
        return NULL;
    }

    /**
     *
     * @return mixed|null|string
     */
    public function getCoverUrlAttribute()
    {
        if ($this->image_cover_uid !== NULL) {
            
            // return all the modifications
            $manipulations = [
                'album-photo',
                'album-photo-square',
                'cover',
                'cover-small'
            ];
            
            $urls = [
                'original' => StorageRepository::imageUrl('document', $this->image_cover_uid, false, 'original')
            ];
            
            foreach ($manipulations as $manipulation) {
                
                $urls[$manipulation] = StorageRepository::imageUrl('document', $this->image_cover_uid, false, $manipulation);
            }
            
            return $urls;
        }
        
        // DOCUMENT NOT FOUND
        return NULL;
    }
}