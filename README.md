# Webunit

[![Build Status](https://travis-ci.org/themichaelhall/webunit.svg?branch=master)](https://travis-ci.org/themichaelhall/webunit)
[![AppVeyor](https://ci.appveyor.com/api/projects/status/github/themichaelhall/webunit?branch=master&svg=true)](https://ci.appveyor.com/project/themichaelhall/webunit/branch/master)
[![codecov.io](https://codecov.io/gh/themichaelhall/webunit/coverage.svg?branch=master)](https://codecov.io/gh/themichaelhall/webunit?branch=master)
[![StyleCI](https://styleci.io/repos/119093998/shield?style=flat&branch=master)](https://styleci.io/repos/119093998)
[![License](https://poser.pugx.org/michaelhall/webunit/license)](https://packagist.org/packages/michaelhall/webunit)
[![Latest Stable Version](https://poser.pugx.org/michaelhall/webunit/v/stable)](https://packagist.org/packages/michaelhall/webunit)
[![Total Downloads](https://poser.pugx.org/michaelhall/webunit/downloads)](https://packagist.org/packages/michaelhall/webunit)

Webunit is a command line client for automated web application tests.

## Requirements

- PHP >= 7.3

## Install with Composer

``` bash
$ composer require michaelhall/webunit
```

## Basic usage

The ```webunit``` client requires a file in text format, containing the tests to run. Pass the name of this file as a command line parameter:

```
$ webunit testfile
```

The tests in the test file consists of one or more test cases. Every test case starts with a command. The most basic test case is just a command, e.g.

```
get https://example.org/
```

This test will be successful if the Url ```https://example.org/``` is functional and does not return an error or redirect status code. Otherwise the test will fail.

A test case can also contain specific assertions:

```
get https://example.org/
assert-contains Example
```

Comments and whitespaces can be used to format the test file:

```
# This is a comment.
get                    https://example.org/
assert-contains        Example

# Another test case.
get                    https://example.org/foobar
assert-status-code     404
```

Some of the assertions can be modified with modifier characters:

```
get                    https://example.org/

# The "!" modifier negates the assertion.
# This assertion will pass if the returned status code is not 404
assert-status-code!    404

# The "^" modifier makes the assertion case insensitive.
# This assertion will pass if result contains "Example", "EXAMPLE", "eXaMpLe" etc.
assert-contains^       example

# The "~" modifier evaluates the assertion as a regular expression.
# This assertion will pass if result contains "Example" or "example".
assert-contains~       [Ee]xample

# Modifiers can be combined.
# This assertion will fail if result contains "FooBar", "foobar" etc.
assert-contains!^      foobar
```

## Commands

### get _url_

Fetches a Url via a ```GET``` request.

```
get https://example.org/
```

## Assertions

### assert-contains _content_

Asserts that the content of the result contains the specified content. Allowed modifiers are ```!```, ```^```, ```~```

```
assert-contains Foo
```

### assert-empty

Asserts that the content of the result is empty. Allowed modifier is ```!```

```
assert-empty
```

### assert-equals _content_

Asserts that the content of the result is the same as the specified content. Allowed modifiers are ```!```, ```^```, ```~```

```
assert-equals Foo
```

### assert-header _header-name[: header-value]_

Asserts that the result contains a header with the specified name and an optional value. Allowed modifiers are ```!```, ```^```, ```~```

```
assert-header Location
assert-header Location: https://example.com/
```

Note: The header name is always case insensitive.

### assert-status-code _status-code_

Asserts that the status code of the result is the same as the specified status code. Allowed modifier is ```!```

Note: This assert must be present for test a to pass if the result has a status code other than 200-299.

```
assert-status-code 301
```

## License

MIT
