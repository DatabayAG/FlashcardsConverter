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

use ilDBConstants;
use ilDBInterface;

class ConverterRepository
{
    public function __construct(
        protected readonly ilDBInterface $db
    ) {
    }

    /**
     * @return array<int, RepoObject>
     */
    public function getFlashcardTrainings(): array
    {
        $query = "
            SELECT d.obj_id, d.`type`, d.title, d.description, r.ref_id 
            FROM object_data d
            JOIN object_reference r ON r.obj_id = d.obj_id
            WHERE `type` = 'xflc'
            ORDER BY d.obj_id asc
        ";
        return $this->getRepoObjects($query);
    }

    /**
     * @return array<int, RepoObject>
     */
    public function getConvertedTrainings(): array
    {
        $query = "
            SELECT d.obj_id, d.`type`, d.title, d.description, r.ref_id 
            FROM object_data d
            JOIN object_reference r ON r.obj_id = d.obj_id
            JOIN il_meta_keyword k ON k.obj_id = d.obj_id 
            WHERE d.`type` = 'glo'
            AND k.keyword = 'FlashcardsConverter'        
        ";
        return $this->getRepoObjects($query);
    }

    public function getTrainingData(int $obj_id): FlashcardsTraining
    {
        $query = "SELECT * FROM rep_robj_xflc_data WHERE obj_id = %s";
        $result = $this->db->queryF($query, [ilDBConstants::T_INTEGER], [$obj_id]);

        if ($row = $this->db->fetchAssoc($result)) {
            return new FlashcardsTraining(
                (int) $row['obj_id'],
                (bool) $row['is_online'],
                isset($row['glossary_ref_id']) ? (int) $row['glossary_ref_id'] : null,
                isset($row['glossary_mode']) ? (string) $row['glossary_mode'] : null,
                isset($row['instructions']) ? (string) $row['instructions'] : null
            );
        }
        return new FlashcardsTraining($obj_id, false, null, null, null);
    }

    /**
     * @return array<int, Flashcard> indexed by term_id
     */
    public function getFlashcardsByTermId(int $obj_id): array
    {
        $query = "SELECT * FROM rep_robj_xflc_cards WHERE obj_id = %s";
        $result = $this->db->queryF($query, [ilDBConstants::T_INTEGER], [$obj_id]);

        $cards = [];
        while ($row = $this->db->fetchAssoc($result)) {
            if (isset($row['term_id'])) {
                $cards[$row['term_id']] = new Flashcard(
                    (int) $row['card_id'],
                    (int) $row['obj_id'],
                    (int) $row['term_id']
                );
            }
        }
        return $cards;
    }

    /**
     * @return array<int, array<int, FlashcardUsage>>indexed by card_id and user_id
     */
    public function getFlashcardUsagesByCardIdAndUserId(int $obj_id): array
    {
        $query = "SELECT * FROM rep_robj_xflc_usage WHERE obj_id = %s";
        $result = $this->db->queryF($query, [ilDBConstants::T_INTEGER], [$obj_id]);

        $usages = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $usages[$row['card_id']][$row['user_id']] = new FlashcardUsage(
                (int) $row['obj_id'],
                (int) $row['user_id'],
                (int) $row['card_id'],
                isset($row['status']) ? (int) $row['status'] : null,
                isset($row['last_checked']) ? (string) $row['last_checked'] : null,
                isset($row['last_result']) ? (int) $row['last_result'] : null,
                isset($row['times_checked']) ? (int) $row['times_checked'] : null,
                isset($row['times_known']) ? (int) $row['times_known'] : null,
                isset($row['last_status']) ? (int) $row['last_status'] : null
            );
        }
        return $usages;
    }

    public function changeObjectTypeToGlossary(int $obj_id): void
    {
        $query = "UPDATE object_data SET `type` = 'glo' WHERE obj_id = %s";
        $result = $this->db->queryF($query, [ilDBConstants::T_INTEGER], [$obj_id]);
    }

    /**
     * @see ilObjGlossary::create()
     */
    public function createGlossaryRecord(int $obj_id, bool $online, bool $term_to_def): void
    {
        $this->db->insert(
            'glossary',
            [
                'id' => [ilDBConstants::T_INTEGER, $obj_id],
                'is_online' => [ilDBConstants::T_TEXT, $online ? 'y' : 'n'],
                'virtual' => [ilDBConstants::T_TEXT, 'coll'],
                'flash_active' => [ilDBConstants::T_TEXT, 'y'],
                'flash_mode' => [ilDBConstants::T_TEXT, $term_to_def ? 'term' : 'def']
            ]
        );
    }

    /**
     * @return array<int, RepoObject>
     */
    private function getRepoObjects(string $query): array
    {
        $result = $this->db->query($query);
        $objects = [];
        while ($row = $this->db->fetchAssoc($result)) {
            $objects[$row['obj_id']] = ($objects[$row['obj_id']] ?? new RepoObject(
                (int) $row['obj_id'],
                (string) $row['type'],
                (string) $row['title'],
                (string) $row['description'],
            ))->withRefId((int) $row['ref_id']);
        }
        return $objects;
    }
}
