<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerDataChangeRequest\Business\Limiter;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\SecurityCheckAttemptContextTransfer;
use Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface;
use Spryker\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestConfig;

class EmailChangeRequestLimiter implements EmailChangeRequestLimiterInterface
{
    protected const string SECURITY_CHECK_TYPE = 'customer_data_change_request:email_change';

    protected const string IDENTIFIER_PATTERN_CUSTOMER = 'customer:%d';

    protected const string IDENTIFIER_PATTERN_EMAIL = 'email:%s';

    protected const string EMAIL_HASH_ALGORITHM = 'sha256';

    public function __construct(
        protected SecurityBlockerClientInterface $securityBlockerClient,
        protected CustomerDataChangeRequestConfig $customerDataChangeRequestConfig
    ) {
    }

    public function reserveRequest(CustomerTransfer $customerTransfer): bool
    {
        $limit = $this->customerDataChangeRequestConfig->getEmailChangeRequestLimit();

        if ($limit <= 0) {
            return true;
        }

        $ttl = $this->customerDataChangeRequestConfig->getEmailChangeRequestLimitTtl();

        // Stops at the first exceeded counter, so the two counters can differ. This is intended: the goal is to stop uncontrolled email sending, not exact counts.
        foreach ($this->getIdentifiers($customerTransfer) as $identifier) {
            $securityCheckAttemptResponseTransfer = $this->securityBlockerClient->incrementAttemptCount(
                $this->createSecurityCheckAttemptContextTransfer($identifier, $ttl),
            );

            if ($securityCheckAttemptResponseTransfer->getNumberOfAttemptsOrFail() > $limit) {
                return false;
            }
        }

        return true;
    }

    protected function createSecurityCheckAttemptContextTransfer(string $identifier, int $ttl): SecurityCheckAttemptContextTransfer
    {
        return (new SecurityCheckAttemptContextTransfer())
            ->setType(static::SECURITY_CHECK_TYPE)
            ->setIdentifier($identifier)
            ->setTtl($ttl);
    }

    /**
     * @return array<string>
     */
    protected function getIdentifiers(CustomerTransfer $customerTransfer): array
    {
        $normalizedEmail = mb_strtolower(trim($customerTransfer->getEmailOrFail()));

        return [
            sprintf(static::IDENTIFIER_PATTERN_CUSTOMER, $customerTransfer->getIdCustomerOrFail()),
            sprintf(static::IDENTIFIER_PATTERN_EMAIL, hash(static::EMAIL_HASH_ALGORITHM, $normalizedEmail)),
        ];
    }
}
