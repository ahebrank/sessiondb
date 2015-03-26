# Getting EE (extensions) to put PHP native sessions in the database

In some cases (e.g., when load balancing is in use) it may be important to centralize PHP sessions.  EE core doesn't really use them (it uses its own db-based session management), but some modules do!

Based on http://shiflett.org/articles/storing-sessions-in-a-database

## Installation and Use

1. Add this folder to your system/expressionengine/hooks directory
2. Add this code to system/expressionengine/config/hooks.php:

```
$hook['pre_controller'] = array(
  'class' => 'Sessiondb',
  'function' => 'hook_session',
  'filepath' => 'hooks/sessiondb',
  'filename' => 'sessiondb.php');
```
3. Change this line in system/expressionengine/config/config.php from `FALSE` to `TRUE`:
```
$config['enable_hooks'] = TRUE;
```
