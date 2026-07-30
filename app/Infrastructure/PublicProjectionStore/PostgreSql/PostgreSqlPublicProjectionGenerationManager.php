<?php

namespace App\Infrastructure\PublicProjectionStore\PostgreSql;

use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationManager;
use App\Application\PublicProjectionRebuild\Contract\PublicProjectionGenerationValidator;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationManifest;
use App\Application\PublicProjectionRebuild\PublicProjectionGenerationTransition;
use App\Application\PublicProjectionStore\PublicProjectionGenerationId;
use PDO;
use Throwable;

final readonly class PostgreSqlPublicProjectionGenerationManager implements PublicProjectionGenerationManager
{
    public function __construct(private PDO $connection, private PublicProjectionGenerationValidator $validator) {}

    public function createCandidate(PublicProjectionGenerationId $generationId): PublicProjectionGenerationTransition
    {
        return $this->transactional(function () use ($generationId): PublicProjectionGenerationTransition {
            $state = $this->state($generationId, true);
            if ($state === 'candidate') {
                return PublicProjectionGenerationTransition::AlreadyApplied;
            }
            if ($state !== null) {
                return PublicProjectionGenerationTransition::InvalidState;
            }
            $statement = $this->connection->prepare("INSERT INTO public_projection.generations (generation_id,state) VALUES (:generation,'candidate')");
            $statement->execute(['generation' => $generationId->value]);

            return PublicProjectionGenerationTransition::Applied;
        });
    }

    public function activate(PublicProjectionGenerationId $generationId, PublicProjectionGenerationManifest $manifest): PublicProjectionGenerationTransition
    {
        return $this->transactional(function () use ($generationId, $manifest): PublicProjectionGenerationTransition {
            $states = $this->lockedStates($generationId);
            if (($states[$generationId->value] ?? null) === 'active') {
                return PublicProjectionGenerationTransition::AlreadyApplied;
            }
            if (! isset($states[$generationId->value])) {
                return PublicProjectionGenerationTransition::GenerationNotFound;
            }
            if ($states[$generationId->value] !== 'candidate') {
                return PublicProjectionGenerationTransition::InvalidState;
            }
            if (! $this->validator->validate($generationId, $manifest)->isValid()) {
                return PublicProjectionGenerationTransition::ValidationFailed;
            }
            $this->connection->exec("UPDATE public_projection.generations SET state='retired' WHERE state='active'");
            $statement = $this->connection->prepare("UPDATE public_projection.generations SET state='active' WHERE generation_id=:generation AND state='candidate'");
            $statement->execute(['generation' => $generationId->value]);

            return PublicProjectionGenerationTransition::Applied;
        });
    }

    public function rollback(PublicProjectionGenerationId $generationId): PublicProjectionGenerationTransition
    {
        return $this->transactional(function () use ($generationId): PublicProjectionGenerationTransition {
            $states = $this->lockedStates($generationId);
            if (($states[$generationId->value] ?? null) === 'active') {
                return PublicProjectionGenerationTransition::AlreadyApplied;
            }
            if (! isset($states[$generationId->value])) {
                return PublicProjectionGenerationTransition::GenerationNotFound;
            }
            if ($states[$generationId->value] !== 'retired') {
                return PublicProjectionGenerationTransition::InvalidState;
            }
            $this->connection->exec("UPDATE public_projection.generations SET state='retired' WHERE state='active'");
            $statement = $this->connection->prepare("UPDATE public_projection.generations SET state='active' WHERE generation_id=:generation AND state='retired'");
            $statement->execute(['generation' => $generationId->value]);

            return PublicProjectionGenerationTransition::Applied;
        });
    }

    private function state(PublicProjectionGenerationId $generationId, bool $lock): ?string
    {
        $statement = $this->connection->prepare('SELECT state FROM public_projection.generations WHERE generation_id=:generation'.($lock ? ' FOR UPDATE' : ''));
        $statement->execute(['generation' => $generationId->value]);
        $state = $statement->fetchColumn();

        return is_string($state) ? $state : null;
    }

    /** @return array<string, string> */
    private function lockedStates(PublicProjectionGenerationId $generationId): array
    {
        $statement = $this->connection->prepare("SELECT generation_id,state FROM public_projection.generations WHERE generation_id=:generation OR state='active' ORDER BY generation_id FOR UPDATE");
        $statement->execute(['generation' => $generationId->value]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $states = [];
        foreach ($rows as $row) {
            $states[(string) $row['generation_id']] = (string) $row['state'];
        }

        return $states;
    }

    private function transactional(callable $operation): PublicProjectionGenerationTransition
    {
        $owner = ! $this->connection->inTransaction();
        if ($owner) {
            $this->connection->beginTransaction();
        }
        try {
            $result = $operation();
            if ($owner) {
                $this->connection->commit();
            }

            return $result;
        } catch (Throwable $error) {
            if ($owner) {
                $this->connection->rollBack();
            }
            throw $error;
        }
    }
}
