/*
	�û���
*/
use wuliu;
set names utf8;

create table if not exists users(
	uid int unsigned not null auto_increment primary key,
	username varchar(30) not null comment '�û���',
	password varchar(32) not null,
	email varchar(30) not null,
	token varchar(50) not null,
	token_exptime int(10) not null,
	status tinyint(1) not null default '0' comment '״̬��0-δ���1-�Ѽ���',
	regtime int(10) not null
)engine=MyISAM default charset=utf8;

/* drop table users; */
/* insert into email_user values (11, 'alice', 'alice', 'alice', 'alice', 123, 0, 123); */