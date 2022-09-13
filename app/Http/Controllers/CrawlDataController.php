<?php

namespace App\Http\Controllers;

use App\Exports\ExportDataCrawl;
use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use DOMElement;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
use Weidner\Goutte\GoutteFacade;
use Excel;

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
        return Excel::download(new ExportDataCrawl(), 'data.csv');
        // return view('main.index');
    }

    public function handleCrawl(Request $request)
    {
        $url = $request->domain;
        try {
            $crawler = GoutteFacade::request('GET', $url);
            // $hasNoJsError = $crawler->filter('.no-js')->first()->text();
            // dd($hasNoJsError);
            // if ($hasNoJsError) {
            //     return response()->json([
            //         "status" => false,
            //         "message" => "Trang web này đang bật tưởng lửa nên không thể crawl dữ liệu"
            //     ], 422);
            // }

            // get title
            $title = $crawler->filter('.product-info__header_title')->first()->text();

            // get size
            $sizes = $crawler->filter('.product-info__variants_value-wrapper .product-info__variants_value .product-info__label')->each(function ($node) {
                return $node->text();
            });

            // get color
            $colors = $crawler->filter('.product-info__thumbnail .product-info__label_text')->each(function ($node) {
                return $node->text();
            });

            // get price
            $price = $crawler->filter('.product-info__header_price-wrapper .product-info__header_price')->first()->text();

            // get price
            $description = $crawler->filter('.product-info__desc-tab-content.product_detail__content')->first()->html();

            // get images
            $images = $crawler->filter('.product-image .product-info__slide img')->each(function (Crawler $node) {
                $element = $node->first();
                return $element->attr("data-src") ?: $element->attr("src");
            });

            $data = [
                "title" => $title ?? '',
                "sizes" => $sizes ?? [],
                "colors" => $colors,
                "price" => $price,
                "images" => $images,
                "description" => $description,
            ];
        } catch (\Exception $e) {
            return response()->json([
                "status" => false,
                "message" => $e->getMessage()
            ], 422);
        }

        return response()->json($data);
    }
}
