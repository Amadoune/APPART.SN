<?php

namespace Appart\Modules\SecurityCompliance\Application\OwnerReader;

use Appart\Modules\SecurityCompliance\Application\OwnerReader\Contract\SecurityComplianceOwnerReaderV1;
use Appart\Modules\SecurityCompliance\Application\OwnerSource\SecurityComplianceOwnerSource;
use Appart\Modules\SecurityCompliance\Application\PublicRead\Contract\SecretInventoryReaderV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryResultV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecretInventoryStatusV1;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceObservedAt;
use Appart\Modules\SecurityCompliance\Application\PublicRead\SecurityComplianceSubjectKey;

final readonly class SecretInventoryOwnerReader implements SecretInventoryReaderV1
{
    public function __construct(private SecurityComplianceOwnerSource $source, private SecurityComplianceOwnerReaderV1 $policy) {}

    public function read(SecurityComplianceSubjectKey $subject, SecurityComplianceObservedAt $observedAt): SecretInventoryResultV1
    {
        $status = SecretInventoryStatusV1::from($this->policy->secretInventory($this->source->readSecretInventory($subject, $observedAt))->status->value);

        return new SecretInventoryResultV1($status, $observedAt);
    }
}
