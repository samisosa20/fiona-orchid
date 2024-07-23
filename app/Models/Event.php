<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Filters\Types\WhereDateStartEnd;
use Orchid\Filters\Filterable;

use App\Models\User;
use App\Models\Movement;

class Event extends Model
{
    use AsSource, Filterable, Attachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'end_event',
        'user_id',
    ];


    /**
     * The attributes for which you can use filters in url.
     *
     * @var array
     */
    protected $allowedFilters = [
        'id' => Where::class,
        'name' => Like::class,
        'end_event' => WhereDateStartEnd::class,
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
        'end_event' => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
    
    public function movements()
    {
        return $this->hasMany(Movement::class, 'event_id', 'id')->with(['account', 'category', 'event', 'transferIn', 'transferOut']);
    }
    
    public function scopeWithBalance($query)
    {
        $query->addSelect([
            'balance' => Movement::selectRaw('cast(ifnull(sum(amount), 0) as float)')
            ->whereColumn('movements.event_id', 'events.id')
        ]);
    }
    
}
