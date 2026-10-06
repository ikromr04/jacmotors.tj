<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\CarCategory;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'pickups' => ['ru' => 'Пикапы', 'tg' => 'Пикапҳо', 'en' => 'Pickups'],
            'crossovers' => ['ru' => 'Кроссоверы', 'tg' => 'Кроссоверҳо', 'en' => 'Crossovers'],
            'passenger' => ['ru' => 'Легковые', 'tg' => 'Сабукравҳо', 'en' => 'Passenger cars'],
            'commercial' => ['ru' => 'Коммерческие', 'tg' => 'Тиҷоратӣ', 'en' => 'Commercial'],
        ];

        $categoryIds = [];
        foreach (array_keys($categories) as $sort => $slug) {
            $categoryIds[$slug] = CarCategory::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $categories[$slug], 'sort' => $sort],
            )->id;
        }

        $cars = [
            ['jac-t9', 'JAC T9', 'pickups', ['ru' => 'Рождён, чтобы побеждать', 'tg' => 'Барои ғалаба таваллуд шудааст', 'en' => 'Born to win'], true],
            ['jac-t8-pro', 'JAC T8 Pro', 'pickups', ['ru' => 'Сила и надёжность в каждой детали', 'tg' => 'Қувва ва эътимоднокӣ дар ҳар ҷузъ', 'en' => 'Strength and reliability in every detail'], true],
            ['jac-t6', 'JAC T6', 'pickups', ['ru' => 'Покоритель дорог', 'tg' => 'Фотеҳи роҳҳо', 'en' => 'Conqueror of the roads'], true],
            ['jac-js8', 'JAC JS8', 'crossovers', ['ru' => 'Теперь 3 ряда сидений', 'tg' => 'Акнун бо 3 қатор курсӣ', 'en' => 'Now with three rows of seats'], true],
            ['jac-js6', 'JAC JS6', 'crossovers', ['ru' => 'Твой момент свободы', 'tg' => 'Лаҳзаи озодии ту', 'en' => 'Your moment of freedom'], false],
            ['jac-js4', 'JAC JS4', 'crossovers', ['ru' => 'Создан, чтобы удивлять', 'tg' => 'Барои ҳайрон кардан сохта шудааст', 'en' => 'Made to amaze'], false],
            ['jac-j7', 'JAC J7', 'passenger', ['ru' => 'Дизайн от кутюр', 'tg' => 'Тарҳи олӣ', 'en' => 'Haute couture design'], true],
            ['jac-j7-plus', 'JAC J7 Plus', 'passenger', ['ru' => 'Лёд и пламя', 'tg' => 'Ях ва аланга', 'en' => 'Ice and fire'], false],
            ['jac-sunray', 'JAC Sunray', 'commercial', null, false],
            ['jac-m4', 'JAC M4', 'commercial', null, false],
            ['jac-n35', 'JAC N35', 'commercial', null, false],
            ['jac-n56', 'JAC N56', 'commercial', null, false],
            ['jac-n80', 'JAC N80', 'commercial', null, false],
            ['jac-n120', 'JAC N120', 'commercial', null, false],
            ['jac-n350', 'JAC N350', 'commercial', null, false],
        ];

        foreach ($cars as $sort => [$slug, $name, $category, $tagline, $featured]) {
            Car::query()->updateOrCreate(['slug' => $slug], [
                'name' => $name,
                'car_category_id' => $categoryIds[$category],
                'tagline' => $tagline,
                'is_featured' => $featured,
                'is_published' => true,
                'sort' => $sort,
            ]);
        }
    }
}
