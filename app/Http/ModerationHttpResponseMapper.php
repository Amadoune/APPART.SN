<?php

namespace App\Http;

use App\Application\ModerationHttp\ModerationHttpResult;
use App\Application\ModerationHttp\ModerationHttpStatus;
use DateTimeInterface;
use UnitEnum;

final readonly class ModerationHttpResponseMapper
{
    /** @return array{status:int,body:array<string, mixed>} */
    public function map(ModerationHttpResult $result): array
    {
        $status = match ($result->status) {
            ModerationHttpStatus::Succeeded => 200,
            ModerationHttpStatus::Created => 201,
            ModerationHttpStatus::NotFound => 404,
            ModerationHttpStatus::Forbidden => 403,
            ModerationHttpStatus::Conflict => 409,
            ModerationHttpStatus::Invalid => 422,
            ModerationHttpStatus::Unavailable => 503,
        };

        return [
            'status' => $status,
            'body' => ['status' => $result->status->value] + $this->normalize($result->data),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = $this->value($value);
        }

        return $data;
    }

    private function value(mixed $value): mixed
    {
        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d\TH:i:s.uP');
        }
        if ($value instanceof UnitEnum) {
            return $value instanceof \BackedEnum ? $value->value : $value->name;
        }
        if (is_object($value)) {
            return $this->normalize(get_object_vars($value));
        }
        if (is_array($value)) {
            return array_map(fn (mixed $item): mixed => $this->value($item), $value);
        }

        return $value;
    }
}
