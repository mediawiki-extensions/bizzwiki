CREATE TABLE /*$wgDBprefix*/supergroups (
  `sgr_user` int(5) unsigned,
  `sgr_group` int(5) unsigned default '0',
  
  PRIMARY KEY (sgr_user,sgr_group),
  KEY (sgr_group)
  
) TYPE=InnoDB;