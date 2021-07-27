<?php
namespace App\Http\Controllers\Dome\Provider; //ExampleController

use App\Http\Controllers\Controller;
use App\Models\Dome\Pet\Pet;
use App\Models\Dome\Provider\Providers;
use App\Repositories\Dome\Pets\PetSocialRepository;
use App\Repositories\Dome\Provider\ProviderSocialRepository; //PS-ExampleRepository.php
use App\Repositories\Dome\Stream\StreamNotificationRepository;
use Illuminate\Http\Request;

class ProviderSocialController extends Controller
{

    /**
     *
     * @param Request $request            
     * @param ProviderSocialRepository $providerSocialRepository            
     * @param
     *            $target_provider_uid
     */
    public function likeProvider(Request $request, ProviderSocialRepository $providerSocialRepository, $target_provider_uid)
    {
        $actor_profile_uid = $request->get('actor_profile_uid');
        
        // Make sure the actor profile exists
        $this->_assertProfileExists($actor_profile_uid);
        
        // Make sure the actor profile belongs to current user
        $this->_assertCurrentProfileBelongsToCurrentUser();
        
        // Make sure the target profile exists
        $this->_assertProviderExists($target_provider_uid);
        
        // Like the profile
        $providerSocialRepository->likeProvider($target_provider_uid, $actor_profile_uid, clientIP());
        
        // Get the updated target profile
        $target_provider = Providers::find($target_provider_uid);
        
        // Return the response
        return $this->respondWithItem($target_provider);
    }

    /**
     *
     * @param Request $request            
     * @param ProviderSocialRepository $providerSocialRepository            
     * @param
     *            $target_provider_uid
     * @param
     *            $vote_uid
     */
    public function unLikeProvider(Request $request, ProviderSocialRepository $providerSocialRepository, $target_provider_uid, $vote_uid)
    {
        $actor_profile_uid = $request->header('X-ACTIVE-PROFILE');
        
        // Make sure the actor profile exists
        $this->_assertProfileExists($actor_profile_uid);
        
        // Make sure the actor profile belongs to current user
        $this->_assertCurrentProfileBelongsToCurrentUser();
        
        // Make sure the target profile exists
        $this->_assertProviderExists($target_provider_uid);
        
        // Like the profile
        $providerSocialRepository->unlikeProvider($vote_uid, $target_provider_uid, $actor_profile_uid);
        
        // Get the updated target profile
        $target_provider = Providers::find($target_provider_uid);
        
        // Return the response
        return $this->respondWithItem($target_provider);
    }

    /**
     *
     * @param ProviderSocialRepository $providerSocialRepository            
     * @param Request $request            
     * @param
     *            $target_provider_uid
     */
    public function getProviderLikes(ProviderSocialRepository $providerSocialRepository, Request $request, $target_provider_uid)
    {
        $actor_profile_uid = $request->get('actor_profile_uid');
        
        // Make sure the profile exists
        $this->_assertProfileExists($actor_profile_uid);
        
        // Make sure the profile belongs to current user
        $this->_assertCurrentProfileBelongsToCurrentUser();
        
        // Make sure the target profile exists
        $this->_assertProviderExists($target_provider_uid);
        
        // Get the likes
        if (isset($actor_profile_uid) && ! empty($actor_profile_uid) && $actor_profile_uid != '') {
            $likes = $providerSocialRepository->getProviderLikesByProfile($request, $target_provider_uid, $actor_profile_uid);
        } else
            $likes = $providerSocialRepository->getProviderLikes($request, $target_provider_uid);
        
        // Get the likes
        // $likes = $providerSocialRepository->getProviderLikes($request, $target_provider_uid);
        
        // Return the response
        return $this->respondWithCollection($likes);
    }

    /**
     *
     * @param Request $request            
     * @param ProviderSocialRepository $providerSocialRepository            
     * @param
     *            $target_provider_uid
     */
    public function getProviderShares(Request $request, ProviderSocialRepository $providerSocialRepository, $target_provider_uid)
    {
        $actor_profile_uid = $request->get('actor_profile_uid');
        
        // Make sure the profile exists
        $this->_assertProfileExists($actor_profile_uid);
        
        // Make sure the profile belongs to current user
        $this->_assertCurrentProfileBelongsToCurrentUser();
        
        // Make sure the target profile exists
        $this->_assertProviderExists($target_provider_uid);
        
        // Get the shares
        $shares = $providerSocialRepository->getProviderLikes($request, $target_provider_uid);
        
        // Collect the shares
        $shares = collect($shares);
        
        // Return the response
        return $this->respondWithCollection($shares);
    }

    /**
     *
     * @param Request $request            
     * @param ProviderSocialRepository $providerSocialRepository            
     * @param
     *            $target_provider_uid
     */
    public function shareProvider(Request $request, ProviderSocialRepository $providerSocialRepository, $target_provider_uid)
    {
        $actor_profile_uid = $request->get('actor_profile_uid');
        
        // Make sure the profile exists
        $this->_assertProfileExists($actor_profile_uid);
        
        // Make sure the profile belongs to current user
        $this->_assertCurrentProfileBelongsToCurrentUser();
        
        // Make sure the target profile exists
        $this->_assertProviderExists($target_provider_uid);
        
        // Share the Provider
        $providerSocialRepository->shareProvider($target_provider_uid, $actor_profile_uid, clientIP());
        
        // Get the updated target profile
        $target_provider = Providers::find($target_provider_uid);
        
        // Initiate the repositories
        $notificationRepository = new StreamNotificationRepository();
        
        $activeProfile = $request->get('api:profile');
        
        // Create the notification
        $notificationRepository->createNotification($target_provider->users()
        		->first()->uid, 'provider', $target_provider_uid, 'profile', $activeProfile->uid, $notificationRepository->capsule($activeProfile) . ' shares your provider ' . $notificationRepository->capsule('provider', $target_provider_uid) . ' profile.');
        
        // Return the response
        return $this->respondWithItem($target_provider);
    }
}