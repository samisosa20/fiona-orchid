<?php

namespace App\Models;

use Orchid\Screen\AsSource;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use AsSource, Filterable, Attachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'content',
        'slug',
        'published',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published' => 'boolean',
    ];
}
