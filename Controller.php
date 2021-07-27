<?php //app/Http/Controllers/Controller.php

# An Extension of the Main Controller where we define some helpfull Methods
# The ideea is to have usefull classes and functions that can be used through out the entire Application
# such as "to verify the actual Profile Exists" or "Validate Request Differently"
# Or extend basic functionalities of Laravel.


namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Validator;
use Illuminate\Http\Request;
use League\Fractal\Manager;
use Dingo\Api\Routing\Helpers;
use App\Traits\ResponseTrait;

class Controller extends BaseController
{
    use Helpers, ResponseTrait;

    /**
     * Constructor
     *
     * @param Manager|null $fractal
     */
    public function __construct(Manager $fractal = null)
    {
        $fractal = $fractal === null ? new Manager() : $fractal;
        $this->setFractal($fractal);
    }

    /**
     * Validate HTTP request against the rules
     *
     * @param Request $request
     * @param array $rules
     * @return bool|array
     */
    protected function validateRequest($params, array $rules)
    {
        // Perform Validation
        $validator = Validator::make($params, $rules);

        if ($validator->fails()) {
            $errorMessages = $validator->errors()->messages();

            // crete error message by using key and value
            foreach ($errorMessages as $key => $value) {
                $errorMessages[$key] = $value[0];
            }

            return $errorMessages;
        }

        return true;
    }




    public function _assertEntityExists($entity_type, $entity_uid, $checkBelongsToCurrentUser = false)
    {
    }

    /**
     * USER & PROFILE ASSERTS
     */

    /**
     *
     * @param
     *            $profile_uid
     * @return mixed
     */
    public function _assertProfileExists($profile_uid)
    {
    }

    /**
     *
     * @param
     *            $document_uid
     */
    public function _assertProfilePhotoExists($document_uid)
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertCurrentProfileBelongsToCurrentUser()
    {
    }

    /**
     *
     * @param
     *            $article_uid
     */
    public function _assertArticleBelongsToCurrentProfile($article_uid)
    {
    }

    /**
     * PROVIDER TRAITS
     */

    /**
     *
     * @param
     *            $provider_uid
     */
    public function _assertProviderExists($provider_uid)
    {
    }

    /**
     */
    public function _assertCurrentProviderBelongsToCurrentProfile()
    {
    }

    /**
     *
     * @param
     *            $document_uid
     */
    public function _assertProviderPhotoExists($document_uid)
    {
    }

    /**
     *
     * @param
     *            $provider_uid
     */
    public function _assertProviderBelongsToCurrentProfile($provider_uid)
    {
    }

    /**
     */
    public function _assertCurrentProviderBelongsToCurrentUser()
    {
    }

    /**
     * PRODUCT TRAITS
     */

    /**
     *
     * @param
     *            $product_uid
     */
    public function _assertStoreProductExists($product_uid)
    {
    }


    /**
     *
     * @param
     *            $question_uid
     * @param array ...$with            
     * @return mixed
     */
    public function _assertStoreProductQuestionExists($question_uid, ...$with)
    {
    }

    /**
     * REVIEW TRAITS
     */

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertStoreProductReviewCommentExists($comment_uid)
    {
    }


    /**
     * ORDER TRAITS
     */

    /**
     *
     * @param
     *            $order_uid
     * @param array ...$with            
     */
    public function _assertStoreOrderExists($order_uid, ...$with)
    {
    }

    /**
     */
    public function assertCurrentOrderBelongsToCurrentProfile($Order)
    {
        return true;
    }

    /**
     * ORDER ADDRESS TRAITS
     */

    /**
     *
     * @param
     *            $address_uid
     * @return mixed
     */
    public function _assertStoreAddressExists($address_uid)
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertStoreAddressBelongsToCurrentProfile()
    {
    }

    /**
     * WISHLIST TRAITS
     */

    /**
     *
     * @param
     *            $wishlist_uid
     * @param array ...$with            
     */
    public function _assertStoreWishlistExists($wishlist_uid, ...$with)
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertStoreWishlistBelongsToCurrentProfile()
    {
    }

    /**
     *
     * @param
     *            $item_uid
     * @param array ...$with            
     * @return mixed
     */
    public function _assertStoreWishlistItemExists($item_uid, ...$with)
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertStoreWishlistItemBelongsToCurrentWishlist()
    {
    }

    /**
     *
     * @param
     *            $product_uid
     * @param
     *            $product_variation_uid
     */
    public function _assertProductAlreadyInCurrentWishlist($product_uid, $product_variation_uid)
    {
    }

    /**
     * STORE CART TRAITS
     */

    /**
     *
     * @param
     *            $cart_uid
     * @param array ...$with            
     * @return mixed
     */
    public function _assertStoreCartExists($cart_uid, ...$with)
    {
    }

    /**
     * Check if products from cart has minimal quantity required.
     */
    public function _assertStoreCartHasProductsWithMinimalQuantity()
    {
    }

    /**
     * FEED TRAITS
     */

    /**
     *
     * @param
     *            $feed_uid
     * @return mixed
     */
    public function _assertFeedExists($feed_uid)
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertFeedSubscribeable()
    {
    }

    /**
     *
     * @param
     *            $owner_type
     * @param
     *            $owner_uid
     * @return mixed
     */
    public function _assertFeedOwnerExists( // TODO: DELETE NOT USED !!!!
        $owner_type,
        $owner_uid
    ) {
    }

    /**
     *
     * @return mixed
     */
    public function _assertCurrentFeedBelongsToCurrentProfile()
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertCurrentFeedBelongsToCurrentUser()
    {
    }

    /**
     */
    public function _assertCurrentProfileSubscribedToCurrentFeed()
    {
    }

    /**
     */
    public function _assertCurrentUserSubscribedToCurrentFeed()
    {
    }

    /**
     * PET ASSERTS
     */

    /**
     *
     * @param
     *            $pet_uid
     */
    public function _assertPetExists($pet_uid)
    {
    }

    /**
     *
     * @param
     *            $document_uid
     */
    public function _assertPetPhotoExists($document_uid)
    {
    }

    /**
     *
     * @param
     *            $name
     */
    public function _assertPetNameExists($name) // TODO: No longer used. Make sure of that, then delete it.
    {
    }

    /**
     *
     * @param string $uid            
     * @return mixed
     */
    public function _assertPetBelongsToCurrentProfile($uid) // TODO: Split into assertPetExists and then this... (not urgent).
    {
    }

    /**
     *
     * @return mixed
     */
    public function _assertCurrentPetBelongsToCurrentUser()
    {
    }

    /**
     *
     * @param
     *            $album_profile_uid
     */
    public function _assertCurrentAlbumBelongsToCurrentProfile($album_profile_uid)
    {
    }

    /**
     * FRIENDS
     */

    /**
     *
     * @param
     *            $sender_type
     * @param
     *            $sender_uid
     * @param
     *            $recipient_type
     * @param
     *            $recipient_uid
     */
    public function _assertFriendshipIsPossible($sender_type, $sender_uid, $recipient_type, $recipient_uid)
    {
    }

    /**
     *
     * @param
     *            $album_uid
     */
    public function _assertProviderAlbumExists($album_uid)
    {
    }

    /**
     *
     * @param
     *            $album_uid
     */
    public function _assertProfileAlbumExists($album_uid)
    {
    }

    /**
     *
     * @param
     *            $album_uid
     */
    public function _assertPetAlbumExists($album_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertProviderAlbumCommentExists($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertProfileAlbumCommentExists($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertPetAlbumCommentExists($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertActivityCommentExists($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertProviderAlbumCommentBelongsToCurrentUser($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertProfileAlbumCommentBelongsToCurrentUser($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertPetAlbumCommentBelongsToCurrentUser($comment_uid)
    {
    }

    /**
     *
     * @param
     *            $comment_uid
     */
    public function _assertActivityCommentBelongsToCurrentUser($comment_uid)
    {
    }
}
