<?php
// Database connection configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "exp9_db";

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Create table if not exists
$tableSql = "CREATE TABLE IF NOT EXISTS registrations (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    country VARCHAR(50),
    age INT,
    phone VARCHAR(20),
    address TEXT,
    gender VARCHAR(10),
    terms VARCHAR(10),
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($tableSql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

$message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // empty check for dob
    $dob = !empty($_POST['dob']) ? $conn->real_escape_string($_POST['dob']) : NULL;
    
    $country = $conn->real_escape_string($_POST['country']);
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : NULL;
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $gender = isset($_POST['Gender']) ? $conn->real_escape_string($_POST['Gender']) : '';
    $terms = isset($_POST['agree']) ? 'Yes' : 'No';

    $stmt = $conn->prepare("INSERT INTO registrations (name, email, password, dob, country, age, phone, address, gender, terms) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Binding parameters
    $stmt->bind_param("sssssissss", $name, $email, $pass, $dob, $country, $age, $phone, $address, $gender, $terms);
    
    if ($stmt->execute()) {
        $message = "<div class='success-message' style='color: green; background-color: #e6ffe6; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #b3ffb3;text-align:center;'>Registration successful! Data stored in database.</div>";
    } else {
        $message = "<div class='error-message' style='color: red; background-color: #ffe6e6; padding: 15px; border-radius: 5px; margin-bottom: 20px; border: 1px solid #ffb3b3;text-align:center;'>Error: " . $stmt->error . "</div>";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration Form to DB</title>
  <link rel="stylesheet" href="style.css">
  <style>
      .app-container {
          max-width: 800px;
          margin: 40px auto;
      }
      .table-section {
          margin-top: 40px;
          width: 100%;
      }
  </style>
</head>
<body>
  <div class="app-container">
    
    <!-- Form Section -->
    <div class="form-section" style="width: 100%;">
      <div class="form-header">
        <h2>Registration form</h2>
        <p>Please fill in your details below to save into DB</p>
      </div>
      
      <?php echo $message; ?>

      <form id="registrationForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>"> 
        <div class="form-row">
          <div class="form-group">
            <label>FULL NAME <span>*</span></label>
            <input type="text" name="name" id="name" placeholder="John Smith" required/>
          </div>
          
          <div class="form-group">
            <label>EMAIL <span>*</span></label>
            <input type="email" name="email" id="email" placeholder="john@company.com" required />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>PASSWORD <span>*</span></label>
            <input type="password" name="password" id="password" placeholder="Minimum 8 characters" required />
          </div>
          
          <div class="form-group">
            <label>DATE OF BIRTH</label>
            <input type="date" name="dob" id="dob" />
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group">
            <label>COUNTRY</label>
            <select name="country" id="country">
              <option value="USA">United States</option>
              <option value="Canada">Canada</option>
              <option value="UK">United Kingdom</option>
              <option value="Australia">Australia</option>
              <option value="Germany">Germany</option>
              <option value="France">France</option>
              <option value="India" selected>India</option>
              <option value="Japan">Japan</option>
              <option value="Korea">South Korea</option>
              <option value="Iceland">Iceland</option>
              <option value="Singapore">Singapore</option>
            </select>
          </div>
          
          <div class="form-group">
            <label>AGE</label>
            <input type="number" name="age" id="age" min="1" max="120" placeholder="25">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group full-width">
            <label>PHONE NUMBER</label>
            <input type="tel" name="phone" id="phone" placeholder="+91 98765 43210">
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group full-width">
            <label>ADDRESS</label>
            <textarea name="address" id="address" rows="2" placeholder="Street address, city, postal code"></textarea>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group full-width">
            <label>GENDER</label>
            <div class="radio-group">
              <div class="radio-options">
                <label class="radio-label">
                  <input type="radio" name="Gender" id="female" value="Female"> Female
                </label>
                <label class="radio-label">
                  <input type="radio" name="Gender" id="male" value="Male"> Male
                </label>
              </div>
            </div>
          </div>
        </div>
        
        <div class="form-row">
          <div class="form-group full-width">
            <div class="checkbox-group">
              <label class="checkbox-label">
                <input type="checkbox" name="agree" id="agree" value="Agreed" required/> 
                I agree to the terms and conditions
              </label>
            </div>
          </div>
        </div>
        
        <div class="button-group" style="padding-bottom: 20px;">
          <button type="submit" class="btn btn-primary" id="addEntryBtn">Submit</button>
          <button type="reset" class="btn btn-secondary">Reset form</button>
        </div>
      </form>
    </div>
    </div>
    
    <!-- Table Section -->
    <div class="table-section">
      <div class="table-header">
        <h3>Stored Registrations (Database)</h3>
      </div>
      
      <div class="table-container">
        <table id="dataTable" style="width: 100%; text-align: left; border-collapse: collapse;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Email</th>
              <th>DOB</th>
              <th>Country</th>
              <th>Age</th>
              <th>Phone</th>
              <th>Gender</th>
              <th>Terms</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $sql = "SELECT id, name, email, dob, country, age, phone, gender, terms FROM registrations ORDER BY id DESC";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["dob"] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row["country"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["age"] ?? '') . "</td>";
                    echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["gender"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["terms"]) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr class='empty-row'><td colspan='9'>No entries yet.</td></tr>";
            }
            $conn->close();
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</body>
</html>
