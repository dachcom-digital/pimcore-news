<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace NewsBundle\Event;

use Pimcore\Model\Document\Editable\Area\Info;
use Symfony\Contracts\EventDispatcher\Event;

class NewsBrickEvent extends Event
{
    protected Info $info;
    protected array $querySettings;
    protected array $additionalViewParams = [];

    public function __construct(Info $info, array $querySettings)
    {
        $this->info = $info;
        $this->querySettings = $querySettings;
    }

    public function getInfo(): Info
    {
        return $this->info;
    }

    public function getQuerySettings(): array
    {
        return $this->querySettings;
    }

    public function setQuerySettings(array $querySettings): void
    {
        $this->querySettings = $querySettings;
    }

    public function getAdditionalViewParams(): array
    {
        if (!is_array($this->additionalViewParams)) {
            return [];
        }

        return $this->additionalViewParams;
    }

    public function addAdditionalViewParams(array $additionalViewParams): void
    {
        if (!is_array($this->additionalViewParams)) {
            $this->additionalViewParams = [];
        }

        $this->additionalViewParams = array_merge($this->additionalViewParams, $additionalViewParams);
    }
}
