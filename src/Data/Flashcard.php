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

class Flashcard
{
    public function __construct(
        private readonly int $card_id,
        private readonly int $obj_id,
        private readonly ?int $term_id
    ) {
    }

    public function getCardId(): int
    {
        return $this->card_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getTermId(): ?int
    {
        return $this->term_id;
    }
}
