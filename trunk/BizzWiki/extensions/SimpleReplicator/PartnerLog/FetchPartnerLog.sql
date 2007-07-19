CREATE TABLE /*$wgDBprefix*/logging_partner (
  -- BIZZWIKI
  -- taken from 'tables.sql' in 'maintenance' directory.
  -- only modified 'rc_id' line & added 'rc_done'
  -- log_id :   not 'auto_increment'
  -- log_done:  status indicator for replication

  log_done tinyint unsigned NOT NULL default '0',
  
  -- Symbolic keys for the general log type and the action type
  -- within the log. The output format will be controlled by the
  -- action field, but only the type controls categorization.
  log_type varbinary(10) NOT NULL default '',
  log_action varbinary(10) NOT NULL default '',
  
  -- Timestamp. Duh.
  log_timestamp binary(14) NOT NULL default '19700101000000',
  
  -- The user who performed this action; key to user_id
  log_user int unsigned NOT NULL default 0,
  
  -- Key to the page affected. Where a user is the target,
  -- this will point to the user page.
  log_namespace int NOT NULL default 0,
  log_title varchar(255) binary NOT NULL default '',
  
  -- Freeform text. Interpreted as edit history comments.
  log_comment varchar(255) NOT NULL default '',
  
  -- LF separated list of miscellaneous parameters
  log_params blob NOT NULL,

  -- Log ID, for referring to this specific log entry, probably for deletion and such.
  log_id int unsigned NOT NULL,

  -- rev_deleted for logs
  log_deleted tinyint unsigned NOT NULL default '0',

  PRIMARY KEY log_id (log_id),
  KEY type_time (log_type, log_timestamp),
  KEY user_time (log_user, log_timestamp),
  KEY page_time (log_namespace, log_title, log_timestamp),
  KEY times (log_timestamp)

) /*$wgDBTableOptions*/;
