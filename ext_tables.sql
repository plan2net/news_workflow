CREATE TABLE tx_newsworkflow_domain_model_relation (
uid INT(11) NOT NULL auto_increment,
pid INT(11) DEFAULT '0' NOT NULL,
uid_news INT(11) NOT NULL,
uid_news_original INT(11) NOT NULL,
pid_target INT(11) NOT NULL,
send_mail INT(11) NOT NULL,
crdate INT(11) unsigned DEFAULT '0' NOT NULL,
PRIMARY KEY (uid),
KEY parent (pid)
);