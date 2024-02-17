#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------


#------------------------------------------------------------
# Table: <<Users>>
#------------------------------------------------------------

CREATE TABLE __Users__(
        idUser       Int  Auto_increment  NOT NULL ,
        name         Varchar (50) NOT NULL ,
        firstname    Varchar (50) NOT NULL ,
        email        Varchar (50) NOT NULL ,
        zipCode      Varchar (50) NOT NULL ,
        city         Varchar (50) NOT NULL ,
        street       Varchar (50) NOT NULL ,
        phone        Varchar (50) NOT NULL ,
        isConfirmed  Bool NOT NULL ,
        gender       Varchar (50) NOT NULL ,
        role         Int NOT NULL ,
        hashPassword Varchar (250) NOT NULL
	,CONSTRAINT __Users___PK PRIMARY KEY (idUser)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Customer
#------------------------------------------------------------

CREATE TABLE Customer(
        idUser       Int NOT NULL ,
        name         Varchar (50) NOT NULL ,
        firstname    Varchar (50) NOT NULL ,
        email        Varchar (50) NOT NULL ,
        zipCode      Varchar (50) NOT NULL ,
        city         Varchar (50) NOT NULL ,
        street       Varchar (50) NOT NULL ,
        phone        Varchar (50) NOT NULL ,
        isConfirmed  Bool NOT NULL ,
        gender       Varchar (50) NOT NULL ,
        role         Int NOT NULL ,
        hashPassword Varchar (250) NOT NULL
	,CONSTRAINT Customer_PK PRIMARY KEY (idUser)

	,CONSTRAINT Customer___Users___FK FOREIGN KEY (idUser) REFERENCES __Users__(idUser)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Employe
#------------------------------------------------------------

CREATE TABLE Employe(
        idUser       Int NOT NULL ,
        countOpe     Int NOT NULL ,
        name         Varchar (50) NOT NULL ,
        firstname    Varchar (50) NOT NULL ,
        email        Varchar (50) NOT NULL ,
        zipCode      Varchar (50) NOT NULL ,
        city         Varchar (50) NOT NULL ,
        street       Varchar (50) NOT NULL ,
        phone        Varchar (50) NOT NULL ,
        isConfirmed  Bool NOT NULL ,
        gender       Varchar (50) NOT NULL ,
        role         Int NOT NULL ,
        hashPassword Varchar (250) NOT NULL
	,CONSTRAINT Employe_PK PRIMARY KEY (idUser)

	,CONSTRAINT Employe___Users___FK FOREIGN KEY (idUser) REFERENCES __Users__(idUser)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Operation
#------------------------------------------------------------

CREATE TABLE Operation(
        idOpe          Int  Auto_increment  NOT NULL ,
        type           Int NOT NULL ,
        name           Varchar (50) NOT NULL ,
        description    Varchar (250) NOT NULL ,
        forfait        DECIMAL (15,3)  NOT NULL ,
        status         Int NOT NULL ,
        createdAt      Datetime NOT NULL ,
        dateRdv        Datetime NOT NULL ,
        finishedAt     Datetime NOT NULL ,
        zipCode_Ope    Varchar (50) NOT NULL ,
        city_Ope       Varchar (50) NOT NULL ,
        street_Ope     Varchar (50) NOT NULL ,
        Customer       Varchar (50) NOT NULL ,
        Employe        Varchar (50) NOT NULL ,
        idUser         Int NOT NULL ,
        idUser_Employe Int NOT NULL
	,CONSTRAINT Operation_AK UNIQUE (Customer,Employe)
	,CONSTRAINT Operation_PK PRIMARY KEY (idOpe)

	,CONSTRAINT Operation_Customer_FK FOREIGN KEY (idUser) REFERENCES Customer(idUser)
	,CONSTRAINT Operation_Employe0_FK FOREIGN KEY (idUser_Employe) REFERENCES Employe(idUser)
)ENGINE=InnoDB;

