


-- Create Accountant table
CREATE TABLE IF NOT EXISTS accountant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL
);



-- Create Companies table
CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(50) NOT NULL,
    company_id VARCHAR(4) GENERATED ALWAYS AS (
        CONCAT(
            UPPER(SUBSTRING(company_name, 1, 2)),
            POSITION(UPPER(SUBSTRING(company_name, 1, 1)) IN 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') - 1
        )
    ) STORED UNIQUE
);

-- Create Branches table
CREATE TABLE IF NOT EXISTS branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(50) NOT NULL,
    company_id VARCHAR(4) NOT NULL,
    branch_id VARCHAR(4) GENERATED ALWAYS AS (
        CONCAT(
            UPPER(SUBSTRING(branch_name, 1, 2)),
            POSITION(UPPER(SUBSTRING(branch_name, 1, 1)) IN 'ABCDEFGHIJKLMNOPQRSTUVWXYZ') - 1
        )
    ) STORED UNIQUE,
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
);

-- Create Company manager table
CREATE TABLE IF NOT EXISTS company_managers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    manager_name VARCHAR(50) NOT NULL,
    company_id VARCHAR(4) NOT NULL,
    company_name VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    FOREIGN KEY (company_id) REFERENCES companies(company_id)
);


-- Create Branch Admins table
CREATE TABLE IF NOT EXISTS branch_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_name VARCHAR(50) NOT NULL,
    branch_id VARCHAR(4) NOT NULL,
    company_name VARCHAR(50) NOT NULL,
    password VARCHAR(50) NOT NULL,
    INDEX (admin_name),  -- Create index for admin_name column
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id)
);


CREATE TABLE IF NOT EXISTS purchase_report (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_no VARCHAR(20) NOT NULL,
    company_id VARCHAR(4) NOT NULL,
    date DATE NOT NULL,
    supplier_id INT NOT NULL,
    supplier_name VARCHAR(50) NOT NULL,
    mobile_no VARCHAR(15),
    paid_amount DECIMAL(10, 2),
    grand_total DECIMAL(10, 2),
    due_amount DECIMAL(10, 2),
    status ENUM('paid', 'due') NOT NULL,
    INDEX (bill_no)
    
);

-- Create Purchased Product table
CREATE TABLE IF NOT EXISTS purchased_product (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bill_no VARCHAR(20) NOT NULL,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(5, 2) NOT NULL,
    quantity INT NOT NULL
);

CREATE TABLE IF NOT EXISTS stock (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    tax DECIMAL(5, 2) NOT NULL,
    quantity INT NOT NULL
);

INSERT INTO accountant (username, password) VALUES ('accountant', MD5('tally01'));

