<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerDataChangeRequest\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\SecurityCheckAttemptResponseTransfer;
use Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Writer\CustomerDataChangeRequestWriterInterface;
use Spryker\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestDependencyProvider;
use Spryker\Zed\CustomerDataChangeRequest\Dependency\CustomerDataChangeRequestToCustomerFacadeInterface;
use Spryker\Zed\CustomerDataChangeRequest\Dependency\CustomerDataChangeRequestToMailFacadeInterface;
use SprykerTest\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerDataChangeRequest
 * @group Business
 * @group Facade
 * @group SendVerificationEmailTest
 * Add your own group annotations below this line
 */
class SendVerificationEmailTest extends Unit
{
    protected const string GLOSSARY_KEY_EMAIL_CHANGE_LIMIT_EXCEEDED = 'customer.change_customer_email_limit_exceeded';

    protected const string MESSAGE_TYPE_ERROR = 'error';

    protected CustomerDataChangeRequestBusinessTester $tester;

    public function testSendVerificationEmailDoesNotSaveRequestAndDoesNotSendEmailWhenLimitIsExceeded(): void
    {
        // Arrange
        $this->mockCustomerFacade();
        $this->mockMailFacadeWithoutCall();
        $this->mockSecurityBlockerClientWithExceededCounter();
        $this->mockCustomerDataChangeRequestWriterWithoutCall();
        $customerTransfer = $this->tester->createTestCustomerTransferWithNewEmail();

        // Act
        $verificationTokenCustomerChangeDataResponseTransfer = $this->tester->getFacade()->sendVerificationEmail($customerTransfer);

        // Assert
        $this->assertFalse($verificationTokenCustomerChangeDataResponseTransfer->getIsSent());
        $this->assertTrue($verificationTokenCustomerChangeDataResponseTransfer->getIsLimitExceeded());
        $this->assertSame(CustomerDataChangeRequestBusinessTester::TEST_CUSTOMER_EMAIL, $customerTransfer->getEmail());
        $this->assertSame(static::GLOSSARY_KEY_EMAIL_CHANGE_LIMIT_EXCEEDED, $customerTransfer->getMessage());
        $this->assertSame(static::MESSAGE_TYPE_ERROR, $customerTransfer->getMessageType());
    }

    protected function mockCustomerFacade(): void
    {
        $customerFacadeMock = $this->createMock(CustomerDataChangeRequestToCustomerFacadeInterface::class);
        $customerFacadeMock
            ->method('getCustomerByCriteria')
            ->willReturn((new CustomerResponseTransfer())->setCustomerTransfer($this->tester->createTestCustomerTransfer()));

        $this->tester->setDependency(CustomerDataChangeRequestDependencyProvider::FACADE_CUSTOMER, $customerFacadeMock);
    }

    protected function mockMailFacadeWithoutCall(): void
    {
        $mailFacadeMock = $this->createMock(CustomerDataChangeRequestToMailFacadeInterface::class);
        $mailFacadeMock
            ->expects($this->never())
            ->method('handleMail');

        $this->tester->setDependency(CustomerDataChangeRequestDependencyProvider::FACADE_MAIL, $mailFacadeMock);
    }

    protected function mockSecurityBlockerClientWithExceededCounter(): void
    {
        $securityBlockerClientMock = $this->createMock(SecurityBlockerClientInterface::class);
        $securityBlockerClientMock
            ->expects($this->once())
            ->method('incrementAttemptCount')
            ->willReturn((new SecurityCheckAttemptResponseTransfer())->setNumberOfAttempts(PHP_INT_MAX));

        $this->tester->setDependency(CustomerDataChangeRequestDependencyProvider::CLIENT_SECURITY_BLOCKER, $securityBlockerClientMock);
    }

    protected function mockCustomerDataChangeRequestWriterWithoutCall(): void
    {
        $customerDataChangeRequestWriterMock = $this->createMock(CustomerDataChangeRequestWriterInterface::class);
        $customerDataChangeRequestWriterMock
            ->expects($this->never())
            ->method('saveEmailChangeRequest');

        $this->tester->mockFactoryMethod('createCustomerDataChangeRequestWriter', $customerDataChangeRequestWriterMock);
    }
}
