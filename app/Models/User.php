<?php

namespace App\Models;

use Orchid\Platform\Models\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Notifications\Notifiable;

use App\Models\Category;
use App\Models\Currency;

class User extends Authenticatable implements JWTSubject, MustVerifyEmailContract
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'badge_id',
        'permissions',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'permissions',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions'          => 'array',
        'email_verified_at'    => 'datetime',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id',
        'name',
        'email',
        'badge_id',
        'permissions',
    ];

    /**
     * The attributes for which can use sort in url.
     *
     * @var array
     */
    protected $allowedSorts = [
        'id',
        'name',
        'email',
        'badge_id',
        'updated_at',
        'created_at',
    ];

    public function transferId()
    {
        return $this->hasOne(Category::class, 'user_id', 'id')->where('group_id', '=', env('GROUP_TRANSFER_ID'));
    }

    public function getJWTIdentifier()
    {
        return $this->getKey(); // Devuelve la clave primaria del usuario
    }

    public function getJWTCustomClaims()
    {
        return []; // Puedes agregar datos personalizados al token JWT si lo deseas
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'badge_id');
    }
}
