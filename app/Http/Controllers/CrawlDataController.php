<?php

namespace App\Http\Controllers;

use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use DOMElement;
use Exception;
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
            // $hasNoJsError = $crawler->filter('body')->first()->text();
            // dd($hasNoJsError);
            // if ($hasNoJsError) {
            //     return response()->json([
            //         "status" => false,
            //         "message" => "Trang web này đang bật tưởng lửa nên không thể crawl dữ liệu"
            //     ], 422);
            // }

            // get title
            $arrClassTitles = [
                ".product-info__header_title",
                ".product-info__body .product-info__header_title"
            ];
            $title = "";
            foreach ($arrClassTitles as $class) {
                try {
                    $title = $crawler->filter($class)->first()->text();
                    if (empty($title)) {
                        continue;
                    }
                    break;
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // get size
            $sizes = [];
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

                    if (is_array($sizes)) {
                        $sizes = array_unique(array_filter($sizes, fn($item) => !empty($item)));
                    }

                    if (empty($sizes)) {
                        continue;
                    }
                    break;
                } catch (\Exception $ex) {
                    continue;
                }
            }

            // get color
            $colors = [];
            $arrClassColor = [
                ".container .product-info__variants_value-wrapper",
                ".product-info__variants-wrapper"
            ];

            foreach ($arrClassColor as $class) {
                try {
                    $colors = $crawler->filter($class)
                        ->first()
                        ->filter('.product-info__variants_value input')
                        ->each(function ($node) {
                            return $node->attr('value');
                        });

                    if (is_array($colors)) {
                        $colors = array_unique(array_filter($colors, fn($item) => !empty($item)));
                    }

                    if (empty($colors)) {
                        continue;
                    }
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }

            // get price
            $regularPrice = 0;
            $salePrice = 0;
            try {
                $regularPrice = $crawler->filter('.product-info__header_price-wrapper .product-info__header_price')->first()->text();
                $regularPrice = (float) ltrim($regularPrice, "$");
            } catch (\Exception $e) {
                //
            }

            try {
                $salePrice = $crawler->filter('.product-info__header_price-wrapper .product-info__header_compare-at-price')->first()->text();
                $salePrice = (float) ltrim($salePrice, "$");
            } catch (\Exception $e) {
                //
            }

            // get description
            $description = "";
            try {
                $description = $crawler->filter('.product-info__desc-tab-content')->first()->html();
            } catch (\Exception $ex) {
                //
            }

            // get images
            $images = [];
            $arrClassImage = [
                ".product-image .product-info__slide img",
                ".product-image .swiper-slide img"
            ];
            foreach ($arrClassImage as $class) {
                try {
                    $images = $crawler->filter($class)->each(function (Crawler $node) {
                        $element = $node->first();
                        return $element->attr("data-lazy") ?: $element->attr("data-src") ?: $element->attr("src");
                    });

                    if (is_array($images)) {
                        $images = array_unique(array_filter($images, fn($item) => !empty($item)));
                    }

                    if (empty($images)) {
                        continue;
                    }
                    $images = array_unique($images);
                    break;
                } catch (\Exception $ex) {
                    continue;
                }
            }

            $data = [
                "title" => $title ?? '',
                "sizes" => $sizes ?? [],
                "colors" => $colors,
                "regular_price" => $regularPrice,
                "sale_price" => $salePrice,
                "images" => $images,
                "description" => $description,
            ];
            $data = array($data);
        } catch (\Exception $e) {
            return redirect()->back()->with(["message.error" => $e->getMessage()])->withInput(["domain" => $url]);
        }
        return redirect()->back()->with(["message.success" => "Crawl Data Thành Công !"])->withInput(["domain" => $url])->with(compact("data"));
    }
}
