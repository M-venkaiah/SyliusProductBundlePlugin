<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusProductBundlePlugin\Unit\Handler;

use BitBag\SyliusProductBundlePlugin\Command\AddProductBundleToCartCommand;
use BitBag\SyliusProductBundlePlugin\Entity\ProductInterface;
use BitBag\SyliusProductBundlePlugin\Handler\AddProductBundleToCartHandler;
use BitBag\SyliusProductBundlePlugin\Handler\AddProductBundleToCartHandler\CartProcessorInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;
use Tests\BitBag\SyliusProductBundlePlugin\Unit\MotherObject\OrderMother;
use Tests\BitBag\SyliusProductBundlePlugin\Unit\MotherObject\ProductBundleMother;
use Tests\BitBag\SyliusProductBundlePlugin\Unit\MotherObject\ProductMother;
use Tests\BitBag\SyliusProductBundlePlugin\Unit\TypeExceptionMessage;
use Webmozart\Assert\InvalidArgumentException;

final class AddProductBundleToCartHandlerTest extends TestCase
{
    /** @var mixed|MockObject|OrderRepositoryInterface */
    private $orderRepository;

    /** @var mixed|MockObject|ProductRepositoryInterface */
    private $productRepository;

    /** @var CartProcessorInterface|mixed|MockObject */
    private $cartProcessor;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepositoryInterface::class);
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->cartProcessor = $this->createMock(CartProcessorInterface::class);
    }

    public function testThrowExceptionIfCartDoesntExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(TypeExceptionMessage::EXPECTED_VALUE_OTHER_THAN_NULL);

        $this->orderRepository->expects(self::once())
            ->method('findCartById')
            ->willReturn(null)
        ;

        $command = new AddProductBundleToCartCommand(0, '', 1);
        $handler = $this->createHandler();
        $handler($command);
    }

    /**
     * @dataProvider pessimisticDataProvider
     */
    public function testPessimisticCase(
        string $exceptionMessage,
        ?OrderInterface $cart,
        ?ProductInterface $product,
        int $quantity,
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($exceptionMessage);

        $this->orderRepository->method('findCartById')
            ->willReturn($cart)
        ;

        $this->productRepository->method('findOneByCode')
            ->willReturn($product)
        ;

        $command = new AddProductBundleToCartCommand(0, '', $quantity);
        $handler = $this->createHandler();
        $handler($command);
    }

    public function pessimisticDataProvider(): array
    {
        $productBundle = ProductBundleMother::create();
        $productWithBundle = ProductMother::createWithBundle($productBundle);

        return [
            'order is a null' => [TypeExceptionMessage::EXPECTED_VALUE_OTHER_THAN_NULL, null, null, 1],
            'product is a null' => [TypeExceptionMessage::EXPECTED_VALUE_OTHER_THAN_NULL, OrderMother::create(), null, 1],
            'product is not a bundle' => [
                'Expected a value to be true. Got: false',
                OrderMother::create(),
                ProductMother::create(),
                1,
            ],
            'quantity is not greater than 0' => [
                'Expected a value greater than 0. Got: 0',
                OrderMother::create(),
                $productWithBundle,
                0,
            ],
        ];
    }

    public function testProcessCart(): void
    {
        $cart = OrderMother::create();
        $this->orderRepository->method('findCartById')
            ->willReturn($cart)
        ;

        $productBundle = ProductBundleMother::create();
        $product = ProductMother::createWithBundle($productBundle);
        $this->productRepository->method('findOneByCode')
            ->willReturn($product)
        ;

        $this->cartProcessor->expects(self::once())
            ->method('process')
            ->with($cart, $productBundle, 2)
        ;

        $command = new AddProductBundleToCartCommand(1, '', 2);
        $handler = $this->createHandler();
        $handler($command);
    }

    public function testAddCartToRepository(): void
    {
        $cart = OrderMother::create();
        $this->orderRepository->method('findCartById')
            ->willReturn($cart)
        ;

        $productBundle = ProductBundleMother::create();
        $product = ProductMother::createWithBundle($productBundle);
        $this->productRepository->method('findOneByCode')
            ->willReturn($product)
        ;

        $this->orderRepository->expects(self::once())
            ->method('add')
            ->with($cart)
        ;

        $command = new AddProductBundleToCartCommand(1, '', 1);
        $handler = $this->createHandler();
        $handler($command);
    }

    private function createHandler(): AddProductBundleToCartHandler
    {
        return new AddProductBundleToCartHandler(
            $this->orderRepository,
            $this->productRepository,
            $this->cartProcessor,
        );
    }
}
