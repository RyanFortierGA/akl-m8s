<?php

namespace App\Support;

use App\Models\Community;
use App\Models\Event;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

class Auckland
{
    public static function prelaunch(): bool
    {
        return (bool) config('auckland.prelaunch', true);
    }

    public static function launchLabel(): string
    {
        return (string) config('auckland.launch_label', 'October');
    }

    /**
     * @return list<string>
     */
    public static function suburbs(): array
    {
        return array_values(array_unique(config('auckland.suburbs', [])));
    }

    /**
     * @return array<string, array{label: string, emoji: string, blurb: string}>
     */
    public static function seriesCatalog(): array
    {
        return config('auckland.series', []);
    }

    /**
     * @return list<array{key: string, label: string, emoji: string, blurb: string, next_label: string|null, url: string}>
     */
    public static function regularNights(?Community $community): array
    {
        $upcoming = $community
            ? $community->events()->published()->upcoming()->orderBy('starts_at')->get()
            : collect();

        return collect(self::seriesCatalog())
            ->map(function (array $series, string $key) use ($upcoming) {
                /** @var Event|null $next */
                $next = $upcoming->first(fn (Event $event) => $event->series === $key);

                return [
                    'key' => $key,
                    'label' => $series['label'],
                    'emoji' => $series['emoji'],
                    'blurb' => $series['blurb'],
                    'next_label' => self::prelaunch()
                        ? null
                        : ($next
                            ? $next->starts_at->timezone('Pacific/Auckland')->format('D j M')
                            : null),
                    'url' => self::prelaunch() || ! $next
                        ? route('home')
                        : route('events.show', [$next->community->slug, $next->slug]),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<In|string>
     */
    public static function suburbRules(bool $required = false): array
    {
        $rules = ['string', Rule::in(self::suburbs())];

        return $required ? array_merge(['required'], $rules) : array_merge(['nullable'], $rules);
    }

    /**
     * @return list<In|string>
     */
    public static function seriesRules(): array
    {
        return ['nullable', 'string', Rule::in(array_keys(self::seriesCatalog()))];
    }
}
