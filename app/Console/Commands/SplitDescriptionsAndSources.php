<?php

namespace App\Console\Commands;

use App\Models\Record;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;

class SplitDescriptionsAndSources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'records:split-descriptions-and-sources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        Record::whereNotNull('description')->chunk(100, function ($records) {
            $source_strings = [
                'Собственная запись', 'собственная запись', 'VHS-кассета', 'staroetv.su', 'Видео взято с',
                'Оцифровка', 'оцифровка', 'оцифровки', 'VHSRip by', 'Гаськов', 'Купин', 'kinozal.tv',
                'специально для группы', 'Перезалив от', 'за предоставленное видео',
                'АТВ', 'DVD', 'канал программы', 'VK', 'YouTube', 'мой жёсткий','моё жёсткий',
                'Перезалив', 'перезалив', 'неизвестный источник', 'Видеотека',
                'Вконтакте', 'ВКонтакте','Гостелерадиофонд','Гостелерадифонд', 'вконтакте', 'за архив', 'материал пользователя', 'Спасибо за запись',
                'архив сайта', 'за поиск', 'из выпуска', 'forum/viewtopic', 'ATV', 'Вырезано из', 'вырезано из', 'Автор -',
                'tvali.eu', 'оцифрвка', 'оцифовка', 'оцирофвка', 'оцфировка','оцифоровка','видеоархив','сайт памяти', 'Собственная видеозапись', 'ALEKS KV', 'Nekontroliruemij_devil',
                'Glowamy', 'Dark Phoenix', 'принадлежат мне','из архива', 'Из архива', 'собственный архив',
                'Копию предоставил', 'Кассету предоставил', '(с)', 'Видео принадлежит','Автор видео', 'tvr.by', 'RFSat',
                'Автор записи', 'Автор: ', 'Из выпуска', 'собственной видеокассеты', 'Нашёл на', 'нашёл на', 'Видеоархив',
                'Алексея Кириленко', 'vk.com', 'ok.ru', 'youtube.com', 'youtu.be'
            ];
            $remove_strings = ['Источник:'];
            $regexes = ['#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', '/(?:www\.)?(?:youtube\.com|vk\.com|(?:[^\s\/]+\.)+(?:ru|com|org|sk))[^\s]*/i'];
            foreach ($records as $record) {
                $description = $record->description;
                if (in_array($description, ['.', '-', '_', '...'])) {
                    $record->update(['description' => '']);
                    $record->save();
                    continue;
                };
                $source = [];
                foreach ($regexes as $regex) {
                    $description = preg_replace_callback($regex, function ($link) use (&$source) {
                        array_push($source, $link[0]);
                        return '';
                    }, $description);
                }
                foreach ($remove_strings as $remove_string) {
                    $description = str_replace($remove_string, '', $description);
                }
                foreach ($source_strings as $source_string) {
                    $description_lines = explode(PHP_EOL, $description);
                    array_filter($description_lines, function ($line) use (&$source, $source_string) {
                        if (str_contains($line, $source_string)) {
                            array_push($source, $line);
                            return false;
                        }
                        return true;
                    });
                    $description = implode(PHP_EOL, $description_lines);
                }
                $source = implode(', ', $source);
                $description = trim($description);
                echo "Source: $source\n Description: $description\n\n";
                if ($description != '' && $source == '') {
                    preg_match('/([0-9]{1,2}):([0-9]{1,2})(?::([0-9]{1,2}))?/', $description, $matches);
                    if (count($matches) > 2) {
                        continue;
                    }

                    $action = select(
                        label: 'Action',
                        options: ['Add to source', 'Skip']
                    );
                    if ($action == 'Add to source') {
                        $source = $description;
                        $description = '';
                        $source_strings[] = $source;
                    }
                }
                $record->update(['description' => $description, 'source' => $source]);
            }
        });
    }
}
