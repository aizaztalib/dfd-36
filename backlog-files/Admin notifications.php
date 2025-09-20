<?php
session_start();
$conn = new mysqli("localhost", "root", "", "slmsdb");
$conn->set_charset('utf8mb4');

if (!isset($_SESSION['admin_id'])) {
    header("Location: adminlogin.php");
    exit();
}

// Fetch all notifications with student + leave details
$sql = "
  SELECT 
    n.id, n.application_id, n.status, n.created_at, n.updated_at,
    s.StudentId AS RollNo, s.FirstName, s.LastName, s.EmailId,
    l.LeaveType, l.FromDate, l.ToDate
  FROM notifications n
  JOIN tblleaves l   ON l.id = n.application_id
  JOIN tblstudents s ON s.id = n.user_id
  ORDER BY n.created_at DESC
";
$result = $conn->query($sql);

// Helper to map numeric status
function statusText($code) {
  return match((int)$code) {
    0 => 'Pending',
    1 => 'Approved',
    2 => 'Rejected',
    default => 'Unknown'
  };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin • Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f8f9fa}
    .main{max-width:1100px;margin:24px auto}
  </style>
</head>
<body>

<!-- If you have a sidebar, include the correct path -->
<?php /* include("sidebar.php"); or include("includes/sidebar.php"); */ ?>

<div class="main">
  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="mb-3">📢 Admin Notifications</h4>

      <?php if ($result && $result->num_rows): ?>
        <div class="table-responsive">
          <table class="table table-bordered align-middle">
            <thead class="table-dark">
              <tr>
                <th>#</th>
                <th>Student</th>
                <th>Roll No</th>
                <th>Leave Type</th>
                <th>From</th>
                <th>To</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
            <?php $i=1; while($row=$result->fetch_assoc()): 
              $badge = ['warning','success','danger'][ (int)$row['status'] ] ?? 'secondary';
            ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['FirstName'].' '.$row['LastName']) ?></td>
                <td><?= htmlspecialchars($row['RollNo']) ?></td>
                <td><?= htmlspecialchars($row['LeaveType']) ?></td>
                <td><?= htmlspecialchars($row['FromDate']) ?></td>
                <td><?= htmlspecialchars($row['ToDate']) ?></td>
                <td><span class="badge bg-<?= $badge ?>"><?= statusText($row['status']) ?></span></td>
                <td><?= htmlspecialchars($row['created_at']) ?></td>
                <td>
                  <form method="post" action="notification_update.php" class="d-flex gap-1">
                    <input type="hidden" name="application_id" value="<?= (int)$row['application_id'] ?>">
                    <button name="approve" class="btn btn-sm btn-success">Approve</button>
                    <button name="reject"  class="btn btn-sm btn-danger">Reject</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <p class="mb-0">No notifications yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
