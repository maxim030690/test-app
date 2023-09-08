<?php

namespace App\Console\Commands;

use App\Services\ArticleService;
use Illuminate\Console\Command;

class SyncArticles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-articles {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Getting an articles from the API';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(ArticleService $articleService)
    {
        $id = $this->argument('id');
        $id ? $articleService->saveSingleArticleById($id) : $articleService->saveArticles();
        $this->info('All done!');

        return Command::SUCCESS;
    }
}
