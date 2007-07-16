CREATE TABLE /*$wgDBprefix*/recentchanges_partner (
  -- BIZZWIKI
  -- taken from 'tables.sql' in 'maintenance' directory.
  -- only modified 'rc_id' line
  
  rc_id int NOT NULL, 
  rc_timestamp varbinary(14) NOT NULL default '',
  rc_cur_time varbinary(14) NOT NULL default '',
  
  -- As in revision
  rc_user int unsigned NOT NULL default '0',
  rc_user_text varchar(255) binary NOT NULL,
  
  -- When pages are renamed, their RC entries do _not_ change.
  rc_namespace int NOT NULL default '0',
  rc_title varchar(255) binary NOT NULL default '',
  
  -- as in revision...
  rc_comment varchar(255) binary NOT NULL default '',
  rc_minor tinyint unsigned NOT NULL default '0',
  
  -- Edits by user accounts with the 'bot' rights key are
  -- marked with a 1 here, and will be hidden from the
  -- default view.
  rc_bot tinyint unsigned NOT NULL default '0',
  
  rc_new tinyint unsigned NOT NULL default '0',
  
  -- Key to page_id (was cur_id prior to 1.5).
  -- This will keep links working after moves while
  -- retaining the at-the-time name in the changes list.
  rc_cur_id int unsigned NOT NULL default '0',
  
  -- rev_id of the given revision
  rc_this_oldid int unsigned NOT NULL default '0',
  
  -- rev_id of the prior revision, for generating diff links.
  rc_last_oldid int unsigned NOT NULL default '0',
  
  -- These may no longer be used, with the new move log.
  rc_type tinyint unsigned NOT NULL default '0',
  rc_moved_to_ns tinyint unsigned NOT NULL default '0',
  rc_moved_to_title varchar(255) binary NOT NULL default '',
  
  -- If the Recent Changes Patrol option is enabled,
  -- users may mark edits as having been reviewed to
  -- remove a warning flag on the RC list.
  -- A value of 1 indicates the page has been reviewed.
  rc_patrolled tinyint unsigned NOT NULL default '0',
  
  -- Recorded IP address the edit was made from, if the
  -- $wgPutIPinRC option is enabled.
  rc_ip varbinary(40) NOT NULL default '',
  
  -- Text length in characters before
  -- and after the edit
  rc_old_len int,
  rc_new_len int,

  -- Visibility of deleted revisions, bitfield
  rc_deleted tinyint unsigned NOT NULL default '0',

  -- Value corresonding to log_id, specific log entries
  rc_logid int unsigned NOT NULL default '0',
  -- Store log type info here, or null
  rc_log_type varbinary(255) NULL default NULL,
  -- Store log action or null
  rc_log_action varbinary(255) NULL default NULL,
  -- Log params
  rc_params blob NOT NULL default '',
  
  -- BIZZWIKI
  -- PRIMARY KEY rc_id (rc_id),
  PRIMARY KEY uid (uid),
  INDEX rc_id (rc_id),
   
  INDEX rc_timestamp (rc_timestamp),
  INDEX rc_namespace_title (rc_namespace, rc_title),
  INDEX rc_cur_id (rc_cur_id),
  INDEX new_name_timestamp (rc_new,rc_namespace,rc_timestamp),
  INDEX rc_ip (rc_ip),
  INDEX rc_ns_usertext (rc_namespace, rc_user_text),
  INDEX rc_user_text (rc_user_text, rc_timestamp)

) /*$wgDBTableOptions*/;