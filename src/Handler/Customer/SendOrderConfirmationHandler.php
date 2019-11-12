<?php

declare(strict_types=1);

namespace Sylius\ShopApiPlugin\Handler\Customer;

use Sylius\Component\Core\Model\CustomerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\ShopApiPlugin\Command\Customer\SendOrderConfirmation;
use Sylius\ShopApiPlugin\Mailer\Emails;
use Webmozart\Assert\Assert;

final class SendOrderConfirmationHandler
{
    /** @var OrderRepositoryInterface */
    private $orderRepository;

    /** @var SenderInterface */
    private $sender;

    public function __construct(OrderRepositoryInterface $orderRepository, SenderInterface $sender)
    {
        $this->orderRepository = $orderRepository;
        $this->sender = $sender;
    }

    public function __invoke(SendOrderConfirmation $sendOrderConfirmation): void
    {
        /** @var OrderInterface $order */
        $order = $this->orderRepository->findOneByTokenValue($sendOrderConfirmation->orderToken());

        Assert::notNull($order, 'Order has not been found.');

        /** @var CustomerInterface $customer */
        $customer = $order->getCustomer();

        $this->sender->send(
            Emails::EMAIL_ORDER_CONFIRMATION,
            [$customer->getEmail()],
            ['order' => $order]
        );
    }
}
