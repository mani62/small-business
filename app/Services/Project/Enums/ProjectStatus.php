<?php

namespace App\Services\Project\Enums;

enum ProjectStatus: string
{
    case PLANNING = 'planning';
    case IN_PROGRESS = 'in_progress';
    case ON_HOLD = 'on_hold';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function getLabel(): string
    {
        return match($this) {
            self::PLANNING => 'Planning',
            self::IN_PROGRESS => 'In Progress',
            self::ON_HOLD => 'On Hold',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function getProgressPercentage(): int
    {
        return match($this) {
            self::PLANNING => 10,
            self::IN_PROGRESS => 50,
            self::ON_HOLD => 30,
            self::COMPLETED => 100,
            self::CANCELLED => 0,
        };
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }
}
