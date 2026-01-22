<?php

namespace App\Constants;

enum RecordComplaintTypes: string
{
    case PlayerNotWorking = 'player-not-working';
    case CopyrightIssues = 'copyright-issues';
    case Other = 'other';
}
