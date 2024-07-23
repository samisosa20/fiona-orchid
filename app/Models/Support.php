<?php

namespace App\Models;

use App\Notifications\SupportCopyCreated;
use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

use App\Notifications\SupportCreated;

class Support extends Model
{
    use AsSource, Filterable, Attachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject',
        'message',
        'user_id',
    ];

    protected static function booted()
    {
        static::created(function ($support) {
            $user = $support->user;

            $user_suppor = User::find(env('SUPPORT_USER_ID'));
            Notification::send($user, new SupportCreated($support));
            Notification::send($user_suppor, new SupportCopyCreated($support, $user));
        });
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
