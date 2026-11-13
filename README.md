# Tests

Run `api-test-server` and add its URL to Moodle's `config.php`:

```php
$CFG->behat_block_motrain_apihost = 'http://localhost:5000';
$CFG->phpunit_block_motrain_apihost = 'http://localhost:5000';
```
