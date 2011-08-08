<?php
/**
 * API per la conversione di drupal 5.x -> 7.x
 * 
 * Ref:
 * http://drupal.org/update/modules/5/6
 * http://drupal.org/update/modules/6/7
 * http://drupal.org/node/394070
 */

define('EC_DRUPAL_VERSION', 5);

if (EC_DRUPAL_VERSION >= 7) {

  /***************************************************************
   * D7 VERSION 
   ***************************************************************/

  function _dcf_hook_boot($module) {
    return true;
  }
  
  function _dcf_hook_init($module) {
    return true;
  }
    
  function _dcf_hook_menu($items, $maycache) {
    return $items;
  }
  
  function _dcr_render_array($output) {
    return $output;
  }
  
  function _dcr_form(&$form) {
    return $form;
  }
  
  function _dcf_internal_path($path) {
    return $path;
  }
  
  function _dco_watchdog($type, $message, $variables = array(), $severity = WATCHDOG_NOTICE, $link = NULL) { // WARN d7 changed WATCHDOG_ costants
    return watchdog($type, $message, $variables, $severity, $link);
  }
  
  function _dco_l($text, $path, array $options = array()) {
    return l($text, $path, $options);
  }
  
  function _dcf_form_validate(&$form, &$form_state) {
    return array('form' => &$form, 'form_state' => &$form_state);
  }
  
  function _dco_theme($name, $args) {
    return theme($name, $args);
  }
  
  function _dcf_theme_signature($args) {
    return array();
  }
  
  function _dcr_hook_theme($specs) { 
    return $specs;
  }

  function _dcf_theme_form(&$args) {
    return array( 'variables' => $args );
  }

  /***************************************************************
   * D7 EXTRA FUNCTIONS 
   ***************************************************************/
  
  function drupal_module_get_min_weight($except_module = false) {
    return !$except_module ? db_query("select min(weight) from {system}")->fetchField() :
      db_query("select min(weight) from {system} where name != :name", array(':name' => $except_module))->fetchField();
  }
  
  function drupal_module_get_weight($name) {
    return db_query("select weight from {system} where name = :name", array(':name' => $name))->fetchField();  
  }
  
  function drupal_module_set_weight($name, $weight) {
    db_update('system')->fields(array('weight' => $weight))->condition('name', $name)->execute();
  }

} elseif (EC_DRUPAL_VERSION >= 6) {
  /***************************************************************
   * D6 VERSION 
   ***************************************************************/

  function _dcf_hook_boot($module) {
    return true;
  }
  
  function _dcf_hook_init($module) {
    return true;
  }
  
  function _dcf_hook_menu($items, $maycache) {
    $new_items = array();
    foreach ($items as $k => $v)
      $new_items[_dcf_internal_path($k)] = $v;
    return $new_items;
  }
  
  function _dcf_convert_render_array(&$a) {
    if (!empty($a['#markup'])) {
      $a['#value'] = $a['#markup'];
      unset($a['#markup']);
    }
    if (!empty($a['#type']) && $a['#type'] == 'actions')
      unset($a['#type']);
    foreach ($a as $k => &$v) 
      if (is_array($v))
        _dcf_convert_render_array($v);
  }
  
  function _dcr_render_array($output) {
    foreach ($output as $k => &$v)
      if ((is_numeric($k) || $k{0} != '#') && is_string($v))
        $v = array( '#type' => 'markup', '#value' => $v, '#weight' => -1 );
    _dcf_convert_render_array($output);
    return drupal_render($output);
  }
  
  function _dcr_form(&$form) {
    foreach ($form as $k => &$v)
      if ((is_numeric($k) || $k{0} != '#') && is_string($v))
        $v = array( '#type' => 'markup', '#value' => $v, '#weight' => -1 );
    _dcf_convert_render_array($form);
    return $form;
  }
  
  function _dcf_internal_path($path) {
    return str_replace(
      array( 'admin/config/system/cron', 'admin/modules' ),
      array( 'admin/build/cron', 'admin/build/modules' ),
      $path
    );
  }
    
  function _dco_watchdog($type, $message, $variables = array(), $severity = WATCHDOG_NOTICE, $link = NULL) { // WARN d7 changed WATCHDOG_ costants
    return watchdog($type, $message, $variables, $severity, $link);
  }
  
  function _dco_l($text, $path, array $options = array()) {
    return l($text, $path, $options);
  }
  
  function _dcf_form_validate(&$form, &$form_state) {
    return array('form' => &$form, 'form_state' => &$form_state);
  }
  
  function _dco_theme($name, $args) {
    return call_user_func_array('theme', array_merge(array($name), array_values($args)));
  }
  
  function _dcf_theme_signature($args) {
    return array( 'variables' => $args );
  }
  
  function _dcr_hook_theme($specs) {
    foreach ($specs as $k => $v) {
      if (!empty($v['variables'])) {
        $v['arguments'] = $v['variables'];
        unset($v['variables']);
      }
      if (!empty($v['render element'])) {
        $v['arguments'] = array ( $v['render element'] => NULL );
        unset($v['render element']);
      }
    }
    return $specs;
  }
  
  function _dcf_theme_form(&$args) {
    return array( 'variables' => array( 'form' => $args ) );
  }
  
  /***************************************************************
   * D6 MISSING FUNCTIONS 
   ***************************************************************/
  
  function drupal_render_children($form) {
    return drupal_render(_dcr_form($form));
  }
  
  /***************************************************************
   * D6 EXTRA FUNCTIONS 
   ***************************************************************/
  
  function drupal_module_get_min_weight($except_module = false) {
    return !$except_module ? db_result(db_query("select min(weight) from {system}")) :
      db_result(db_query("select min(weight) from {system} where name != '%s'", $except_module));
  }
  
  function drupal_module_get_weight($name) {
    return db_result(db_query("select weight from {system} where name = '%s'", $name));  
  }
  
  function drupal_module_set_weight($name, $weight) {
    db_query("update {system} set weight = %d where name = '%s'", $weight, $name);
  }
  
} else {

  /***************************************************************
   * D5 VERSION 
   ***************************************************************/
  
  function _dcf_hook_boot($module) {
    // Never called in D5
    return true;
  }
  
  function _dcf_hook_init($module) {
    // Should call hook_boot
    if (function_exists($f = $module . '_hook_boot'))
      $f();
    return false;
  }
  
  function _dcf_hook_menu($items, $maycache) {
    // $maycache = false NOT supported (should call hook_init) 
    if (!$maycache)
      return false;
    
    $new_items = array();
    foreach ($items as $path => $i) {
      $item = array(
        'path' => _dcf_internal_path($path),
      );
      if (!empty($i['title']))
        $item['title'] = t($i['title']);
      if (!empty($i['description']))
        $item['description'] = t($i['description']);
      if (!empty($i['page callback']))
        $item['callback'] = $i['page callback'];
      if (!empty($i['page arguments']))
        $item['callback arguments'] = $i['page arguments'];
      if (!empty($i['access arguments']))
        $item['access'] = user_access($i['access arguments']);
      if (!empty($i['access callback']) && $i['access callback'] === TRUE)
        $item['access'] = TRUE;
        
      if (!empty($i['type']))
        $item['type'] = $i['type'];
      if (!empty($i['weight']))
        $item['weight'] = $i['weight'];
      $new_items[] = $item;
    }
    return $new_items;
  }
  
  function _dcf_convert_render_array(&$a) {
    if (!empty($a['#markup'])) {
      $a['#value'] = $a['#markup'];
      unset($a['#markup']);
    }
    if (!empty($a['#type']) && $a['#type'] == 'actions')
      unset($a['#type']);
    foreach ($a as $k => &$v) 
      if (is_array($v))
        _dcf_convert_render_array($v);
  }
  
  function _dcr_render_array($output) {
    foreach ($output as $k => &$v)
      if ((is_numeric($k) || $k{0} != '#') && is_string($v))
        $v = array( '#type' => 'markup', '#value' => $v, '#weight' => -1 );
    _dcf_convert_render_array($output);
    return drupal_render($output);
  }
  
  function _dcr_form(&$form) {
    foreach ($form as $k => &$v)
      if ((is_numeric($k) || $k{0} != '#') && is_string($v))
        $v = array( '#type' => 'markup', '#value' => $v, '#weight' => -1 );
    _dcf_convert_render_array($form);
    return $form;
  }
  
  function _dcf_internal_path($path) {
    return str_replace(
      array( 'admin/reports', 'admin/config/system/cron', 'admin/modules' ),
      array( 'admin/logs', 'admin/build/cron', 'admin/build/modules' ),
      $path
    );
  }
  
  function _dco_watchdog($type, $message, $variables = array(), $severity = WATCHDOG_NOTICE, $link = NULL) { // WARN d7 changed WATCHDOG_ costants
    return watchdog($type, t($message, $variables), $severity, $link);
  }
  
  function _dco_l($text, $path, array $options = array()) {
    return l($text, $path, 
      !empty($options['attributes']) ? $options['attributes'] : array(), 
      !empty($options['query']) ? $options['query'] : NULL, 
      !empty($options['fragment']) ? $options['fragment'] : NULL, 
      !empty($options['absolute']) ? $options['absolute'] : FALSE, 
      !empty($options['html']) ? $options['html'] : FALSE);
  }
  
  function _dcf_form_validate(&$form, &$form_state) {
    $rform = array(
      'form_id' => array(
        '#value' => &$form
      )
    );
    $rform_state = array( // TODO If $form_state['op'] i should set 'op'
      'values' => &$form_state,
    
    );
    if (empty($rform_state['values']['form_id']))
      $rform_state['values']['form_id'] = &$form;
    return array('form' => &$rform, 'form_state' => &$rform_state);
  }
  
  function _dco_theme($name, $args) {
    return call_user_func_array('theme', array_merge(array($name), array_values($args)));
  }
  
  function _dcf_theme_signature($args) {
    return array( 'variables' => $args );
  }
  
  function _dcr_hook_theme($specs) {
    return $specs;
  }
  
  function _dcf_theme_form(&$args) {
    return array( 'variables' => array( 'form' => $args ) );
  }
  
  /***************************************************************
   * D5 MISSING FUNCTIONS 
   ***************************************************************/
  
  function ip_address() {
    return $_SERVER['REMOTE_ADDR'];
  }
  
  function drupal_render_children($form) {
    return drupal_render(_dcr_form($form));
  }

  /***************************************************************
   * D5 EXTRA FUNCTIONS 
   ***************************************************************/
  
  function drupal_module_get_min_weight($except_module = false) {
    return !$except_module ? db_result(db_query("select min(weight) from {system}")) :
      db_result(db_query("select min(weight) from {system} where name != '%s'", $except_module));
  }
  
  function drupal_module_get_weight($name) {
    return db_result(db_query("select weight from {system} where name = '%s'", $name));  
  }
  
  function drupal_module_set_weight($name, $weight) {
    db_query("update {system} set weight = %d where name = '%s'", $weight, $name);
  }
}

/*
$_dcr_.... Variabile che andrà rimossa (non serve piu')
  In genere sono parametri in signature di funzioni o passate a funzioni
  Es:
    function elysia_cron_menu($_dcr_maycache = true) {
    ...
    return _dcf_hook_menu($items, $_dcr_maycache);

$_dco_.... variabile alla quale andrà rimosso il prefisso _dco_ 
  in c'e' genere una funzione filtro successiva (da rimuovere anch'essa)
  Es:
    function elysia_cron_settings_form_validate($_dco_form, &$_dco_form_state) {
    extract(_dcf_form_validate($_dco_form, $_dco_form_state));

function _dcf_ funzione filtro che andrà rimossa 
  In quanto poi si puo' usare direttamente l'input
  Da notare che va rimossa tutta la riga, anche l'extract o l'if che la usa

function _dco_ funzione override. Basterà togliere "_dco_" iniziale
  Funzione che c'e' in D67 ma non in D5, o che aveva una signature diversa
  
function _dcr_ funzione filtro usata per i return che andrà rimossa, ma SOLO il nome funzione e gli eventuali parametri oltre al primo
  Es:
    return _dcr_render_array($output);
  Diventa:
    return $output;
  



SCHEME / INSTALL
------------------------------------------------------------------------
| [D56] 3. New Schema API [EC]
| [D56] 4. New format for hook_install() [EC]
| [D56] 5. New format for hook_uninstall() [EC]
| [D56] 6. New format for hook_update_N() [EC]
| [D56] 23. new hook_update_N naming convention TODO
| [D67]   5 The $ret parameter has been removed from all Schema operations
| [D67]   6 Update hooks now return strings or throw exceptions, and update_sql() is no more
\ TODO RES: Il file .install deve essere specifico per versione. In D5 si puo' mettere che se trova il file .install_d5 usa quello (cosi' in .install c'e' il D7)

| [D56] 24. New syntax for .info files
| [D56] 25. Core compatibility now specified in .info files
| [D56] 26. PHP compatibility now specified in .info files
| [D67]  15 Module .info files can have configure line <<< PER CRON METTERLO
\ TODO RES: .info specifico per versione e .info_d5

| [D67] Module .info files must now specify all loadable code files explicitly
\ TODO RES: i require_once vanno messi in un "if (D5) {" oppure con un commento che fa capire che andranno tolti

| [D67]   1 Schema descriptions are no longer translated
| [D67]   2 Schema descriptions are now plain text instead of HTML
| [D67]   8 Database prefixes are now per-connection
\ IGNORE

DB
------------------------------------------------------------------------
| [D67] A completely new database API has been added
\ TODO RES: La nuova lib si riesce a mettere in D5, quindi si puo' usare
    http://drupal.org/update/modules/6/7#dbtng
    http://drupal.org/node/310069

[D56] 52. Remove db_num_rows() method
[D56] 53. Remove $row argument from db_result() method
[D56] 68. db_next_id() is gone, and replaced as db_last_insert_id()
[D67]   1 The datetime field type has been removed in favour of database engine specific types
[D67]  17 db_rewrite_sql() replaced with hook_query_alter()
[D67]   4 db_result() has been removed; use ->fetchField() instead
[D67]   5 Do not use SELECT COUNT(*) to check for existence of rows in a table
[D67]   4 db_is_active() has been removed


MENU / PERM
------------------------------------------------------------------------
| [D56] 1. Entirely new menu system [EC]
|   http://drupal.org/node/103114
\ TODO RES: usare _dcf_hook_menu e _dco_maycache [EC] 
  
| [D56] 51. hook_init() is split up into hook_init() and hook_boot() [<--- riguarda $maycache = false che va in _init e vecchio _init che va in _boot]
\ TODO RES: si usa come D7 hook_boot e hook_init. Come prima riga del primo:
  if (!_dcf_hook_boot('elysia_cron')) return;
  Del secondo:
  if (!_dcf_hook_init('elysia_cron')) return;
  hook_init quindi andrà messo anche se non usato! [EC]

| [D67] hook_perm() renamed to hook_permission()
| [D67] Permissions have titles (required) and descriptions (optional)
| [D67] Permissions are no longer sorted alphabetically
\ TODO Duplicare dichiarazioni con il nuovo formato [EC]

| [D67]   6 Menu "page callbacks" and blocks should return an array and hook_page_alter() (Render arrays discussion) +++
\ TODO RES: [EC]
    $output = array(
      '#type' => 'markup',
      '#markup' => $output
    );
    return _dcr_render_array($output);

[D67]   4 Changed hook_menu_link_alter() (removed the $menu parameter)
[D67]  11 Renamed menu_path_is_external() to url_is_external()
[D67]   3 The function menu_valid_path() has been renamed to drupal_valid_path(), and its inputs have changed
[D67]   7 Function menu_tree_data() now expects an array of links instead of a query results
[D67]   5 menu_default_node_menu replaced with per-content type settings

| [D56] 77. hook_access() added parameter (, $account) [NEW]
\ IGNORE

NODE
------------------------------------------------------------------------
[D56] 20. node/add is now menu generated
[D56] 49. hook_nodeapi('submit') has been replaced by op='presave'
[D56] 56. Node access modules : simplified hook_enable / hook_disable / hook_node_access_records
[D56] 57. node_access_rebuild($batch_mode = TRUE) / node_access_needs_rebuild()
[D67]   2 hook_nodeapi, hook_node_type, hook_user, and hook_block removed and replaced with families of related functions
[D67]   3 In hook_node_info() change 'module' back to 'base' and change 'node' to 'node_content'
[D67]   6 node_load() and node_load_multiple()
[D67]  13 Move node, taxonomy, and comment links into $node->content; Deprecate hook_link()
[D67]  14 hook_nodeapi, hook_node_type, hook_user, and hook_block removed and replaced with families of related functions
[D67]  10 Replace node_view() with node_build()
[D67]  13 node_invoke_nodeapi() removed
[D67]  14 Removed $op "rss item" from hook_nodeapi() in favor of 'rss' view mode
[D67]   4 node_get_types($op) replaced by node_type_get_$op()
[D67]   9 $teaser parameter changed to $view_mode in node viewing functions and hooks, $node->build_mode property removed
[D67]   8 hook_nodeapi_xxx() becomes hook_node_xxx()
[D67]  16 hook_nodeapi, hook_node_type, hook_user, and hook_block removed and replaced with families of related functions
[D67]  21 hook_access() removed in favor of hook_node_access()
[D67]   5 Node body field now requires normal field API usage

SYSTEM
------------------------------------------------------------------------
| [D56] 21. New watchdog hook, logging and alerts
| [D56] 22. Parameters of watchdog() changed
\ TODO RES Usare _dco_watchdog nel nuovo formato al posto di watchdog [EC]

[D56] 28. cache_set parameter order has changed
[D56] 29. Cache set and get automatically (un)serialize complex data types
[D56] 43. custom_url_rewrite() replaced +++
[D56] 46. hook_help() parameters are changed
[D56] 48. node_feed() parameters changed
[D67]   3 Use module_implements not module_list when calling hook implementations
[D67]  15 drupal_eval() renamed to php_eval
[D67]   6 Renamed module_rebuild_cache() to system_rebuild_module_data(), renamed system_theme_data() to system_rebuild_theme_data(), and added system_get_info()
[D67]  22 hook_filter() and hook_filter_tips() replaced by hook_filter_info()
[D67]  32 Text formats access is now controlled by permissions, and filter_access() parameters have changed
[D67]  33 The parameters to filter_formats() have changed
[D67]   4 drupal_alter() now takes at most 3 parameters by reference
[D67]   9 Replaced custom_url_rewrite_inbound() with hook_url_inbound_alter()
[D67]  10 hook_load() signature and return value change



[D67]  19 drupal_goto() follows parameters of url() EC!!!!!!!!!!!
[D67]  23 url() 'query' field must be array
[D67]   2 l() function class attribute
[D67]  23 Convert class attributes to array in favor of a string ++++++++
TODO TODO TODO


[D67]  24 Query string functions renamed +++
[D67]   4 Text formats (input formats) must be defined
[D67]   5 Text format (input format) identifier is now a machine name

| [D67]   3 WATCHDOG_EMERG was renamed to WATCHDOG_EMERGENCY
\ IGNORE

STRINGS
------------------------------------------------------------------------
[D56] 17. $locale became $language
[D56] 79. Translations are looked for in ./translations
[D67]   7 Added string context support to t() and format_plural(), changed parameters

USER
------------------------------------------------------------------------
[D56] 44. hook_user('view'), hook_profile_alter() and profile theming
[D56] 74. user_authenticate() changed parameters
[D67]   4 user_load_multiple() and hook_user_load(), user_load() signature change, and $reset parameter
[D67]  10 Parameters to function user_authenticate() changed
[D67]  14 Removed several unnecessary arguments to various hook_user_$op hooks and removed hook_profile_alter
[D67]  20 hook_user_form(), hook_user_register() are gone
[D67]  21 hook_user_validate() and hook_user_submit() are gone
[D67]  22 hook_user_after_update() replaced by hook_user_update(), amended by hook_user_presave() for common operations
[D67]  10 New constants for user registration settings, and the default has been changed to "Visitors, but administrator approval is required"
[D67]   6 Arbitrary user data is harder to stash in the user object  ++++++

TAXONOMY
------------------------------------------------------------------------
[D56] 9. Taxonomy terms are now associated with node revisions, not just nodes
[D56] 50. taxonomy_get_vocabulary() changed to taxonomy_vocabulary_load()
[D56] 82. taxonomy_override_selector variable allows alternate taxonomy form operations
[D67]   4 Taxonomy CRUD functions renamed and refactored
[D67]   5 New taxonomy hooks for term and vocabulary
[D67]   7 taxonomy_term_load() and taxonomy_term_load_multiple()
[D67]  10 New taxonomy hooks for term and vocabulary
[D67]  12 taxonomy_get_tree()
[D67]  20 Taxonomy db table names have changed to begin with 'taxonomy_'
[D67]   2 Added taxonomy_vocabulary_load_multiple()
[D67]   3 Related terms functionality was removed from taxonomy.module
[D67]  37 All taxonomy functions relating to nodes have been removed or refactored
[D67]   8 Replaced taxonomy_term_path(), hook_term_path(), language_url_rewrite(), and custom_url_alter_outbound() with hook_url_outbound_alter()
[D67]  18 Taxonomy synonyms have been removed
[D67]  14 taxonomy_form_all() removed
[D67]   5 taxonomy_term_view() and taxonomy-term.tpl.php for term display

FILE SYSTEM
------------------------------------------------------------------------
[D56] 34. {files} table changed
[D56] 35. file_check_upload() merged into file_save_upload()
[D56] 36. {file_revisions} table is now {upload}
[D67]   5 File API reworked, stream notation required, file_unmanaged_* functions
[D67]   8 file_load_multiple()
[D67]   1 User pictures are now managed files
[D67]  18 Removed FILE_STATUS_TEMPORARY
[D67]  19 file_prepare_directory() (replacement for file_check_directory() will now recursively create directories
[D67]  40 file_check_directory() renamed to file_prepare_directory()
[D67]   4 system_retrieve_file() API cleanup
[D67]   6 Rename file to file_managed ???
[D67]   1 file_directory_path() has been removed

FORM
------------------------------------------------------------------------
| [D56] 2. Major FormAPI improvements
|   http://drupal.org/node/144132
\ TODO RES [PARZIALE!!!]
    function elysia_cron_settings_form_validate($_dco_form, &$_dco_form_state) {
      extract(_dcf_form_validate($_dco_form, $_dco_form_state));
      ... e poi usare $form e $form_state
    
[D56] 13. hook_form_alter() parameters have changed
[D56] 38. Node previews and adding form fields to the node form
[D56] 47. Change "Submit" to "Save" on buttons
[D56] 80. hook_submit() has been removed

| [D67] Use '#markup' not '#value' for markup
| CHECK: (va bene sempre? - NO: per i submit non è cosi'....)
|   '#value' => | "#value" => | ['#value'] = | ["#value"] = 
\ TODO RES [PARZIALE?] [EC]
    Usare _dcr_form(&$form)... esempio return _dcr_form($form);

[D67]  17 Easier check for node form during hook_form_alter()
[D67]  12 drupal_execute() renamed to drupal_form_submit()
[D67]  18 drupal_get_form() returns a render array instead of a string
[D67]   7 The signature of the callback from drupal_get_form() changed to add $form
[D67]  14 form_clean_id() has been renamed to drupal_clean_css_identifier()
[D67]  15 New #type 'text_format' for text format-enabled form elements
[D67]   2 Removal of FAPI $form['#redirect'] and $_REQUEST['destination'] ++++
[D67]   9 Form submit buttons consistently grouped in actions array
[D67]  11 hook_form_alter and hook_form_FORM_ID_alter run together for each module
[D67]   2 hook_form_BASE_FORM_ID_alter() is invoked for shared form constructors

THEME
------------------------------------------------------------------------
[D56] 18. New hook_theme() registry
[D56] 19. template_preprocess_* with .tpl.php files
[D67]   8 Element #type property no longer treated as a theme function in drupal_render()
[D67]  21 Renamed drupal_set_content() and drupal_get_content()
[D67]  22 Instead of theme('page', ...), think of drupal_set_page_content()


| [D67]   2 theme() now takes only two arguments ++++++++++
\ TODO RES Automatizzare è un problema, bisogna conoscere i nomi argomenti di tutti i casi... si possono automatizzare i casi specifici (es table)
    Cmq va sostituito theme con _dco_theme, tenendo i parametri in ordine
    Es:
    $output .= theme('table', array('Job / Rule', 'Last run', 'Last exec time', 'Exec count', 'Avg/Max Exec time'), $rows);
    diventa:
    $output .= _dco_theme('table', array('header' => array('Job / Rule', 'Last run', 'Last exec time', 'Exec count', 'Avg/Max Exec time'), 'row' => $rows));
    [EC]
\ TODO RES Per quanto riguarda le dichiarazioni, vanno cambiate cosi': [EC]
  function theme_test($a, $b, $c = 1) {
  in:
  function theme_test($variables, $_dcr_b, $_dcr_c = 1) {
    extract(_dcf_theme_signature(array('a' => $variables, 'b' => $_dcr_b, 'c' => $_dcr_c)); // Usare l'extract puo' avere problemi con var passate per riferimento (ma si poteva?)
    if (empty($variables['c']))
      $variables['c'] = 1;  // I default vanno fatti comunque [TODO In realta' andrebbero messi nell'hook_theme e qui gestiti...]


| [D67]   3 hook_theme() requires "variables" or "render element" instead of "arguments" to better integrate with drupal_render()
\ TODO Questa è da capire meglio: credo basti sostituire arguments con variables sempre per i miei casi [EC]
    RES [PARZIALE?]
    return _dcr_hook_theme(...);

[D67]   2 theme('placeholder') replaced by drupal_placeholder()
[D67]   8 Preprocess functions need to now specify "theme_hook_suggestion(s)" instead of "template_file(s)"
[D67]   9 Use #theme='links__MODULE' or #theme='links__MODULE_EXTRA_CONTEXT' when adding links to a render array
[D67]  10 Use #type='link' for adding a single link to a render array, particularly for tables that include operation links like 'edit', 'delete', etc.
[D67]  11 Added entity_prepare_view() and hook_entity_prepare_view()
[D67]   1 New method for altering the theme used to display a page (global $custom_theme variable removed) +++
[D67]  12 theme_pager() no longer takes limit parameter
[D67]  13 theme_username() parameters changed

INTERFACE
------------------------------------------------------------------------
[D56] 39. JavaScript behaviors: new approach to attaching behaviors
[D56] 40. JavaScript themeing
[D56] 41. Translation of JavaScript files
[D56] 42. JavaScript aggregation
[D56] 73. Use drupal_set_breadcrumb() instead of menu_set_location() to set custom breadcrumbs
[D56] 78. Can't add javascript or CSS to header in hook_footer() [VEDI SOTTO CHE E' STATO TOLTO]
[D67]   2 Changed parameters for drupal_add_js() and drupal_add_css()
[D67]   7 Replace 'core', 'module' and 'theme' with 'file' in drupal_add_js()
[D67]   4 New hook_js_alter to alter JavaScript
[D67]   3 Ability to reset JavaScript and CSS
[D67]   6 The function drupal_set_html_head() has been renamed to drupal_add_html_head()
[D67]   7 Inline cascading stylesheets from drupal_add_css()
[D67]   8 Attached JavaScript and CSS for forms
[D67]  17 Changes to HTTP header functions
[D67]   1 hook_footer() was removed, $closure became $page_bottom, $page_top added
[D67]  11 JavaScript variable Drupal.jsEnabled has been removed
[D67]  19 Weighting of stylesheets
[D67]  34 Rename drupal_to_js() and drupal_json() to drupal_json_encode() and drupal_json_output()
[D67]  16 drupal_set_header() and drupal_get_header() renamed to drupal_add_http_header() and drupal_get_http_header()
[D67]   2 HTTP Status code setting with drupal_add_http_header() changed (on top of a previous API change)
[D67]   8 Javascript and CSS loading changes

DRUPAL PATHS
------------------------------------------------------------------------
| [D56] 69. admin/logs renamed to admin/reports
| [D67] http://drupal.org/node/719612
| [D67]   5 Changed log out path from 'logout' to 'user/logout' for consistency Note: Link goes to separate sub-page!
| [D67]   4 Moved statistics settings from admin/reports/settings to admin/settings/statistics admin/config/system/statistics and added a new 'administer statistics' permission
| [D67]   1 Moved filter module administrative URLs from admin/settings/filters/* to admin/settings/filter/*
| [D67]  15 Many paths to admin screens have changed
| [D67]   6 Changed Clean URLs and Search settings page path
\ TODO RES: Passare tutti i path da _dcf_internal_path (quelli di _hook_menu vengono già fatti da _dcf_hook_menu) [EC]

| [D67]   3 Added a new top-level 'international' admin menu item

MISC
------------------------------------------------------------------------
| [D56] 7. The arguments to url() and l() have changed
\ TODO RES Usare _dco_l al posto di l con il nuovo formato [EC]

[D56] 14. hook_link_alter() parameters have changed
[D56] 15. hook_profile_alter() parameters have changed
[D56] 16. hook_mail_alter() parameters have changed
[D56] 71. drupal_mail() parameters have changed
[D67]   8 Parameters to check_markup() have changed, default format change
      USATO
[D67]  20 Session functions are renamed.
[D67]  15 Parameters for actions_synchronize() have changed
[D67]  16 Parameters for drupal_http_request() have changed
[D67]   9 Make sticky tableheaders optional
[D67]  16 "use PHP for settings" permission should be used for all PHP settings rights (replaces "use PHP for block visibility")
[D67]  23 hook_elements() renamed to hook_element_info()
[D67]  25 drupal_urlencode() replaced by drupal_encode_path()


[D67]  39 All e-mails are considered to originate as HTML ????
[D67]   8 hook_update_index() only runs when searching enabled for a given module
[D67]   9 Format date types "small" and "large" have been changed to "short" and "long"
[D67]  10 "Boxes" have been renamed to "custom blocks"
[D67]  12 Arguments to xmlrpc() have changed
[D67]   9 Node, filter and comment modules tables renamed to singular


------------------------------------------------------------------------
------------------------------------------------------------------------
------------------------------------------------------------------------

DONE
------------------------------------------------------------------------
[D67]  19 Replace drupal_clone() with clone
DONE viene ancora usato in moduli esterni, cmq basta:
function drupal_clone($object) { return clone($object); }


IGNORE?
------------------------------------------------------------------------
[D56] 30. node_revision_list() now returns keyed array
[D56] 45. Distributed Authentication changes
[D56] 59. Removed several functions from drupal.js
[D56] 61. new helper function: db_placeholders()
[D56] 63. Check node access before emailing content
[D56] 64. form property #DANGEROUS_SKIP_CHECK removed
[D56] 65. "Access control" renamed to "permissions"
[D56] 66. locale_refresh_cache() has been removed
[D56] 75. Automatically add Drupal.settings.basePath
[D56] 76. Get an object relevant on specific paths ?????
[D67]   1 drupal_set_title() uses check_plain() by default
[D67]   3 Changed Drupal.behaviors to objects having the methods 'attach' and 'detach'
[D67]   6 "administer nodes" permission split into "administer nodes", "access content overview", and "bypass node access"
[D67]   4 Use absolute path (constructed from DRUPAL_ROOT) when including a file
[D67]  18 Update functions in .install files must include a Doxygen style comment
[D67] New permission tables
[D67]   6 Settings passed locally to JavaScript Behaviors
[D67]  19 Add Doxygen @file tag to all install files
[D67]   2 Hide empty menu categories with access callback
[D67]   8 Alternative cache implementations changed
[D67]  12 Login validation change for distributed authentication modules
[D67]  13 jQuery UI (1.7) was added into core
[D67]  15 #theme recommended for specifying theme function
[D67]  18 Removed taxonomy module support for multiple tids and depth in term paths
[D67]  21 hook_node_access_records() now applies to unpublished nodes; 'view own unpublished content' permission added
[D67]  12 xmlrpc() wrapper function removed
[D67]  36 API for modules providing search has changed
[D67]  41 Block Cache constants renamed to DRUPAL_CACHE
[D67]  11 Remove moderate column from node_schema()

IGNORE
------------------------------------------------------------------------
[D56] 10. format_plural() accepts replacements
[D56] 11. New drupal_alter() function for developers
[D56] 37. drupal_add_css() supports automatic RTL CSS discovery
[D56] 58. Upgraded to jQuery 1.2.3
[D56] 60. The book module has been rewritten to use the new menu system
[D56] 62. Comment settings are now per node type
[D56] 81. hook_comment() no longer supports the 'form' operation, use hook_form_alter() instead
[D67] Drupal 7 requires PHP 5.2 or higher
[D67] _comment_load() is now comment_load()
[D67]   6 Removed file_set_status()
[D67]   5 Default parameter when getting variables
[D67]   7 Block module now optional
[D67]   8 drupal_uninstall_module() is now drupal_uninstall_modules()
[D67] Some #process functions have been renamed
[D67]  15 file_validate_extensions() enforces check for uid=1
[D67]  16 file_scan_directory() and drupal_system_listing() use preg regular expressions
[D67]   2 file_scan_directory() now uses a preg regular expression for the nomask parameter
[D67]   2 file_scan_directory()'s optional parameters are now an array
[D67]   7 file_scan_directory() now uses same property names as file_load()
[D67] Use defined constant REQUEST_TIME instead of time()
IGNORE, HINT: Aggiornare elysia_common e usare ovunque elysia_now
[D67] referer_uri() has been removed
[D67] Rebuild functions have changed names
IGNORE non usavo mai drupal_rebuild_code_registry o drupal_rebuild_theme_registry
function drupal_theme_rebuild() {
  drupal_rebuild_theme_registry();
}
function registry_rebuild() {
  drupal_rebuild_code_registry();
}
[D67] Comment status values in the database have flipped so they match node status
[D67]  11 Parameters swapped in book_toc()
[D67]   3 Commenting style - use 'Implements hook_foo().' when documenting hooks
[D67]  10 comment_save() now supports programmatic saving
[D67]  11 comment_validate() has been removed
[D67]  14 comment_node_url() has been removed
[D67]  20 Added comment_load_multiple() and hook_comment_load()
[D67]  24 Blog API module removed from Drupal core
[D67]   9 .module file available during install
[D67]  17 drupal_add_css() now supports external CSS files
[D67]  18 New hook_comment_presave() for comments
[D67]  12 Comment.timestamp split into 'created' and 'changed'
[D67]  13 Comment rendering overhaul
[D67]   6 COMMENT_NODE_* constants have new names, but same values
[D67]   3 'comment_form' form ID changed to 'comment_node_TYPE_form'

IGNORE NEW
------------------------------------------------------------------------
[D56]  8. Variable names can now be 128 characters long
[D56] 12. New module_load_include() and module_load_all_includes() functions for developers
[D56] 27. New db_column_exists() method
[D56] 31. New operation in image.inc: image_scale_and_crop()
[D56] 32. New user_mail_tokens() method
[D56] 33. New ip_address() function when working behind proxies
[D56] 54. Block-level caching
[D56] 55. Batch operations : progressbar for heavy computations
[D56] 67. FormAPI image buttons are now supported
[D56] 70. New helper function: drupal_match_path()
[D56] 72. New hook: hook_mail
[D67]   7 New hooks: hook_modules_installed, hook_modules_enabled, hook_modules_disabled, and hook_modules_uninstalled
[D67]  19 New user_cancel API
[D67]   1 JavaScript should be compatible with other libraries than jQuery
[D67]   3 External JavaScript can now be referenced through drupal_add_js()
[D67]   5 jQuery 1.3.x
[D67]   5 Standardized API for static variables and resetting them
[D67]  10 Save new users and nodes with specified IDs
[D67]  20 Add node_delete_multiple()
[D67]   1 Node access hooks now have drupal_alter() functions
[D67]   5 Added hook_block_info_alter()
[D67]  17 Ability to add multiple JavaScript/CSS files at once
[D67]  22 New tar archive library added
[D67]   6 Added drupal_set_time_limit()
[D67]   7 Module .info files can now optionally specify the version number of the module it depends on
[D67]  20 AHAH/AJAX Processing has changed; #ajax, new 'callback' member of the array, and the callback must be rewritten
[D67]  25 Added API functions for creating, loading, updating, and deleting user roles and permissions
[D67]  26 New hook: hook_file_url_alter()
[D67]  27 jQuery Once method for applying JavaScript behaviors once
[D67]  28 Database schema (un)installed automatically
[D67]  29 User 1 is now called site maintenance account
[D67]  30 CRUD hooks for menu links: hook_menu_link_insert(), hook_menu_link_update(), hook_menu_link_delete()
[D67]  31 Default text formats have been revamped
[D67]  35 theme_links() has a new parameter $heading for accessibility
[D67]  38 Added hook_entity_load()
[D67]   1 Trigger and Actions API overhaul
[D67]  13 New entity_info_cache_clear() API function
[D67]  17 New hook_hook_info() added
[D67]   1 New hooks: hook_admin_paths() and hook_admin_paths_alter()
[D67]   4 Language neutral content now has an explicit language associated with it
[D67]   5 New API function: format_username() and new hook: hook_username_alter()
[D67]   6 Functions called very often that need a drupal_static() variable can use an optimized way of calling that function
[D67]   7 A theme hook name followed by a double underscore ('__') is a default 'pattern'
[D67]  12 New pattern for cross-database, performant, case-insensitive comparisons
[D67]   3 Block tables renamed
[D67]   4 Block deltas are now specified as strings
[D67]   1 New language negotiation API introduced
[D67]   1 New 'restrict access' parameter in hook_permission() for labeling unsafe permissions
[D67]   3 User-configured time zones now serve as the default time zone for PHP date/time functions
[D67]   7 New hook_module_implements_alter
[D67]  13 Translatable Fields
[D67]   7 Two new functions added: hook_page_build() and hook_page_alter()
[D67]  13 Foreign keys added to core database table schema
[D67]   2 New update dependency system, affecting the order in which module updates are run
[D67]  15 Custom menu API [INTERESSANTE]

*/