<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Models\Page;
use Illuminate\Contracts\View\View;

class PageController extends Controller
{
    public function show(Page $page): View
    {
        abort_unless($page->is_published, 404);

        return view('page', ['page' => $page]);
    }

    public function support(): View
    {
        return view('support', [
            'dealers' => Dealer::query()->active()->get(),
        ]);
    }
}
