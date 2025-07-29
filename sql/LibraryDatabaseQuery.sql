Create DATABASE	LibraryDatabase;

Use LibraryDatabase;

Create table LibrarianAccount (
	username varchar(30) Primary Key UNIQUE NOT NULL,
	password varchar(30)
    );
    
Create table Inventory (
	Total_Books int,
    Available_Books int,
    Checked_Out_Books int
);
    
CREATE TABLE Books (
    id varchar(10) PRIMARY KEY,
    title varchar(255) NOT NULL,
    author varchar(255) NOT NULL,
    isbn varchar(20) NOT NULL,
    status enum('available', 'checked-out', 'overdue') DEFAULT 'available',
    location varchar(20) NOT NULL
);

Create table UserAccount (
	User_ID int auto_increment Primary Key UNIQUE NOT NULL,
    Renter_Username varchar(30),
    Renter_Password varchar(30)
);

Create table Rental (
	Rental_ID int,
    Checked_Out_Date date,
    Due_Date date,
    Return_Date date
);

Insert Into LibrarianAccount (username, password) Values ('Anthony', '12345');
    
INSERT INTO Books (id, title, author, isbn, status, location) VALUES 
('BK001', 'Harry Potter and the Philosophers Stone', 'J.K. Rowling', '978-0747532699', 'available', 'A-12-3'),
('BK002', '1984', 'George Orwell', '978-0452284234', 'checked-out', 'B-05-7'),
('BK003', 'To Kill a Mockingbird', 'Harper Lee', '978-0061120084', 'available', 'C-08-2'),
('BK004', 'Pride and Prejudice', 'Jane Austen', '978-0141439518', 'overdue', 'A-15-1'),
('BK005', 'The Great Gatsby', 'F. Scott Fitzgerald', '978-0743273565', 'available', 'D-03-9');