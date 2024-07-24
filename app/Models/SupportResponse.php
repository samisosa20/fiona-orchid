<?php

namespace App\Models;

use App\Notifications\SupportResponseNotification;
use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class SupportResponse extends Model
{
    use AsSource, Filterable, Attachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'support_id',
    ];

    protected static function booted()
    {
        static::created(function ($response) {
            $user = $response->support->user;

            Notification::send($user, new SupportResponseNotification($response));
        });
    }

    public function support()
    {
        return $this->hasOne(Support::class, 'id', 'support_id');
    }
}
