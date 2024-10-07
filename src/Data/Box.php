<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace FlashcardsConverter\Data;

class Box
{
    public function __construct(
        private readonly int $box_nr,
        private readonly int $usr_id,
        private readonly int $glo_id,
        private ?string $last_access
    ) {
    }

    public function getBoxNr(): int
    {
        return $this->box_nr;
    }

    public function getUsrId(): int
    {
        return $this->usr_id;
    }

    public function getGloId(): int
    {
        return $this->glo_id;
    }

    public function getLastAccess(): ?string
    {
        return $this->last_access;
    }

    public function withLastAccess(?string $last_access): Box
    {
        $clone = clone $this;
        $clone->last_access = $last_access;
        return $clone;
    }
}
