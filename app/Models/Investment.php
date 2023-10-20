<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\User;
use App\Models\Movement;
use App\Models\Currency;
use App\Models\InvestmentAppreciation;

class Investment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'init_amount',
        'end_amount',
        'badge_id',
        'date_investment',
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
        'init_amount' => 'float',
        'end_amount' => 'float',
        'date_investment' => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function movements()
    {
        return $this->hasMany(Movement::class, 'investment_id', 'id')->with(['account', 'category', 'event', 'transferIn', 'transferOut']);
    }
    
    public function appreciations()
    {
        return $this->hasMany(InvestmentAppreciation::class, 'investment_id', 'id');
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'badge_id');
    }
    
}
