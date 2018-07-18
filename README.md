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