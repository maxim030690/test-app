<?php

namespace App\Services;

use App\Services\Api\Auth;
use App\Services\Api\ApiFacade;
use App\Services\Api\Validate;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\RequestException;
use App\Models\Articles;
use Illuminate\Support\Facades\Storage;


class ArticleService
{
    /**
     * @var ApiFacade
     */
    public ApiFacade $api;

    /**
     * @var array|Repository|Application|mixed
     */
    public array $config;

    public function __construct()
    {
        $this->config = config('services.articles_api');
        $this->api = new ApiFacade(
            new Validate(),
            new Auth($this->config['api_url']),
            $this->config['client_id'],
            $this->config['client_secret']
        );
    }

    /**
     * @return void
     */
    public function saveArticles()
    {
        $articles = $this->getQuery("{$this->config['api_url']}/api/articles");
        $data = $articles->json()['data'];
        foreach ($data as $key => $item) {
            $data[$key] = $this->prepareArticleToSave($item);
        }

        foreach (array_chunk($data, 1000) as $chunk) {
            Articles::insert($chunk);
        }

        foreach ($data as $item) {
            $this->saveImage($item['image']);
        }
    }

    /**
     * @param string $url
     * @return bool
     */
    public function saveImage(string $url): bool
    {
        if (!$this->urlExists($url)) {
            return false;
        }

        if (!getimagesize($url)) {
            return false;
        }

        $contents = file_get_contents($url);
        $name = substr($url, strrpos($url, '/') + 1);

        return Storage::put($name, $contents);
    }

    /**
     * Check if url exist
     *
     * @param $url
     * @return bool
     */
    private function urlExists($url) {
        $result = false;
        $url = filter_var($url, FILTER_VALIDATE_URL);
        $handle = curl_init($url);
        curl_setopt_array($handle, array(
            CURLOPT_FOLLOWLOCATION => TRUE,     // we need the last redirected url
            CURLOPT_NOBODY => TRUE,             // we don't need body
            CURLOPT_HEADER => FALSE,            // we don't need headers
            CURLOPT_RETURNTRANSFER => FALSE,    // we don't need return transfer
            CURLOPT_SSL_VERIFYHOST => FALSE,    // we don't need verify host
            CURLOPT_SSL_VERIFYPEER => FALSE     // we don't need verify peer
        ));

        curl_exec($handle);
        curl_getinfo($handle, CURLINFO_EFFECTIVE_URL);  // Try to get the last url
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE); // Get http status from last url

        if($httpCode == 200) {
            $result = true;
        }

        return $result;

        /* Close curl connection */
        curl_close($handle);
    }

    /**
     * @param $id
     * @return void
     */
    public function saveSingleArticleById($id): void
    {
        $article = $this->getQuery("{$this->config['api_url']}/api/articles/{$id}");
        $data = $this->prepareArticleToSave($article->json()['data']);
        Articles::create($data);
        $this->saveImage($data['image']);
    }

    /**
     * Sync articles data. If some article doesn't exist in API it'll remove article from DB
     *
     * @return void
     */
    public function syncArticles(): void
    {
        $articles = self::getQuery("{$this->config['api_url']}/api/articles");
        $data = $articles->json()['data'];
        $ids = array_map(fn($item) => $item['id'], $data);

        Articles::select()->whereNotIn('article_id', $ids)->delete();
    }

    /**
     * @param string $uri
     * @return Response
     */
    private function getQuery(string $uri): Response
    {
        try {
            return Http::withToken($this->api->auth->getToken())->retry(2, 0, function (Exception $exception, PendingRequest $request) {
                if (!$exception instanceof RequestException || $exception->response->status() !== 401) {
                    return false;
                }

                $request->withToken($this->api->auth->refreshToken());

                return true;
            })->get($uri);
        } catch (Exception $exception) {
            error_log('Caught exception: ' . $exception->getMessage());
            exit();
        }
    }

    /**
     * @param array $data
     * @return array
     */
    private function prepareArticleToSave(array &$data): array
    {
        $data['article_id'] = $data['id'];
        $data['created_at'] = now();
        $data['updated_at'] = now();
        unset($data['id']);

        return $data;
    }
}
