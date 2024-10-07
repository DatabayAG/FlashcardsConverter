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

class FlashcardsTraining
{
    /**
     * Mode: show term ans ask for definitions
     */
    public const GLOSSARY_MODE_TERM_DEFINITIONS = 'term_def';

    /**
     * Mode: show definition 1 and ask for term
     */
    public const GLOSSARY_MODE_DEFINITION_TERM = 'def_term';

    /**
     * Mode: show first definition and ask for others
     */
    public const GLOSSARY_MODE_DEFINITIONS = 'defs';

    public function __construct(
        private readonly int $obj_id,
        private readonly bool $is_online,
        private readonly ?int $glossary_ref_id,
        private readonly ?string $glossary_mode,
        private readonly ?string $instructions
    ) {
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function isOnline(): bool
    {
        return $this->is_online;
    }

    public function getGlossaryRefId(): ?int
    {
        return $this->glossary_ref_id;
    }

    public function getGlossaryMode(): ?string
    {
        return $this->glossary_mode;
    }

    public function getInstructions(): ?string
    {
        return $this->instructions;
    }
}
