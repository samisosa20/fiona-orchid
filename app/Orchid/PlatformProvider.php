<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * @param Dashboard $dashboard
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * @return Menu[]
     */
    public function registerMainMenu(): array
    {
        return [
            Menu::make(__('Movements'))
                ->icon('move')
                ->route('platform.movement.create'),

            Menu::make(__('Accounts'))
                ->icon('monitor')
                ->route('platform.accounts'),

            Menu::make(__('Events'))
                ->icon('hourglass')
                ->route('platform.events'),

            Menu::make(__('Budget'))
                ->icon('flag')
                ->list([
                    Menu::make(__('Create'))
                    ->icon('plus')
                    ->route('platform.budgets'),
                    Menu::make(__('Report'))
                    ->icon('docs')
                    ->route('platform.budgets.report'),
                ]),
            
            Menu::make(__('Heritages'))
                ->icon('book-open')
                ->route('platform.heritages'),

            Menu::make(__('Settings'))
                ->icon('code')
                ->list([
                    Menu::make(__('Categories'))
                    ->icon('bag')
                    ->route('platform.categories'),
                    Menu::make(__('Payments'))
                    ->icon('heart')
                    ->route('platform.payments'),
                ])
                ->title(__('Settings')),


            Menu::make(__('Users'))
                ->icon('user')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access rights')),

            Menu::make(__('Roles'))
                ->icon('lock')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles'),
        ];
    }

    /**
     * @return Menu[]
     */
    public function registerProfileMenu(): array
    {
        return [
            Menu::make(__('Profile'))
                ->route('platform.profile')
                ->icon('user'),
        ];
    }

    /**
     * @return ItemPermission[]
     */
    public function registerPermissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}
