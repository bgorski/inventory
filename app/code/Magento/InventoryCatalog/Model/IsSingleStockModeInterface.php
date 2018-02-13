<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Service checks if the system has multiple stocks configured
 *
 * @api
 */
interface IsSingleStockModeInterface
{
    /**
     * Check if system has more than one enabled Source configured
     *
     * @return bool
     */
    public function execute(): bool;
}
