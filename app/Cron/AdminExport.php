<?php

namespace App\Console\Commands;

use App\Models\FeedExports;
use App\Models\GamesBets;
use App\Models\Players;
use App\Orchid\Filters\GamesBetFilter;
use App\Orchid\Filters\IssuedBonusesFilter;
use App\Orchid\Filters\PlayersFilter;
use Carbon\Carbon;
use Illuminate\Console\Command;
use function App\Orchid\Screens\Players\getStatus;
use Illuminate\Http\Request;

class AdminExport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:export {id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(Request $request)
    {
        echo $this->argument('id');
        $feed = FeedExports::find($this->argument('id'));

        try {
            $filters = json_decode($feed->filters);
            foreach ($filters as $key => $value) {
                $request[$key] = $value;
            }
            $url = $this->save_storage($feed->type_name);
            $feed->url = $url;
            $feed->status = 2;
            $feed->save();
            echo $url;
        }catch (\Exception $exception){
            $feed->status = 3;
            $feed->save();
            echo $exception->getMessage();
        }

        return 0;
    }

    public function save_storage($filename)
    {
        $s = \Storage::disk('public');
        $path = '/exports/admin_csv/' . $filename . '-' . Carbon::now()->format('Y-m-d H:i:s') . '.csv';

        $csvFile = tmpfile();
        $csvPath = stream_get_meta_data($csvFile)['uri'];

        $fd = fopen($csvPath, 'w');

        $this->$filename($fd);

        fclose($fd);

        $s->putFileAs('', $csvPath, $path);
        return $path;
    }

    public function players($fd)
    {
        $columns = array('E-mail', 'ID', 'Country', 'Sign Up', 'Balance', 'Deposit Sum', 'Bonuses', 'GGR', 'Status', 'Partner Email');
        fputcsv($fd, $columns);

        $tasks = \App\Models\Players::query()
            ->filtersApply([PlayersFilter::class])
            ->leftJoin('countries', 'players.country_id', '=', 'countries.id')
            ->leftJoin('languages', 'players.language_id', '=', 'languages.id')
            ->leftJoin('partners', 'players.partner_id', '=', 'partners.id')
            ->whereNull('players.deleted_at')
            ->select('players.id', 'players.email', 'players.fullname', 'countries.name as country', 'players.created_at',
                'players.deposit_count', 'players.deposit_sum', 'players.bonus_count', 'players.ggr',
                'players.balance', 'players.status', 'languages.name as language', 'players.mail_verified', 'partners.email as partner')
            ->orderBy('id', 'DESC')
            ->get();

        echo count($tasks);

        foreach ($tasks as $task) {
            $wallets = \DB::table('wallets')
                ->where('user_id', $task->id)
                ->join('currency', 'wallets.currency_id', '=', 'currency.id')
                ->select('wallets.balance', 'currency.rate')
                ->get();
            $balance = $wallets->map(function ($item, $key) {
                return ((float)$item->balance / (float)$item->rate);
            })->sum();

            $wallets = \App\Models\Payments::query()
                ->where('user_id', $task->id)
                ->where('type_id', 3)
                ->where('status', 1)
                ->join('currency', 'payments.currency_id', '=', 'currency.id')
                ->select('payments.amount', 'payments.amount_usd', 'currency.rate')
                ->get();
            $deposit_sum = $wallets->map(function ($item, $key) {
                if ($item->amount_usd) {
                    return $item->amount_usd;
                }
                return ((float)$item->balance / (float)$item->rate);
            })->sum();;

            fputcsv($fd, [
                'E-mail' => $task->email,
                'ID' => $task->id,
                'Country' => $task->country,
                'Sign Up' => $task->created_at,
                'Balance' => $balance,
                'Deposit Sum' => $deposit_sum,
                'Status' => Players::STATUS[$task->status] ?? '-',
                'Partner Email' => $task->partner,
            ]);
        }
    }

    public function bets($fd)
    {
        $columns = array('ID', 'Title', 'Currency', 'Balance before', 'Balance After', 'Bets sum', 'Payoff sum', 'Profit', 'Player', 'Created at', 'Finished at');

        $tasks = GamesBets::filters()
            ->filtersApply([GamesBetFilter::class])
            ->Join('wallets', 'games_bets.wallet_id', '=', 'wallets.id')
            ->Join('currency', 'wallets.currency_id', '=', 'currency.id')
            ->Join('players', 'games_bets.user_id', '=', 'players.id')
            ->Join('games', 'games_bets.game_id', '=', 'games.id')
            ->select(
                'currency.code as currency',
                'games_bets.bet_sum',
                'games_bets.id',
                'games.name as title',
                'games.id as games_id',
                'games_bets.payoffs_sum',
                'games_bets.balance_before',
                'games_bets.balance_after',
                'games_bets.profit',
                'games_bets.bonus_issue',
                'games_bets.jackpot_win',
                'players.id as player_id',
                'players.email as player',
                'games_bets.created_at',
                'games_bets.updated_at'
            )
            ->orderBy('games_bets.id', 'DESC')
            ->get();

        fputcsv($fd, $columns);

        foreach ($tasks as $task) {
            fputcsv($fd, [
                'ID' => $task->id,
                'Title' => $task->title,
                'Currency' => $task->currency,
                'Balance Before' => $task->balance_before,
                'Balance After' => $task->balance_after,
                'Bets sum' => $task->bet_sum,
                'Payoff sum' => $task->payoffs_sum,
                'Profit' => $task->profit,
                'Player' => $task->player,
                'Created at' => $task->created_at,
                'Finished at' => $task->created_at,
            ]);
        }
    }

    public function issued_bonuses($fd){
        $columns = array('ID', 'Email', 'Title', 'Date Received', 'Active until', 'Wager');

        $tasks = \App\Models\BonusIssue::filters()
            ->filtersApply([IssuedBonusesFilter::class])->select()
            ->leftJoin('bonuses','bonus_issue.bonus_id', '=', 'bonuses.id')
            ->leftJoin('players', 'bonus_issue.user_id', '=', 'players.id')
            ->leftJoin('currency', 'bonus_issue.currency_id', '=', 'currency.id')
            ->select('bonus_issue.id', 'players.email', 'players.id as player_id', 'bonuses.title', 'bonuses.strategy_id as strategy',  'bonus_issue.amount',
                'currency.code as currency', 'bonus_issue.to_wager', 'bonus_issue.custom_title', 'bonus_issue.wagered', 'bonus_issue.active_until', 'bonus_issue.stage', 'bonus_issue.created_at')
            ->orderBy('bonus_issue.id', 'DESC')
            ->get();

        fputcsv($fd, $columns);

        foreach ($tasks as $task) {

            $percent = 100 * ($task->to_wager > 0 ? $task->wagered  / $task->to_wager : 0);

            fputcsv($fd, [
                'ID' => $task->id,
                'Email' => $task->email,
                'Title' => $task->title,
                'Date Received' => $task->created_at,
                'Active until' => $task->active_until,
                'Wager' =>  $task->wagered . ' / ' . $task->to_wager .' ('. bcdiv($percent, 1, 0). '%)',
            ]);
        }

    }
}
