# Webunit

[![Tests](https://github.com/themichaelhall/webunit/actions/workflows/tests.yml/badge.svg?branch=master)](https://github.com/themichaelhall/webunit/actions)
[![License](https://poser.pugx.org/michaelhall/webunit/license)](https://packagist.org/packages/michaelhall/webunit)
[![Latest Stable Version](https://poser.pugx.org/michaelhall/webunit/v/stable)](https://packagist.org/packages/michaelhall/webunit)
[![Total Downloads](https://poser.pugx.org/michaelhall/webunit/downloads)](https://packagist.org/packages/michaelhall/webunit)

Webunit is a command line client for automated web application tests.

## Requirements

- PHP >= 8.0

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

This test will be successful if the Url ```https://example.org/``` is functional and does not return an error or redirect status code. Otherwise, the test will fail.

The Url can also be fetched with one of the other supported HTTP methods:

```
delete https://example.org/
patch https://example.org/
post https://example.org/
put https://example.org/
```

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

Some assertions can be modified with modifier characters:

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

The request can be modified with request modifiers:

```
put                    https://example.org/
with-header            Content-Type: application/json
with-raw-content       {"Foo": "Bar"}
```

The test file can set variables to be reused for the tests.

Variables are evaluated at parse-time in a manner similar to the preprocessor directives in languages like C and C#.

```
# Set the variable "Url" to the value "https://example.com".
set                    Url = https://example.com/

# get https://example.com/
get                    {{ Url }}

# get https://example.com/another-page
get                    {{ Url }}another-page
```

It is also possible to set variables from the command line:

```
$ webunit --set=Url=https://example.com/ testfile
```

```
# get https://example.com/
get                    {{ Url }}
```

A default value can be used to set the variable if not already set.

Example 1:

```
$ webunit testfile
```

```
# "Url" is not set. Set the value to "https://example.com/".
set-default            Url = https://example.com/

# get https://example.com/
get                    {{ Url }}
```

Example 2:

```
$ webunit --set=Url=https://example.org/ testfile
```

```
# "Url" is already set to "https://example.org/". Do not change it.
set-default            Url = https://example.com/

# get https://https://example.org/
get                    {{ Url }}
```

The following escape sequences can be used in the test file:

| Escape sequence | Character       |
|-----------------|-----------------|
| \\n             | Line feed       |
| \\r             | Carriage return |
| \\s             | Space           |
| \\t             | Horizontal tab  |
| \\\\            | Backslash       |

```
get                    https://example.org/
assert-contains        <html>\n<head>\n\s
```

## Commands

### delete _url_

Fetches a Url via a ```DELETE``` request.

```
delete https://example.org/
```

### get _url_

Fetches a Url via a ```GET``` request.

```
get https://example.org/
```

### patch _url_

Fetches a Url via a ```PATCH``` request.

```
patch https://example.org/
```

### post _url_

Fetches a Url via a ```POST``` request.

```
post https://example.org/
```

### put _url_

Fetches a Url via a ```PUT``` request.

```
put https://example.org/
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

Note: The header name is always case-insensitive.

### assert-status-code _status-code_

Asserts that the status code of the result is the same as the specified status code. Allowed modifier is ```!```

Note: This assert must be present for a test to pass if the result has a status code other than 200-299.

```
assert-status-code 301
```

## Request modifiers

### with-header _header-name: header-value_

Sets an HTTP-header with the specified name to the specified value.

```
with-header Accept-Language: en
```

### with-post-file _parameter-name = file-path_

Sets a POST-parameter with the specified name to a file to be uploaded. The file path can be either absolute or relative to the webunit test script. This request modifier can not be used for GET requests and can not be combined with the _with-raw-content_ request modifier.

```
with-post-file File = ../../files/foo.txt
```

### with-post-parameter _parameter-name = parameter-value_

Sets a POST-parameter with the specified name to the specified value. This request modifier can not be used for GET requests and can not be combined with the _with-raw-content_ request modifier.

```
with-post-parameter Text = Foo
```

### with-raw-content _content_

Sets the request body content to the specified content. This request modifier can not be used for GET requests and can not be combined with the _with-post-file_ and _with-post-parameter_ request modifiers.

```
with-raw-content {"Foo": "Bar"}
```

## License

MIT
