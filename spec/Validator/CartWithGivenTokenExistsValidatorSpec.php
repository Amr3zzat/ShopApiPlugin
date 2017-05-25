<?php

namespace spec\Sylius\ShopApiPlugin\Validator;

use Prophecy\Argument;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\ShopApiPlugin\Validator\CartWithGivenTokenExistsValidator;
use Sylius\ShopApiPlugin\Validator\Constraints\CartWithGivenTokenExists;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class CartWithGivenTokenExistsValidatorSpec extends ObjectBehavior
{
    function let(ExecutionContextInterface $executionContext, OrderRepositoryInterface $orderRepository)
    {
        $this->beConstructedWith($orderRepository);

        $this->initialize($executionContext);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(CartWithGivenTokenExistsValidator::class);
    }

    function it_does_not_add_constraint_if_order_exists(
        OrderInterface $order,
        OrderRepositoryInterface $orderRepository,
        ExecutionContextInterface $executionContext
    ) {
        $orderRepository->findOneBy(['tokenValue' => 'ORDERTOKEN', 'state' => OrderInterface::STATE_CART])->willReturn($order);

        $executionContext->addViolation(Argument::any(), Argument::any())->shouldNotBeCalled();

        $this->validate('ORDERTOKEN', new CartWithGivenTokenExists());
    }

    function it_adds_constraint_if_order_does_not_exits_exists(
        OrderRepositoryInterface $orderRepository,
        ExecutionContextInterface $executionContext
    ) {
        $orderRepository->findOneBy(['tokenValue' => 'ORDERTOKEN', 'state' => OrderInterface::STATE_CART])->willReturn(null);

        $executionContext->addViolation('sylius.shop_api.cart.not_exists')->shouldBeCalled();

        $this->validate('ORDERTOKEN', new CartWithGivenTokenExists());
    }
}
