<?php

namespace App\Console\Commands;

use App\Helpers\DatesHelper;
use App\Models\Article;
use Illuminate\Console\Command;

class SetOldArticleDates extends Command
{
    protected $signature = 'articles:set-old-dates';

    public function handle()
    {
        $years = [];
        for ($i = 1950; $i <= 2007; $i++) {
            $years[] = $i;
        }

        Article::chunk(100, function ($articles) use ($years) {
            foreach ($articles as $article) {
                $title_data = explode('.', $article->title);

                if (count($title_data) == 1) {
                    continue;
                }
                $supposed_date_text = $title_data[0];
                $has_year = array_filter($years, fn($year) => str_contains($supposed_date_text, $year));
                if (!$has_year) {
                    continue;
                }

                echo 'Article title: ' . $article->title . PHP_EOL;
                try {
                    $guessed = DatesHelper::guess($supposed_date_text);
                    echo 'Guessed date: ' . $guessed['date']->format('d.m.Y') . PHP_EOL;
                    $article->created_at = $guessed['date'];
                    $article->save();
                } catch (\Exception $e) {
                    echo 'Could not parse date'.PHP_EOL;
                }
            }
        });
    }
}
