<?php

namespace App\Orchid;

use App\Models\Games;
use App\Models\Players;
use Illuminate\Support\Facades\Auth;
use Orchid\Platform\Dashboard;
use Orchid\Screen\Actions\Menu;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
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
            Menu::make('Players')
                ->icon('people')
                ->slug('player')
                ->badge(function () {
                    return Players::query()->select('id')->count();
                }, Color::SUCCESS())
                ->list([
                    Menu::make('All Players')->route('platform.players')->permission('platform.players'),
                    Menu::make('User Sessions')->route('platform.players.sessions')->slug('player_sessions')->permission('platform.players.sessions'),
                    Menu::make('Transactions')->route('platform.players.transactions')->slug('player_transactions')->permission('platform.players.transactions'),
                    Menu::make('Suspicions')->route('platform.suspicions')->slug('player_suspicions')->permission('platform.suspicions'),
                    Menu::make('Documents')->route('platform.players.documents')->slug('player_documents')->permission('platform.players.documents'),
                    Menu::make('Dormant Players')->route('platform.players.dormant')->slug('player_dormant')->permission('platform.players.dormant'),
                    Menu::make('Filters')->route('platform.filters.players')->slug('player_filter')->permission('platform.filters.players'),
                    Menu::make('Groups')->route('platform.filters.groups')->slug('player_group')->permission('platform.filters.groups'),
                ]),


//            Menu::make('Bets')
//                ->route('platform.players.players_bets')
//                ->place('player'),


            Menu::make('Games')
                ->icon('game-controller')
                ->slug('games')
                ->list([
                    Menu::make('List of Games')
                        ->route('platform.games')->permission('platform.games'),
                    Menu::make('Game Sessions')
                        ->route('platform.games.session')
                        ->slug('game_sessions')->permission('platform.games.session'),
                    Menu::make('Bets')
                        ->route('platform.games.bets')
                        ->slug('game_bets')->permission('platform.games.bets'),
                    Menu::make('Filters')
                        ->route('platform.filters.games')
                        ->permission('platform.filters.games')
                        ->slug('Filter Games'),
                    Menu::make('Denomination')
                        ->route('platform.games.denomination')
                        ->slug('denomination'),
                ]),


            Menu::make('Bonuses')
                ->icon('star')
                ->slug('bonus')
                ->list([
                    Menu::make('Bonuses & Freespin')
                        ->route('platform.bonuses')->permission('platform.bonuses'),

                    Menu::make('Issued Freespin')
                        ->route('platform.bonuses.freespin')->permission('platform.bonuses.freespin'),

                    Menu::make('Issued Bonuses')
                        ->slug('issued')
                        ->route('platform.bonuses.issued')->permission('platform.bonuses.issued'),

                    Menu::make('History')
                        ->route('platform.bonuses.history')->permission('platform.bonuses.history'),
                ]),


            Menu::make('Payments')
                ->icon('wallet')
                ->slug('payments')
                ->list([
                    Menu::make('Payments')
                        ->slug('payments_all')
                        ->route('platform.payments')
                    ->permission('platform.payments'),
                    Menu::make('Crypto Address')
                        ->route('platform.payments.crypto')->permission('platform.payments.crypto'),
                    Menu::make('Payment Systems')
                        ->route('platform.payments.systems')->permission('platform.payments.systems'),
                    Menu::make('Cash Flow Transactions')
                        ->route('platform.payments.cash_flow_transactions')->permission('platform.payments.cash_flow_transactions'),
                ]),

            Menu::make('Finance')
                ->icon('money')
                ->slug('finance')
                ->list([
                    Menu::make('Game Providers Report')
                        ->slug('finance_game-providers-report')
                        ->route('platform.finance.game-providers-report')->permission('platform.finance.game-providers-report'),
                    Menu::make('Revenue Report')
                        ->route('platform.finance.revenue-report')->permission('platform.finance.revenue-report'),
                    Menu::make('Income Statement Report')
                        ->route('platform.finance.income-statement-report')->permission('platform.finance.income-statement-report'),
                    Menu::make('Exchange Rates')
                        ->route('platform.finance.exchange-rates')->permission('platform.finance.exchange-rates'),
                    Menu::make('Gifts')
                        ->route('platform.finance.gifts')->permission('platform.finance.gifts'),
                    Menu::make('KPI report')
                        ->route('platform.finance.kpi-report')->permission('platform.finance.kpi-report'),
                    Menu::make('Cash Report')
                        ->route('platform.finance.cash-report')->permission('platform.finance.cash-report'),
                    Menu::make('New Cash Report')
                        ->canSee(false)
                        ->route('platform.finance.new-cash-report')->permission('platform.finance.new-cash-report'),
                    Menu::make('Balance Corrections')
                        ->route('platform.finance.balance-corrections')->permission('platform.finance.balance-corrections'),
                    Menu::make('Jackpot contributions & Gifts report')
                        ->canSee(false)
                        ->route('platform.finance.jackpot-and-gifts')->permission('platform.finance.jackpot-and-gifts'),
                    Menu::make('Mga Report')
                        ->route('platform.finance.mga-report')->permission('platform.finance.mga-report'),
                ]),


            Menu::make('Marketing')
                ->icon('euro')
                ->slug('marketing')
                ->list([
                    Menu::make('Churn Rate Report')
                        ->route('platform.finance.churn-rate-report')->permission('platform.finance.churn-rate-report'),
                    Menu::make('Games Popularity Report')
                        ->route('platform.finance.games-popularity-report')->permission('platform.finance.games-popularity-report'),
                    Menu::make('Games Related Report')
                        ->route('platform.finance.games-report')->permission('platform.finance.games-report'),
                    Menu::make('Games Sessions Report')
                        ->route('platform.finance.games-sessions-report')->permission('platform.finance.games-sessions-report'),
                ]),


            Menu::make('Feed')
                ->icon('feed')
                ->slug('feed')
                ->list([
                    Menu::make('Events')
                        ->slug('feed_events')
                        ->route('platform.feed.events')->permission('platform.feed.events'),
                    Menu::make('Exports')
                        ->slug('feed_exports')
                        ->route('platform.feed.exports')->permission('platform.feed.exports'),
                    Menu::make('Lucky Spin')
                        ->slug('feed_lucky_spin')
                        ->route('platform.feed.lucky'),
                ]),


            Menu::make('Static')
                ->icon('paste')
                ->slug('static')
                ->list([
                    Menu::make('Pages')
                        ->route('platform.pages')->permission('platform.pages'),

                    Menu::make('Blocks')
                        ->route('platform.blocks')->permission('platform.pages'),

                    Menu::make('Landings')
                        ->route('platform.landings'),

                    Menu::make('Mail Texts')
                        ->route('platform.mail'),

                    Menu::make('Mail Templates')
                        ->route('platform.mail-templates'),

                    Menu::make('Mail Statistics')
                        ->route('platform.mail-statistics'),
                ]),


            Menu::make('Languages')
                ->icon('globe')
                ->slug('locale')
                ->route('platform.locale'),


            Menu::make('Attendances')
                ->icon('friends')
                ->slug('attendances')
                ->permission('platform.attendances')
                ->route('platform.attendances'),
//
//            Menu::make('Filters')
//                ->icon('filter')
//                ->slug('filter')
//                ->list(),


            Menu::make('Settings')
                ->icon('settings')
                ->slug('settings')
                ->list([
                    Menu::make(__('Users'))
                        ->route('platform.systems.users')
                        ->permission('platform.systems.users'),

                    Menu::make(__('Roles'))
                        ->route('platform.systems.roles')
                        ->permission('platform.systems.roles'),
                ]),

            Menu::make('Factory')
                ->icon('database')
                ->slug('factory')
                ->list([
                    Menu::make(__('Players'))
                        ->route('platform.factory.players'),
                ]),
        ];
    }

    /**
     * @return Menu[]
     */
    public function registerProfileMenu(): array
    {
        return [
            Menu::make('Profile')
                ->route('platform.profile')
                ->class('dropdown-item')
                ->icon('user'),
        ];
    }

    /**
     * @return Menu[]
     */
    public function registerSystemMenu(): array
    {
        return [
            Menu::make(__('Access rights'))
                ->icon('lock')
                ->slug('Auth')
                ->active('platform.systems.*')
                ->permission('platform.systems.index')
                ->sort(1000)->list([
                    Menu::make(__('Users'))
                        ->icon('user')
                        ->route('platform.systems.users')
                        ->permission('platform.systems.users')
                        ->sort(1000)
                        ->title(__('All registered users')),

                    Menu::make(__('Roles'))
                        ->icon('lock')
                        ->route('platform.systems.roles')
                        ->permission('platform.systems.roles')
                        ->sort(1000)
                        ->title(__('A Role defines a set of tasks a user assigned the role is allowed to perform.')),
                ]),

        ];
    }

    /**
     * @return ItemPermission[]
     */
    public function registerPermissions(): array
    {
        return [
            ItemPermission::group(__('Systems'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),

            ItemPermission::group('Attendances')
                ->addPermission('platform.attendances', 'Attendances'),

            ItemPermission::group('Players Page Permissions')
                ->addPermission('platform.players', 'Players')
                ->addPermission('platform.players.profile', 'Players View')
                ->addPermission('platform.players.new_player', 'New Player')
                ->addPermission('platform.players.transactions', 'Player Transactions')
                ->addPermission('platform.players.dormant', 'Player Dormant')
                ->addPermission('platform.players.documents', 'Player Documents')
                ->addPermission('platform.players.change', 'Player Change History')
                ->addPermission('platform.players.edit', 'Player Edit')
                ->addPermission('platform.players.balance', 'Player Balance')
                ->addPermission('platform.players.limits', 'Player Limits')
                ->addPermission('platform.players.session', 'Player Session')
                ->addPermission('platform.players.sessions', 'Player Sessions')
                ->addPermission('platform.players.suspicion', 'Player Suspicion')
                ->addPermission('platform.players.games', 'Player Games')
                ->addPermission('platform.players.bonuses', 'Player Bonuses')
                ->addPermission('platform.suspicions', 'Suspicions'),

            ItemPermission::group('Games Page Permissions')
                ->addPermission('platform.games', 'Games')
                ->addPermission('platform.games.view', 'Games View')
                ->addPermission('platform.games.edit', 'Games Edit')
                ->addPermission('platform.games.create', 'Games Create')
                ->addPermission('platform.games.bets', 'Games Bets')
                ->addPermission('platform.games.bet.view', 'Games Bets View')
                ->addPermission('platform.games.session', 'Games Session'),

            ItemPermission::group('Bonuses Page Permissions')
                ->addPermission('platform.bonuses', 'Bonuses')
                ->addPermission('platform.bonuses.issued', 'issued Bonuses')
                ->addPermission('platform.bonuses.freespin', 'Bonuses freespin')
                ->addPermission('platform.bonuses.freespin.view', 'Bonuses freespin view')
                ->addPermission('platform.bonuses.history', 'Bonuses history')
                ->addPermission('platform.bonuses.history.view', 'Bonuses history View')
                ->addPermission('platform.bonuses.issued.view', 'Bonuses issued View'),

            ItemPermission::group('Payments Page Permissions')
                ->addPermission('platform.payments', 'Payments')
                ->addPermission('platform.payments.crypto', 'Payments Crypto')
                ->addPermission('platform.payments.systems', 'Payments Systems')
                ->addPermission('platform.payments.systems.sorting', 'Payments Systems sorting')
                ->addPermission('platform.payments.cash_flow_transactions', 'Payments cash flow transactions')
                ->addPermission('platform.payments.btc_address.view', 'Payments Crypto View')
                ->addPermission('platform.payments.view', 'Payments View'),

            ItemPermission::group('Page Permissions')
                ->addPermission('platform.pages', 'Page')
                ->addPermission('platform.pages.edit', 'Page Edit'),

            ItemPermission::group('Filters Page Permissions')
                ->addPermission('platform.filters.players', 'Filters Players')
                ->addPermission('platform.filters.players.create', 'Filters Players Create')
                ->addPermission('platform.filters.players.edit', 'Players Edit')
                ->addPermission('platform.filters.games', 'Games')
                ->addPermission('platform.filters.groups', 'Groups'),

            ItemPermission::group('Finance Page Permissions')
                ->addPermission('platform.finance.game-providers-report', 'Game providers report')
                ->addPermission('platform.finance.revenue-report', 'Revenue Report')
                ->addPermission('platform.finance.income-statement-report', 'Income statement report')
                ->addPermission('platform.finance.exchange-rates', 'exchange-rates')
                ->addPermission('platform.finance.gifts', 'gifts')
                ->addPermission('platform.finance.kpi-report', 'kpi-report')
                ->addPermission('platform.finance.cash-report', 'cash-report')
                ->addPermission('platform.finance.new-cash-report', 'new-cash-report')
                ->addPermission('platform.finance.balance-corrections', 'balance-corrections')
                ->addPermission('platform.finance.jackpot-and-gifts', 'jackpot-and-gifts')
                ->addPermission('platform.finance.mga-report', 'mga-report'),

            ItemPermission::group('Marketing Page Permissions')
                ->addPermission('platform.finance.bonuses-report', 'bonuses-report')
                ->addPermission('platform.finance.churn-rate-report', 'churn-rate-report')
                ->addPermission('platform.finance.games-popularity-report', 'games-popularity-report')
                ->addPermission('platform.finance.games-report', 'games-report')
                ->addPermission('platform.finance.games-sessions-report', 'games-sessions-report')
                ->addPermission('platform.finance.new-players-report', 'new-players-report')
                ->addPermission('platform.finance.players-limits', 'players-limits')
                ->addPermission('platform.finance.players-report', 'players-report')
                ->addPermission('platform.finance.players-stats', 'players-stats')
                ->addPermission('platform.finance.players-summary-report', 'players-summary-report'),

            ItemPermission::group('Feed Page Permissions')
                ->addPermission('platform.feed.events', 'events')
                ->addPermission('platform.feed.exports', 'exports'),

            ItemPermission::group('Attendances Page Permissions')
                ->addPermission('platform.attendances', 'attendances')
                ->addPermission('platform.attendance.view', 'view'),

            ItemPermission::group('Main')
                ->addPermission('platform.main.balance', 'View Balance')
        ];
    }

    /**
     * @return string[]
     */
    public function registerSearchModels(): array
    {
        return [
            // ...Models
            // \App\Models\User::class
        ];
    }
}
