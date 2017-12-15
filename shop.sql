/*==============================================================*/
/* DBMS name:      MySQL 5.0                                    */
/* Created on:     11. 12. 2017 20:20:22                        */
/*==============================================================*/


drop table if exists administrators;

drop table if exists auth;

drop table if exists customers;

drop table if exists images;

drop table if exists in_order;

drop table if exists orders;

drop table if exists products;

drop table if exists sellers;

drop table if exists users;

drop table if exists validations;



/*==============================================================*/
/* Table: auth                                                  */
/*==============================================================*/
create table auth
(
   id_user              int not null,
   code                 varchar(255) not null,
   expire				datetime,
   primary key (code),
   foreign key (id_user) references users (id_user)
);

/*==============================================================*/
/* Table: images                                                */
/*==============================================================*/
create table images
(
   id_image             int not null auto_increment,
   id_product           int not null,
   path                 varchar(255) not null,
   primary key (id_image),
   foreign key (id_product) references products (id_product)
);

/*==============================================================*/
/* Table: in_order                                              */
/*==============================================================*/
create table in_order
(
   id_order             int not null,
   id_product           int not null,
   num_products			int not null,
   price				float not null,
   primary key (id_order, id_product)
);

/*==============================================================*/
/* Table: orders                                                */
/*==============================================================*/
create table orders
(
   id_order             int not null auto_increment,
   id_user              int not null,
   created				datetime not null default current_timestamp,
   status            	bool,
   finished            	bool,
   processed            bool,
   primary key (id_order),
   foreign key (id_user) references users (id_user)
);

/*==============================================================*/
/* Table: products                                              */
/*==============================================================*/
create table products
(
   id_product           int not null auto_increment,
   name                 varchar(255) not null,
   price                float not null,
   active               bool,
   rating               float,
   num_ratings          int,
   primary key (id_product)
);



/*==============================================================*/
/* Table: users                                                 */
/*==============================================================*/
create table users
(
   id_user              int not null auto_increment,
   email                varchar(100) not null,
   password             varchar(100) not null,
   first_name           varchar(100) not null,
   last_name            varchar(100) not null,
   type					varchar(10) not null,
   primary key (id_user)
);

/*==============================================================*/
/* Table: administrators                                        */
/*==============================================================*/
create table administrators
(
   id_user              int not null,
   primary key (id_user),
   foreign key (id_user) references users (id_user)
);

/*==============================================================*/
/* Table: sellers                                               */
/*==============================================================*/
create table sellers
(
   id_user              int not null,
   active		boolean,
   primary key (id_user),
   foreign key (id_user) references users (id_user)
);

/*==============================================================*/
/* Table: customers                                             */
/*==============================================================*/
create table customers
(
   id_user              int not null,
   address              varchar(100) not null,
   street               varchar(10) not null,
   id_postal			varchar(10) not null,
   phone                varchar(20) not null,
   active				boolean,
   primary key (id_user),
   foreign key (id_user) references users (id_user),
   foreign key (id_postal) references postals (id_postal)
);

/*==============================================================*/
/* Table: postals                                           */
/*==============================================================*/
create table postals
(
   id_postal              varchar(10) not null,
   city              	  varchar(100) not null,
   primary key (id_postal)
);


/*==============================================================*/
/* Table: validations                                           */
/*==============================================================*/
create table validations
(
   id_user              int not null,
   validation_code      varchar(100) not null,
   validated            bool,
   primary key (validation_code),
   foreign key (id_user) references users (id_user)
);


