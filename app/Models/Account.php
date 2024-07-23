<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;

use App\Models\User;
use App\Models\Movement;
use App\Models\Currency;
use App\Models\TypeAccount;

class Account extends Model
{
    use AsSource, Filterable, Attachable, SoftDeletes;

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
        'limit',
        'interest',
        'type_id',
        'user_id',
    ];

    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'description' => Like::class,
        'name' => Like::class,
        'type_id' => Where::class,
        'badge_id' => Where::class,
        'user_id' => Where::class,
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
        'init_amount' => 'float',
        'limit' => 'float',
        'interest' => 'float'
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

    public function type()
    {
        return $this->hasOne(TypeAccount::class, 'id', 'type_id');
    }
}
