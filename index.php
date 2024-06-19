<?php
session_start();
$servername = "localhost";
$username = "id22295133_admin";
$password = "Admin@123";  // Replace with the actual database password
$dbname = "id22295133_db_2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to log user details
function log_user_details($details) {
    $logFile = 'log_user.txt';
    $currentLogs = file_exists($logFile) ? file_get_contents($logFile) : '';
    $currentLogs .= $details . "\n";
    file_put_contents($logFile, $currentLogs);
}

// Get IP details using ipinfo
function get_ip_details($token) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = "https://ipinfo.io/{$ip}/json?token={$token}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

if (!isset($_COOKIE['visited'])) {
    $ipDetails = get_ip_details('901de406103d6b');
    log_user_details($ipDetails);
    setcookie('visited', 'true', time() + (86400 * 30), "/"); // 30 days
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['password'])) {
        // Handle password submission
        $password = $_POST['password'];

        if ($password === "98211") {
            $_SESSION['authenticated'] = true;
            header('Location: index.php');
            exit();
        } else {
            $error = "Wrong password.";
        }
    } elseif (isset($_POST['individual_name']) && isset($_POST['company_name'])) {
        // Handle access request submission
        $individual_name = $_POST['individual_name'];
        $company_name = $_POST['company_name'];

        $sql = "INSERT INTO access_requests (individual_name, company_name) VALUES ('$individual_name', '$company_name')";
        if ($conn->query($sql) === TRUE) {
            $message = "Request submitted successfully.";
        } else {
            $error = "Error: " . $sql . "<br>" . $conn->error;
        }
    } elseif (isset($_POST['request_ip_access'])) {
        // Handle IP access request submission
        $ipDetails = get_ip_details('901de406103d6b');
        $ip = json_decode($ipDetails, true)['ip'];

        $sql = "INSERT INTO ip_access_requests (ip_address) VALUES ('$ip')";
        if ($conn->query($sql) === TRUE) {
            $ip_message = "IP access request submitted successfully.";
        } else {
            $ip_error = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>GitHub-like UI</title>
    <script>
        function showMyIP() {
            fetch('https://ipinfo.io/json?token=901de406103d6b')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('ip-details').innerText = JSON.stringify(data, null, 2);
                    document.getElementById('ip-details-container').style.display = 'block';
                });
        }
    </script>
</head>
<body>
    <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']): ?>
        <div class="container">
            <h2>My Portfolio</h2>
            <p>Welcome to my portfolio!</p>
            <!-- Add your portfolio content here -->
        </div>
    <?php else: ?>
        <div class="container">
            <h2>Enter your password</h2>
            <form method="post">
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Access</button>
            </form>
            <?php
            if (isset($error)) {
                echo '<p style="color: red;">' . $error . '</p>';
            }
            ?>
        </div>
        <button class="request-access" onclick="document.getElementById('request-access-form').style.display='block'">Request Access</button>
        <div id="request-access-form" style="display:none;">
            <div class="container">
                <h2>Request Access</h2>
                <button1 onclick="showMyIP()">Show My IP</button1>
                <form method="post">
                    <input type="text" name="individual_name" placeholder="Individual/ Organisation Name" required>
                    <input type="email" name="company_name" placeholder="Email" required>
                    <button type="submit">Request Access</button>
                </form>
                
                <div id="ip-details-container" style="display:none;">
                    <pre id="ip-details"></pre>
                    <form method="post">
                        <input type="hidden" name="request_ip_access" value="1">
                        <label for="entity-name">Individual/Company Name:</label>
        <input type="text" id="entity-name" name="entity_name" required>
                        <button type="submit">Request to Grant Access to this IP Address</button>
                    </form>
                </div>
                <?php
                if (isset($message)) {
                    echo '<p style="color: green;">' . $message . '</p>';
                }
                if (isset($ip_message)) {
                    echo '<p style="color: green;">' . $ip_message . '</p>';
                }
                if (isset($ip_error)) {
                    echo '<p style="color: red;">' . $ip_error . '</p>';
                }
                ?>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
