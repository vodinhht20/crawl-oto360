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
use GuzzleHttp\Psr7\Request as Psr7Request;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel as FacadesExcel;

class CrawlDataController extends Controller
{
    const TYPE_ONLY = 1;
    const TYPE_COLLECTION = 2;

    public function __construct(
        private ProductRepository $productRepo,
        private ProductImageRepository $productImageRepo
    )
    {

    }

    public function index(Request $request)
    {
        $data[] = $this->header();
        for ($i=1; $i <= 10; $i++) {
            $body =[
                [
                    'name' => 'ajax',
                    'contents' => 'loadQuestion'
                ],
                [
                    'name' => 'id',
                    'contents' => $i
                ]
            ];
            try {
                $data[] = $this->handleCrawl($i, $body);
            } catch (\Exception $e) {
                continue;
            }
        }

        for ($j=1; $j <= 60; $j++) {
            for ($i=1; $i <= 35; $i++) {
                if ($i == 1) {
                    $body =[
                        [
                            'name' => 'ajax',
                            'contents' => 'loadQuestionPrev'
                        ],
                        [
                            'name' => 'current',
                            'contents' => $i + 1
                        ],
                        [
                            'name' => 'exam',
                            'contents' => $j
                        ],
                        [
                            'name' => 'selected',
                            'contents' => 0
                        ]
                    ];
                } else {
                    $body =[
                        [
                            'name' => 'ajax',
                            'contents' => 'loadQuestionNext'
                        ],
                        [
                            'name' => 'current',
                            'contents' => $i - 1
                        ],
                        [
                            'name' => 'exam',
                            'contents' => $j
                        ],
                        [
                            'name' => 'selected',
                            'contents' => 0
                        ]
                    ];
                }
                try {
                    $data[] = $this->handleCrawl($i, $body);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
        return FacadesExcel::download(new ExportDataCrawl($data, []), "test.xlsx");
    }

    public function handleCrawl($id, $body, $title)
    {
        $client = new Client();
        $headers = [
        'Cookie' => 'PHPSESSID=qc01i31s75hl1rtsnb9haa8p0f'
        ];
        $options = [
            'multipart' => $body
        ];
        $request = new Psr7Request('POST', 'https://oto360.net/thi-lai-xe', $headers);
        $res = $client->sendAsync($request, $options)->wait();
        $content = $res->getBody()->getContents();
        $crawler = new Crawler($content);
        return [
            $id,
            $this->getQuesition($crawler),
            $this->getImagesQuestion($crawler),
            $this->getAnwser($crawler),
            "",
            $this->getResult($crawler),
        ];
    }

    public function getQuesition($crawler)
    {
        return $crawler->filter(".question_content")->text();
    }

    public function getAnwser($crawler)
    {
        $answers = $crawler->filter("blockquote a")->each(function (Crawler $node) {
            return $node->text();
        });
        return implode(PHP_EOL, $answers);
    }

    public function getResult($crawler)
    {
        return $crawler->filter("blockquote .answer-Y")->text();
    }

    public function header()
    {
        return [
            "STT",
            "Question",
            "Images_question",
            "Anwser",
            "Images_anwser",
            "Result"
        ];
    }

    public function getImagesQuestion($crawler)
    {
        try {
            return $crawler->filter("img")->attr("src");
        } catch (\Exception $e) {
            return "";
        }
    }
}

