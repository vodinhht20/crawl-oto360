<?php

namespace App\Http\Controllers;

use App\Exports\ExportDataCrawl;
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
        // return Excel::download(new ExportDataCrawl(), 'data.csv');
        return view('main.index');
    }

    public function handleCrawl(Request $request)
    {
        $url = $request->domain;
        try {
            $crawler = GoutteFacade::request('GET', $url);
            $formatData[] = $this->headers;
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


            // get size - get color
            $positionAttribute = "size";
            try {
                $positionAttribute = strtolower($crawler
                ->filter(".product-info__variants_title")
                ->first()
                ->text());
            } catch (\Exception $e) {
                //
            }

            // get size
            $sizes = [];
            $arrClassSizes = [
                ".container .product-info__variants_value-wrapper",
                ".product-info__variants-wrapper",
                ".product-info__variants_items"
            ];
            foreach ($arrClassSizes as $class) {
                try {
                    $sizes = $crawler->filter($class)
                        ->last()
                        ->filter('input')
                        ->each(function ($node) {
                            return $node->attr('value');
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
            $sizes = implode(", ", $sizes);

            // get color
            $colors = [];
            $arrClassColor = [
                ".container .product-info__variants_value-wrapper",
                ".product-info__variants-wrapper",
                ".product-info__variants_items"
            ];
            foreach ($arrClassColor as $class) {
                try {
                    $colors = $crawler->filter($class)
                        ->first()
                        ->filter('input') // .product-info__variants_value input
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
            $colors = implode(", ", $colors);

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
            $images = array_map(function($image) {
                return 'https:' . $image;
            }, $images);
            $images = implode(", ", $images);

            $data = [
                "id" => uniqid(),
                "title" => $title ?? '',
                "regular_price" => $regularPrice,
                "sale_price" => $salePrice,
                "images" => $images,
            ];

            if ($positionAttribute == "size") {
                $data["sizes"] = $colors;
                $data["colors"] = $sizes;
            } else {
                $data["sizes"] = $sizes;
                $data["colors"] = $colors;
            }

            $data["description"] = $description;

            $formatData[] = [
                $data['id'], // ID
                "variation", // Type
                "", // SKU
                $data['title'], // Name
                1, // Published
                0, // Is featured?
                "visible", // Visibility in catalog
                "", // Short description
                $data['description'], // Description
                "", // Date sale price starts
                "", // Date sale price ends
                "taxable", // Tax status
                "", // Tax class
                1, // In stock?
                "", // Stock
                "", // Low stock amount
                0, // Backorders allowed?
                0, // Sold individually?
                "", // Weight (kg)
                "", // Length (cm)
                "", // Width (cm)
                "", // Height (cm)
                1, // Allow customer reviews?
                "", // Purchase note
                $data['sale_price'] == $data['sale_price'] ? "" : $data['sale_price'], // Sale price
                $data['regular_price'], // Regular price
                "", // Categories
                "", // Tags
                "", // Shipping class
                $data['images'], // Images
                "", // Download limit
                "", // Download expiry days
                "", // Parent
                "", // Grouped products
                "", // Upsells
                "", // Cross-sells
                "", // External URL
                "", // Button text
                0, // Position
                "", // Attribute 1 name
                "", // Attribute 1 value(s)
                "", // Attribute 1 visible
                "", // Attribute 1 global
                "", // Meta: _et_pb_post_hide_nav
                "", // Meta: _et_pb_page_layout
                "", // Meta: _et_pb_side_nav
                "", // Meta: _et_pb_use_builder
                "", // Meta: _et_pb_first_image
                "", // Meta: _et_pb_truncate_post
                "", // Meta: _et_pb_truncate_post_date
                "", // Meta: _et_pb_old_content
                "", // Attribute 1 default
                "Color", // Attribute 2 name
                $data['colors'], // Attribute 2 value(s)
                "", // Attribute 2 visible
                "", // Attribute 2 global
                "", // Attribute 2 default
                "Size", // Attribute 3 name
                $data['sizes'], // Attribute 3 value(s)
                "", // Attribute 3 visible
                "", // Attribute 3 global
                "", // Attribute 3 default
            ];
            $fileName = "data_" . date('Y_m_d_H_i') . ".csv";
            return Excel::download(new ExportDataCrawl($formatData), $fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with(["message.error" => $e->getMessage()])->withInput(["domain" => $url]);
        }
        return redirect()->back()->with(["message.success" => "Crawl Data Thành Công !"])->withInput(["domain" => $url])->with(compact("data"));
    }

    private $headers = [
        "ID",
        "Type",
        "SKU",
        "Name",
        "Published",
        "Is featured?",
        "Visibility in catalog",
        "Short description",
        "Description",
        "Date sale price starts",
        "Date sale price ends",
        "Tax status",
        "Tax class",
        "In stock?",
        "Stock",
        "Low stock amount",
        "Backorders allowed?",
        "Sold individually?",
        "Weight (kg)",
        "Length (cm)",
        "Width (cm)",
        "Height (cm)",
        "Allow customer reviews?",
        "Purchase note",
        "Sale price",
        "Regular price",
        "Categories",
        "Tags",
        "Shipping class",
        "Images",
        "Download limit",
        "Download expiry days",
        "Parent",
        "Grouped products",
        "Upsells",
        "Cross-sells",
        "External URL",
        "Button text",
        "Position",
        "Attribute 1 name",
        "Attribute 1 value(s)",
        "Attribute 1 visible",
        "Attribute 1 global",
        "Meta: _et_pb_post_hide_nav",
        "Meta: _et_pb_page_layout",
        "Meta: _et_pb_side_nav",
        "Meta: _et_pb_use_builder",
        "Meta: _et_pb_first_image",
        "Meta: _et_pb_truncate_post",
        "Meta: _et_pb_truncate_post_date",
        "Meta: _et_pb_old_content",
        "Attribute 1 default",
        "Attribute 2 name",
        "Attribute 2 value(s)",
        "Attribute 2 visible",
        "Attribute 2 global",
        "Attribute 2 default",
        "Attribute 3 name",
        "Attribute 3 value(s)",
        "Attribute 3 visible",
        "Attribute 3 global",
        "Attribute 3 default",
    ];
}

