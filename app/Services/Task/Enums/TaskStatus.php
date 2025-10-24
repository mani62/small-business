<?php

namespace App\Services\Task\Enums;

enum TaskStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function getLabel(): string
    {
        return match($this) {
            self::TODO => 'To Do',
            self::IN_PROGRESS => 'In Progress',
            self::DONE => 'Done',
        };
    }

    public function getProgressPercentage(): int
    {
        return match($this) {
            self::TODO => 0,
            self::IN_PROGRESS => 50,
            self::DONE => 100,
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
