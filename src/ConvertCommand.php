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

namespace FlashcardsConverter;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ILIAS\Glossary\Flashcard\FlashcardBoxDBRepository;
use FlashcardsConverter\Data\FlashcardsTraining;
use ILIAS\Glossary\Flashcard\FlashcardTermDBRepository;
use FlashcardsConverter\Data\RepoObject;
use ilObjGlossary;
use ilGlossaryTerm;
use FlashcardsConverter\Data\Box;
use ILIAS\MetaData\Services\ServicesInterface as MetaDataService;
use ILIAS\DI\RBACServices;
use ilRbacReview;
use FlashcardsConverter\Data\ConverterRepository;
use Throwable;

class ConvertCommand extends Command
{
    private const NAME = 'convert';

    protected ConverterRepository $conv_repo;
    protected FlashcardBoxDBRepository $box_repo;
    protected FlashcardTermDBRepository $term_repo;
    protected MetaDataService $lom;
    protected RBACServices $rbac;

    public function __construct()
    {
        global $DIC;
        $this->lom = $DIC->learningObjectMetadata();
        $this->conv_repo = new ConverterRepository($DIC->database());
        $this->box_repo = new FlashcardBoxDBRepository($DIC->database());
        $this->term_repo = new FlashcardTermDBRepository($DIC->database());
        $this->rbac = $DIC->rbac();

        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription(
            "Converts flashcard objects to collection glossaries."
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('execute convert command' . "\n");
        foreach ($this->conv_repo->getFlashcardTrainings() as $object) {
            $output->writeln('CONVERT: ' . $object->getInfoline());
            try {
                $this->convertObject($object);
            } catch (Throwable $e) {
                $output->writeln($e->getMessage());
                $output->writeln($e->getTraceAsString());
                return 1;
            }
        }
        return 0;
    }

    /**
     * Convert a flashcards object to a collection glossary with active flashcards mode
     */
    private function convertObject(RepoObject $object): void
    {
        $training = $this->conv_repo->getTrainingData($object->getObjId());

        $this->conv_repo->changeObjectTypeToGlossary($object->getObjId());
        $this->conv_repo->createGlossaryRecord(
            $training->getObjId(),
            $training->isOnline(),
            $training->getGlossaryMode() === FlashcardsTraining::GLOSSARY_MODE_TERM_DEFINITIONS
        );

        // now the former training should be an empty collection glossary
        $glossary = new ilObjGlossary($training->getObjId(), false);

        // add a keyword to identify the glossary as being converted (will be read by list command)
        $manipulator = $this->lom->manipulate($glossary->getId(), $glossary->getId(), 'glo');
        $manipulator = $manipulator->prepareCreateOrUpdate($this->lom->paths()->keywords(), 'FlashcardsConverter');

        // add former instructions as a second description, keep the main description
        // instructions will only be shown on the metadata tab
        if (!empty($training->getInstructions())) {
            $description = $glossary->getDescription();
            $manipulator = $manipulator->prepareCreateOrUpdate(
                $this->lom->paths()->descriptions(),
                $description ?: ' ',
                $training->getInstructions()
            );
        }
        $manipulator->execute();

        $this->addMissingPermissions($object);
        $this->migrateCards($training, $glossary);
    }

    /**
     * Add the 'edit content' permission if 'write' permission is given
     */
    private function addMissingPermissions(RepoObject $object): void
    {
        $write = ilRbacReview::_getOperationIdByName('write');
        $edit = ilRbacReview::_getOperationIdByName('edit_content');

        foreach ($object->getRefIds() as $ref_id) {
            foreach ($this->rbac->review()->getParentRoleIds($ref_id) as $role_id => $role_data) {
                $ops = $this->rbac->review()->getRoleOperationsOnObject($role_id, $ref_id);
                if (in_array($write, $ops) && !in_array($edit, $ops)) {
                    $ops[] = $edit;
                }
                $this->rbac->admin()->grantPermission($role_id, $ops, $ref_id);
            }
        }
    }

    /**
     * Migrate the flashcard usages to new term usages and set last access of the new boxes
     */
    private function migrateCards(FlashcardsTraining $training, ilObjGlossary $glossary): void
    {
        $cards = $this->conv_repo->getFlashcardsByTermId($training->getObjId());
        $usages = $this->conv_repo->getFlashcardUsagesByCardIdAndUserId($training->getObjId());

        /** @var array<int, array<int, Box>> $boxes indexed by user_id and box_nr */
        $boxes = [];

        if (!empty($training->getGlossaryRefId())) {
            $source_ids = [];

            // source repository of the flashcard training may be collective
            // then take all of its glossaries, otherwise take it
            $source = new ilObjGlossary($training->getGlossaryRefId(), true);
            if ($source->getVirtualMode() === 'coll') {
                $source_ids = $source->getGlossariesForCollection();
            } else {
                $source_ids[] = $source->getId();
            }

            foreach ($source_ids as $source_id) {
                $glossary->addGlossaryForCollection($source_id);

                foreach (ilGlossaryTerm::getTermsOfGlossary($source_id) as $term_id) {
                    if (!empty($card = $cards[$term_id] ?? null) && is_array($usages[$card->getCardId()] ?? null)) {
                        foreach ($usages[$card->getCardId()] as $user_id => $usage) {
                            if (empty($usage->getLastChecked())) {
                                continue;
                            }

                            // flashcards startbox is 0, glossary startbox is 1
                            $last_box = (int) $usage->getLastStatus() + 1;
                            $current_box = (int) $usage->getStatus() + 1;

                            $this->term_repo->createEntry(
                                $term_id,
                                $user_id,
                                $training->getObjId(),
                                $current_box,
                                $usage->getLastChecked()
                            );
                            $box = $boxes[$usage->getUserId()][$last_box] ?? new Box(
                                $last_box,
                                $user_id,
                                $training->getObjId(),
                                $usage->getLastChecked()
                            );

                            // last access of box is highest last access of items last trained for this box
                            if (empty($box->getLastAccess()) || $usage->getLastChecked() > $box->getLastAccess()) {
                                $box = $box->withLastAccess($usage->getLastChecked());
                            }
                            $boxes[$usage->getUserId()][$last_box] = $box;
                        }
                    }
                }
            }
            foreach ($boxes as $user_id => $user_boxes) {
                foreach ($user_boxes as $box) {
                    $this->box_repo->createOrUpdateEntry(
                        $box->getBoxNr(),
                        $box->getUsrId(),
                        $training->getObjId(),
                        $box->getLastAccess()
                    );
                }
            }
        }
    }
}
