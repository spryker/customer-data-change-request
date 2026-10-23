<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerDataChangeRequest\Business;

use Generated\Shared\Transfer\CustomerDataChangeRequestCollectionTransfer;
use Generated\Shared\Transfer\CustomerDataChangeRequestCriteriaTransfer;
use Generated\Shared\Transfer\CustomerDataChangeRequestTransfer;
use Generated\Shared\Transfer\CustomerDataChangeResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\VerificationTokenCustomerChangeDataResponseTransfer;

interface CustomerDataChangeRequestFacadeInterface
{
    /**
     * Specification:
     *  - Retrieves the existing customer data based on the provided customer ID.
     *  - Generates a verification token for the email change request.
     *  - Sends a verification email if the new email differs from the current one.
     *  - Does not send the email when the customer or the new email reached the configured limit of email change requests in the current fixed window (`0` means no limit). The window starts at the first request and lasts `CustomerDataChangeRequestConfig::getEmailChangeRequestLimitTtl()` seconds.
     *  - The counter of the new email is shared by all customers, so requests of one customer to an email also count for other customers that request the same email.
     *  - When the limit is reached, restores the current email, sets the `customer.change_customer_email_limit_exceeded` message with `messageType=error` and returns `isLimitExceeded=true`.
     *  - Increments the email change request counter of the customer, then the counter of the new email, and compares each new count with the limit. Stops at the first counter that exceeds the limit, so the other counter is not incremented.
     *  - Each counter is incremented atomically on its own; the two counters are not updated in one atomic operation.
     *  - The counters are incremented before the request is saved and the email is sent, so a failed save or send and a rejected request still count.
     *
     * @api
     */
    public function sendVerificationEmail(CustomerTransfer $customerTransfer): VerificationTokenCustomerChangeDataResponseTransfer;

    /**
     * Specification:
     * - Handles customer data change requests by delegating processing to the appropriate strategy.
     * - Iterates through available customer data change request strategies and applies applicable ones. If no strategy matches the request, an error response is returned.
     *
     * @api
     */
    public function changeCustomerData(CustomerDataChangeRequestTransfer $customerDataChangeRequestTransfer): CustomerDataChangeResponseTransfer;

    /**
     * Specification:
     * - Retrieves customer data change request collection.
     *
     * @api
     */
    public function getCustomerDataChangeRequestCollection(
        CustomerDataChangeRequestCriteriaTransfer $customerDataChangeRequestCriteriaTransfer
    ): CustomerDataChangeRequestCollectionTransfer;
}
