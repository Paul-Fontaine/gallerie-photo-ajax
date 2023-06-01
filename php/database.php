<?php
    require_once('constants.php');

    //----------------------------------------------------------------------------
    //--- dbConnect --------------------------------------------------------------
    //----------------------------------------------------------------------------
    // Create the connection to the database.
    // \return False on error and the database otherwise.
    function dbConnect()
    {
        try
        {
            $db = new PDO('pgsql:host='.DB_SERVER.';port='.DB_PORT.';dbname='.DB_NAME, DB_USER, DB_PASSWORD);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
        }
        catch (PDOException $exception)
        {
            error_log('Connection error: '.$exception->getMessage());
            return false;
        }
        return $db;
    }

    //----------------------------------------------------------------------------
    //--- dbRequestPhotos --------------------------------------------------------
    //----------------------------------------------------------------------------
    // Get all photos.
    // \param db The connected database.
    // \return The list of small photos.
    function dbRequestPhotos($db)
    {
        try
        {
            $request = 'SELECT id, small AS src FROM photos';
            $statement = $db->prepare($request);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $exception)
        {
            error_log('Request error: '.$exception->getMessage());
            return false;
        }
        return $result;
    }

    //----------------------------------------------------------------------------
    //--- dbRequestPhoto ---------------------------------------------------------
    //----------------------------------------------------------------------------
    // Get a specific photo.
    // \param db The connected database.
    // \param id The id of the photo.
    // \return The photo.
    function dbRequestPhoto($db, $id)
    {
        try
        {
            $request = 'SELECT id, title, large AS src FROM photos WHERE id=:id';
            $statement = $db->prepare($request);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
        }
        catch (PDOException $exception)
        {
            error_log('Request error: '.$exception->getMessage());
            return false;
        }
        return $result;
    }

    //----------------------------------------------------------------------------
    //--- dbRequestComments --------------------------------------------------------
    //----------------------------------------------------------------------------
    // Function to get all comments (if $login='') or the comments of a user
    // (otherwise).
    // \param db The connected database.
    // \param login The login of the user (for specific request).
    // \return The list of comments.
    function dbRequestComments($db, $photoid)
    {
        try
        {
            $request = 'SELECT * FROM comments WHERE photoid=:photoid ORDER BY id';
            $statement = $db->prepare($request);
            $statement->bindParam(':photoid', $photoid, PDO::PARAM_STR, 20);
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (PDOException $exception)
        {
            error_log('Request error: '.$exception->getMessage());
            return false;
        }
        return $result;
    }

    //----------------------------------------------------------------------------
    //--- dbAddComment ------------------------------------------------------------
    //----------------------------------------------------------------------------
    // Add a comment.
    // \param db The connected database.
    // \param login The login of the user.
    // \param text The comment to add.
    // \return True on success, false otherwise.
    function dbAddComment($db, $userlogin, $photoid, $text)
    {
        try
        {
            $request = 'INSERT INTO comments(userlogin, photoid, comment) VALUES(:login, :photoid, :text)';
            $statement = $db->prepare($request);
            $statement->bindParam(':login', $userlogin, PDO::PARAM_STR, 20);
            $statement->bindParam(':photoid', $photoid, PDO::PARAM_STR, 20);
            $statement->bindParam(':text', $text, PDO::PARAM_STR, 256);
            $statement->execute();
        }
        catch (PDOException $exception)
        {
            error_log('Request error: '.$exception->getMessage());
            return false;
        }
        return true;
    }

    //----------------------------------------------------------------------------
    //--- dbModifyComment ----------------------------------------------------------
    //----------------------------------------------------------------------------
    // Function to modify a comment.
    // \param db The connected database.
    // \param id The id of the comment to update.
    // \param login The login of the user.
    // \param text The new comment.
    // \return True on success, false otherwise.
    function dbModifyComment($db, $id, $userlogin, $text)
    {
        try
        {
            $request = 'UPDATE comments SET comment=:text WHERE id=:id AND userlogin=:userlogin';
            $statement = $db->prepare($request);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':userlogin', $userlogin, PDO::PARAM_STR, 20);
            $statement->bindParam(':text', $text, PDO::PARAM_STR, 256);
            $statement->execute();
        }
        catch (PDOException $exception)
        {
            error_log('Request error: '.$exception->getMessage());
            return false;
        }
        return true;
    }

    //----------------------------------------------------------------------------
    //--- dbDeleteComment ----------------------------------------------------------
    //----------------------------------------------------------------------------
    // Delete a comment.
    // \param db The connected database.
    // \param id The id of the comment.
    // \param login The login of the user.
    // \return True on success, false otherwise.
    function dbDeleteComment($db, $id, $userlogin)
    {
        try
        {
            $request = 'DELETE FROM comments WHERE id=:id AND userlogin=:userlogin';
            $statement = $db->prepare($request);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':userlogin', $userlogin, PDO::PARAM_STR, 20);
            $statement->execute();
        }
        catch (PDOException $exception)
        {
            error_log('Request error: '.$exception->getMessage());
            return false;
        }
        return true;
    }
?>