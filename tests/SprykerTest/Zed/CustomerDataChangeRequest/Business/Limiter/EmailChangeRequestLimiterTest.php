<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CustomerDataChangeRequest\Business\Limiter;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Client\SecurityBlocker\SecurityBlockerConfig;
use Spryker\Client\SecurityBlocker\SecurityBlockerDependencyProvider;
use Spryker\Client\SecurityBlocker\SecurityBlockerFactory;
use Spryker\Zed\CustomerDataChangeRequest\Business\Limiter\EmailChangeRequestLimiter;
use Spryker\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestConfig;
use SprykerTest\Client\SecurityBlocker\Helper\InMemorySecurityBlockerRedisClient;
use SprykerTest\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CustomerDataChangeRequest
 * @group Business
 * @group Limiter
 * @group EmailChangeRequestLimiterTest
 * Add your own group annotations below this line
 */
class EmailChangeRequestLimiterTest extends Unit
{
    protected const int TTL = 900;

    protected const int TTL_BELOW_MINIMUM = 1;

    protected const int MIN_LIMIT_TTL_SECONDS = 60;

    protected const int OTHER_CUSTOMER_ID = 2;

    protected const string OTHER_EMAIL = 'other.test@spryker.com';

    protected const string STORAGE_KEY_PATTERN_CUSTOMER = 'kv:customer_data_change_request:email_change:customer%%3A%d';

    protected const string STORAGE_KEY_PATTERN_EMAIL = 'kv:customer_data_change_request:email_change:email%%3A%s';

    protected CustomerDataChangeRequestBusinessTester $tester;

    protected InMemorySecurityBlockerRedisClient $redisClient;

    protected function _before(): void
    {
        parent::_before();

        $this->redisClient = new InMemorySecurityBlockerRedisClient();
        $this->tester->setDependency(SecurityBlockerDependencyProvider::CLIENT_REDIS, $this->redisClient, SecurityBlockerFactory::class);
    }

    public function testReserveRequestReturnsTrueUntilLimitIsReached(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(2);
        $customerTransfer = $this->tester->createTestCustomerTransferWithNewEmail();

        // Act
        $isFirstRequestReserved = $emailChangeRequestLimiter->reserveRequest($customerTransfer);
        $isSecondRequestReserved = $emailChangeRequestLimiter->reserveRequest($customerTransfer);
        $isThirdRequestReserved = $emailChangeRequestLimiter->reserveRequest($customerTransfer);

        // Assert
        $this->assertTrue($isFirstRequestReserved);
        $this->assertTrue($isSecondRequestReserved);
        $this->assertFalse($isThirdRequestReserved);
    }

    public function testReserveRequestReturnsFalseForSameCustomerAndOtherEmailAfterLimitIsReached(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(1);
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Act
        $isRequestReserved = $emailChangeRequestLimiter->reserveRequest(
            $this->createCustomerTransfer(CustomerDataChangeRequestBusinessTester::TEST_CUSTOMER_ID, static::OTHER_EMAIL),
        );

        // Assert
        $this->assertFalse($isRequestReserved);
    }

    public function testReserveRequestReturnsFalseForOtherCustomerAndSameEmailAfterLimitIsReached(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(1);
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Act
        $isRequestReserved = $emailChangeRequestLimiter->reserveRequest(
            $this->createCustomerTransfer(static::OTHER_CUSTOMER_ID, strtoupper(CustomerDataChangeRequestBusinessTester::TEST_NEW_CUSTOMER_EMAIL)),
        );

        // Assert
        $this->assertFalse($isRequestReserved);
    }

    public function testReserveRequestReturnsTrueForOtherCustomerAndOtherEmailAfterLimitIsReached(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(1);
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Act
        $isRequestReserved = $emailChangeRequestLimiter->reserveRequest(
            $this->createCustomerTransfer(static::OTHER_CUSTOMER_ID, static::OTHER_EMAIL),
        );

        // Assert
        $this->assertTrue($isRequestReserved);
    }

    public function testReserveRequestIncrementsCounterBeforeLimitIsChecked(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(1);
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Act
        $isRequestReserved = $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Assert
        $this->assertFalse($isRequestReserved);
        $this->assertSame('2', $this->redisClient->get($this->getRedisConnectionKey(), $this->getCustomerStorageKey(CustomerDataChangeRequestBusinessTester::TEST_CUSTOMER_ID)));
    }

    public function testReserveRequestReturnsTrueAndStoresNoCounterWhenLimitIsZero(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(0);
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Act
        $isRequestReserved = $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Assert
        $this->assertTrue($isRequestReserved);
        $this->assertNull($this->redisClient->get($this->getRedisConnectionKey(), $this->getCustomerStorageKey(CustomerDataChangeRequestBusinessTester::TEST_CUSTOMER_ID)));
    }

    public function testReserveRequestSetsWindowTtlOnCounters(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(5);

        // Act
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Assert
        $this->assertTrue($this->hasTtlInRange($this->getCustomerStorageKey(CustomerDataChangeRequestBusinessTester::TEST_CUSTOMER_ID)));
        $this->assertTrue($this->hasTtlInRange($this->getEmailStorageKey(CustomerDataChangeRequestBusinessTester::TEST_NEW_CUSTOMER_EMAIL)));
    }

    public function testReserveRequestRaisesTtlBelowMinimumToMinimum(): void
    {
        // Arrange
        $emailChangeRequestLimiter = $this->createEmailChangeRequestLimiter(5, static::TTL_BELOW_MINIMUM);

        // Act
        $emailChangeRequestLimiter->reserveRequest($this->tester->createTestCustomerTransferWithNewEmail());

        // Assert
        $storageKey = $this->getCustomerStorageKey(CustomerDataChangeRequestBusinessTester::TEST_CUSTOMER_ID);
        $this->assertTrue($this->hasTtlInRange($storageKey, static::MIN_LIMIT_TTL_SECONDS));
        $this->assertFalse($this->hasTtlInRange($storageKey, static::TTL_BELOW_MINIMUM));
    }

    protected function createEmailChangeRequestLimiter(int $limit, int $ttl = self::TTL): EmailChangeRequestLimiter
    {
        /** @var \Spryker\Zed\CustomerDataChangeRequest\Business\CustomerDataChangeRequestBusinessFactory $customerDataChangeRequestBusinessFactory */
        $customerDataChangeRequestBusinessFactory = $this->tester->getFactory();

        return new EmailChangeRequestLimiter(
            $customerDataChangeRequestBusinessFactory->getSecurityBlockerClient(),
            $this->createConfigMock($limit, $ttl),
        );
    }

    protected function createConfigMock(int $limit, int $ttl): CustomerDataChangeRequestConfig
    {
        $configMock = $this->createMock(CustomerDataChangeRequestConfig::class);
        $configMock->method('getEmailChangeRequestLimit')->willReturn($limit);
        $configMock->method('getEmailChangeRequestLimitTtl')->willReturn($ttl);

        return $configMock;
    }

    protected function createCustomerTransfer(int $idCustomer, string $email): CustomerTransfer
    {
        return (new CustomerTransfer())
            ->setIdCustomer($idCustomer)
            ->setEmail($email);
    }

    protected function hasTtlInRange(string $storageKey, int $maxTtl = self::TTL): bool
    {
        $ttl = $this->redisClient->ttl($this->getRedisConnectionKey(), $storageKey);

        return $ttl > 0 && $ttl <= $maxTtl;
    }

    protected function getRedisConnectionKey(): string
    {
        return (new SecurityBlockerConfig())->getRedisConnectionKey();
    }

    protected function getCustomerStorageKey(int $idCustomer): string
    {
        return sprintf(static::STORAGE_KEY_PATTERN_CUSTOMER, $idCustomer);
    }

    protected function getEmailStorageKey(string $email): string
    {
        return sprintf(static::STORAGE_KEY_PATTERN_EMAIL, hash('sha256', mb_strtolower($email)));
    }
}
