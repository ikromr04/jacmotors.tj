<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Models\CarCategory;
use App\Models\Post;
use App\Models\Slide;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        return view('home', [
            'slides' => Slide::query()->active()->with('car')->get(),
            'categories' => CarCategory::query()
                ->orderBy('sort')
                ->with(['cars' => fn ($query) => $query->published()])
                ->get()
                ->filter(fn (CarCategory $category) => $category->cars->isNotEmpty()),
            'promotions' => Post::query()->published()->ofType(PostType::Promotion)->limit(3)->get(),
            'news' => Post::query()->published()->ofType(PostType::News)->limit(3)->get(),
        ]);
    }
}
