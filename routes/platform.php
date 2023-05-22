<?php

declare(strict_types=1);

use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;

use App\Models\Account;

use App\Orchid\Screens\Account\AccountListScreen;
use App\Orchid\Screens\Account\AccountEditScreen;
use App\Orchid\Screens\Account\MovementsListScreen;
use App\Orchid\Screens\Heritage\HeritageListScreen;
use App\Orchid\Screens\Heritage\HeritageEditScreen;
use App\Orchid\Screens\Heritage\HeritageYearListScreen;
use App\Orchid\Screens\Event\EventListScreen;
use App\Orchid\Screens\Event\EventEditScreen;
use App\Orchid\Screens\Movement\MovementEditScreen;
use App\Orchid\Screens\Category\CategoryListScreen;
use App\Orchid\Screens\Category\CategoryEditScreen;
use App\Orchid\Screens\Report\ReportScreen;
use App\Orchid\Screens\Payment\PaymentListScreen;
use App\Orchid\Screens\Payment\PaymentEditScreen;



/*
|--------------------------------------------------------------------------
| Dashboard Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the need "dashboard" middleware group. Now create something great!
|
*/

// Main
Route::screen('/main', ReportScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Profile'), route('platform.profile')));

// Platform > System > Users > User
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit')
    ->breadcrumbs(fn (Trail $trail, $user) => $trail
        ->parent('platform.systems.users')
        ->push($user->name, route('platform.systems.users.edit', $user)));

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.users')
        ->push(__('Create'), route('platform.systems.users.create')));

// Platform > System > Users
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Users'), route('platform.systems.users')));

// Platform > System > Roles > Role
Route::screen('roles/{role}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(fn (Trail $trail, $role) => $trail
        ->parent('platform.systems.roles')
        ->push($role->name, route('platform.systems.roles.edit', $role)));

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.systems.roles')
        ->push(__('Create'), route('platform.systems.roles.create')));

// Platform > System > Roles
Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Roles'), route('platform.systems.roles')));


/** --------------------------------------------------- */

// Platform > accounts
Route::screen('/accounts', AccountListScreen::class)
    ->name('platform.accounts')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Accounts'), route('platform.accounts')));

// Platform > Accounts > Movements
Route::screen('accounts/{account}/movements', MovementsListScreen::class)
->name('platform.accounts.movements')
->breadcrumbs(fn (Trail $trail, $account) => $trail
    ->parent('platform.accounts')
    ->push($account->name, route('platform.accounts.movements', $account)));

// Platform > Accounts > Edit
Route::screen('accounts/{account}/edit', AccountEditScreen::class)
->name('platform.accounts.edit')
->breadcrumbs(fn (Trail $trail, $account) => $trail
    ->parent('platform.accounts')
    ->push($account->name, route('platform.accounts.edit', $account)));

// Platform > accounts > Create
Route::screen('accounts/create', AccountEditScreen::class)
->name('platform.accounts.create')
->breadcrumbs(fn (Trail $trail) => $trail
    ->parent('platform.accounts')
    ->push(__('Create'), route('platform.accounts.create')));

// Platform > Heritage
Route::screen('/heritages', HeritageListScreen::class)
    ->name('platform.heritages')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Heritages'), route('platform.heritages')));

// Platform > Heritage > Edit
Route::screen('heritages/{year}/year', HeritageYearListScreen::class)
->name('platform.heritages.year')
->breadcrumbs(fn (Trail $trail,string $year) => $trail
    ->parent('platform.heritages')
    ->push($year, route('platform.heritages.year', $year)));

// Platform > Heritage > Edit
Route::screen('heritages/{heritage}/edit', HeritageEditScreen::class)
->name('platform.heritages.edit')
->breadcrumbs(fn (Trail $trail, $heritage) => $trail
    ->parent('platform.heritages')
    ->push($heritage->name, route('platform.heritages.edit', $heritage)));

// Platform > Heritage > Create
Route::screen('heritages/create', HeritageEditScreen::class)
->name('platform.heritages.create')
->breadcrumbs(fn (Trail $trail) => $trail
    ->parent('platform.heritages')
    ->push(__('Create'), route('platform.heritages.create')));

// Platform > Event
Route::screen('/events', EventListScreen::class)
    ->name('platform.events')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Events'), route('platform.events')));

// Platform > Event > Edit
Route::screen('events/{event}/edit', EventEditScreen::class)
->name('platform.events.edit')
->breadcrumbs(fn (Trail $trail, $event) => $trail
    ->parent('platform.events')
    ->push($event->name, route('platform.events.edit', $event)));

// Platform > Event > Create
Route::screen('events/create', EventEditScreen::class)
->name('platform.events.create')
->breadcrumbs(fn (Trail $trail) => $trail
    ->parent('platform.events')
    ->push(__('Create'), route('platform.events.create')));

// Platform > Movements > Create
Route::screen('movement/create', MovementEditScreen::class)
->name('platform.movement.create')
->breadcrumbs(fn (Trail $trail) => $trail
    ->parent('platform.index')
    ->push(__('Create'), route('platform.movement.create')));

// Platform > Movements > Edit
Route::screen('movement/{movement}/edit', MovementEditScreen::class)
->name('platform.movement.edit')
->breadcrumbs(fn (Trail $trail, $movement) => $trail
    ->parent('platform.index')
    ->push(__('Movement'))
    ->push(__('Edit'), route('platform.movement.edit', $movement)));

// Platform > Category
Route::screen('/categories', CategoryListScreen::class)
    ->name('platform.categories')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Categories'), route('platform.categories')));

// Platform > Category > Edit
Route::screen('categories/{category}/edit', CategoryEditScreen::class)
->name('platform.categories.edit')
->breadcrumbs(fn (Trail $trail, $category) => $trail
    ->parent('platform.categories')
    ->push($category->name, route('platform.categories.edit', $category)));

// Platform > Category > Create
Route::screen('categories/create', CategoryEditScreen::class)
->name('platform.categories.create')
->breadcrumbs(fn (Trail $trail) => $trail
    ->parent('platform.categories')
    ->push(__('Create'), route('platform.categories.create')));

// Platform > Payment
Route::screen('/payments', PaymentListScreen::class)
    ->name('platform.payments')
    ->breadcrumbs(fn (Trail $trail) => $trail
        ->parent('platform.index')
        ->push(__('Payments'), route('platform.payments')));

// Platform > Payment > Edit
Route::screen('payments/{payment}/edit', PaymentEditScreen::class)
->name('platform.payments.edit')
->breadcrumbs(fn (Trail $trail, $payment) => $trail
    ->parent('platform.payments')
    ->push(__('Edit'), route('platform.payments.edit', $payment)));

// Platform > Payment > Create
Route::screen('payments/create', PaymentEditScreen::class)
->name('platform.payments.create')
->breadcrumbs(fn (Trail $trail) => $trail
    ->parent('platform.payments')
    ->push(__('Create'), route('platform.payments.create')));
