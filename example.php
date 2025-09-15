<?php
// example.php - CHR + immunization scheduler + transfer case (no date in checklist)

$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "ebakunado_db";
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("DB Failed: " . $conn->connect_error);

// Create tables if not exist
$conn->query("CREATE TABLE IF NOT EXISTS Child_Health_Records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_name VARCHAR(100),
    child_gender VARCHAR(20),
    child_birth_date DATE,
    child_address VARCHAR(255),
    mother_name VARCHAR(100),
    father_name VARCHAR(100),
    birth_weight DECIMAL(5,2) NULL,
    birth_height DECIMAL(5,2) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;");

$conn->query("CREATE TABLE IF NOT EXISTS Immunization_Records (
    id INT AUTO_INCREMENT PRIMARY KEY,
    child_id INT NOT NULL,
    vaccine_name VARCHAR(100),
    dose_no VARCHAR(50),
    due_date DATE,
    date_given DATE NULL,
    weight DECIMAL(6,2) NULL,
    height DECIMAL(6,2) NULL,
    temperature DECIMAL(4,1) NULL,
    status VARCHAR(20) DEFAULT 'Pending',
    FOREIGN KEY (child_id) REFERENCES Child_Health_Records(id) ON DELETE CASCADE
) ENGINE=InnoDB;");

// Vaccine List
$vaccines = [
    ["BCG", "at birth"],
    ["Hepatitis B", "at birth"],
    ["Pentavalent (DPT-HepB-Hib) - 1st", "6 weeks"],
    ["OPV - 1st", "6 weeks"],
    ["PCV - 1st", "6 weeks"],
    ["Pentavalent (DPT-HepB-Hib) - 2nd", "10 weeks"],
    ["OPV - 2nd", "10 weeks"],
    ["PCV - 2nd", "10 weeks"],
    ["Pentavalent (DPT-HepB-Hib) - 3rd", "14 weeks"],
    ["OPV - 3rd", "14 weeks"],
    ["PCV - 3rd", "14 weeks"],
    ["IPV", "14 weeks"],
    ["MMR / Measles - 1st", "9 months"],
    ["MMR / Measles - 2nd", "12 months"]
];

// Schedule calculator
function calculate_due_date($birthdate, $sched) {
    $d = new DateTime($birthdate);
    $s = strtolower($sched);
    if (strpos($s,"birth")!==false) return $d->format("Y-m-d");
    if (preg_match('/(\d+)\s*weeks?/',$s,$m)) {$d->modify("+{$m[1]} week"); return $d->format("Y-m-d");}
    if (preg_match('/(\d+)\s*months?/',$s,$m)) {$d->modify("+{$m[1]} month"); return $d->format("Y-m-d");}
    return $d->format("Y-m-d");
}

$msgs = [];

// Register Child
if (isset($_POST['register_child'])) {
    $name = $_POST['child_name']; $gender = $_POST['child_gender']; $dob = $_POST['child_birth_date'];
    $addr = $_POST['child_address']; $mother=$_POST['mother_name']; $father=$_POST['father_name'];
    $bw = $_POST['birth_weight'] ?: null; $bh = $_POST['birth_height'] ?: null;

    if ($name && $dob) {
        $stmt=$conn->prepare("INSERT INTO Child_Health_Records (child_name,child_gender,child_birth_date,child_address,mother_name,father_name,birth_weight,birth_height) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssssssdd",$name,$gender,$dob,$addr,$mother,$father,$bw,$bh);
        if($stmt->execute()){
            $cid=$stmt->insert_id;
            // insert vaccines
            $q=$conn->prepare("INSERT INTO Immunization_Records (child_id,vaccine_name,dose_no,due_date,date_given,status) VALUES (?,?,?,?,?,?)");
            foreach($GLOBALS['vaccines'] as $i=>$v){
                $due=calculate_due_date($dob,$v[1]);
                $status="Pending"; $dateGiven=NULL;
                if(isset($_POST['given'][$i])){ // checked by parent/BHW
                    $status="Completed"; $dateGiven=$due;
                }
                $q->bind_param("isssss",$cid,$v[0],$v[1],$due,$dateGiven,$status);
                $q->execute();
            }
            $msgs[]="<span style='color:green'>Child registered with vaccine schedule and transfer history.</span>";
        }
    }
}

// BHW update
if (isset($_POST['update_vaccine'])) {
    $id=intval($_POST['record_id']); $dg=$_POST['date_given'];
    $wt=$_POST['weight'] ?: null; $ht=$_POST['height'] ?: null; $temp=$_POST['temperature'] ?: null;
    $status=$dg ? "Completed":"Pending";
    $sql="UPDATE Immunization_Records SET date_given=?,weight=?,height=?,temperature=?,status=? WHERE id=?";
    $q=$conn->prepare($sql);
    $q->bind_param("sdddsi",$dg,$wt,$ht,$temp,$status,$id);
    if($q->execute()) $msgs[]="<span style='color:blue'>Record updated.</span>";
}

// fetch
$children=$conn->query("SELECT * FROM Child_Health_Records ORDER BY child_name");
$imms=$conn->query("SELECT ir.*,c.child_name FROM Immunization_Records ir JOIN Child_Health_Records c ON c.id=ir.child_id ORDER BY c.child_name,ir.due_date");

?>
<!doctype html><html><head>
<title>CHR Example</title>
<style>body{font-family:Arial;margin:20px}form{border:1px solid #ccc;padding:10px;margin-bottom:15px;border-radius:6px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #aaa;padding:5px;text-align:center}</style>
</head><body>
<h1>Child Health Record Example</h1>
<?php foreach($msgs as $m) echo "<p>$m</p>"; ?>

<h2>Register Child (Parent)</h2>
<form method="post">
<label>Name*</label><input name="child_name" required>
<label>Gender</label><select name="child_gender"><option>Male</option><option>Female</option></select>
<label>Birth Date*</label><input type="date" name="child_birth_date" required>
<label>Address</label><input name="child_address">
<label>Mother</label><input name="mother_name"><label>Father</label><input name="father_name">
<label>Birth Weight (kg)</label><input type="number" step="0.01" name="birth_weight">
<label>Birth Height (cm)</label><input type="number" step="0.1" name="birth_height">

<h3>Vaccine History (check if already given)</h3>
<?php foreach($vaccines as $i=>$v): ?>
<label><input type="checkbox" name="given[<?=$i?>]"> <?=$v[0]?> (<?=$v[1]?>)</label><br>
<?php endforeach; ?>

<button name="register_child">Register Child</button>
</form>

<h2>BHW Update</h2>
<form method="post">
<select name="record_id" required>
<option value="">--choose record--</option>
<?php $r=$conn->query("SELECT ir.id,c.child_name,ir.vaccine_name,ir.status FROM Immunization_Records ir JOIN Child_Health_Records c ON c.id=ir.child_id");
while($row=$r->fetch_assoc()){echo "<option value='{$row['id']}'>{$row['child_name']} - {$row['vaccine_name']} [{$row['status']}]</option>";} ?>
</select>
<label>Date Given</label><input type="date" name="date_given">
<label>Weight (kg)</label><input type="number" step="0.01" name="weight">
<label>Height (cm)</label><input type="number" step="0.1" name="height">
<label>Temp (°C)</label><input type="number" step="0.1" name="temperature">
<button name="update_vaccine">Save Update</button>
</form>

<h2>Children</h2>
<table><tr><th>ID</th><th>Name</th><th>Birth</th></tr>
<?php while($c=$children->fetch_assoc()){echo "<tr><td>{$c['id']}</td><td>{$c['child_name']}</td><td>{$c['child_birth_date']}</td></tr>";} ?>
</table>

<h2>Immunization Records</h2>
<table><tr><th>ID</th><th>Child</th><th>Vaccine</th><th>Due</th><th>Date Given</th><th>Status</th></tr>
<?php while($im=$imms->fetch_assoc()){echo "<tr><td>{$im['id']}</td><td>{$im['child_name']}</td><td>{$im['vaccine_name']}</td><td>{$im['due_date']}</td><td>{$im['date_given']}</td><td>{$im['status']}</td></tr>";} ?>
</table>
</body></html>
