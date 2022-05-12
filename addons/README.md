Add-ons folder
==============

This is the folder in which Motrain add-ons should be installed.

Available hooks
---------------

### extend_block_motrain_content

Allows to inject content after the block generated its own content. This is useful to inject JavaScript.

```php
/**
 * @param manager $manager The manager.
 * @param renderer $renderer The block's renderer.
 * @return string|null
 */
function motrainaddon_pluginname_extend_block_motrain_content($manager, $renderer);

```
