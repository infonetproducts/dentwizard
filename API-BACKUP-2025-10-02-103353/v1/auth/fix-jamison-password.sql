-- SQL to set Jamison's password
UPDATE Users 
SET Password = 'dentwizard' 
WHERE Email = 'jkrugger@infonetproducts.com';

-- Verify the update
SELECT ID, FirstName, LastName, Email, Password 
FROM Users 
WHERE Email = 'jkrugger@infonetproducts.com';