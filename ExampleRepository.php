<?php
namespace App\Repositories\Dome\Provider; //ExampleRepository

use App\Events\Dome\Provider\ProviderShare;
use App\Events\Dome\Provider\ProviderUnvoted;
use App\Events\Dome\Provider\ProviderVoted;
use App\Models\Dome\Pet\Pet;
use App\Models\Dome\Provider\Providers;
use App\Repositories\Dome\UIDRepository;
use App\Traits\SortishTrait;
use DB;

/**
 * Class UserProfilePhotoSocialRepository
 *
 * @package App\Repositories\Dome\User\Profile
 */
class ProviderSocialRepository
{
    use SortishTrait;

    /**
     *
     * @var array
     */
    private $tables = [
        'votes' => 'providers_votes',
        'shares' => 'providers_shares'
    ];

    /**
     *
     * @param
     *            $target_provider_uid
     * @param
     *            $actor_profile_uid
     * @param
     *            $ip
     */
    public function likeProvider($target_provider_uid, $actor_profile_uid, $ip = '')
    {
        // Make sure Provider is not already liked by this actor
        if ($this->isProviderLiked($target_provider_uid, $actor_profile_uid)) {
            return;
        }
        
        // Generate the UID
        $uid = UIDRepository::generateUid();
        
        // Save the like
        DB::table($this->tables['votes'])->insert([
            'uid' => $uid,
            'target_provider_uid' => $target_provider_uid,
            'actor_profile_uid' => $actor_profile_uid,
            'created_at' => time(),
            'created_ip' => $ip
        ]);
        
        // Increment the Pet total_likes
        event(new ProviderVoted($target_provider_uid));
    }

    /**
     *
     * @param
     *            $like_uid
     * @param
     *            $target_provider_uid
     * @param
     *            $actor_profile_uid
     */
    public function unlikeProvider($like_uid, $target_provider_uid, $actor_profile_uid)
    {
        // Make sure Pet is already liked by this actor
        if (! $this->isProviderLiked($target_provider_uid, $actor_profile_uid)) {
            return;
        }
        
        // Delete the like
        DB::table($this->tables['votes'])->where('uid', $like_uid)
            ->where('target_provider_uid', $target_provider_uid)
            ->where('actor_profile_uid', $actor_profile_uid)
            ->delete();
        
        // Decrement the Provider total_votes
        event(new ProviderUnvoted($target_provider_uid));
    }

    /**
     *
     * @param
     *            $request
     * @param
     *            $target_provider_uid
     * @return mixed
     */
    public function getProviderLikes($request, $target_provider_uid)
    {
        // Init the sortish trait
        $this->sortishInit($request, [
            'created_at',
            'updated_at'
        ]);
        
        // Select the votes
        $votes = DB::table($this->tables['votes'])->where('target_provider_uid', $target_provider_uid);
        
        $votes = $this->sortishGet($votes);
        
        // Return the votes
        return $votes;
    }

    public function getProviderLikesByProfile($request, $target_provider_uid, $actor_profile_uid)
    {
        // Init the sortish trait
        $this->sortishInit($request, [
            'created_at',
            'updated_at'
        ]);
        
        // Select the votes
        $votes = DB::table($this->tables['votes'])->where('target_provider_uid', $target_provider_uid)->where('actor_profile_uid', $actor_profile_uid);
        
        $votes = $this->sortishGet($votes);
        
        // Return the votes
        return $votes;
    }

    /**
     *
     * @param
     *            $target_provider_uid
     * @param
     *            $actor_profile_uid
     * @param
     *            $ip
     * @return mixed
     */
    public function shareProvider($target_provider_uid, $actor_profile_uid, $ip = '')
    {
        // Generate the uid
        $uid = UIDRepository::generateUid();
        
        // Insert in shares table
        DB::table($this->tables['shares'])->insert([
            'uid' => $uid,
            'target_provider_uid' => $target_provider_uid,
            'actor_profile_uid' => $actor_profile_uid,
            'created_at' => time(),
            'created_ip' => $ip
        ]);
        
        // Increment the photo total_likes and do the propagation
        event(new ProviderShare($target_provider_uid, $actor_profile_uid, $ip));
        
        // Get the newly inserted share
        $share = DB::table($this->tables['shares'])->where('uid', $uid)->first();
        
        // Return the share
        return $share;
    }

    /**
     *
     * @param
     *            $request
     * @param
     *            $target_provider_uid
     * @return mixed
     */
    public function getProviderShares($request, $target_provider_uid)
    {
        $this->sortishInit($request, [
            'created_at',
            'updated_at'
        ]);
        
        // photo shares
        $shares = DB::table($this->tables['shares'])->where('target_provider_uid', $target_provider_uid);
        
        $shares = $this->sortishGet($shares);
        
        return $shares;
    }

    /**
     *
     * @param
     *            $target_provider_uid
     * @param
     *            $actor_profile_uid
     * @return bool
     */
    protected function isProviderLiked($target_provider_uid, $actor_profile_uid)
    {
        // Select the like
        $check = DB::table($this->tables['votes'])->where('target_provider_uid', $target_provider_uid)
            ->where('actor_profile_uid', $actor_profile_uid)
            ->first();
        
        // Make sure it is not liked already
        if ($check === NULL) {
            return false;
        }
        
        return true;
    }

    /**
     *
     * @param
     *            $like_uid
     * @return mixed
     */
    public function getProviderLike($like_uid)
    {
        // Select the vote
        $vote = DB::table($this->tables['votes'])->where('uid', $like_uid)->first();
        
        // Return the vote
        return $vote;
    }
}