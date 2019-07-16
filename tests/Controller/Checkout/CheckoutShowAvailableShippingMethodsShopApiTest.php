<?php

declare(strict_types=1);

namespace Tests\Sylius\ShopApiPlugin\Controller\Checkout;

use Sylius\ShopApiPlugin\Command\Cart\AddressOrder;
use Sylius\ShopApiPlugin\Command\Cart\PickupCart;
use Sylius\ShopApiPlugin\Command\Cart\PutSimpleItemToCart;
use Sylius\ShopApiPlugin\Model\Address;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Tests\Sylius\ShopApiPlugin\Controller\JsonApiTestCase;

final class CheckoutShowAvailableShippingMethodsShopApiTest extends JsonApiTestCase
{
    /**
     * @test
     */
    public function it_does_not_provide_details_about_available_shipping_method_for_non_existing_cart(): void
    {
        $response = $this->showAvailableShippingMethods('WRONGTOKEN');
        $this->assertResponse($response, 'cart_with_given_token_does_not_exist', Response::HTTP_NOT_FOUND);
    }

    /**
     * @test
     */
    public function it_provides_details_about_available_shipping_method(): void
    {
        $this->loadFixturesFromFiles(['shop.yml', 'country.yml', 'shipping.yml']);

        $token = 'SDAOSLEFNWU35H3QLI5325';

        /** @var MessageBusInterface $bus */
        $bus = $this->get('sylius_shop_api_plugin.command_bus');
        $bus->dispatch(new PickupCart($token, 'WEB_GB'));
        $bus->dispatch(new PutSimpleItemToCart($token, 'LOGAN_MUG_CODE', 5));
        $bus->dispatch(new AddressOrder(
            $token,
            Address::createFromArray([
                'firstName' => 'Sherlock',
                'lastName' => 'Holmes',
                'city' => 'London',
                'street' => 'Baker Street 221b',
                'countryCode' => 'GB',
                'postcode' => 'NWB',
                'provinceName' => 'Greater London',
            ]), Address::createFromArray([
                'firstName' => 'Sherlock',
                'lastName' => 'Holmes',
                'city' => 'London',
                'street' => 'Baker Street 221b',
                'countryCode' => 'GB',
                'postcode' => 'NWB',
                'provinceName' => 'Greater London',
            ])
        ));

        $response = $this->showAvailableShippingMethods($token);
        $this->assertResponse($response, 'checkout/get_available_shipping_methods', Response::HTTP_OK);
    }

    /**
     * @test
     */
    public function it_does_not_provide_details_about_available_shipping_method_before_addressing(): void
    {
        $this->loadFixturesFromFiles(['shop.yml']);

        $token = 'SDAOSLEFNWU35H3QLI5325';

        /** @var MessageBusInterface $bus */
        $bus = $this->get('sylius_shop_api_plugin.command_bus');
        $bus->dispatch(new PickupCart($token, 'WEB_GB'));
        $bus->dispatch(new PutSimpleItemToCart($token, 'LOGAN_MUG_CODE', 5));

        $response = $this->showAvailableShippingMethods($token);
        $this->assertResponse($response, 'checkout/get_available_shipping_methods_failed', Response::HTTP_BAD_REQUEST);
    }

    private function showAvailableShippingMethods(string $token): Response
    {
        $this->client->request(
            'GET',
            sprintf('/shop-api/checkout/%s/shipping', $token),
            [],
            [],
            self::CONTENT_TYPE_HEADER
        );

        return $this->client->getResponse();
    }
}
