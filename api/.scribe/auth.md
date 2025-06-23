# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_AUTH_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Use the login endpoint to obtain a Bearer token. Include it as `Authorization: Bearer {token}` in your requests.
