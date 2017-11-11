<?php namespace Gzero\Cms\Model;

use Faker\Provider\Base;
use Gzero\Base\Models\User;

class Role extends Base {

    /**
     * @var string
     */
    protected $table = 'acl_roles';

    /**
     * @var array
     */
    protected $fillable = [
        'name'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    /**
     * The users that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'acl_user_role')->withTimestamps();
    }

    /**
     * The permissions that belong to the role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'acl_permission_role')->withTimestamps();
    }

}
