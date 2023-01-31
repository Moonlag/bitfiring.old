<?php

declare(strict_types=1);

use App\Orchid\Screens\Bonuses\Bonuses;
use App\Orchid\Screens\Examples\ExampleCardsScreen;
use App\Orchid\Screens\Examples\ExampleChartsScreen;
use App\Orchid\Screens\Examples\ExampleFieldsAdvancedScreen;
use App\Orchid\Screens\Examples\ExampleFieldsScreen;
use App\Orchid\Screens\Examples\ExampleLayoutsScreen;
use App\Orchid\Screens\Examples\ExampleScreen;
use App\Orchid\Screens\Examples\ExampleTextEditorsScreen;
use App\Orchid\Screens\Games\CreateGame;
use App\Orchid\Screens\Games\EditGame;
use App\Orchid\Screens\Games\ViewGame;
use App\Orchid\Screens\Games\GameBets;
use App\Orchid\Screens\PlatformScreen;
use App\Orchid\Screens\Players\Events;
use App\Orchid\Screens\Players\UsersSessions;
use App\Orchid\Screens\Role\RoleEditScreen;
use App\Orchid\Screens\Role\RoleListScreen;
use App\Orchid\Screens\User\UserEditScreen;
use App\Orchid\Screens\User\UserListScreen;
use App\Orchid\Screens\User\UserProfileScreen;
use Illuminate\Support\Facades\Route;
use Tabuna\Breadcrumbs\Trail;
use App\Orchid\Screens\Players\Players;
use App\Orchid\Screens\Players\ViewPlayer;
use App\Orchid\Screens\Games\Games;
use App\Orchid\Screens\Payments\Payments;
use App\Orchid\Screens\Suspicions;
use App\Orchid\Screens\Pages\Pages;
use App\Orchid\Screens\MediaLibrary;
use App\Orchid\Screens\Players\ChangeBalance;
use App\Orchid\Screens\Players\AccountLimit;
use App\Orchid\Screens\Players\UserSessions;
use App\Orchid\Screens\Players\Suspicion;
use App\Orchid\Screens\Players\Bets;
use App\Orchid\Screens\Pages\PagesEdit;
use App\Orchid\Screens\Players\EditPlayer;
use App\Orchid\Screens\Players\PlayersBets;

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
Route::screen('/main', PlatformScreen::class)
    ->name('platform.main');

// Platform > Profile
Route::screen('profile', UserProfileScreen::class)
    ->name('platform.profile')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Profile'), route('platform.profile'));
    });

// Platform > System > Users
Route::screen('users/{user}/edit', UserEditScreen::class)
    ->name('platform.systems.users.edit');

// Platform > System > Users > Create
Route::screen('users/create', UserEditScreen::class)
    ->name('platform.systems.users.create')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.systems.users')
            ->push(__('Create'), route('platform.systems.users.create'));
    });

// Platform > System > Users > User
Route::screen('users', UserListScreen::class)
    ->name('platform.systems.users')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Users'), route('platform.systems.users'));
    });

// Platform > System > Roles > Role
Route::screen('roles/{roles}/edit', RoleEditScreen::class)
    ->name('platform.systems.roles.edit')
    ->breadcrumbs(function (Trail $trail, $role) {
        return $trail
            ->parent('platform.systems.roles')
            ->push(__('Role'), route('platform.systems.roles.edit', $role));
    });

// Platform > System > Roles > Create
Route::screen('roles/create', RoleEditScreen::class)
    ->name('platform.systems.roles.create')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.systems.roles')
            ->push(__('Create'), route('platform.systems.roles.create'));
    });

Route::screen('roles', RoleListScreen::class)
    ->name('platform.systems.roles')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Roles'), route('platform.systems.roles'));
    });



// Platform > Profile
Route::screen('players/all', Players::class)
    ->name('platform.players')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.index')
            ->push(__('Players'), route('platform.players'));
    });

Route::screen('player/{id}/view', ViewPlayer::class)->name('platform.players.profile')
    ->breadcrumbs(function (Trail $trail, $id) {
       $player = \App\Models\Players::find($id);
        return $trail
            ->parent('platform.players')
            ->push(__($player->email . ($player->partner ? ' (' . $player->partner->fullname . ')' : '')), route('platform.players.profile', $id));
    });


Route::screen('players/new', \App\Orchid\Screens\Players\NewPlayer::class)->name('platform.players.new_player')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.players')
            ->push(__('New'), route('platform.players.new_player'));
    });


Route::screen('players/suspicions', Suspicions::class)->name('platform.suspicions')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.players')
            ->push(__('Suspicions'), route('platform.suspicions'));
    });

Route::screen('players/transactions', \App\Orchid\Screens\Players\Transactions::class)->name('platform.players.transactions')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.players')
            ->push(__('Transactions'), route('platform.players.transactions'));
    });


Route::screen('players/dormant', \App\Orchid\Screens\Players\Dormant::class)->name('platform.players.dormant')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.players')
            ->push(__('Dormant'), route('platform.players.dormant'));
    });

Route::screen('players/documents', \App\Orchid\Screens\Players\Documents::class)->name('platform.players.documents')
    ->breadcrumbs(function (Trail $trail) {
        return $trail
            ->parent('platform.players')
            ->push(__('Documents'), route('platform.players.documents'));
    });

Route::screen('player/{id}/change-history', \App\Orchid\Screens\Players\ChangesHistory::class)
    ->name('platform.players.change')
    ->breadcrumbs(function (Trail $trail, $id) {
        return $trail
            ->parent('platform.players.profile', $id)
            ->push(__('Change History'), route('platform.players.change', $id));
    });
Route::screen('player/{id}/edit', EditPlayer::class)->name('platform.players.edit')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Edit Player'), route('platform.players.edit', $id));
});
Route::screen('player/{id}/balance', ChangeBalance::class)->name('platform.players.balance')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Change Balance'), route('platform.players.balance', $id));
});
Route::screen('player/{id}/limits', AccountLimit::class)->name('platform.players.limits')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Account Limit'), route('platform.players.limits', $id));
});
Route::screen('player/{id}/session', UserSessions::class)->name('platform.players.session')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Players Sessions'), route('platform.players.session', $id));
});
Route::screen('players/sessions', UsersSessions::class)->name('platform.players.sessions')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.players')
        ->push(__('Sessions'), route('platform.players.sessions'));
});
Route::screen('player/{id}/suspicion', Suspicion::class)->name('platform.players.suspicion')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Suspicion'), route('platform.players.suspicion', $id));
});
Route::screen('player/{id}/games', \App\Orchid\Screens\Players\Games::class)->name('platform.players.games')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Games'), route('platform.players.games', $id));
});
Route::screen('player/{id}/bets', Bets::class)->name('platform.players.bets')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Bets'), route('platform.players.bets', $id));
});
Route::screen('player/{id}/swap', \App\Orchid\Screens\Players\SwapPlayer::class)->name('platform.players.swap')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Swap'), route('platform.players.swap', $id));
});
Route::screen('player/{id}/bonuses', \App\Orchid\Screens\Players\Bonuses::class)->name('platform.players.bonuses')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Bonuses'), route('platform.players.bonuses', $id));
});
Route::screen('player/{id}/events', Events::class)->name('platform.players.events')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.players.profile', $id)
        ->push(__('Events'), route('platform.players.events', $id));
});

Route::screen('players/filters', \App\Orchid\Screens\Filters\Players::class)->name('platform.filters.players')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.players')
        ->push(__('Filters'), route('platform.filters.players'));
});
Route::screen('players/filter/create', \App\Orchid\Screens\Filters\PlayersCreate::class)->name('platform.filters.players.create')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.filters.players')
        ->push(__('New'), route('platform.filters.players.create'));
});
Route::screen('players/filters/{id}/edit', \App\Orchid\Screens\Filters\PlayerEdit::class)->name('platform.filters.players.edit')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.filters.players')
        ->push(__('Edit'), route('platform.filters.players.edit', $id));
});
Route::screen('players/groups', \App\Orchid\Screens\Filters\Groups::class)->name('platform.filters.groups')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.players')
        ->push(__('Groups'), route('platform.filters.groups'));
});


Route::screen('games', Games::class)->name('platform.games')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('List of Games'), route('platform.games'));
});

Route::screen('game/{id}/view', ViewGame::class)->name('platform.games.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.games')
        ->push(__('View'), route('platform.games.view', $id));
});

Route::screen('game/{id}/edit', EditGame::class)->name('platform.games.edit')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.games.view', $id)
        ->push(__('Edit'), route('platform.games.edit', $id));
});

Route::screen('game/{id}/create', CreateGame::class)->name('platform.games.create')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.games.view', $id)
        ->push(__('Create'), route('platform.games.create', $id));
});

Route::screen('game/bets', GameBets::class)->name('platform.games.bets')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.games')
        ->push(__('Bets'), route('platform.games.bets'));
});

Route::screen('game/bet/{id}/view', \App\Orchid\Screens\Games\ViewBet::class)->name('platform.games.bet.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.games.bets')
        ->push(__('View'), route('platform.games.bet.view', $id));
});
Route::screen('game/session', \App\Orchid\Screens\Games\GameSessions::class)->name('platform.games.session')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.games')
        ->push(__('Sessions'), route('platform.games.session'));
});
Route::screen('game/denomination', \App\Orchid\Screens\Games\Denomination::class)->name('platform.games.denomination')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.games')
        ->push(__('Denomination'), route('platform.games.denomination'));
});

Route::screen('bonuses/all', Bonuses::class)->name('platform.bonuses')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Bonuses & Freespin'), route('platform.bonuses'));
});
Route::screen('bonuses/new', \App\Orchid\Screens\Bonuses\NewBonuses::class)->name('platform.bonuses.new')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.bonuses')
        ->push(__('New'), route('platform.bonuses.new'));
});
Route::screen('bonuses/{id}/edit', \App\Orchid\Screens\Bonuses\Edit::class)->name('platform.bonuses.edit')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.bonuses')
        ->push(__('Edit'), route('platform.bonuses.edit', $id));
});
Route::screen('bonuses/issued-bonuses', \App\Orchid\Screens\Bonuses\IssuedBonuses::class)->name('platform.bonuses.issued')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.bonuses')
        ->push(__('Issued Bonuses'), route('platform.bonuses.issued'));
});
Route::screen('bonuses/freespin-issued', \App\Orchid\Screens\Bonuses\Freespin::class)->name('platform.bonuses.freespin')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.bonuses')
        ->push(__('Freespin Issued Bonuses'), route('platform.bonuses.freespin'));
});
Route::screen('bonuses/freespin-issued/{id}/view', \App\Orchid\Screens\Bonuses\FreespinView::class)->name('platform.bonuses.freespin.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.bonuses.freespin')
        ->push(__('View'), route('platform.bonuses.freespin.view', $id));
});
Route::screen('bonuses/issued-history', \App\Orchid\Screens\Bonuses\IssuesHistory::class)->name('platform.bonuses.history')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.bonuses')
        ->push(__('Issues History'), route('platform.bonuses.history'));
});
Route::screen('bonuses/issued-history/{id}/view', \App\Orchid\Screens\Bonuses\IssuesHistoryView::class)->name('platform.bonuses.history.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.bonuses.history')
        ->push(__('Issues History'), route('platform.bonuses.history.view', $id));
});
Route::screen('bonuses/issued-bonus/{id}/view', \App\Orchid\Screens\Bonuses\IssuedBonusesView::class)->name('platform.bonuses.issued.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.bonuses.issued')
        ->push(__('View'), route('platform.bonuses.issued.view', $id));
});

Route::screen('payments/all', Payments::class)->name('platform.payments')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Payments'), route('platform.payments'));
});
Route::screen('payments/btc_address', \App\Orchid\Screens\Payments\BitcoinAddresses::class)->name('platform.payments.crypto')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.payments')
        ->push(__('Crypto Address'), route('platform.payments.crypto'));
});
Route::screen('payments/payment_systems_details', \App\Orchid\Screens\Payments\PaymentSystemsDetails::class)->name('platform.payments.systems')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.payments')
        ->push(__('Payment Systems Details'), route('platform.payments.systems'));
});
Route::screen('payments/payment_systems_sorting', \App\Orchid\Screens\Payments\PaymentSystemsSorting::class)->name('platform.payments.systems.sorting')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.payments')
        ->push(__('Payment Systems Sorting'), route('platform.payments.systems.sorting'));
});
Route::screen('payments/cash-flow-transactions', \App\Orchid\Screens\Payments\CashFlowTransactions::class)->name('platform.payments.cash_flow_transactions')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.payments')
        ->push(__('Cash Flow Transactions'), route('platform.payments.cash_flow_transactions'));
});
Route::screen('payments/btc_address/{id}/view', \App\Orchid\Screens\Payments\BitcoinView::class)->name('platform.payments.btc_address.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.payments.crypto')
        ->push(__('View'), route('platform.payments.btc_address.view', $id));
});
Route::screen('payments/{id}/view', \App\Orchid\Screens\Payments\View::class)->name('platform.payments.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.payments')
        ->push(__('View'), route('platform.payments.view', $id));
});


Route::screen('locale', \App\Orchid\Screens\Languages\Locale::class)->name('platform.locale')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Languages'), route('platform.locale'));
});


Route::screen('pages', Pages::class)->name('platform.pages')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Pages'), route('platform.pages'));
});

Route::screen('page/new', \App\Orchid\Screens\Pages\PagesNew::class)->name('platform.pages.new')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.pages')
        ->push(__('New'), route('platform.pages.new'));
});


Route::screen('pages/{id}/{lang}/edit', PagesEdit::class)->name('platform.pages.edit')->breadcrumbs(function (Trail $trail, $id, $lang) {
    return $trail
        ->parent('platform.pages')
        ->push(__("Edit ($lang)"), route('platform.pages.edit', ['id' => $id, 'lang' => $lang]));
});

Route::screen('blocks', \App\Orchid\Screens\Pages\Blocks::class)->name('platform.blocks')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Blocks'), route('platform.blocks'));
});


Route::screen('block/new', \App\Orchid\Screens\Pages\BlockNew::class)->name('platform.block.new')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.blocks')
        ->push(__('Block New'), route('platform.block.new'));
});

Route::screen('block/{id}/{lang}/edit', \App\Orchid\Screens\Pages\BlockEdit::class)->name('platform.block.edit')->breadcrumbs(function (Trail $trail, $id, $lang) {
    return $trail
        ->parent('platform.blocks')
        ->push(__("Block Edit ($lang)"), route('platform.block.edit', ['id' => $id, 'lang' => $lang]));
});

Route::screen('landings', \App\Orchid\Screens\Pages\Landings::class)->name('platform.landings')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Landings'), route('platform.landings'));
});


Route::screen('landings/new', \App\Orchid\Screens\Pages\LandingsNew::class)->name('platform.landings.new')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.landings')
        ->push(__('Landings New'), route('platform.landings.new'));
});

Route::screen('landings/{id}/edit', \App\Orchid\Screens\Pages\LandingsEdit::class)->name('platform.landings.edit')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.landings')
        ->push(__('Landings Edit'), route('platform.landings.edit', $id));
});

Route::screen('mail', \App\Orchid\Screens\Pages\Mail::class)->name('platform.mail')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Mail'), route('platform.mail'));
});

Route::screen('mail/new', \App\Orchid\Screens\Pages\MailNew::class)->name('platform.mail.new')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.mail')
        ->push(__('Mail New'), route('platform.mail.new'));
});

Route::screen('mail/{id}/{lang}/edit', \App\Orchid\Screens\Pages\MailEdit::class)->name('platform.mail.edit')->breadcrumbs(function (Trail $trail, $id, $lang) {
    return $trail
        ->parent('platform.mail')
        ->push(__('Mail Edit'), route('platform.mail.edit', ['id' => $id, 'lang' => $lang]));
});

Route::screen('mail_statistics', \App\Orchid\Screens\Pages\MailStatistics::class)->name('platform.mail-statistics')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Mail Statistics'), route('platform.mail-statistics'));
});

Route::screen('mail_templates', \App\Orchid\Screens\Pages\MailTemplates::class)->name('platform.mail-templates')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Mail Templates'), route('platform.mail-templates'));
});


Route::screen('mail_templates/new', \App\Orchid\Screens\Pages\MailTemplatesNew::class)->name('platform.mail-templates.new')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.mail-templates')
        ->push(__('Mail Template New'), route('platform.mail-templates.new'));
});

Route::screen('mail_templates/{id}/{lang}/edit', \App\Orchid\Screens\Pages\MailTemplatesEdit::class)->name('platform.mail-templates.edit')->breadcrumbs(function (Trail $trail, $id, $lang) {
    return $trail
        ->parent('platform.mail-templates')
        ->push(__('Mail Edit'), route('platform.mail-templates.edit', ['id' => $id, 'lang' => $lang]));
});

//Route::screen('media-library', MediaLibrary::class)->name('platform.media-library');

Route::screen('filters/games', \App\Orchid\Screens\Filters\Games::class)->name('platform.filters.games');


Route::screen('finance/game-providers-report', \App\Orchid\Screens\Finance\GameProvidersReport::class)->name('platform.finance.game-providers-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Game Providers Report'), route('platform.finance.game-providers-report'));
});
Route::screen('finance/revenue-report', \App\Orchid\Screens\Finance\RevenueReport::class)->name('platform.finance.revenue-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Revenue Report'), route('platform.finance.revenue-report'));
});
Route::screen('finance/income-statement-report', \App\Orchid\Screens\Finance\IncomeStatementReport::class)->name('platform.finance.income-statement-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Income Statement Report'), route('platform.finance.income-statement-report'));
});
Route::screen('finance/exchange-rates', \App\Orchid\Screens\Finance\ExchangeRates::class)->name('platform.finance.exchange-rates')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Exchange Rates'), route('platform.finance.exchange-rates'));
});
Route::screen('finance/gifts', \App\Orchid\Screens\Finance\Gifts::class)->name('platform.finance.gifts')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Gifts'), route('platform.finance.gifts'));
});
Route::screen('finance/kpi-report', \App\Orchid\Screens\Finance\KPIreport::class)->name('platform.finance.kpi-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('KPI Report'), route('platform.finance.kpi-report'));
});
Route::screen('finance/cash-report', \App\Orchid\Screens\Finance\CashReport::class)->name('platform.finance.cash-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Cash Report'), route('platform.finance.cash-report'));
});
Route::screen('finance/new-cash-report', \App\Orchid\Screens\Finance\NewCashReport::class)->name('platform.finance.new-cash-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('New Cash Report'), route('platform.finance.new-cash-report'));
});
Route::screen('finance/balance-corrections', \App\Orchid\Screens\Finance\BalanceCorrections::class)->name('platform.finance.balance-corrections')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Balance Corrections'), route('platform.finance.balance-corrections'));
});
Route::screen('finance/jackpot-and-gifts', \App\Orchid\Screens\Finance\JackpotAndGifts::class)->name('platform.finance.jackpot-and-gifts')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Jackpot And Gifts'), route('platform.finance.jackpot-and-gifts'));
});
Route::screen('finance/mga-report', \App\Orchid\Screens\Finance\MgaReport::class)->name('platform.finance.mga-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Mga Report'), route('platform.finance.mga-report'));
});

Route::screen('marketing/bonuses-report', \App\Orchid\Screens\Marketing\BonusesReport::class)->name('platform.finance.bonuses-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Bonuses Report'), route('platform.finance.bonuses-report'));
});
Route::screen('marketing/churn-rate-report', \App\Orchid\Screens\Marketing\ChurnRateReport::class)->name('platform.finance.churn-rate-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Churn Rate Report'), route('platform.finance.churn-rate-report'));
});
Route::screen('marketing/games-popularity-report', \App\Orchid\Screens\Marketing\GamesPopularityReport::class)->name('platform.finance.games-popularity-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Games Popularity Report'), route('platform.finance.games-popularity-report'));
});
Route::screen('marketing/games-report', \App\Orchid\Screens\Marketing\GamesReport::class)->name('platform.finance.games-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Games Report'), route('platform.finance.games-report'));
});
Route::screen('marketing/games-sessions-report', \App\Orchid\Screens\Marketing\GamesSessions::class)->name('platform.finance.games-sessions-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Games Sessions'), route('platform.finance.games-sessions-report'));
});
Route::screen('marketing/new-players-report', \App\Orchid\Screens\Marketing\NewPlayersReport::class)->name('platform.finance.new-players-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('New Players Report'), route('platform.finance.new-players-report'));
});
Route::screen('marketing/players-limits', \App\Orchid\Screens\Marketing\PlayersLimits::class)->name('platform.finance.players-limits')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Players Limits'), route('platform.finance.players-limits'));
});
Route::screen('marketing/players-report', \App\Orchid\Screens\Marketing\PlayersReport::class)->name('platform.finance.players-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Players Report'), route('platform.finance.players-report'));
});
Route::screen('marketing/players-stats', \App\Orchid\Screens\Marketing\PlayersStats::class)->name('platform.finance.players-stats')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Players Stats'), route('platform.finance.players-stats'));
});
Route::screen('marketing/players-summary-report', \App\Orchid\Screens\Marketing\PlayersSummaryReport::class)->name('platform.finance.players-summary-report')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Players Summary Report'), route('platform.finance.players-summary-report'));
});

Route::screen('feed/events', \App\Orchid\Screens\Feed\Events::class)->name('platform.feed.events')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Events'), route('platform.feed.events'));
});
Route::screen('feed/exports', \App\Orchid\Screens\Feed\Exports::class)->name('platform.feed.exports')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Exports'), route('platform.feed.exports'));
});
Route::screen('feed/lucky_spin', \App\Orchid\Screens\Feed\Lucky::class)->name('platform.feed.lucky')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Lucky Spin'), route('platform.feed.lucky'));
});

Route::screen('attendances', \App\Orchid\Screens\Attendances::class)->name('platform.attendances')->breadcrumbs(function (Trail $trail) {
    return $trail
        ->parent('platform.index')
        ->push(__('Attendances'), route('platform.attendances'));
});
Route::screen('attendance/{id}/view', \App\Orchid\Screens\AttendancesView::class)->name('platform.attendance.view')->breadcrumbs(function (Trail $trail, $id) {
    return $trail
        ->parent('platform.attendances')
        ->push(__('View'), route('platform.attendance.view', $id));
});

Route::screen('factory/players', \App\Orchid\Screens\Factory\UserScreen::class)->name('platform.factory.players');

Route::screen('/2fa', \App\Orchid\Screens\TwoFA::class)
    ->name('platform.fa');
