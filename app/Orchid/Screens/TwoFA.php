<?php

namespace App\Orchid\Screens;

use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class TwoFA extends Screen
{
    use \Orchid\Fortify\TwoFactorScreenAuthenticatable;
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = 'TwoFA';

    public $fa = false;
    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        $this->fa = empty(Auth::user()->two_factor_secret);

        return [
            'url' => json_encode(['route' =>route('platform.main')])
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            $this->twoFactorCommandBar(),
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        return [
            $this->twoFactorLayout(),
            Layout::view('orchid.2fa-script')->canSee($this->fa),
            Layout::view('orchid.2fa-script-enabled')->canSee(!$this->fa)
        ];
    }
}
