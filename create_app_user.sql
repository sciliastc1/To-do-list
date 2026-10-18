-- Database user creation script for the Task Flow application
CREATE USER 'taskflow'@'localhost' IDENTIFIED BY '~#######~';
GRANT SELECT, INSERT, UPDATE, DELETE ON to_do_list.* TO 'taskflow'@'localhost';
FLUSH PRIVILEGES;
