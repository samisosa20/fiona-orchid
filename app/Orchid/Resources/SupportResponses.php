<?php

namespace App\Orchid\Resources;

use App\Models\Support;
use App\Models\SupportResponse;

use Orchid\Crud\Resource;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\TD;

class SupportResponses extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = SupportResponse::class;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(): array
    {
        return [
            Select::make('support_id')
                ->fromModel(Support::selectRaw("CONCAT(supports.id, ' - ', subject, ' - ', users.email) as name, supports.id")
                ->join("users", "supports.user_id", "=", "users.id")
                , 'name')
                ->empty()
                ->required()
                ->title(__('Support')),
            Quill::make('content')
                ->title('content')
                ->required()
                ->base64(true),
        ];
    }

    /**
     * Get the columns displayed by the resource.
     *
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('id'),

            TD::make('support_id'),

            TD::make('created_at', 'Date of creation')
                ->render(function ($model) {
                    return $model->created_at->toDateTimeString();
                }),

            TD::make('updated_at', 'Update date')
                ->render(function ($model) {
                    return $model->updated_at->toDateTimeString();
                }),
        ];
    }

    /**
     * Get the sights displayed by the resource.
     *
     * @return Sight[]
     */
    public function legend(): array
    {
        return [];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [];
    }
}
