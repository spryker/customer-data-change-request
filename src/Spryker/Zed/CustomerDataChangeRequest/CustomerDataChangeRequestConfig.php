<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerDataChangeRequest;

use Spryker\Shared\Customer\CustomerConstants;
use Spryker\Zed\Kernel\AbstractBundleConfig;

/**
 * @method \Spryker\Shared\CustomerDataChangeRequest\CustomerDataChangeRequestConfig getSharedConfig()
 */
class CustomerDataChangeRequestConfig extends AbstractBundleConfig
{
    /**
     *  Specification:
     *  - Defines the name of the email template for the customer email change verification.
     *
     * @api
     *
     * @var string
     */
    public const CUSTOMER_EMAIL_CHANGE_VERIFICATION_MAIL_TYPE = 'CUSTOMER_EMAIL_CHANGE_VERIFICATION_MAIL';

    /**
     * Specification:
     * - Defines the name of the email template for the customer email change notification.
     *
     * @api
     *
     * @var string
     */
    public const CUSTOMER_EMAIL_CHANGE_NOTIFICATION_MAIL_TYPE = 'CUSTOMER_EMAIL_CHANGE_NOTIFICATION_MAIL';

    /**
     * @var string
     */
    protected const EMAIL_CHANGE_TOKEN_URL = '/customer-data-change-request/change-email?verification_token=%s&_store=%s';

    /**
     * @var string
     */
    protected const EMAIL_CHANGE_TOKEN_URL_WITHOUT_STORE = '/customer-data-change-request/change-email?verification_token=%s';

    protected const int EMAIL_CHANGE_REQUEST_LIMIT = 3;

    protected const int EMAIL_CHANGE_REQUEST_LIMIT_TTL_SECONDS = 900;

    /**
     * Specification:
     * - Returns the maximum number of email change requests per customer and per target email in one fixed window.
     * - The window starts at the first request and ends after `getEmailChangeRequestLimitTtl()` seconds, so up to two times the limit is possible around the end of a window.
     * - The counter of a target email is shared by all customers.
     * - Returns `0` when no limit is applied. A negative value also disables the limit.
     *
     * @api
     */
    public function getEmailChangeRequestLimit(): int
    {
        return static::EMAIL_CHANGE_REQUEST_LIMIT;
    }

    /**
     * Specification:
     * - Returns the length of the email change request limit window in seconds, counted from the first request in the window.
     * - A value below `60` is raised to `60` seconds by `SecurityBlockerClient::incrementAttemptCount()`.
     *
     * @api
     */
    public function getEmailChangeRequestLimitTtl(): int
    {
        return static::EMAIL_CHANGE_REQUEST_LIMIT_TTL_SECONDS;
    }

    /**
     * Specification:
     * - Returns the expiration time in minutes for the email change verification token.
     *
     * @api
     *
     * @uses \Spryker\Shared\CustomerDataChangeRequest\CustomerDataChangeRequestConfig::getEmailChangeVerificationExpirationMinutes()
     */
    public function getEmailChangeVerificationExpirationMinutes(): int
    {
        return $this->getSharedConfig()->getEmailChangeVerificationExpirationMinutes();
    }

    /**
     * Specification:
     * - Provides a email change token url.
     *
     * @api
     */
    public function getEmailChaneTokenUrl(string $token, ?string $storeName = null): string
    {
        if ($storeName === null) {
            return sprintf($this->getHostYves() . static::EMAIL_CHANGE_TOKEN_URL_WITHOUT_STORE, $token);
        }

        return sprintf($this->getHostYves() . static::EMAIL_CHANGE_TOKEN_URL, $token, $storeName);
    }

    /**
     * @api
     */
    protected function getHostYves(): string
    {
        return $this->get(CustomerConstants::BASE_URL_YVES);
    }
}
