<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * XML sitemap: home, shop, every active category and every active product.
     */
    public function index(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('shop.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
        ];

        foreach (Category::query()->active()->get() as $category) {
            $urls[] = [
                'loc' => route('shop.index', ['category' => $category->slug]),
                'lastmod' => $category->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ];
        }

        foreach (Product::query()->active()->get(['slug', 'updated_at']) as $product) {
            $urls[] = [
                'loc' => route('product.show', $product),
                'lastmod' => $product->updated_at?->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7',
            ];
        }

        foreach (['about', 'contact', 'returns', 'privacy', 'terms'] as $page) {
            $urls[] = [
                'loc' => route("pages.{$page}"),
                'changefreq' => 'monthly',
                'priority' => '0.3',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * robots.txt — keep crawlers out of cart, checkout and admin.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /admin',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ];

        return response(implode("\n", $lines))
            ->header('Content-Type', 'text/plain');
    }
}
