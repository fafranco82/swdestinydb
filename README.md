SWDestinyDB
=======

# Very quick guide on how to install a local copy

This guide assumes you know how to use the command-line and that your machine has php and mysql installed.

- install composer: https://getcomposer.org/download/
- install npm: https://www.npmjs.com/
- clone the repo somewhere
- cd to it
- run `npm install`
- run `composer install` (at the end it will ask for the [database configuration parameters](https://github.com/bravesheep/database-url-bundle#usage)).
- run `php app/console doctrine:database:create`
- run `php app/console doctrine:schema:create`
- run `php app/console app:import:std ../swdestinydb-json-data` or whatever the path to your SWDestinyDB JSON data repository is
- run `php app/console app:import:trans ../swdestinydb-json-data` if you want to import the translations
- run `php app/console server:run`

## Setup an admin account

- register
- make sure your account is enabled (or run `php app/console fos:user:activate <username>`)
- run `php app/console fos:user:promote --super <username>`

## Translating the site

The translation messages are found in `src/AppBundle/Resources/translations` in XLIFF format files (`*.xliff`). The main messages are found in `messages.<locale>.xliff` file, where `<locale>` is the 2-letters code of the language, so `messages.es.xliff` will contain the messages translated into Spanish. You can found the 2-letters code of your language [here](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes).

To edit and maintain translations files, these are the steps:

- run `php app/console translation:extract --config=AppBundle <locale>`. This will create the `messages.<locale>.xliff` file (or update it with new contents).
- run `php app/console server:run`
- Open your web browser and navigate to `http://localhost:8000/_trans`. Here you can select file and locale from dropdowns on the top.
- Find the message you want to translate, edit the text in the textarea. The text will be saved automacally when the textarea lose focus.

Appart of the above, thera are two files that need to be created by copying the English version (or the language of your choice):

- Copy `footer.en.html.twig` in `src/AppBundle/Resources/views` to `footer.<locale>.html.twig` and edit it, inserting your translation credits right after the author credits if you wish.
- Copy `no-decks-text.en.twig` in `src/AppBundle/Resources/views/Builder` to `no-decks-text.<locale>.html.twig` and edit it.