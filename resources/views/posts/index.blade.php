@php
    $isPromotion = $type === \App\Enums\PostType::Promotion;
    $title = $isPromotion ? __('site.posts.promotions') : __('site.posts.news');
@endphp

<x-layout :title="$title" :description="$isPromotion ? __('site.posts.promotions_text') : __('site.posts.news_text')">
    <x-page-hero :title="$title" />

    <section class="section">
        <div class="container-x">
            @if ($posts->isEmpty())
                <p class="py-16 text-center text-lg text-ink/50">{{ __('site.posts.empty') }}</p>
            @else
                <div class="grid gap-x-6 gap-y-12 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($posts as $post)
                        @include('posts.card', ['post' => $post])
                    @endforeach
                </div>
                <div class="mt-12">{{ $posts->links() }}</div>
            @endif
        </div>
    </section>

    @include('partials.cta')
</x-layout>
