<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use Illuminate\Contracts\View\View;

class DealerController extends Controller
{
    public function __invoke(): View
    {
        return view('dealers', [
            'dealers' => Dealer::query()->active()->get(),
        ]);
    }
}
