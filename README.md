# Mediotype Engineering Code Standard

A collection of code tests designed to enforce the Mediotype way.

# How to Install

For local development, deploy as follows:

    git clone git@github.com:Mediotype/CodeStandard.git
    cd CodeStandard
    composer install

# How to Use

To run sniffs manually, run:

    php vendor/bin/phpcs \
        --standard=src/Rules/Structure/PHP \
        --report=code \
        /path/to/your/code

## PHPStorm Code Sniff Integration

`@todo` Fill in setup steps

To add Mediotype sniffs to PHPStorm code sniffer integration, first make sure you have CodeStandard repository cloned locally and that composer dependencies are installed.

Then, in PHPStorm, navigate to:

* Preferences > Languages & Frameworks > PHP > Quality Tools

Open Code Sniffer tab, click "..." icon,  click file system icon near _PHP Code Sniffer path_ field and then select path to phpcs binary from CodingStandard project. At the end it could look similar to following one:

    /Volumes/Sites/CodeStandard/vendor/bin/phpcs

When it's done, you can use _Validate_ button to check if everything is configured correctly.

Close configuration window, and in the main PHPStorm configuration window navigate to:

* Preferences > Editor > Inspections

Locate PHP Code Sniffer in the inspections list, change _Coding Standard_ to _Custom_ and open selector. In the new window click on file system icon and browse ruleset.xml from the CodingStandard repository. Finally, it can look as follows:

    /Volumes/Sites/CodeStandard/src/Rules/Structure/PHP/ruleset.xml

Close configuration window, now custom ruleset should now be used by both background and manual inspection.   

# PHPStorm Code Style Scheme

In addition to code sniffs, a PHP code style scheme is provided with this standard:

    src/Rules/Structure/PHP/PHPStormProjectConfig.xml

To apply this configuration, download it to your local machine. Then, navigate to:

* Preferences > Editor > Code Style > PHP

In the _Scheme_ selector, choose whether you want to apply at _Project_ or _Global_ scope. Then, click the "more" icon,
and select _Import Scheme > Intellij IDEA code style XML_. Select the given scheme file and apply your changes.

Your IDE will not automatically format your code according to our standard.

## Code Arrangement

The provided code style scheme can also arrange your PHP class structure to conform to our standard. To use the feature,
open any PHP class you wish to arrange, then while it is the active file, select _Code > Rearrange Code_.

(c) 2018 Mediotype