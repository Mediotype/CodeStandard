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

## PHPStorm Integration

`@todo` Fill in setup steps

(c) 2018 Mediotype