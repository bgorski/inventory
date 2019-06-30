<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupQuote\Plugin\Quote\Address;

use Magento\InventoryInStorePickupQuote\Model\ResourceModel\DeleteQuoteAddressPickupLocation;
use Magento\InventoryInStorePickupQuote\Model\ResourceModel\SaveQuoteAddressPickupLocation;
use Magento\InventoryInStorePickupShippingApi\Model\Carrier\InStorePickup;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Save or delete selected Pickup Location Code for Quote Address.
 */
class ManageAssignmentOfPickupLocationToQuoteAddress
{
    /**
     * @var SaveQuoteAddressPickupLocation
     */
    private $saveQuoteAddressPickupLocation;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var DeleteQuoteAddressPickupLocation
     */
    private $deleteQuoteAddressPickupLocation;

    /**
     * @param SaveQuoteAddressPickupLocation $saveQuoteAddressPickupLocation
     * @param DeleteQuoteAddressPickupLocation $deleteQuoteAddressPickupLocation
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        SaveQuoteAddressPickupLocation $saveQuoteAddressPickupLocation,
        DeleteQuoteAddressPickupLocation $deleteQuoteAddressPickupLocation,
        CartRepositoryInterface $cartRepository
    ) {
        $this->saveQuoteAddressPickupLocation = $saveQuoteAddressPickupLocation;
        $this->cartRepository = $cartRepository;
        $this->deleteQuoteAddressPickupLocation = $deleteQuoteAddressPickupLocation;
    }

    /**
     * Save information about associate Pickup Location Code to Quote Address.
     *
     * @param Address $subject
     * @param Address $result
     *
     * @return Address
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAfterSave(Address $subject, Address $result): Address
    {
        $quote = $this->cartRepository->get($subject->getQuoteId());

        if (!$subject->getExtensionAttributes() ||
            !$quote->getExtensionAttributes() ||
            !$quote->getExtensionAttributes()->getShippingAssignments() ||
            !($subject->getAddressType() === Address::ADDRESS_TYPE_SHIPPING)
        ) {
            return $result;
        }

        $shippingAssignments = $quote->getExtensionAttributes()->getShippingAssignments();

        /** @var ShippingAssignmentInterface $shippingAssignment */
        $shippingAssignment = current($shippingAssignments);
        $shipping = $shippingAssignment->getShipping();

        if (!($shipping->getMethod() === InStorePickup::DELIVERY_METHOD &&
            $subject->getExtensionAttributes()->getPickupLocationCode())
        ) {
            $this->deleteQuoteAddressPickupLocation->execute((int)$subject->getId());

            return $result;
        }

        $this->saveQuoteAddressPickupLocation->execute(
            (int)$subject->getId(),
            $subject->getExtensionAttributes()->getPickupLocationCode()
        );

        return $result;
    }
}
