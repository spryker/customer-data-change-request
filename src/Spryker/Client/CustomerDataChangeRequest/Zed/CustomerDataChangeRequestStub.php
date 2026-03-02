<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\CustomerDataChangeRequest\Zed;

use Generated\Shared\Transfer\CustomerDataChangeRequestCollectionTransfer;
use Generated\Shared\Transfer\CustomerDataChangeRequestCriteriaTransfer;
use Generated\Shared\Transfer\CustomerDataChangeRequestTransfer;
use Generated\Shared\Transfer\CustomerDataChangeResponseTransfer;
use Spryker\Client\ZedRequest\ZedRequestClient;

class CustomerDataChangeRequestStub implements CustomerDataChangeRequestStubInterface
{
    public function __construct(protected ZedRequestClient $zedStub)
    {
    }

    public function changeCustomerData(CustomerDataChangeRequestTransfer $customerDataChangeRequestTransfer): CustomerDataChangeResponseTransfer
    {
        /** @var \Generated\Shared\Transfer\CustomerDataChangeResponseTransfer $customerDataChangeResponseTransfer */
        $customerDataChangeResponseTransfer = $this->zedStub->call('/customer-data-change-request/gateway/change-customer-data', $customerDataChangeRequestTransfer);

        return $customerDataChangeResponseTransfer;
    }

    public function getCustomerDataChangeRequestCollection(
        CustomerDataChangeRequestCriteriaTransfer $customerDataChangeRequestCriteriaTransfer
    ): CustomerDataChangeRequestCollectionTransfer {
        /** @var \Generated\Shared\Transfer\CustomerDataChangeRequestCollectionTransfer $customerDataChangeRequestCollectionTransfer */
        $customerDataChangeRequestCollectionTransfer = $this->zedStub->call('/customer-data-change-request/gateway/get-customer-data-change-request-collection', $customerDataChangeRequestCriteriaTransfer);

        return $customerDataChangeRequestCollectionTransfer;
    }
}
