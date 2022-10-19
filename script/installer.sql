create table if not exists Country
(
    id           int auto_increment
    primary key,
    country_name varchar(100) not null
    );

create table if not exists Address
(
    id            int auto_increment
    primary key,
    street_suffix varchar(50)  not null,
    street_name   varchar(100) not null,
    street_number int          not null,
    postal_code   varchar(50)  null,
    region        varchar(100) null,
    city          varchar(100) not null,
    country_id    int          not null,
    constraint fk_address_country
    foreign key (country_id) references Country (id)
    );

create table if not exists Customer
(
    id         int auto_increment
    primary key,
    first_name varchar(100) not null,
    last_name  varchar(100) not null,
    email      varchar(100) not null,
    password   varchar(64)  not null,
    birthday   date         not null,
    telephone  varchar(12)  not null,
    address_id int          not null,
    constraint fk_customer_address
    foreign key (address_id) references Address (id)
    );
