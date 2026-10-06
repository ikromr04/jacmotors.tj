<article class="group relative flex flex-col">
    <div class="relative aspect-[16/10] overflow-hidden rounded-3xl bg-surface">
        @if ($post->image)
            <img src="{{ media($post->image) }}" alt="" loading="lazy" class="size-full object-cover transition duration-700 ease-out-soft group-hover:scale-105">
        @else
            <div class="grid size-full place-items-center text-ink/20"><x-logo.jac class="h-12 w-auto" /></div>
        @endif
        @if ($post->ends_at)
            <span class="absolute top-4 left-4 rounded-full bg-white px-3 py-1 text-xs font-semibold">
                {{ __('site.posts.until', ['date' => $post->ends_at->translatedFormat('j F Y')]) }}
            </span>
        @endif
    </div>
    <p class="mt-5 text-xs font-semibold tracking-wider text-ink/50 uppercase">{{ $post->published_at?->translatedFormat('j F Y') }}</p>
    <h3 class="mt-2 text-xl font-extrabold tracking-tight transition group-hover:text-brand">
        <a href="{{ $post->url() }}" class="after:absolute after:inset-0">{{ $post->tr('title') }}</a>
    </h3>
    @if ($excerpt = $post->tr('excerpt'))
        <p class="mt-2 line-clamp-2 text-sm text-ink/60">{{ $excerpt }}</p>
    @endif
</article>
