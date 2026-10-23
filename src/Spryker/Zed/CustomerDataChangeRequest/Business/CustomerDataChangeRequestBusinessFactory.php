<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerDataChangeRequest\Business;

use Spryker\Client\SecurityBlocker\SecurityBlockerClientInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Customer\Strategy\ConfirmCustomerDataChangeRequestStrategyInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Customer\Strategy\EmailConfirmCustomerDataChangeRequestStrategy;
use Spryker\Zed\CustomerDataChangeRequest\Business\Limiter\EmailChangeRequestLimiter;
use Spryker\Zed\CustomerDataChangeRequest\Business\Limiter\EmailChangeRequestLimiterInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Logger\AuditLogger;
use Spryker\Zed\CustomerDataChangeRequest\Business\Logger\AuditLoggerInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Mail\CustomerDataChangeRequestMailSender;
use Spryker\Zed\CustomerDataChangeRequest\Business\Mail\CustomerDataChangeRequestMailSenderInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Notifier\NotificationEmailSender;
use Spryker\Zed\CustomerDataChangeRequest\Business\Notifier\NotificationEmailSenderInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Verifier\VerificationEmailSender;
use Spryker\Zed\CustomerDataChangeRequest\Business\Verifier\VerificationEmailSenderInterface;
use Spryker\Zed\CustomerDataChangeRequest\Business\Writer\CustomerDataChangeRequestWriter;
use Spryker\Zed\CustomerDataChangeRequest\Business\Writer\CustomerDataChangeRequestWriterInterface;
use Spryker\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestDependencyProvider;
use Spryker\Zed\CustomerDataChangeRequest\Dependency\CustomerDataChangeRequestToCustomerFacadeInterface;
use Spryker\Zed\CustomerDataChangeRequest\Dependency\CustomerDataChangeRequestToGlossaryFacadeInterface;
use Spryker\Zed\CustomerDataChangeRequest\Dependency\CustomerDataChangeRequestToMailFacadeInterface;
use Spryker\Zed\CustomerDataChangeRequest\Dependency\CustomerDataChangeRequestToUtilTextServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestConfig getConfig()
 * @method \Spryker\Zed\CustomerDataChangeRequest\Persistence\CustomerDataChangeRequestEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\CustomerDataChangeRequest\Persistence\CustomerDataChangeRequestRepositoryInterface getRepository()
 */
class CustomerDataChangeRequestBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\CustomerDataChangeRequest\Business\Verifier\VerificationEmailSender
     */
    public function createVerificationEmailSender(): VerificationEmailSenderInterface
    {
        return new VerificationEmailSender(
            $this->getCustomerFacade(),
            $this->createCustomerDataChangeRequestMailSender(),
            $this->getConfig(),
            $this->getUtilTextService(),
            $this->createCustomerDataChangeRequestWriter(),
            $this->createEmailChangeRequestLimiter(),
        );
    }

    public function createEmailChangeRequestLimiter(): EmailChangeRequestLimiterInterface
    {
        return new EmailChangeRequestLimiter(
            $this->getSecurityBlockerClient(),
            $this->getConfig(),
        );
    }

    public function createCustomerDataChangeRequestMailSender(): CustomerDataChangeRequestMailSenderInterface
    {
        return new CustomerDataChangeRequestMailSender($this->getMailFacade());
    }

    public function createCustomerDataChangeRequestWriter(): CustomerDataChangeRequestWriterInterface
    {
        return new CustomerDataChangeRequestWriter(
            $this->getEntityManager(),
            $this->getRepository(),
            $this->getConfirmCustomerDataChangeRequestStrategies(),
        );
    }

    public function createEmailConfirmCustomerDataChangeRequestStrategy(): ConfirmCustomerDataChangeRequestStrategyInterface
    {
        return new EmailConfirmCustomerDataChangeRequestStrategy(
            $this->getRepository(),
            $this->getEntityManager(),
            $this->getCustomerFacade(),
            $this->createAuditLogger(),
            $this->getGlossaryFacade(),
            $this->createNotificationEmailSender(),
        );
    }

    public function createAuditLogger(): AuditLoggerInterface
    {
        return new AuditLogger();
    }

    public function createNotificationEmailSender(): NotificationEmailSenderInterface
    {
        return new NotificationEmailSender(
            $this->getMailFacade(),
            $this->getConfig(),
        );
    }

    public function getCustomerFacade(): CustomerDataChangeRequestToCustomerFacadeInterface
    {
        return $this->getProvidedDependency(CustomerDataChangeRequestDependencyProvider::FACADE_CUSTOMER);
    }

    public function getMailFacade(): CustomerDataChangeRequestToMailFacadeInterface
    {
        return $this->getProvidedDependency(CustomerDataChangeRequestDependencyProvider::FACADE_MAIL);
    }

    public function getUtilTextService(): CustomerDataChangeRequestToUtilTextServiceInterface
    {
        return $this->getProvidedDependency(CustomerDataChangeRequestDependencyProvider::SERVICE_UTIL_TEXT);
    }

    /**
     * @return array<\Spryker\Zed\CustomerDataChangeRequest\Business\Customer\Strategy\ConfirmCustomerDataChangeRequestStrategyInterface>
     */
    public function getConfirmCustomerDataChangeRequestStrategies(): array
    {
        return [
            $this->createEmailConfirmCustomerDataChangeRequestStrategy(),
        ];
    }

    public function getSecurityBlockerClient(): SecurityBlockerClientInterface
    {
        return $this->getProvidedDependency(CustomerDataChangeRequestDependencyProvider::CLIENT_SECURITY_BLOCKER);
    }

    public function getGlossaryFacade(): CustomerDataChangeRequestToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(CustomerDataChangeRequestDependencyProvider::FACADE_GLOSSARY);
    }
}
