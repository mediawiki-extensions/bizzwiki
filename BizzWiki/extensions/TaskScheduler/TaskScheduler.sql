CREATE TABLE /*$wgDBprefix*/task_scheduler (

  -- unique ID
  ts_id int NOT NULL auto_increment, 

  -- Task enabled/disabled
  ts_enable tinyint unsigned NOT NULL default '0', 

  -- Task Scheduler's lastest error code
  ts_code tinyint unsigned NOT NULL default '0', 

  -- when was this task created.
  ts_creation_timestamp varbinary(14) NOT NULL default '',
  
  -- when was the task last run
  ts_last_run_timestamp varbinary(14) NOT NULL default '',
  
  -- when ~ should the task run
  ts_next_run_timestamp varbinary(14) NOT NULL default '',  

  -- the PHP class to use for instantiating an object
  ts_class varchar(255) binary NOT NULL default '',
 
  -- the frequency at which the task must be run
  -- this is a function of the timebase
  ts_frequency tinyint unsigned NOT NULL default '0',
  
  -- the task priority (relative scale)
  ts_priority tinyint unsigned NOT NULL default '0',

  -- a state variable to keep until next round.
  ts_state varchar(255) binary NOT NULL default '',
 
  PRIMARY KEY ts_id (ts_id),
  INDEX ts_id (ts_id),
   
  INDEX ts_next_run_timestamp (ts_next_run_timestamp),
  INDEX ts_last_run_timestamp (ts_last_run_timestamp),

  INDEX ts_class (ts_class)  

) /*$wgDBTableOptions*/;