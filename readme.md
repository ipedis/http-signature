Publispeak HTTP Signature library
--

This library is required for internal publispeak HTTP request. 
The choice of Guzzle for the HTTP Client :
- Guzzle allows to inject middleware into its stack, with which 
additional headers can be added in the request object

Installation
==

Update `composer.json` and add a repository:

    "repositories": [
        {
            "type": "vcs",
            "url": "bitbucket:ipedis/http-signature.git"
        }
    ]
    
    
Require the library:

    "require": {
        "ipedis/http-signature": "^1.0.0"
    }


Folder structure
==

* **demo** Will contain all examples for actual covered behavior from this library.
* **src** Set of class or trait available.
* **docs** All documentation.


Sending a signed Http request
==

Use the `HttpClient` trait to send an HTTP request. The signature will be added automaticaly by the trait.

```
[...]

use HttpClient;

[...]

$response = $this->getClient()->get(<url>);
$response = $this->getClient()->post(<url>);
$response = $this->getClient()->delete(<url>);

[...]

```


Signing a request
==

Use the `Signer` trait to add signature to a PSR-7 compatible request

```
[...]

use Signer;

[...]

$request = $this->sign($request);

[...]

```

Verify a request
==

Use the `Verify` trait to check validity of a PSR-7 compatible request

```
[...]

use Verifier;

[...]

$isValid = $this->verify($request)

[...]

```


How it works
==

## Adding the signature

Take Method (GET, POST …)

Take  full Url path (exemple : http://recovery.publispeak.local/api/event/dispatch

Create a timestamp as which will be added as HEADER of each signed query.

Take string serialized request body  

Concatenated all of this information by a predictable order. Example:

```
  Pattern:
  METHOD.url.timestamp.stringified_body
  Sample:
  GET.http://recovery.publispeak.local/api/event/dispatch.13903209123.{foo:bar}
```

then calculate the HMAC_256 of this string.

```
  Computed HMAC:
  bc1c3a7513079cd8e02a4ef367bf72e161e0fa7207cd1e89c3daf3ee682897ef
```

## Verify the signature

Take the received request and perform the same calculation to compare computed HMAC and received sign HEADER.

```
$sourceString = METHOD.url.timestamp.stringified_body
if (timestamp older than 1m)
   reject query, it can be Man to the middle who try to replay query

$computedHash = hash_hmac('sha256', $sourceString, backup_secret_token)
if (hash_equals($computedHash, $receivedSignHeader)
    we have legit request
```

## How to test

View documentation folder
