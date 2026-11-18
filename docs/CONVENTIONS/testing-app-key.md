# Test-suite APP_KEY

`phpunit.xml` sets a fixed `APP_KEY` for the test run, as both `<env>` and `<server>` with `force="true"`.

## Why

- The GitHub CI pipeline has no `.env`, so `APP_KEY` is empty there.
- Any test that goes through the `web` middleware group (e.g. `HorizonBrowserAuthTest`) hits `EncryptCookies`. That needs an encryption key, or it throws `MissingAppKeyException`.
- Laravel reads `$_SERVER` before `$_ENV`, so the key is set in both. `force="true"` means tests always use this key, never a developer's local `.env` key or an empty shell variable.

## Is it a secret?

No. It's a random key generated only for the test suite. It encrypts cookies in in-memory test requests and is used by nothing else. **Never** reuse it for a real environment, and never put a real environment's key in `phpunit.xml`.

## Rotating

You can replace it at any time with the output of:

```bash
php -r 'echo "base64:".base64_encode(random_bytes(32)), PHP_EOL;'
```
