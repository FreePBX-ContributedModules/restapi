Call Forward
===

# Overview of Call Forward

Call Forwarding allow you to forward all your incoming calls to a different phone number, as permitted by your outbound route.

# Call Forward API

## List All Call Forward Users
    
    GET admin/index.php/rest/callforward/users

### Response
```
Header:
	Status - 404 Not Found
Body:
[
"status": "error",
"msg": "Invalid Responce"
}
```

or 

```
Header:
	Status - 200 OK
Body:
[
  [
    {
      "/CF/4003": "11111111111"
    },
    {
      "/CFU/4003": "22222222222"
    },
    {
      "/CFB/4003": "33333333333"
    }
  ]
]
```

## Get Individual Call Forward User

    GET admin/index.php/rest/callforward/users/:id

### Response
```
Header: 
	Status - 404 Not Found
Body:
{
"status": "error",
"msg": "Invalid Responce"
}
```

or

```
Header:
	Status - 200 OK
Body:
{
"CF": "11111111111",
"CFU": "22222222222",
"CFB": "33333333333"
}
```

## Get Individual Call Forward Settings Based on Type

    GET admin/index.php/rest/callforward/users/:id/:type

### Response
```
Header:
        Status - 404 Not Found
Body:
{
"status": "error",
"msg": "Invalid Responce"
}
```

or

```
Header:
        Status - 200 OK
Body:
"11111111111"
```

## Get Individual Call Forward Ringtimer Settings

    GET admin/index.php/rest/callforward/users/:id/ringtimer
    
### Response
``` 
Header:
        Status - 404 Not Found
Body:
{
"status": "error",
"msg": "Invalid Responce"
}
```

or

```
Header:
        Status - 200 OK
Body:

```

## Add or modify Individual Call Forward Settings

    PUT admin/index.php/rest/callforward/users/:id/ringtimer

### Parameters - TODO

ringtimer
: *integer* - Valid options are between -1 and 120

### Response
```
Header:
        Status - 200 OK
Body:
"120"
```

## Add or modify Individual Call Forward Settings

    PUT admin/index.php/rest/callforward/users/:id

### Parameters - TODO

number 
: *integer* - Phone number to forward calls to\:

type
: *string* - Valid options are as follows\:
1. CF - Call forward unconditional
2. CFU - Call forward unavailable
3. CFB - Call forward busy

### Response
```
Header:
	Status - 200 OK
Body:
```

