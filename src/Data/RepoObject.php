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

class RepoObject
{
    private int $obj_id;
    private string $type;
    private string $title;
    private string $description;
    private array $ref_ids;

    public function __construct(
        int $obj_id,
        string $type,
        string $title,
        string $description
    ) {
        $this->obj_id = $obj_id;
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return int[]
     */
    public function getRefIds(): array
    {
        return $this->ref_ids;
    }

    public function withRefId(int $ref_id): RepoObject
    {
        $clone = clone $this;
        $clone->ref_ids[] = $ref_id;
        return $clone;
    }

    public function getInfoline(): string
    {
        return  $this->getObjId() . "\t" . $this->getType() . "\t" . $this->getTitle() . "\t"
            . implode(', ', $this->getRefIds());
    }

}
