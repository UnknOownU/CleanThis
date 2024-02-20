#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------


#------------------------------------------------------------
# Table: User
#------------------------------------------------------------

CREATE TABLE User(
        id_user       Int  Auto_increment  NOT NULL ,
        name          Varchar (50) NOT NULL ,
        firstname     Varchar (50) NOT NULL ,
        email         Varchar (50) NOT NULL ,
        zipcode       Varchar (10) NOT NULL ,
        city          Varchar (50) NOT NULL ,
        street        Varchar (100) NOT NULL ,
        phone         Varchar (25) NOT NULL ,
        is_confirmed  Bool NOT NULL ,
        role          Int NOT NULL ,
        hash_password Varchar (250) NOT NULL
	,CONSTRAINT User_PK PRIMARY KEY (id_user)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Operation
#------------------------------------------------------------

CREATE TABLE Operation(
        id_ope            Int  Auto_increment  NOT NULL ,
        type              Int NOT NULL ,
        name              Varchar (100) NOT NULL ,
        price             Int NOT NULL ,
        description       Varchar (250) NOT NULL ,
        status            Int NOT NULL ,
        created_at        Datetime NOT NULL ,
        rdv_at            Datetime NOT NULL ,
        zipcode_ope       Varchar (10) NOT NULL ,
        city_ope          Varchar (50) NOT NULL ,
        street_ope        Varchar (100) NOT NULL ,
        customer          Varchar (100) NOT NULL COMMENT "Récupérer l'id_user du client qui achete la prestation"  ,
        salarie           Varchar (100) NOT NULL COMMENT "Récupérer l'id_user du salarie qui s'occupe de la prestation"  ,
        id_user           Int NOT NULL ,
        id_user_Accomplir Int NOT NULL
	,CONSTRAINT Operation_PK PRIMARY KEY (id_ope)

	,CONSTRAINT Operation_User_FK FOREIGN KEY (id_user) REFERENCES User(id_user)
	,CONSTRAINT Operation_User0_FK FOREIGN KEY (id_user_Accomplir) REFERENCES User(id_user)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Documents
#------------------------------------------------------------

CREATE TABLE Documents(
        id_doc   Int  Auto_increment  NOT NULL ,
        type     Int NOT NULL ,
        customer Varchar (50) NOT NULL ,
        url      Varchar (250) NOT NULL ,
        id_ope   Int NOT NULL
	,CONSTRAINT Documents_PK PRIMARY KEY (id_doc)

	,CONSTRAINT Documents_Operation_FK FOREIGN KEY (id_ope) REFERENCES Operation(id_ope)
)ENGINE=InnoDB;

