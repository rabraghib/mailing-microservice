<?php

namespace App\Interface;

final class MailRequestStatus
{
    public const SENT = 'sent';
    public const FAILED = 'failed';
    public const PROCESSING = 'processing';
}