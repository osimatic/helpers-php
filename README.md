# helpers-php

## Optional dependencies

Some classes require additional packages that are not included by default.

### `Osimatic\Person\GoogleContact` and `Osimatic\Messaging\FirebaseMessaging`

These classes require `google/apiclient`. Add it to your project:

```bash
composer require google/apiclient:^2
```