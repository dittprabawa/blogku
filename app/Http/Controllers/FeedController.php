<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class FeedController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()
            ->with(['user', 'category'])
            ->latest('published_at')
            ->take(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">'."\n";
        $xml .= "<channel>\n";
        $xml .= '<title>'.$this->esc(config('app.name', 'Blog'))."</title>\n";
        $xml .= '<link>'.$this->esc(route('blog.index'))."</link>\n";
        $xml .= '<atom:link href="'.$this->esc(route('feed')).'" rel="self" type="application/rss+xml" />'."\n";
        $xml .= '<description>'.$this->esc('Kumpulan artikel dan tulisan terbaru seputar teknologi dan gaya hidup.')."</description>\n";
        $xml .= "<language>id</language>\n";

        foreach ($posts as $post) {
            $link = route('blog.show', $post);

            $xml .= "<item>\n";
            $xml .= '<title>'.$this->esc($post->title)."</title>\n";
            $xml .= '<link>'.$this->esc($link)."</link>\n";
            $xml .= '<guid isPermaLink="true">'.$this->esc($link)."</guid>\n";
            $xml .= '<pubDate>'.($post->published_at ?? $post->created_at)->toRfc2822String()."</pubDate>\n";
            $xml .= '<description>'.$this->esc($post->excerpt ?: Str::limit(strip_tags($post->body), 200))."</description>\n";
            $xml .= '<category>'.$this->esc($post->category->name)."</category>\n";
            $xml .= "</item>\n";
        }

        $xml .= "</channel>\n</rss>";

        return response($xml, 200)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    protected function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}