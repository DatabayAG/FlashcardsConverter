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

class FlashcardUsage
{
    public function __construct(
        private readonly int $obj_id,
        private readonly int $user_id,
        private readonly int $card_id,
        private readonly ?int $status,
        private readonly ?string $last_checked,
        private readonly ?int $last_result,
        private readonly ?int $times_checked,
        private readonly ?int $times_known,
        private readonly ?int $last_status
    ) {
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getCardId(): int
    {
        return $this->card_id;
    }

    /**
     * @return int<0,max>|null box number
     */
    public function getStatus(): ?int
    {
        return $this->status;
    }

    /**
     * @return string|null  Datetime string
     */
    public function getLastChecked(): ?string
    {
        return $this->last_checked;
    }

    public function getLastResult(): ?int
    {
        return $this->last_result;
    }

    public function getTimesChecked(): ?int
    {
        return $this->times_checked;
    }

    public function getTimesKnown(): ?int
    {
        return $this->times_known;
    }

    public function getLastStatus(): ?int
    {
        return $this->last_status;
    }
}
