<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Event;

class Movement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_id',
        'category_id',
        'description',
        'amount',
        'trm',
        'date_purchase',
        'transfer_id',
        'event_id',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'user_id',
        'account_id',
        'category_id',
        'transfer_id',
        'event_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_purchase' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'amount' => 'float',
        'trm' => 'float',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }
    
    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }
    
    public function transferOut()
    {
        return $this->hasOne(Movement::class, 'id', 'transfer_id')->with('account');
    }

    public function transferIn()
    {
        return $this->hasOne(Movement::class, 'transfer_id', 'id')->with('account');
    }
    
    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
    
}
