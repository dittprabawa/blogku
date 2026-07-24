<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $posts = Post::published()->orderBy('updated_at', 'desc')->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        $xml .= $this->urlTag(
            route('blog.index'),
            optional($posts->first())->updated_at ?? now(),
            'daily',
            '1.0'
        );

        foreach ($posts as $post) {
            $xml .= $this->urlTag(route('blog.show', $post), $post->updated_at, 'weekly', '0.8');
        }

        $xml .= '</urlset>';

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    protected function urlTag(string $loc, $lastmod, string $changefreq, string $priority): string
    {
        return sprintf(
            "  <url>\n    <loc>%s</loc>\n    <lastmod>%s</lastmod>\n    <changefreq>%s</changefreq>\n    <priority>%s</priority>\n  </url>\n",
            htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8'),
            $lastmod->toAtomString(),
            $changefreq,
            $priority
        );
    }
}
