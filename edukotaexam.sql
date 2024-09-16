
CREATE TABLE quegrp (
    gid INT AUTO_INCREMENT PRIMARY KEY,
    Title VARCHAR(255) NOT NULL
);

-- Step 4: Create the `onlineexam` table
CREATE TABLE onlineexam (
    eid INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    class VARCHAR(50),
    insid INT,
    estatus VARCHAR(50),
    duration INT,
    tmarks INT,
    date DATE
);

-- Step 5: Create the `question` table and connect it to `quegrp` and `onlineexam`
CREATE TABLE question (
    qid INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    gid INT,
    eid INT, -- Added foreign key column to reference the `onlineexam` table
    toption TEXT,
    mark INT,
    upload VARCHAR(255),
    FOREIGN KEY (gid) REFERENCES quegrp(gid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (eid) REFERENCES onlineexam(eid) ON DELETE CASCADE ON UPDATE CASCADE -- Foreign key linking to `onlineexam`
);

-- Step 6: Create the `qoption` table
CREATE TABLE qoption (
    oid INT AUTO_INCREMENT PRIMARY KEY,
    qid INT,
    name VARCHAR(255),
    img VARCHAR(255),
    FOREIGN KEY (qid) REFERENCES question(qid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Step 7: Create the `queans` table
CREATE TABLE queans (
    ansid INT AUTO_INCREMENT PRIMARY KEY,
    qid INT,
    oid INT,
    FOREIGN KEY (qid) REFERENCES question(qid) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (oid) REFERENCES qoption(oid) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Step 8: Create the `users` table for user registration
CREATE TABLE users (
    uid INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(50),
    name VARCHAR(255) NOT NULL,
    parents_number VARCHAR(15),
    contact_number VARCHAR(15),
    address TEXT,
    class VARCHAR(50),
    school VARCHAR(255),
    date_of_exam DATE
);