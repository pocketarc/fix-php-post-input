## What is brunodebarros/fix-php-post-input

`brunodebarros/fix-php-post-input` fixes your `$_POST` and `$_FILES` if they're empty and they shouldn't be.

**Why?** Some incorrectly setup servers will not pass the right information required for PHP to automatically process input data (e.g. they'll say the HTTP method was GET because of an internal redirect). 

For apps where you control the environment, this is easy to resolve. For self-hosted apps, that anyone can install anywhere, it's important to make sure that things continue to work without having to ask less tech-savvy users to mess with their server configurations. 

In those cases, this library will automatically fix the `$_POST` and `$_FILES` arrays for you so that you can continue working without having to worry about it.

As a bonus, it will also process JSON (`application/json`) input into `$_POST` for you.

## Requirements

* PHP 5.4 or HHVM

## Installation

`brunodebarros/fix-php-post-input` loads automatically with Composer.

```
composer require brunodebarros/fix-php-post-input
```

## Usage

Once required, the package will automatically fix your `$_POST`/`$_FILES` for you. If they don't need fixing, this package will do nothing.

## Suggestions, questions and complaints.

If you've got any suggestions, questions, or anything you don't like about this library, [you should create an issue here](https://github.com/BrunoDeBarros/fix-php-post-input/issues/new). Feel free to fork this project, if you want to contribute to it.