<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * You can find more information about us on https://bitbag.io and write us
 * an email on hello@bitbag.io.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusProductBundlePlugin\Unit\MotherObject;

use BitBag\SyliusProductBundlePlugin\Command\AddProductBundleItemToCartCommand;
use BitBag\SyliusProductBundlePlugin\Entity\ProductBundleItemInterface;

final class AddProductBundleItemToCartCommandMother
{
    public static function create(ProductBundleItemInterface $bundleItem): AddProductBundleItemToCartCommand
    {
        return new AddProductBundleItemToCartCommand($bundleItem);
    }
}
