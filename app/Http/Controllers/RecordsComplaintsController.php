<?php

namespace App\Http\Controllers;

use App\Constants\RecordComplaintTypes;
use App\Models\RecordComplaint;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class RecordsComplaintsController extends Controller
{
    private function canIgnoreAutoComplaint($text): bool { // ignore useless player messages
        $messages = [
            'due to low buffer',
            'with buffer holes',
            "Nudging 'currentTime'",
            'Error: Aborted',
            'did not increase buffered coverage',
            'MediaSource closed while media attached',
            'HTTP Error 0',
            'A network error (status 0)'
        ];
        foreach($messages as $message) {
            if (stripos($text, $message) !== false) return true;
        }
        return false;
    }

    public function add()
    {
        $rules = [
            'description' => 'sometimes',
            'record_id' => 'required|exists:records,id',
            'type' => Rule::enum(RecordComplaintTypes::class),
            'auto' => 'sometimes|boolean',
        ];
        if (!auth()->user() && request()->input('type') != RecordComplaintTypes::PlayerNotWorking->value) {
            $rules['contact'] = 'required';
        }
        $data = request()->validate($rules);
        $complaint_exists = RecordComplaint::where([
            'record_id' => $data['record_id'],
            'type' => $data['type'],
            'description' => $data['description'] ?? ''
        ])->whereDate('created_at', '>=', Carbon::now()->subDays(1))->count() > 0;

        if ($complaint_exists) {
            return [
                'status' => 1,
                'text' => 'Жалоба уже на рассмотрении'
            ];
        }

        $complaint = new RecordComplaint($data);
        if (auth()->user()) {
            $complaint->user_id = auth()->user()->id;
        }

        $complaint->user_agent = request()->header('User-Agent');

        if (!$complaint->auto || $this->canIgnoreAutoComplaint($complaint->description)) {
            $complaint->save();
        }

        return [
            'status' => 1,
            'text' => 'Ваша жалоба отправлена, спасибо!'
        ];
    }

}
