<?php

namespace App\Http\Controllers;

use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use DOMElement;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Image;
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
        try {
            $crawler = GoutteFacade::request('GET', $url);
            $hasNoJsError = $crawler->filter('body')->first()->text();
            // if ($hasNoJsError) {
            //     return response()->json([
            //         "status" => false,
            //         "message" => "Trang web này đang bật tưởng lửa nên không thể crawl dữ liệu"
            //     ], 422);
            // }

            // get title
            // $arrClassTitles = [
            //     ".product-info__header_title",
            //     ".product-info__body .product-info__header_title"
            // ];
            // $title = "";
            // foreach ($arrClassTitles as $class) {
            //     try {
            //         $title = $crawler->filter($class)->first()->text();
            //         break;
            //     } catch (\Exception $ex) {
            //         continue;
            //     }
            // }

            // get size
            $sizes = "";
            $arrClassSizes = [
                ".container .product-info__variants_value-wrapper",
                ".product-info__variants-wrapper"
            ];
            foreach ($arrClassSizes as $class) {
                try {
                    $sizes = $crawler->filter($class)
                        ->last()
                        ->filter('label')
                        ->each(function ($node) {
                            return $node->text();
                        });
                    break;
                } catch (\Exception $ex) {
                    continue;
                }
            }
            dd($sizes);

            // get color
            $colors = $crawler->filter('.container .product-info__variants_value-wrapper')
                ->first()
                ->filter('.product-info__variants_value label')
                ->each(function ($node) {
                    return $node->text();
                });

            // get price
            $price = $crawler->filter('.product-info__header_price-wrapper .product-info__header_price')->first()->text();

            // get price
            $description = $crawler->filter('.product-info__desc-tab-content')->first()->html();

            // get images
            $images = $crawler->filter('.product-image .swiper-slide img')->each(function (Crawler $node) {
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
