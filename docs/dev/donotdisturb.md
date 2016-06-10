Do Not Disturb
===

# Overview of Do Not Disturb

Don't want to be bothered? No worries, simply activate do not disturb and your calls will go directly to voicemail without ever ringing your phone.

# Do Not Disturb API

## List All Do Not Disturb Users
    
    GET admin/index.php/rest/donotdisturb/users

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
[
  {
    "4003": "enabled",
    "4006": "enabled"
  }
]
```

## Get Individual Do Not Disturb User

    GET admin/index.php/rest/donotdisturb/users/:id

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
{status: "enabled|disabled"}
```

## Add or modify Individual Do Not Disturb Settings

    PUT admin/index.php/rest/donotdisturb/users/:id

### Parameters - TODO

state 
: *string* - Valid options are as follows\:
1. disabled - DND Disabled
2. enabled - DND Enabled

### Response
```
Header:
	Status - 200 OK
Body:
```

