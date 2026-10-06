<?php

namespace App\Http\Controllers;

use App\Enums\PostType;
use App\Models\Post;
use Illuminate\Contracts\View\View;

class PostController extends Controller
{
    public function promotions(): View
    {
        return $this->list(PostType::Promotion);
    }

    public function news(): View
    {
        return $this->list(PostType::News);
    }

    public function showPromotion(Post $post): View
    {
        return $this->show($post, PostType::Promotion);
    }

    public function showNews(Post $post): View
    {
        return $this->show($post, PostType::News);
    }

    private function list(PostType $type): View
    {
        return view('posts.index', [
            'type' => $type,
            'posts' => Post::query()->published()->ofType($type)->paginate(9),
        ]);
    }

    private function show(Post $post, PostType $type): View
    {
        abort_unless($post->type === $type && $post->is_published, 404);
        abort_if($post->published_at?->isFuture(), 404);

        return view('posts.show', [
            'post' => $post,
            'more' => Post::query()->published()->ofType($type)->whereKeyNot($post->getKey())->limit(3)->get(),
        ]);
    }
}
