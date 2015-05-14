#
# Table structure for table 'tx_twsitemap_domain_model_entry'
#
CREATE TABLE tx_twsitemap_domain_model_entry (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	origin varchar(64) DEFAULT '' NOT NULL,
	source varchar(32) DEFAULT '' NOT NULL,
	domain varchar(200) DEFAULT '' NOT NULL,
	loc tinytext NOT NULL,
	lastmod int(11) DEFAULT '0' NOT NULL,
	changefreq int(11) DEFAULT '0' NOT NULL,
	priority double(11,2) DEFAULT '0.00' NOT NULL,
	language varchar(5) DEFAULT '' NOT NULL,
	position int(11) DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY domain (domain,priority),
	UNIQUE entrygroup (language,origin,domain,source)
);

#
# Table structure for table 'tx_twsitemap_domain_model_sitemap'
#
CREATE TABLE tx_twsitemap_domain_model_sitemap (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,

	domain varchar(64) DEFAULT '' NOT NULL,
	target_domain varchar(64) DEFAULT '' NOT NULL,
	scheme varchar(8) DEFAULT '' NOT NULL,
	gz tinyint(4) unsigned DEFAULT '0' NOT NULL,

	tstamp int(11) unsigned DEFAULT '0' NOT NULL,
	crdate int(11) unsigned DEFAULT '0' NOT NULL,
	cruser_id int(11) unsigned DEFAULT '0' NOT NULL,
	deleted tinyint(4) unsigned DEFAULT '0' NOT NULL,
	hidden tinyint(4) unsigned DEFAULT '0' NOT NULL,
	starttime int(11) unsigned DEFAULT '0' NOT NULL,
	endtime int(11) unsigned DEFAULT '0' NOT NULL,

	PRIMARY KEY (uid),
	KEY parent (pid),
	KEY domain (domain)
);

#
# Table structure for table 'pages'
#
CREATE TABLE pages (
    tx_twsitemap_nofollow tinyint(4) unsigned DEFAULT '0' NOT NULL,    
    tx_twsitemap_noindex tinyint(4) unsigned DEFAULT '0' NOT NULL
);