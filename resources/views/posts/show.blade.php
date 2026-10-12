@php
    $isPromotion = $post->type === \App\Enums\PostType::Promotion;
    $schema = array_filter([
        '@type' => 'NewsArticle',
        'headline' => $post->tr('title'),
        'description' => strip_tags($post->tr('excerpt') ?? ''),
        'image' => $post->image ? media($post->image) : null,
        'datePublished' => $post->published_at?->toAtomString(),
        'dateModified' => $post->updated_at?->toAtomString(),
        'mainEntityOfPage' => url()->current(),
        'author' => ['@id' => route('home').'#dealer'],
        'publisher' => ['@id' => route('home').'#dealer'],
    ]);
@endphp

<x-layout :title="$post->tr('title')" :description="$post->tr('excerpt')" :image="$post->image" type="article" :published-at="$post->published_at" :schema="[$schema]">
    <article>
        <header class="bg-surface">
            <div class="container-x max-w-4xl py-14 md:py-20">
                <a href="{{ route($isPromotion ? 'promotions.index' : 'news.index') }}" class="link-arrow text-ink/60">
                    <x-glyph name="arrow-left" class="size-4" />
                    {{ $isPromotion ? __('site.posts.promotions') : __('site.posts.news') }}
                </a>
                <h1 class="h-section mt-6">{{ $post->tr('title') }}</h1>
                <p class="mt-4 text-sm text-ink/50">
                    {{ $post->published_at?->translatedFormat('j F Y') }}
                    @if ($post->ends_at)
                        · {{ __('site.posts.until', ['date' => $post->ends_at->translatedFormat('j F Y')]) }}
                    @endif
                </p>
            </div>
        </header>

        <div class="container-x max-w-4xl py-12 md:py-16">
            @if ($post->image)
                <img src="{{ media($post->image) }}" alt="" class="mb-10 w-full rounded-3xl">
            @endif

            @if ($excerpt = $post->tr('excerpt'))
                <p class="mb-8 text-xl font-medium text-ink/80">{{ $excerpt }}</p>
            @endif

            {{-- Body is written by administrators in the rich editor. --}}
            <div class="prose-content">{!! $post->tr('body') !!}</div>

            @if ($isPromotion)
                <button type="button" class="btn btn-primary mt-10" x-data x-on:click="$store.modal.open('purchase')">
                    {{ __('site.actions.request') }}
                </button>
            @endif
        </div>
    </article>

    @if ($more->isNotEmpty())
        <section class="section bg-surface">
            <div class="container-x">
                <h2 class="h-section">{{ __('site.posts.more') }}</h2>
                <div class="mt-10 grid gap-6 md:grid-cols-3">
                    @foreach ($more as $item)
                        @include('posts.card', ['post' => $item])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-layout>
