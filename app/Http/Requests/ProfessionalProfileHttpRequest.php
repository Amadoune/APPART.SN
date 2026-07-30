<?php

namespace App\Http\Requests;

use Appart\Modules\IdentityAccess\Domain\ValueObject\AccountId;
use Illuminate\Foundation\Http\FormRequest;
use LogicException;

final class ProfessionalProfileHttpRequest extends FormRequest
{
    public function accountId(): AccountId
    {
        $accountId = $this->attributes->get('iam_account_id');
        if (! $accountId instanceof AccountId) {
            throw new LogicException('The authenticated AccountId is required.');
        }

        return $accountId;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [];
    }
}
