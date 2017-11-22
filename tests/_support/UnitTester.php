<?php namespace Cms;

use Cms\_generated\UnitTesterActions;
use Gzero\Core\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = null)
 *
 * @SuppressWarnings(PHPMD)
 */
class UnitTester extends \Codeception\Actor {
    use UnitTesterActions;

    /**
     * Login as admin in to app
     *
     * @return User
     */
    public function loginAsAdmin()
    {
        return $this->login('admin@gzero.pl', 'test');
    }

    /**
     * Login as normal user
     *
     * @return User
     */
    public function loginAsUser()
    {
        $user = $this->haveUser(['password' => Hash::make('secret')]);
        return $this->login($user->email, 'secret');
    }

    /**
     * Login in to app
     *
     * @param $email
     * @param $password
     */
    public function login($email, $password)
    {
        $I = $this;
        $I->amLoggedAs(['email' => $email, 'password' => $password], 'web');
        $I->seeAuthentication();
    }
}
