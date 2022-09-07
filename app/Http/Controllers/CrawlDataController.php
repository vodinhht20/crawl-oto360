<?php

namespace App\Http\Controllers;

use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Weidner\Goutte\GoutteFacade;

class CrawlDataController extends Controller
{
    public function __construct(
        private ProductRepository $productRepo,
        private ProductImageRepository $productImageRepo
    )
    {

    }

    public function index(Request $request)
    {
        return view('main.index');
    }

    public function handleCrawl(Request $request)
    {
        $url = $request->domain;

        $crawler = GoutteFacade::request('GET', $url);

        // get title
        $title = $crawler->filter('.product-info__header_title')->each(function ($node) {
            return $node->text();
        });

        // get title
        $sizes = $crawler->filter('.product-info__variants_value-wrapper .product-info__variants_value .product-info__label')->each(function ($node) {
            return $node->text();
        });

        $data = [
            "title" => $title[0] ?? '',
            "sizes" => $sizes ?? [],

        ];
        return response()->json($data);
    }
}
