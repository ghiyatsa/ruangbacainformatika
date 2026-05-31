<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogService
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public function log(
        string $action,
        string $description,
        Model|string|null $subject = null,
        array $properties = [],
        ?Authenticatable $actor = null,
    ): ?ActivityLog {
        $actor ??= Auth::user();
        $request = $this->currentRequest();

        if (! $actor instanceof Authenticatable && $request === null) {
            return null;
        }

        if (! $actor instanceof Authenticatable && app()->runningInConsole()) {
            return null;
        }

        $activityLog = new ActivityLog([
            'action' => $action,
            'description' => $description,
            'subject_label' => $this->subjectLabel($subject),
            'properties' => $this->normalizeProperties($properties),
            'ip_address' => $request?->ip(),
            'user_agent' => Str::limit((string) $request?->userAgent(), 65535, ''),
        ]);

        if ($actor !== null) {
            $activityLog->user()->associate($actor);
        }

        if ($subject instanceof Model) {
            $activityLog->subject()->associate($subject);
        }

        $activityLog->save();

        return $activityLog;
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  list<string>  $hiddenKeys
     * @param  array<string, mixed>  $extraProperties
     */
    public function logSettingsUpdate(
        string $section,
        string $label,
        array $before,
        array $after,
        array $hiddenKeys = [],
        array $extraProperties = [],
    ): ?ActivityLog {
        $changes = $this->diffValues($before, $after, $hiddenKeys);

        if ($changes === []) {
            return null;
        }

        return $this->log(
            "settings.{$section}.updated",
            "{$label} diperbarui",
            $label,
            array_merge([
                'section' => $section,
                'changes' => $changes,
            ], $extraProperties),
        );
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @param  list<string>  $hiddenKeys
     * @return array<string, array{before: mixed, after: mixed}>
     */
    public function diffValues(array $before, array $after, array $hiddenKeys = []): array
    {
        $changes = [];

        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $beforeValue = $before[$key] ?? null;
            $afterValue = $after[$key] ?? null;

            if ($this->comparableValue($beforeValue) === $this->comparableValue($afterValue)) {
                continue;
            }

            $changes[$key] = [
                'before' => in_array($key, $hiddenKeys, true) ? '[REDACTED]' : $this->normalizeValue($beforeValue),
                'after' => in_array($key, $hiddenKeys, true) ? '[REDACTED]' : $this->normalizeValue($afterValue),
            ];
        }

        return $changes;
    }

    protected function currentRequest(): ?Request
    {
        if (! app()->bound('request')) {
            return null;
        }

        $request = request();

        return $request instanceof Request ? $request : null;
    }

    protected function subjectLabel(Model|string|null $subject): ?string
    {
        if (is_string($subject)) {
            return $subject;
        }

        if (! $subject instanceof Model) {
            return null;
        }

        foreach (['title', 'name', 'catalog_title', 'email'] as $key) {
            $value = $subject->getAttribute($key);

            if (filled($value)) {
                return (string) $value;
            }
        }

        return class_basename($subject).' #'.$subject->getKey();
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<string, mixed>
     */
    protected function normalizeProperties(array $properties): array
    {
        return Arr::map($properties, fn (mixed $value): mixed => $this->normalizeValue($value));
    }

    protected function comparableValue(mixed $value): string
    {
        return json_encode($this->normalizeValue($value));
    }

    protected function normalizeValue(mixed $value): mixed
    {
        if ($value instanceof Model) {
            return [
                'type' => class_basename($value),
                'id' => $value->getKey(),
                'label' => $this->subjectLabel($value),
            ];
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(\DateTimeInterface::ATOM);
        }

        if (is_array($value)) {
            return Arr::map($value, fn (mixed $nestedValue): mixed => $this->normalizeValue($nestedValue));
        }

        if (is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return $value;
    }
}
