<?php

namespace App\Enums;

enum WorkType: string
{
    case Design = 'design';
    case Content = 'content';
    case Both = 'both';

    /** @return list<string> */
    public function requiredBriefTypes(): array
    {
        return match ($this) {
            self::Design => ['design'],
            self::Content => ['content'],
            self::Both => ['design', 'content'],
        };
    }

    /** @return list<string> */
    public function requiredClickUpTaskTypes(): array
    {
        return $this->requiredBriefTypes();
    }
}
