<?php namespace Gzero\Entity;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Gzero\Entity\Presenter\UserPresenter;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * This file is part of the GZERO CMS package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Class User
 *
 * @package    Gzero\Entity
 * @author     Adrian Skierniewski <adrian.skierniewski@gmail.com>
 * @copyright  Copyright (c) 2014, Adrian Skierniewski
 */
class User extends Base implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword;

    /**@TODO proper method for adding new fillable fields from package with migrations */
    /**
     * @var array
     */
    protected $fillable = [
        'email',
        'nickName',
        'firstName',
        'lastName',
        'password',
        'hasSocialIntegrations'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password'];

    /**
     * The roles that belong to the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'ACLUserRoles')->withTimestamps();
    }

    /**
     * It checks if given user have specified permission
     *
     * @param string $permission Permission name
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        $permissionsMap = cache()->get('permissions:' . $this->id, null);
        if ($permissionsMap === null) {
            $permissionsMap = $this->buildPermissionsMap();
            cache()->forever('permissions:' . $this->id, $permissionsMap);
        }
        return in_array($permission, $permissionsMap);
    }

    /**
     * Get the unique identifier for the user.
     *
     * @return mixed
     */
    public function getAuthIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the token value for the "remember me" session.
     *
     * @return string
     */
    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    /**
     * Set the token value for the "remember me" session.
     *
     * @param string $value token
     *
     * @return void
     */
    public function setRememberToken($value)
    {
        $this->rememberToken = $value;
    }

    /**
     * Get the column name for the "remember me" token.
     *
     * @return string
     */
    public function getRememberTokenName()
    {
        return 'rememberToken';
    }

    /**
     * Get the e-mail address where password reminders are sent.
     *
     * @return string
     */
    public function getReminderEmail()
    {
        return $this->email;
    }

    /**
     * Return a created presenter.
     *
     * @return \Robbo\Presenter\Presenter
     */
    public function getPresenter()
    {
        return new UserPresenter($this);
    }

    /**
     * It build permission map.
     * Later we store this map cache.
     *
     * @return array
     */
    private function buildPermissionsMap()
    {
        $permissionsMap = [];
        $roles          = $this->roles()->with('permissions')->get()->toArray();
        foreach ($roles as $role) {
            if (!empty($role['permissions'])) {
                foreach ($role['permissions'] as $permission) {
                    $permissionsMap[] = $permission['name'];
                }
            }
        }
        return array_unique($permissionsMap);
    }
}
