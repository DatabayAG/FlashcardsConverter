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
use FlashcardsConverter\Data\ConverterRepository;

class ListCommand extends Command
{
    private const NAME = 'list';

    private readonly ConverterRepository $conv_repo;

    public function __construct()
    {
        global $DIC;
        $this->conv_repo = new ConverterRepository($DIC->database());
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription(
            "Lists all flashcard objects that can be converted and all already converted glossaries."
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $trainings = $this->conv_repo->getFlashcardTrainings();
        $converted = $this->conv_repo->getConvertedTrainings();

        $output->writeln(count($trainings) . ' flashcard objects');
        $output->writeln(count($converted) . ' converted objects ');
        $output->writeln("obj_id\ttype\ttitle\tref_ids");

        foreach (array_merge($trainings, $converted) as $object) {
            $output->writeln($object->getInfoline());
        }
        return 0;
    }
}
