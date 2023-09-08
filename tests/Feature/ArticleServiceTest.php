<?php

namespace Tests\Feature;

use App\Models\Articles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\ArticleService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArticleServiceTest extends TestCase
{
    use RefreshDatabase;


    /**
     * @covers App\Services\ArticleService\saveArticles
     * @covers App\Services\ArticleService\saveImage
     * @covers App\Services\ArticleService\urlExists
     */
    public function testSaveArticles()
    {
        $articleService = app()->make(ArticleService::class);
        $articleService->saveArticles();
        $data = Articles::orderBy('created_at', 'desc')->take(1)->get()->toArray();
        $this->assertIsArray($data[0]);
        $this->assertEquals(
            array_keys($data[0]),
            [
                'id',
                'article_id',
                'title',
                'image',
                'created_at',
                'updated_at'
            ]
        );
    }

    /**
     * @covers App\Services\ArticleService\saveSingleArticleById
     * @covers App\Services\ArticleService\saveImage
     * @covers App\Services\ArticleService\urlExists
     */
    public function testSaveSingleArticleById()
    {
        $articleService = app()->make(ArticleService::class);
        $response = Http::withToken($articleService->api->auth->getToken())->get("{$articleService->config['api_url']}/api/articles");
        $articleService->saveSingleArticleById($response['data'][0]['id']);
        $data = Articles::select()->get()->toArray();
        $this->assertIsArray($data[0]);
        $this->assertEquals(
            array_keys($data[0]),
            [
                'id',
                'article_id',
                'title',
                'image',
                'created_at',
                'updated_at'
            ]
        );
    }
}
