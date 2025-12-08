<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AiFoundation\VendorAdapter;

interface VendorProviderPluginInterface
{
    /**
     * Specification:
     * - Returns a vendor-specific adapter instance for AI operations.
     * - The adapter implements vendor-specific logic for communicating with AI providers.
     * - The adapter is responsible for mapping requests and responses between Spryker transfers and vendor-specific formats.
     */
    public function getVendorAdapter(): VendorAdapterInterface;
}
