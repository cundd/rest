#
# Table structure for table 'fe_users'
#
CREATE TABLE fe_users (
    tx_rest_apikey tinytext
);


#
# Table structure for table 'tx_rest_domain_model_document'
#
CREATE TABLE tx_rest_domain_model_document (

	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	id varchar(255) DEFAULT '' NOT NULL,
	db varchar(255) DEFAULT '' NOT NULL,
	data_protected text NOT NULL,

  tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,


	PRIMARY KEY (uid),
	UNIQUE KEY guid (db,id),
	KEY parent (pid),
	KEY db (db),
	KEY id (id)
);
