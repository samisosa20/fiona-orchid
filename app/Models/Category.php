<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'group_id',
        'category_id',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function categoryFather()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->withTrashed();
    }

    public function subCategories()
    {
        return $this->hasMany(Category::class, 'category_id', 'id')->withTrashed();
    }

    public function scopeListCategory($query, $user)
    {
        return $query->where([
            ['categories.user_id', $user],
            ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
        ])
            ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(b.name, "\n ", categories.name)) as title, categories.category_id as category_father')
            ->leftJoin('categories as b', 'b.id', 'categories.category_id')
            ->orderBy('categories.name');
    }
}
