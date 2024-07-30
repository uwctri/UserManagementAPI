# User User Management API - Redcap External Module

## What does it do?

Exposes an API, which requires a super API token, that can be used to ...

## Requests

```sh
POST /api/?type=module&prefix=user_management_api&page=api

body {
    "token": "string" 
    "action": "string"
    "user": "username" 
    ... // Optional parameters specific to action
}

```

## Responses

```sh
{
    "status": "string",  // 'success' or 'failure'
    "message": "string", // Explination of action that occured
    "data": "JSON",      // User object described below
}
```

## User Object

```sh
{
    ...
}
```

## Installing

You can install the module from the REDCap EM repo or drop it directly in your modules folder (i.e. `redcap/modules/user_management_api_v1.0.0`).
