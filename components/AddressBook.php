<?php namespace RainLab\UserPlus\Components;

use Cms;
use Auth;
use Flash;
use RainLab\User\Models\User;
use Cms\Classes\ComponentBase;
use ApplicationException;
use ForbiddenException;

/**
 * AddressBook manages user profile information.
 */
class AddressBook extends ComponentBase
{
    /**
     * @var array addresses cache
     */
    protected $addresses;

    /**
     * componentDetails
     */
    public function componentDetails()
    {
        return [
            'name' => "Address Book",
            'description' => "Manages addresses for the user."
        ];
    }

    /**
     * defineProperties
     */
    public function defineProperties()
    {
        return [];
    }

    /**
     * onUpdateAddress
     */
    public function onUpdateAddress()
    {
        $user = $this->user();
        if (!$user) {
            throw new ForbiddenException;
        }

        // Create new address
        if (post('address_create')) {
            $address = $user->addresses()->make();
        }
        // Lookup existing address
        else {
            $addressId = post('address_id');
            if (!$addressId || !$user->addresses) {
                throw new ApplicationException(__("Address not found."));
            }

            $address = $user->addresses->find(post('address_id'));
            if (!$address) {
                throw new ApplicationException(__("Address not found."));
            }
        }

        // Update or delete address
        if (post('address_delete') && $address->exists) {
            $address->delete();
        }
        else {
            $address->fill(array_except(post(), ['address_id', 'address_create']));
            $address->save();
        }

        // Refresh addresses stored in memory
        $user->unsetRelations();

        if ($flash = Cms::flashFromPost(__("Your address book has been updated."))) {
            Flash::success($flash);
        }

        if ($redirect = Cms::redirectFromPost()) {
            return $redirect;
        }
    }

    /**
     * user returns the logged in user
     */
    public function user(): ?User
    {
        return Auth::user();
    }

    /**
     * addresses returns addresses owned by the user
     */
    public function addresses()
    {
        return $this->addresses ??= Auth::user()?->addresses;
    }

    /**
     * hasAddresses
     */
    public function hasAddresses()
    {
        return count($this->addresses() ?: []) > 0;
    }
}
