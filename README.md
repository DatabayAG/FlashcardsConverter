# FlashcardsConverter

Add-on for ILIAS 9 to convert flashcard plugin objects into glossaries with flashcard presentation 

## Requirements

- PHP: [![Minimum PHP Version](https://img.shields.io/badge/Minimum_PHP-8.1.x-blue.svg)](https://php.net/) [![Maximum PHP Version](https://img.shields.io/badge/Maximum_PHP-8.2.x-blue.svg)](https://php.net/)
- ILIAS: [![Version](https://img.shields.io/badge/ILIAS-9.x-orange.svg)](https://ilias.de/)

## Installation

Before installing this add-on ensure all requirements are given.

1. Create a subdirectory of the ILIAS installation directory that is not tracked by Git, e.g. `.env`
2. Clone the repository of this add-on in that directory, e.g. as `.env\FlashcardsConverter`
3. run `composer install` in the directory of the add-on

## Usage

This add-on is a command-line tool. Move to its directory and run one of the following commands:

1. run `php convert.php list` to get a list of flashcard objects and converted glossaries
2. run `php convert.php convert` to convert the objects to glossaries

You can remove the directory of the add-on once the conversion is done. 

## Specifications

Each flashcard training is converted directly into a collection glossary, so it remains in the same place and retains its ID. As the type changes, previous permanent links become invalid. This must be resolved in an installation that uses such links by redirecting the web server configuration.

The collection glossary will have the following settings:
- Title/description: taken from the flashcard training
- Content Assembly: Collective glossary
- Online: is taken from the flashcard object
- Tile image: none selected
- Presentation Mode: In Table Form, shorten after 200 characters (default setting)
- Flashcard Training: Active. The modes “Term vs. Definition” and “Definition vs. Term” are taken from the training. The “Definitions” mode is converted to “Definition vs. Term”.
- Enable Download: Inactive (default setting)
- Manage Custom Metadata: Inactive (default setting)

The current object permissions of the flashcard training (view, read, copy, edit settings, delete, change permission settings) are transferred to the newly created collective glossary. The additional permission 'edit_content' of the collective glossary is taken from the permission “'dit settings' of the flashcard training. For collective glossaries, this right means the selection of the original glossaries, which corresponds to the selection of the glossary in the training.

No permission templates are changed. This means, for example, that the object permissions of the collective glossary created for a course role can differ from the default permissions for glossaries. When changing permissions settings with 'Change existing objects' or applying a didactic template, the converted trainings are treated like glossaries.

If the flashcard training has instructions, they are added as a second description in the LOM metadata of the collective glossary in the 'General' section. It is not directly visible, but can be called up with editing permissions on the 'Metadata' tab in order to be copied to another location at a later time.

The glossary used for the training is added to the created collective glossary under “Content”. If the glossary used was already a collective glossary, its original glossaries are added to the new collective glossary under “Content”, as collective glossaries can only select normal glossaries.

During conversion, the learning states (cards in boxes) are transferred from the training to the flashcard function of the collective glossary. In the glossary, the boxes have no capacity limit compared to the training. This means that there is no 'Fill up start compartment' function. The terms in a collective glossary are also automatically updated from the underlying glossary. After the conversion, all terms in the glossary that have not yet been trained are automatically available in the start box.

## Correlations

An active flashcards plugin is not needed to run this converter.

## Bugs

None known.

## Other information

#### License

https://www.gnu.org/licenses/gpl-3.0.en.html
