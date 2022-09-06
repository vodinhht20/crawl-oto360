<?php

namespace App\Http\Controllers;

use App\Repositories\ProductImageRepository;
use App\Repositories\ProductRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $response = Http::withHeaders([
                // 'date'=> 'Tue, 06 Sep 2022 17:37:14 GMT',
                // 'content-type'=> 'text/html; charset=UTF-8',
                // 'transfer-encoding'=> 'chunked',
                // 'connection'=> 'close',
                // 'request-id'=> '9212c9f3-f6b5-4272-9a1b-ae756a922850',
                // 'set-cookie'=> 'awesomeab=; Path=/; Max-Age=0,__cf_bm=x2Tsx43_nsF2LVYJAJEku9oFvh7_7lcgBz1nzvr70Qc-1662485834-0-Acu72/wAL5YJnJLOIb9QKKkI90YWOWsmtlS4DYBLSuJURlXOmg1qolIkVhAhXRcMdcIrVobNzU80ggQI/t5+Fj8=; path=/; expires=Tue, 06-Sep-22 18:07:14 GMT; domain=.www.auoing.com; HttpOnly; Secure; SameSite=None',
                // 'strict-transport-security'=> 'max-age=315360000; includeSubdomains',
                // 'vary'=> 'Accept-Encoding, Accept-Encoding',
                // 'x-content-type-options'=> 'nosniff',
                // 'x-download-options'=> 'noopen',
                // 'x-powered-by'=> 'ASP.NET',
                // 'x-store-id'=> '240711',
                // 'x-store-locale'=> 'en-US',
                // 'cf-cache-status'=> 'DYNAMIC',
                // 'server'=> 'cloudflare',
                // 'cf-ray'=> '7468f8b17e5824c7-HKG',
                // 'content-encoding'=> 'gzip',
                // 'alt-svc'=> 'h3=":443"; ma=86400, h3-29=":443"; ma=86400',
                'User-Agent'=> 'User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36',
                'set-cookie' => '_c_id=1662483077543114782; Path=/; Max-Age=31536000,awesomeab=ywgd9411-loong-v22s26s5; Path=/; Max-Age=86400,sw_session=63177a85d7259; expires=Tue, 06-Sep-2022 17:14:37 GMT; path=/; httponly,_identity_cart=51cfb1e7-a42a-4be2-92b7-e6b90aa0a166; expires=Wed, 13-Aug-2121 16:51:17 GMT; path=/; httponly,store_locale=en-US; expires=Wed, 06-Sep-2023 16:51:17 GMT; path=/; httponly,__cf_bm=fWUUdBTsxC_uqo1Oawk73fZXGsY8zWoCGni8t43jruo-1662483078-0-ARQb+fat0YkfeojmHGaTnl1HpOthW8f2hyyV/pCXb2i0dqBDgEUtOJnqYVuU/dQ6KAp4I1dEtzkYAjv/iGczoH0=; path=/; expires=Tue, 06-Sep-22 17:21:18 GMT; domain=.www.auoing.com; HttpOnly; Secure; SameSite=None'
            ])
            ->get($url);
        dd($response->body());

        // insert product
        $productRaw = $response['product'];
        $productExist = $this->productRepo->where('_id', $productRaw['id'])->first();
        if ($productExist) {
            echo "Trang web này đã tồn tại !";
            die;
        }
        $attributes = [
            "_id" => $productRaw['id'] ?? '',
            "name" => $productRaw['title'] ?? '',
            "price" => $productRaw['variants'][0]['price'] ?? '',
            "description" => $productRaw['body_html'] ?? '',
            "category" => $productRaw['product_type'] ?? '',
        ];
        $productNew = $this->productRepo->create($attributes);

        // insert image of product
        $imageRaws = $productRaw['images'];
        $images = [];
        $index = 1;
        foreach ($imageRaws as $image) {
            $images[] = [
                'product_id' => $productNew->id,
                'links' => $image['src'],
                'is_primary' => $index == 1 ? $index : 0,
                'created_at' => now(),
                'updated_at' => now()
            ];
            $index++;
        }
        $productImages = $this->productImageRepo->insert($images);
    }

    private function getProductSiteMap($url, $exploy = 'product')
    {

    }
}
