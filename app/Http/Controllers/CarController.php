<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\CarCategory;
use Illuminate\Contracts\View\View;

class CarController extends Controller
{
    public function index(): View
    {
        return view('cars.index', [
            'categories' => CarCategory::query()
                ->orderBy('sort')
                ->with(['cars' => fn ($query) => $query->published()])
                ->get()
                ->filter(fn (CarCategory $category) => $category->cars->isNotEmpty()),
        ]);
    }

    public function show(Car $car): View
    {
        abort_unless($car->is_published, 404);

        $car->load(['sections', 'category', 'documents']);

        return view('cars.show', [
            'car' => $car,
            'related' => Car::query()
                ->published()
                ->with('category')
                ->whereKeyNot($car->getKey())
                ->reorder()
                ->orderByRaw('car_category_id <=> ? desc', [$car->car_category_id])
                ->orderBy('sort')
                ->limit(4)
                ->get(),
        ]);
    }
}
