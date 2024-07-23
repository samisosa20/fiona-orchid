<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

use App\Models\User;
use App\Models\Account;
use App\Models\Category;
use App\Models\Event;
use App\Models\Investment;

class Movement extends Model
{
    use AsSource, Filterable, Attachable;

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
        'investment_id',
        'user_id',
        'add_withdrawal',
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
        'investment_id',
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
        'add_withdrawal' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function category()
    {
        return $this->hasOne(Category::class, 'id', 'category_id')->withTrashed();
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id')->withTrashed();
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
    public function investment()
    {
        return $this->hasOne(Investment::class, 'id', 'investment_id');
    }

    public function scopeFilter($query, $request)
    {
        $query->when($request->query('category'), function ($query) use ($request) {
            $query->whereHas('category', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->query('category') . '%');
            });
        })
            ->when($request->query('amount'), function ($query) use ($request) {
                $query->where('amount', '=', $request->query('amount'));
            })
            ->when($request->query('description'), function ($query) use ($request) {
                $query->where('description', 'like', '%' . $request->query('description') . '%');
            })
            ->when($request->query('date'), function ($query) use ($request) {
                $query->whereDate('date_purchase', '=', $request->query('date'));
            })
            ->when($request->query('start_date'), function ($query) use ($request) {
                $query->whereDate('date_purchase', '>=', $request->query('start_date'));
            })
            ->when($request->query('end_date'), function ($query) use ($request) {
                $query->whereDate('date_purchase', '<=', $request->query('end_date'));
            })
            ->when($request->query('event_id'), function ($query) use ($request) {
                $query->whereHas('event', function ($query) use ($request) {
                    $query->where('id', '=', $request->query('event_id'));
                });
            });
    }
}
