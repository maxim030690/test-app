<?php

namespace Tests\Unit;

use App\Services\ArticleService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testIsApiAlive()
    {
        $articleService = app()->make(ArticleService::class);
        $response = Http::withToken($articleService->api->auth->getToken())->get("{$articleService->config['api_url']}/api/articles");

        $this->assertEquals(Response::HTTP_OK, $response->status());
    }
}
