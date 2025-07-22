Create DATABASE	LibraryDatabase;

Use LibraryDatabase;

Create table LibrarianAccount (
	username varchar(30) Primary Key UNIQUE NOT NULL,
	password varchar(30)
    );

Insert Into LibrarianAccount (username, password) Values ('Anthony', '12345');
    