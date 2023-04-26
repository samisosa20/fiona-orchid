<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\Movement;
use App\Models\Currency;

class Account extends Model
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
        'badge_id',
        'init_amount',
        'type',
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
        'init_amount' => 'float'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function movements()
    {
        return $this->hasMany(Movement::class, 'account_id', 'id')->with(['account', 'category', 'event', 'transfer']);
    }
    
    public function scopeWithBalance($query)
    {
        $query->addSelect([
            'balance' => Movement::selectRaw('cast(ifnull(sum(amount), 0) as double)')
            ->whereColumn('movements.account_id', 'accounts.id')
        ]);
    }
    
    public function scopeWithIncomeExpensiveWithoutTransf($query)
    {
        $query->addSelect([
            'incomes' => Movement::selectRaw('cast(ifnull(sum(amount), 0) as double)')
            ->whereColumn('movements.account_id', 'accounts.id')
            ->whereHas('category', function ($query){
                $query->where([
                    ['group_id', '<>', env('GROUP_TRANSFER_ID')]
                ]);
            })
            ->where([
                ['amount', '>', 0]
            ]),
            'expensives' => Movement::selectRaw('cast(ifnull(sum(amount), 0) as double)')
            ->whereColumn('movements.account_id', 'accounts.id')
            ->whereHas('category', function ($query){
                $query->where([
                    ['group_id', '<>', env('GROUP_TRANSFER_ID')]
                ]);
            })
            ->where([
                ['amount', '<', 0]
            ])
        ]);
    }
    
    public function currency()
    {
        return $this->hasOne(Currency::class, 'id', 'badge_id');
    }
}
