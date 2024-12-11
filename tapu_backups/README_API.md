# API documentation

The tapu-backups host stores the backups of b2 hosts instances.

## Host

### POST _/status_

Get the status of the b2 host.

#### Request Headers

| key          | value            |
|--------------|------------------|
| Content-Type | application/json |

#### Body

```json
{}
```

### POST _/release-expired-tokens_

Release the tokens that are expired:
  - delete the token
  - delete the temporary ftp user

#### Request Headers

| key          | value            |
|--------------|------------------|
| Content-Type | application/json |

#### Body

```json
{}
```

## Instance

### POST _/instance/backups_

Get the stored backups of a specific instance.

#### Request Headers

| key          | value            |
|--------------|------------------|
| Content-Type | application/json |

#### Body

```json
{"instance":"equal.local"}
```

| key      | required | default | values | Note                      |
|----------|:--------:|:-------:|--------|---------------------------|
| instance |   true   |         |        | Must be a valid instance. |

## Token

### POST _/token/create_

Create a token to export or import an instance backup.
Only one token for an instance can be created at a time.

#### Request Headers

| key          | value            |
|--------------|------------------|
| Content-Type | application/json |

#### Body

```json
{"instance":"equal.local"}
```

| key      | required | default | values | Note                      |
|----------|:--------:|:-------:|--------|---------------------------|
| instance |   true   |         |        | Must be a valid instance. |

### POST _/token/release_

Release a token because the export or import of a backup is finished.
It'll allow other backup operations to take place.


#### Request Headers

| key          | value            |
|--------------|------------------|
| Content-Type | application/json |

#### Body

```json
{
  "instance":"equal.local",
  "token":"4534098a50233b15cb05734b1bbd64c8"
}
```

| key      | required | default | values | Note                       |
|----------|:--------:|:-------:|--------|----------------------------|
| instance |   true   |         |        | Must be a valid instance.  |
| token    |   true   |         |        | Must be an existing token. |
