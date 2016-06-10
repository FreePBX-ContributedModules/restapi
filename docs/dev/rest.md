# RESTapi Documentation

### 1. Introduction

FreePBX was designed to work as a highly integrated set of modules. While it offers some level of modularity, it really doesn't lend well to "outside" process. The purpose of the restful api module is to allow common FreePBX methods and properties to be accessed externally, in a RESTful manner. Herein, the RESTful module will be refered to as Rest.

### 2. Design Concepts
Rest was designed with the following constraints:
 
 1. To be able to work independently of FreePBX 
 2. To be responsible for its own security independently of FreePBX
 3. To log and offer debugging of its own, as per #1

Rest was also written with code cleanliness and clarity in mind. Unlike it's hosts' code base, Rest strives not just to be code that "gets the job done", but rather code its authors can be proud of.

### 3. Design Overview

Rest is composed of four primary parts:

1. `Api` does the grunt of the work, instantiated the various components and calling end controllers to manipulate data as requested.
2. `RestAuth` is responsible for security of the application.
3. `Router` handles matching requests agains know possible matches
4. `RestLogger` does all the logging.

#### 3.1 Terminology

* `map` - a possible match to a uri. Maps are submitted by modules. When a request comes in, ALL possible maps are considered
* `route` - a matched map. There can be multiple routes in a single request
* `verb` - the html verb used when making the request. Currently implemented are: `DELETE`, `GET`, `POST`, and `PUT`

### 4. Putting it all together
When a call request hits the server, a small external file, `index.php`, checks to see if the request is in a traditional FreepBX format (`admin/config.php?display=some_module`), or if it seem to be a Rest call (`admin/index.php/rest/`). It then instantiates the `Api` class, and calls `Api->main()`.

#### 4.1 Api
The `Api` class is the heavy lifter in Rest. On instantiation, the api class instantiates the required objects, as follows:

 1. `RestAuth` - which provides security to Rest
 2. `RestLogger` - which provides logging resources
 3. `req` - the request object. This holds all request related information, including headers, server data (ip address, etc, as necessary), the request tokens parameters (key, usage stats, etc) - if a matching token is found.
 4. `res` - the response. This gets populated in the course of the various actions of of `Api`. It is instantiated as a `stdClass`, a blank default class.
 
On instantiation, the api class also registers all available maps, and parses them in to maps. The primary design pattern for Rest as a whole and `Api` specifically is [Front Controller pattern](http://en.wikipedia.org/wiki/Front_Controller_pattern). ALL classes instantiated by `Api` are passed a reference to `$this`, and can access `Api` like: `$this->api` (note: controllers obviously need to built to accept `$this` as an argument in their constructers and assign it to `$this->api`).
 
#### 4.1 Router
Prior to calling `Router`, the api class gathers all maps. Maps are received from two places:

1. module.xml's as cached in FreePBX's database. These are received via the `modulelist` class and stored in `$this->mods`
2. maps.php of modules folded in to the restapi module. These maps are NOT cached, and are parsed at every instantiation. The file tree is as follows:

```
restapi/
...
restapi/modules/
restapi/modules/some_mod/
restapi/modules/some_mod/maps.php
restapi/modules/some_mod/controllers/
...
```
`Api` will only add maps of enabled modules. `Api` does NOT check module dependencies before adding maps (this does happen later on though). Once the maps are built up, `Router` is instantiated as `$this->router`. All maps are iterated over and added to `Router`.

URI's are excepted to be prefixed with `rest/` and an optional version number like `rest/v1/` or `rest/1/`. `Router` will not match a URI that isn't prefixed as such. As multiple maps can be matched, `Router` will put all matches in array `$this->routes`.

Having our routes in place, we next check the server status, and return an appropriate header if the server is unavailable

We also return a `404` if we don't have any routes.

#### 4.2 RestAuth
Next, we run some security tests, abbreviated AAA:

* **A**uthentication - ensuring the user is who they claim who they are
* **A**uthorization - ensuring the user is eligible to access the requested resource
* **A**ccounting - keeping track of a users usage, and enforcing quotes

Note: in this section we use the term client and token interchangeably. The intention can be understood as "a client which is using a given token".

##### 4.2.1 Authentication
Authentication is about confining identity of the client.

For authentication, we assume two pieces of data: a token, and a token key. While the token must be sent with every request, the key is considered private and is only used to sign the request operation side (i.e. client side for the request, server side for the response).

Both a client and a server have a token and a key. Keys should be transmitted only via a secure mechanism and never as part of a request. A server will reject an improperly signed request (see 4.2.1) or a request who's token is marked as disabled'. A properly implemented client will ignore server responses who's signature is invalid as well.

We check the message signature to ensure that the message contents are as sent and have not been manipulated. Here is how we build the signature, in pseudocode. Note that the actual hashing algorithm is available in `$this->hash_algo`:

```
$a = sha256($uri . ':' . strtolower($verb);
$b = sha256($token . ':' . $nonce);
$c = sha256(base64_encode($body);
$data = sha256($a . ':' + $b . ':' . $c);
$key = ''; //user or server's token key

$sig = hash_hmac('sha256', $data, $key);
```

Nonce is a randomly generated string. Ensure you ALWAYS use a fresh nonce for every request. Being that the signature is always unique (due to the expected fresh nonce), to prevent a [replay attack](http://en.wikipedia.org/wiki/Replay_attack) we ensure that a signature has never been used before. Known signatures will be rejected.

If the sent signature matches our generated signature, than the request is deemed **authenticated**.

##### 4.2.2 Authorization
Authorization is the permission of an authenticated client to access a resource. A client can be authenticated (i.e. at a server level), but not authorized (at a resource level) to access a given resource. The inverse is never true.

To determine authorization, we check to see if the token is authorized to access the modules hosting the requested route. If ANY given route is unauthorized, the entire request will fail (even if there are other authorized routes).

If the request is of type `user` (as specified in the routes map), we also ensure that the client has access to the given user.

A client can have access to one or more modules, and to one or more users. A client can also have wildcard access to ALL modules and/or ALL users.

When a client's access match all requested routes, it is considered authorized.

##### 4.2.3 Accounting
Accounting keeps track of a clients usage of Rest and controls quotas and access restrictions. (at this time time cal's are not implemented).

Request quotes are set on a per token, per hour bases. A client will pass accounting as long as it doesn't exceed its quotas.

### 4.3 Logger
`Logger` allows for any bit of data to be logged. By default the following data is logged as the log's "header":

1. log id
2. time
3. token
4. signature
5. client ip
6. server token

Additional data or actions can be logged on demand. Example of data logged includes:

1. Router
2. Request
3. AAA results
4. Any errors
5. Response body
6. Response headers

### 4.4 Route processing
After AAA is done, `Api` moves on to route processing. We attempt to include a controller based on its path advertised in a map. If `controller_path` is defined, we except a controller in either `AMPWEBROOT/admin/modules/restapi/modules/<module name>/controllers/` or `AMPWEBROOT/admin/modules/<module name>/controllers/`.

For routes that don't offer a `controller_path`, we assume a naming convention of `class.<controller_name>.php`, at the paths above.

If the controller of any route fails to be found, the request is aborted.

We then ensure the "host" module of the route is included, as well as all of its dependencies. As above, if a given module or dependency is unavailable (disabled, not installed, etc), the request will fail.

Next, we instantiate the controller and add it as a property of the route. We also ensure that our desired `method` exits, and fail if it doesn't.

### 4.5 Route controllers
Using `Router`s iterator, we iterate over all routes, calling the previously instantiated objects, passing it the routes parameters (`$r['inst']->$r['method']($r['params'])`). We except a true or blanks response from the controller, a false answer will result in the the request failing. If the answer is true of type bool (i.e. `$responce === true`), we return a simple header depending on the requesting verb:

* for `GET` we return 204
* for `PUT` and `DELETE` we return 200
* for `POST` we return 201

There are assumed to be some limitations in the method, it may be addressed when they are come across. If the response is true but not of type bool, we add the data received to the response bod and assume a header of 200.

### 4.5 Response
Finally, we collate all (if any) body array's, flatten them, and convert them to a json array. We then build a signature, add it to the headers, and send out the rep once headers. Lat but not least, we send out the body, logging it on the way out.
