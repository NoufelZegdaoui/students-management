<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load the XML file
$xml = simplexml_load_file('data.xml') or die("Error: Cannot create object");

// Check if the form is submitted to add a new student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if it's a form to add a new student
    if (isset($_POST['codeET'], $_POST['nomET'], $_POST['prenomET'], $_POST['age']) &&
        !empty($_POST['codeET']) && !empty($_POST['nomET']) && !empty($_POST['prenomET']) && !empty($_POST['age'])) {

        // Add new 'etudiant' to the XML
        $newEtudiant = $xml->addChild('etudiant');
        $newEtudiant->addChild('codeET', htmlspecialchars($_POST['codeET']));
        $newEtudiant->addChild('nomET', htmlspecialchars($_POST['nomET']));
        $newEtudiant->addChild('prenomET', htmlspecialchars($_POST['prenomET']));
        $newEtudiant->addChild('age', htmlspecialchars($_POST['age']));

        // Save the updated XML file
        if ($xml->asXML('data.xml')) {
            echo "<p>New student added successfully!</p>";
        } else {
            echo "<p>Error saving XML etudiant.</p>";
        }
    }
    // Check if it's a form to modify a student
    elseif (isset($_POST['update_codeET'], $_POST['update_nomET'], $_POST['update_prenomET'], $_POST['update_age'])) {
        // Modify the student
        $codeToUpdate = $_POST['update_codeET'];

        foreach ($xml->etudiant as $etudiant) {
            if ((string)$etudiant->codeET === $codeToUpdate) {
                $etudiant->nomET = htmlspecialchars($_POST['update_nomET']);
                $etudiant->prenomET = htmlspecialchars($_POST['update_prenomET']);
                $etudiant->age = htmlspecialchars($_POST['update_age']);
                break;
            }
        }

        // Save the updated XML file
        if ($xml->asXML('data.xml')) {
            echo "<p>Student updated successfully!</p>";
        } else {
            echo "<p>Error updating XML etudiant.</p>";
        }
    }
    // Check if it's a form to delete a student
    elseif (isset($_POST['delete_codeET'])) {
        // Get the student code to delete
        $codeToDelete = $_POST['delete_codeET'];
    
        // Convert SimpleXML to DOMDocument for deletion
        $dom = dom_import_simplexml($xml)->ownerDocument;
    
        $xpath = new DOMXPath($dom);
        $query = "//etudiant[codeET='$codeToDelete']";
    
        $entries = $xpath->query($query);
    
        if ($entries->length > 0) {
            foreach ($entries as $entry) {
                $entry->parentNode->removeChild($entry);
            }
    
            // Save the updated XML file
            if ($dom->save('data.xml')) {
                echo "<p>Student deleted successfully!</p>";
            } else {
                echo "<p>Error saving XML after deletion.</p>";
            }
        } else {
            echo "<p>No student found with code: " . htmlspecialchars($codeToDelete) . "</p>";
        }
    }
}

// Start HTML output
echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Student List</title>";
echo "<link rel='stylesheet' href='styles.css'>";
echo "</head>";
echo "<body>";

// Start container
echo "<div class='container'>";

echo "<h1>List of Students</h1>";

// Form to add new Etudiant
echo "<h2>Add New Student</h2>";
echo "<form method='POST'>
        <label for='codeET'>Code: </label><input type='text' name='codeET' required><br>
        <label for='nomET'>Name: </label><input type='text' name='nomET' required><br>
        <label for='prenomET'>first Name: </label><input type='text' name='prenomET' required><br>
        <label for='age'>Age: </label><input type='number' name='age' required><br>
        <input type='submit' value='Add Etudiant'>
      </form>";

// Display the Etudiants in a table with Edit and Delete buttons
echo "<table>";
echo "<tr><th>Code</th><th>Name</th><th>First Name</th><th>Age</th><th>Actions</th></tr>";

foreach ($xml->etudiant as $etudiant) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($etudiant->codeET) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant->nomET) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant->prenomET) . "</td>";
    echo "<td>" . htmlspecialchars($etudiant->age) . "</td>";

    // Add Delete and Edit buttons for each student
    echo "<td>
            <form action='' method='POST' style='display:inline;'>
                <input type='hidden' name='delete_codeET' value='" . htmlspecialchars($etudiant->codeET) . "'>
                <input type='submit' value='Delete'>
            </form>
            <form action='' method='POST' style='display:inline;'>
                <input type='hidden' name='update_codeET' value='" . htmlspecialchars($etudiant->codeET) . "'>
                <input type='submit' value='Edit'>
            </form>
          </td>";
    echo "</tr>";
}

echo "</table>";

// If an "Edit" button is clicked, show the form to update the student's information
if (isset($_POST['update_codeET'])) {
    $codeToUpdate = $_POST['update_codeET'];
    $etudiantToEdit = null;

    foreach ($xml->etudiant as $etudiant) {
        if ((string)$etudiant->codeET === $codeToUpdate) {
            $etudiantToEdit = $etudiant;
            break;
        }
    }

    if ($etudiantToEdit) {
        echo "<h2>Edit Etudiant</h2>";
        echo "<form method='POST'>
                <input type='hidden' name='update_codeET' value='" . htmlspecialchars($etudiantToEdit->codeET) . "'>
                <label for='update_nomET'>Name: </label><input type='text' name='update_nomET' value='" . htmlspecialchars($etudiantToEdit->nomET) . "' required><br>
                <label for='update_prenomET'>First Name: </label><input type='text' name='update_prenomET' value='" . htmlspecialchars($etudiantToEdit->prenomET) . "' required><br>
                <label for='update_age'>Age: </label><input type='number' name='update_age' value='" . htmlspecialchars($etudiantToEdit->age) . "' required><br>
                <input type='submit' value='Update Etudiant'>
              </form>";
    }
}

echo "</div>";

echo "</body>";
echo "</html>";
