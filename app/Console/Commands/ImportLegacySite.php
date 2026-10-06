<?php

namespace App\Console\Commands;

use App\Enums\DocumentType;
use App\Models\Car;
use App\Models\Document;
use App\Models\Slide;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * One-off import of the content of the previous jacmotors.tj site (static Blade pages)
 * into the database. Images and PDFs are copied from the old public/storage folder when
 * present, otherwise downloaded from jac-motors.kz.
 */
#[Signature('jac:import-legacy {path : Path to the old Laravel project} {--no-download : Do not download files missing locally} {--ca= : CA bundle for HTTPS downloads, if PHP has none}')]
#[Description('Import models, slides and documents from the previous version of the site')]
class ImportLegacySite extends Command
{
    /** Old page file => new car slug. */
    private const CARS = [
        'jac-t9' => 'jac-t9', 't8-pro' => 'jac-t8-pro', 'jac-t6' => 'jac-t6', 'jac-j7' => 'jac-j7',
        'jac-j7-plus' => 'jac-j7-plus', 'jac-js8' => 'jac-js8', 'jac-js4' => 'jac-js4', 'js6' => 'jac-js6',
        'N35' => 'jac-n35', 'N56' => 'jac-n56', 'N80' => 'jac-n80', 'N120' => 'jac-n120', 'N350' => 'jac-n350',
        'sunray' => 'jac-sunray', 'm4' => 'jac-m4',
    ];

    private string $root;

    private array $copied = [];

    public function handle(): int
    {
        $this->root = rtrim(str_replace('\\', '/', $this->argument('path')), '/');

        if (! is_dir("{$this->root}/resources/views/pages")) {
            $this->error("Old site not found in {$this->root}");

            return self::FAILURE;
        }

        $this->importCards();
        $this->importCars();
        $this->importSlides();
        $this->importDocuments('manuals', DocumentType::Manual);
        $this->importDocuments('regulations', DocumentType::Regulation);

        $this->newLine();
        $this->info('Done. Files stored: '.count(array_filter($this->copied, fn ($path) => ! Str::startsWith($path, 'http'))));

        return self::SUCCESS;
    }

    private function importCards(): void
    {
        $this->components->task('Model cards', function () {
            $xpath = $this->load('models');

            foreach ($xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' product ')]") as $card) {
                $link = $xpath->query('.//a[contains(@class, "product_title")]', $card)->item(0);
                $img = $xpath->query('.//img', $card)->item(0);
                $slug = self::CARS[$this->routeName($link?->getAttribute('href'))] ?? null;

                if ($slug && $img && ($car = Car::query()->firstWhere('slug', $slug))) {
                    $car->update(['card_image' => $this->file([$this->webp($card, $xpath), $img->getAttribute('src')], 'cars/cards')]);
                }
            }
        });
    }

    private function importCars(): void
    {
        foreach (self::CARS as $page => $slug) {
            $car = Car::query()->firstWhere('slug', $slug);

            if (! $car) {
                continue;
            }

            $this->components->task($car->name, function () use ($page, $car) {
                $xpath = $this->load($page);
                $data = [];

                // Hero: the first slide of the main slider, separate images for mobile and desktop.
                $slide = $xpath->query('//div[contains(@class, "main-slider")]//div[contains(@class, "swiper-slide")]')->item(0);
                if ($slide) {
                    $data['hero_image_mobile'] = $this->file($this->source($xpath, $slide, 'max-width'), 'cars/hero');
                    $data['hero_image'] = $this->file($this->source($xpath, $slide, 'min-width'), 'cars/hero');
                }

                // Colours.
                $colors = [];
                foreach ($xpath->query('//div[contains(@class, "picker__images")]/img') as $img) {
                    $key = $img->getAttribute('data-img');
                    $button = $xpath->query("//a[contains(@class, 'picker__color')][@data-img='{$key}']")->item(0);
                    preg_match('/#[0-9a-f]{3,8}/i', (string) $button?->getAttribute('style'), $hex);

                    $colors[] = [
                        'name' => ['ru' => $img->getAttribute('alt')],
                        'hex' => $hex[0] ?? '#cccccc',
                        'image' => $this->file($img->getAttribute('src'), 'cars/colors'),
                    ];
                }
                $data['colors'] = $colors ?: null;

                // Description and brochure.
                $excerpt = $xpath->query('//div[contains(@class, "picker__text-excerpt")]')->item(0);
                if ($excerpt && filled(trim($excerpt->textContent))) {
                    $data['description'] = ['ru' => $this->cleanHtml($this->innerHtml($excerpt))];
                }

                $pdf = $xpath->query('//a[contains(@href, ".pdf")]')->item(0);
                if ($pdf) {
                    $data['brochure'] = $this->file($pdf->getAttribute('href'), 'cars/brochures');
                }

                $tour = $xpath->query('//div[contains(@class, "total-view__app")]//iframe')->item(0);
                $data['tour_url'] = $tour?->getAttribute('src') ?: null;

                $car->update($data);

                // Feature sections (desktop markup only, the mobile one duplicates it).
                $car->sections()->delete();
                foreach ($xpath->query('//div[contains(@class, "cb_imgs")]') as $index => $block) {
                    $items = [];
                    foreach ($xpath->query('.//div[contains(@class, "cascade-block__image")]/div[contains(@class, "pic")]', $block) as $pic) {
                        $key = $pic->getAttribute('data-img');
                        $title = $xpath->query(".//li[@data-img='{$key}']", $block)->item(0)?->textContent
                            ?? $xpath->query('.//img', $pic)->item(0)?->getAttribute('alt');
                        $items[] = [
                            'title' => ['ru' => $this->text($title)],
                            'text' => ['ru' => $this->text($xpath->query('.//p', $pic)->item(0)?->textContent)],
                            'image' => $this->file([$this->webp($pic, $xpath), $xpath->query('.//img', $pic)->item(0)?->getAttribute('src')], 'cars/sections'),
                        ];
                    }

                    $text = $xpath->query('.//div[contains(@class, "cascade-block__text")]', $block)->item(0);
                    $car->sections()->create([
                        'label' => ['ru' => $this->text($xpath->query('.//span[contains(@class, "suptitle")]', $text)->item(0)?->textContent)],
                        'title' => ['ru' => $this->text($xpath->query('.//h2', $text)->item(0)?->textContent)],
                        'text' => ['ru' => $this->text($xpath->query('./p', $text)->item(0)?->textContent)],
                        'items' => $items,
                        'sort' => $index,
                    ]);
                }
            });
        }
    }

    private function importSlides(): void
    {
        $this->components->task('Home slider', function () {
            $xpath = $this->load('index');
            Slide::query()->delete();

            foreach ($xpath->query('//div[contains(@class, "main-slider")]//div[contains(@class, "swiper-slide")]') as $index => $slide) {
                $link = $xpath->query('.//a[contains(@class, "btn-more")]', $slide)->item(0);
                $slug = self::CARS[$this->routeName($link?->getAttribute('href'))] ?? null;

                Slide::query()->create([
                    'car_id' => $slug ? Car::query()->where('slug', $slug)->value('id') : null,
                    'title' => ['ru' => $this->text($xpath->query('.//*[contains(@class, "h1")]', $slide)->item(0)?->textContent)],
                    'subtitle' => ['ru' => $this->text($xpath->query('.//*[contains(@class, "h3")]', $slide)->item(0)?->textContent)],
                    'image' => $this->file($this->source($xpath, $slide, 'min-width'), 'slides'),
                    'image_mobile' => $this->file($this->source($xpath, $slide, 'max-width'), 'slides'),
                    'sort' => $index,
                ]);
            }

            // Models whose own banner is gone get the home slider banner instead.
            foreach (Slide::query()->whereNotNull('car_id')->get() as $slide) {
                Car::query()->whereKey($slide->car_id)->whereNull('hero_image')->update([
                    'hero_image' => $slide->image,
                    'hero_image_mobile' => $slide->image_mobile,
                ]);
            }
        });
    }

    private function importDocuments(string $page, DocumentType $type): void
    {
        $this->components->task($type->getLabel(), function () use ($page, $type) {
            $xpath = $this->load($page);
            Document::query()->where('type', $type)->delete();
            $cars = Car::query()->pluck('id', 'name');

            foreach ($xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' product ')]") as $index => $item) {
                $title = $this->text($xpath->query('.//*[contains(@class, "product_title")]', $item)->item(0)?->textContent);
                $file = $xpath->query('.//a[contains(@href, ".pdf")]', $item)->item(0)?->getAttribute('href');

                if (! $title || ! $file) {
                    continue;
                }

                Document::query()->create([
                    'type' => $type,
                    'car_id' => $cars[$title] ?? $cars['JAC '.$title] ?? null,
                    'title' => ['ru' => $title],
                    'image' => $this->file([$this->webp($item, $xpath), $xpath->query('.//img', $item)->item(0)?->getAttribute('src')], 'documents'),
                    'file' => $this->file($file, 'documents'),
                    'sort' => $index,
                ]);
            }
        });
    }

    /**
     * Parse an old Blade page, replacing the Blade helpers that appear in attributes.
     */
    private function load(string $page): DOMXPath
    {
        $html = file_get_contents("{$this->root}/resources/views/pages/{$page}.blade.php");
        $html = preg_replace("/\{\{\s*asset\('([^']+)'\)\s*\}\}/", '/$1', $html);
        $html = preg_replace("/\{\{\s*route\('([^']+)'\)\s*\}\}/", 'route:$1', $html);

        $dom = new DOMDocument;
        libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
        libxml_clear_errors();

        return new DOMXPath($dom);
    }

    private function routeName(?string $href): ?string
    {
        return $href && str_starts_with($href, 'route:') ? substr($href, 6) : null;
    }

    /** srcsets of the <source> tags matching a media query, webp first. */
    private function source(DOMXPath $xpath, DOMElement $element, string $media): array
    {
        return collect(iterator_to_array($xpath->query(".//source[contains(@media, '{$media}')]", $element)))
            ->sortBy(fn (DOMElement $source) => $source->getAttribute('type') === 'image/webp' ? 0 : 1)
            ->map(fn (DOMElement $source) => $source->getAttribute('srcset'))
            ->values()
            ->all();
    }

    private function webp(DOMElement $element, DOMXPath $xpath): ?string
    {
        return $xpath->query(".//source[@type='image/webp']", $element)->item(0)?->getAttribute('srcset') ?: null;
    }

    /**
     * Store a file from the old site on the public disk and return its path.
     * Candidates (e.g. webp, then the original png) are tried in order; files missing
     * on both the old and the source site are skipped, so they can be uploaded in the admin.
     */
    private function file(string|array|null $urls, string $directory): ?string
    {
        $urls = array_values(array_unique(array_filter((array) $urls)));

        foreach ($urls as $url) {
            if ($path = $this->store($url, $directory)) {
                return $path;
            }
        }

        if (! $urls) {
            return null;
        }

        $this->components->warn('Not found: '.implode(', ', $urls));

        return null;
    }

    private function store(string $url, string $directory): ?string
    {
        if (array_key_exists($url, $this->copied)) {
            return $this->copied[$url];
        }

        // "/storage/x" (local) or "https://jac-motors.kz/storage/x" (remote) => "storage/x".
        $relative = ltrim(preg_replace('#^https?://[^/]+#', '', $url), '/');
        $target = $directory.'/'.basename($relative);
        $disk = Storage::disk('public');

        if ($disk->exists($target)) {
            return $this->copied[$url] = $target;
        }

        $local = "{$this->root}/public/{$relative}";

        if (is_file($local)) {
            $disk->put($target, file_get_contents($local));

            return $this->copied[$url] = $target;
        }

        if ($this->option('no-download') || ! Str::startsWith($url, 'http')) {
            return $this->copied[$url] = null;
        }

        try {
            $response = Http::timeout(60)
                ->when($this->option('ca'), fn ($http, $ca) => $http->withOptions(['verify' => $ca]))
                ->retry(2, 500, throw: false)
                ->get($url);

            if (! $response->successful()) {
                return $this->copied[$url] = null;
            }

            $disk->put($target, $response->body());

            return $this->copied[$url] = $target;
        } catch (Throwable) {
            return $this->copied[$url] = null;
        }
    }

    private function innerHtml(DOMElement $element): string
    {
        return collect(iterator_to_array($element->childNodes))
            ->map(fn ($node) => $element->ownerDocument->saveHTML($node))
            ->implode('');
    }

    private function cleanHtml(string $html): string
    {
        $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><a>');
        $html = preg_replace('/\s(style|class)="[^"]*"/', '', $html);

        return trim(preg_replace('/\s+/', ' ', $html));
    }

    private function text(?string $value): ?string
    {
        $value = trim(preg_replace('/\s+/u', ' ', html_entity_decode((string) $value)));

        return $value === '' ? null : $value;
    }
}
