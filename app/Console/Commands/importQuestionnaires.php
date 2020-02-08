<?php

namespace App\Console\Commands;

use App\ForumMessage;
use App\ArticleCategory;
use App\Helpers\CSVHelper;
use App\Questionnaire;
use App\QuestionnaireVariant;
use App\UserAward;
use App\UserReputation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class importQuestionnaires extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaires:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $messages_with_questionnaires = ForumMessage::where('questionnaire', '!=','')->where('questionnaire','!=', '0')->get();
        foreach ($messages_with_questionnaires as $message) {
            $data = explode("`", $message->questionnaire);
            $title = $data[0];
            $questionnaire = Questionnaire::firstOrNew([
                'topic_id' => $message->topic_id
            ]);
            $questionnaire->title = $title;
            $questionnaire->multiple_variants = (bool)$data[23];
            $questionnaire->save();
            echo "Added questionnaire: ".$questionnaire->title.PHP_EOL;

            QuestionnaireVariant::where(['questionnaire_id' => $questionnaire->id])->delete();
            $variants = array_filter(array_slice($data, 1, 10));
            $i = 0;
            foreach ($variants as $variant) {
                $questionnaire_variant = new QuestionnaireVariant([
                    'questionnaire_id' => $questionnaire->id,
                    'title' => $variant,
                    'order' => $i,
                    'initial_count' => (int)$data[$i + 11]
                ]);
                $questionnaire_variant->save();
                echo "Added questionnaire variant: ".$questionnaire_variant->title.PHP_EOL;
                $i++;
            }
        }
    }
}
