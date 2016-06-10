Call Waiting
===

# Overview of Call Waiting

Call waiting allows a call to come through even when your already on the phone, giving you the ultimate option of if you want to answer or not.

# Call Waiting API

## List All Do Not Disturb Users
    
    GET admin/index.php/rest/callwaiting/users

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
    "/CW/4003": "ENABLED"
  }
]
```

## Get Individual Call Waiting User

    GET admin/index.php/rest/callwaiting/users/:id

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
"ENABLED"
```

## Add or modify Individual Call Waiting Settings

    PUT admin/index.php/rest/callwaiting/users/:id

### Parameters - TODO

state 
: *string* - Valid options are as follows\:
1. empty string - Callwaiting Disabled
2. ENABLED - Callwaiting Enabled

### Response
```
Header:
	Status - 200 OK
Body:
```

