<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}
$user_id = $_SESSION['user_id'] ?? '';
$fname = $_SESSION['fname'] ?? '';
$lname = $_SESSION['lname'] ?? '';
$gender = $_SESSION['gender'] ?? '';
$place = $_SESSION['place'] ?? '';
$user_fname = $fname . ' ' . $lname;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Immunization</title>

</head>
<body>
<!--CREATE TABLE IF NOT EXISTS Child_Health_Records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_name VARCHAR(100) NOT NULL,
    child_gender ENUM('Male','Female') NOT NULL,
    child_birth_date DATE NOT NULL,
    place_of_birth VARCHAR(255),           -- hospital, home, etc.
    mother_name VARCHAR(100) NOT NULL,
    father_name VARCHAR(100),              -- optional but good to have
    address VARCHAR(255) NOT NULL,
    birth_weight DECIMAL(5,2) NULL,         -- kg
    birth_height DECIMAL(5,2) NULL,         -- cm
    birth_attendant VARCHAR(100) NULL,      -- midwife, doctor, etc.
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
); -->
    <h1>Request Immunization</h1>
    <h1>gender: <?php echo $gender; ?></h1>
    <form id="requestform" method="post" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
        <input type="text" name="child_fname" placeholder="Baby First Name" required value="sample ">
        <input type="text" name="child_lname" placeholder="Baby Last Name" required value="sample">
        <label for="child_gender">Baby Gender</label>
        <label for="child_gender">Male</label>
        <input type="radio" name="child_gender" placeholder="Baby Gender" value="Male" required checked>
        <label for="child_gender">Female</label>
        <input type="radio" name="child_gender" placeholder="Baby Gender" value="Female" required>
        <input type="date" name="child_birth_date" placeholder="Baby Birth Date" required value="2025-01-01">
        <input type="text" name="place_of_birth" placeholder="Place of Birth" value="sample">
        <?php if($gender == 'Male'): ?>
            <input type="text" name="father_name" placeholder="Father Name" value="<?php echo $user_fname; ?>">
            <input type="text" name="mother_name" placeholder="Mother Name" required value="sample">
    
        <?php else: ?>
            <input type="text" name="mother_name" value="<?php echo $user_fname; ?>" required>
            <input type="text" name="father_name" placeholder="Father Name" value="sample">
        <?php endif; ?>
        <input type="text" name="child_address" placeholder="Baby Address" value="<?php echo $place; ?>" required>
        <input type="text" name="birth_weight" placeholder="Baby Birth Weight" value="sample">
        <input type="text" name="birth_height" placeholder="Baby Birth Height" value="sample">
        <input type="text" name="birth_attendant" placeholder="Birth Attendant" value="sample">
        <input type="file" name="babys_card" accept=".jpg,.jpeg,.png,.pdf" title="Upload Baby's Card (JPG, PNG, or PDF)">
        <button type="submit">Request</button>
    </form>
</body>
<script>
document.querySelector('#requestform').addEventListener('submit', function(e) {
    e.preventDefault();
    Request_Immunization();
});

async function Request_Immunization() {
    const formData = new FormData(requestform);
    // const doc = await fetch('../../php/users/request_immunization.php', {
    const doc = await fetch('../../php/supabase/users/request_immunization.php', {
        method: 'POST',
        body: formData
    });
    const data = await doc.json();
    console.log(data);
    
    if (data.status === 'success') {
        let message = 'Child health record saved successfully!\n';
        message += 'Baby ID: ' + data.baby_id + '\n';
        
        
        alert(message);
        requestform.reset();
    } else {
        alert('Error: ' + data.message);
    }
}
</script>
</html>